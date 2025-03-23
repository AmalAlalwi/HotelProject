<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
class UpdateRoomAvailability extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rooms:is_available';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update room availability based on booking dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        // الغرف التي انتهت فترة حجزها
        $bookings = Booking::where('check_out_date', '<', $today)->get();

        foreach ($bookings as $booking) {
            $room = $booking->room;
            $room->update(['is_available' => true]);
        }

        $this->info('Room availability updated successfully!');
    }
}
