<?php
namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingService;
use App\Models\Room;
use App\Models\Service;
use App\Services\InvoiceService;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\InvoiceItem;

class BookingServiceController extends Controller
{
    use GeneralTrait;
    public $invoiceService;
    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }
    public function index(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'perPage'=>'required|integer|min:1',
        ]);
        if ($valid->fails()) {
            return response(['errors'=>$valid->errors()],422);
        }
        $user = $request->user();
        $perPage = $request->query('perPage', 10);
        $bookings = $user->BookingsService()->with('service')->paginate($perPage);
        if($bookings->isEmpty()){
            return $this->returnError('400', 'No bookings found');
        }

        return $this->returnData('bookings', $bookings,"The bookings retrieved successfully");

    }
    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $service = Service::find($request->service_id);
        if(!$service){
            return $this->returnError('400', 'Service is not found');
        }
        // Remove this general availability check as we will do a detailed date range check
        // if (!$service->is_available) {
        //     return $this->returnError('400', 'Service is not available');
        // }

        $newCheckIn = Carbon::parse($request->check_in_date);
        $newCheckOut = Carbon::parse($request->check_out_date);

        $overlappingBooking = BookingService::where('service_id', $request->service_id)
            ->where(function ($query) use ($newCheckIn, $newCheckOut) {
                $query->whereBetween('check_in_date', [$newCheckIn, $newCheckOut->subDay()])
                    ->orWhereBetween('check_out_date', [$newCheckIn->addDay(), $newCheckOut])
                    ->orWhere(function ($query) use ($newCheckIn, $newCheckOut) {
                        $query->where('check_in_date', '<=', $newCheckIn)
                            ->where('check_out_date', '>=', $newCheckOut);
                    });
            })
            // Only consider overlapping bookings that are paid or partially paid
            ->whereHas('invoice', function ($query) {
                $query->whereIn('payment_status', ['paid', 'partial']);
            })
            ->first();

        if ($overlappingBooking) {
            $existingCheckIn = Carbon::parse($overlappingBooking->check_in_date);
            $existingCheckOut = Carbon::parse($overlappingBooking->check_out_date);

            $message = "Service is unavailable for the requested period. It is booked from {$existingCheckIn->format('Y-m-d')} to {$existingCheckOut->format('Y-m-d')}.";

            // Determine if there's any available portion before the booked period
            if ($newCheckIn->lt($existingCheckIn)) {
                $availableUntil = $existingCheckIn->subDay();
                if ($newCheckIn->lte($availableUntil)) {
                    $message .= " You can book it from {$newCheckIn->format('Y-m-d')} to {$availableUntil->format('Y-m-d')}.";
                }
            }
            // Determine if there's any available portion after the booked period
            if ($newCheckOut->gt($existingCheckOut)) {
                $availableFrom = $existingCheckOut->addDay();
                if ($newCheckOut->gte($availableFrom)) {
                    $message .= " You can book it from {$availableFrom->format('Y-m-d')} to {$newCheckOut->format('Y-m-d')}.";
                }
            }
            return $this->returnError('400', $message);
        }

        $totalPrice = $service->price * (strtotime($request->check_out_date) - strtotime($request->check_in_date)) / (60 * 60 * 24);

        $booking = BookingService::create([
            'user_id' => $user->id,
            'service_id' => $request->service_id,
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'total_price' => $totalPrice,
        ]);
        
        $days = Carbon::parse($request->check_in_date)->diffInDays($request->check_out_date);
        $this->invoiceService->addItemOrCreateInvoice(
            $user->id,
            'service',
            $service->id,
            "Service from {$request->check_in_date} to {$request->check_out_date}",
            $days,
            $service->price
        );
        return response()->json([
            'message' => 'Service reserved successfully. Please make a payment (partial or total) to confirm your reservation. The service will remain available until payment is received.',
            'booking' => $booking
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request,string $id)
    {
        $user = $request->user();
        $booking = $user->BookingsService()->with('service')->find($id);

        if (!$booking) {
            return $this->returnError('400', 'Booking not found');
        }

        return response()->json($booking);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,string $id)
    {
        $user = $request->user();
        $booking = $user->BookingsService()->find($id);
        if (!$booking) {
            return $this->returnError('400', 'Booking not found');
        }

        $service = $booking->service;

        // Find the associated invoice
        $invoiceItem = \App\Models\InvoiceItem::where('item_type', 'service')
                                            ->where('item_id', $service->id)
                                            ->first();
        
        if ($invoiceItem) {
            $invoice = $invoiceItem->invoice;
            if ($invoice) {
                // Always make service available upon booking deletion, regardless of payment status.
                // Further logic might be needed here based on cancellation/refund policies for paid bookings.
                $service->update(['is_available' => true]);

                // Delete invoice items and invoice if it's no longer needed
                $invoiceItem->delete();

                // Check if there are other items in the invoice
                if ($invoice->items()->count() === 0) {
                    $invoice->delete();
                } else {
                    // Recalculate total price if other items exist
                    $invoice->total_price = $invoice->items()->sum('total_price');
                    $invoice->save();
                }
            }
        } else {
            // If no invoice found, assume it's an old booking or an issue, still make service available.
            $service->update(['is_available' => true]);
        }

        $booking->delete();
        return $this->returnSuccess('Booking deleted successfully',200);

    }
}
