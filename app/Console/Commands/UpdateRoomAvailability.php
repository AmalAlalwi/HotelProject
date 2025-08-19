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
    protected $description = 'Updates room availability based on booking check-in dates.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $bookings = Booking::where('check_in_date', '<=', $today)
                            ->whereHas('room', function ($query) {
                                $query->where('is_available', true);
                            })
                            ->get();

        foreach ($bookings as $booking) {
            $room = $booking->room;
            if ($room) {
                $room->update(['is_available' => false]);
                $this->info("Room {$room->id} marked as unavailable.");
            }
        }

        $this->info('Room availability updated successfully.');
    }
}
