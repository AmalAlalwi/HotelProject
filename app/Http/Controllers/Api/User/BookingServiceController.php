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

    /**
     * Store a newly created resource in storage.
     */
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
        if (!$service->is_available) {
            return $this->returnError('400', 'Service is not available');
        }

        $totalPrice = $service->price * (strtotime($request->check_out_date) - strtotime($request->check_in_date)) / (60 * 60 * 24);

        $booking = BookingService::create([
            'user_id' => $user->id,
            'service_id' => $request->service_id,
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'total_price' => $totalPrice,
        ]);

        $service->update(['is_available' => false]);
        $days = Carbon::parse($request->check_in_date)->diffInDays($request->check_out_date);
        $this->invoiceService->addItemOrCreateInvoice(
            $user->id,
            'service',
            $service->id,
            "Service from {$request->check_in_date} to {$request->check_out_date}",
            $days,
            $service->price
        );
        return response()->json($booking, 201);
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
        $service->update(['is_available' => true]);
        $booking->delete();
        return $this->returnSuccess('Booking deleted successfully',200);

    }
}
