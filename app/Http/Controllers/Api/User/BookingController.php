<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $room = Room::find($request->room_id);

        if (!$room->is_available) {
            return $this->returnError('400', 'Room is not available');
        }

        $totalPrice = $room->price * (strtotime($request->check_out_date) - strtotime($request->check_in_date)) / (60 * 60 * 24);

        $booking = Booking::create([
            'user_id' => $user->id,
            'room_id' => $request->room_id,
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'total_price' => $totalPrice,
        ]);

        $room->update(['is_available' => false]);

        return response()->json($booking, 201);
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
