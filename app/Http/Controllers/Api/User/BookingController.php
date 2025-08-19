<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Services\InvoiceService;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
   public $invoiceService;
    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }
    use GeneralTrait;
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
        $bookings = $user->bookings()->with('room')->paginate($perPage);
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
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        DB::beginTransaction();
        try{
        $room = Room::find($request->room_id);

        // Remove this general availability check as we will do a detailed date range check
        // if (!$room->is_available) {
        //     return $this->returnError('400', 'Room is not available');
        // }

        $newCheckIn = Carbon::parse($request->check_in_date);
        $newCheckOut = Carbon::parse($request->check_out_date);

        $overlappingBooking = Booking::where('room_id', $request->room_id)
            ->where(function ($query) use ($newCheckIn, $newCheckOut) {
                $query->whereBetween('check_in_date', [$newCheckIn, $newCheckOut->subDay()])
                    ->orWhereBetween('check_out_date', [$newCheckIn->addDay(), $newCheckOut])
                    ->orWhere(function ($query) use ($newCheckIn, $newCheckOut) {
                        $query->where('check_in_date', '<=', $newCheckIn)
                            ->where('check_out_date', '>=', $newCheckOut);
                    });
            })
            ->first();

        if ($overlappingBooking) {
            $existingCheckIn = Carbon::parse($overlappingBooking->check_in_date);
            $existingCheckOut = Carbon::parse($overlappingBooking->check_out_date);

            $message = "Room is unavailable for the requested period. It is booked from {$existingCheckIn->format('Y-m-d')} to {$existingCheckOut->format('Y-m-d')}.";

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

        $totalPrice = $room->price * (strtotime($request->check_out_date) - strtotime($request->check_in_date)) / (60 * 60 * 24);

        $booking = Booking::create([
            'user_id' => $user->id,
            'room_id' => $request->room_id,
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'total_price' => $totalPrice,
        ]);

        if (Carbon::parse($request->check_in_date)->isToday()) {
            $room->update(['is_available' => false]);
        }
        $days = Carbon::parse($request->check_in_date)->diffInDays($request->check_out_date);
        $this->invoiceService->addItemOrCreateInvoice(
            $user->id,
            'room',
            $room->id,
            "Room from {$request->check_in_date} to {$request->check_out_date}",
            $days,
            $room->price
        );
        DB::commit();
        return response()->json($booking, 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request,string $id)
    {
        $user = $request->user();
        $booking = $user->bookings()->with('room')->find($id);

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
        $booking = $user->bookings()->find($id);
        if (!$booking) {
            return $this->returnError('400', 'Booking not found');
        }
        $room = $booking->room;
        $room->update(['is_available' => true]);
        $booking->delete();
        return $this->returnSuccess('Booking deleted successfully',200);

    }
}
