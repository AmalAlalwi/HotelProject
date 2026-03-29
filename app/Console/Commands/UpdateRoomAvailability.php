<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Illuminate\Support\Carbon;

class UpdateRoomAvailability extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rooms:update-availability';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates room availability based on booking check-in and check-out dates.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();


    // Mark rooms as available if check-out date has passed (regardless of payment)
$bookingsToFree = Booking::where('check_out_date', '<=', $today)
->whereHas('room', function ($query) {
    $query->where('is_available', false); // Only consider rooms currently marked as unavailable
})
->get();

foreach ($bookingsToFree as $booking) {
$room = $booking->room;
if ($room) {
    $room->update(['is_available' => true]);
    $this->info("Room {$room->id} marked as available after check-out.");
}
}


        // Mark rooms as available if check-out date has passed and associated invoice is paid
        $bookingsToFree = Booking::where('check_out_date', '<=', $today)
                                ->whereHas('room', function ($query) {
                                    $query->where('is_available', false); // Only consider rooms currently marked as unavailable
                                })
                                ->whereHas('invoice', function ($query) {
                                    $query->where('payment_status', 'paid');
                                })
                                ->get();

        foreach ($bookingsToFree as $booking) {
            $room = $booking->room;
            if ($room) {
                $room->update(['is_available' => true]);
                $this->info("Room {$room->id} marked as available after check-out.");
            }
        }

        // Mark rooms as available if associated invoice is unpaid and check-in date is overdue
        $overdueUnpaidBookings = Booking::where('check_in_date', '<=', $today)
                                        ->whereHas('room', function ($query) {
                                            $query->where('is_available', false); // Only consider rooms currently marked as unavailable
                                        })
                                        ->whereDoesntHave('invoice', function ($query) {
                                            $query->whereIn('payment_status', ['paid', 'partial']);
                                        })
                                        ->get();

        foreach ($overdueUnpaidBookings as $booking) {
            $room = $booking->room;
            if ($room) {
                $room->update(['is_available' => true]);
                $this->info("Room {$room->id} marked as available due to overdue unpaid booking.");
            }
        }

        $this->info('Room availability updated successfully.');
    }
}
