<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $now = now();
            $bookings = \App\Models\Booking::where('check_out_date', '<=', $now)->get();

            foreach ($bookings as $booking) {

                $room = $booking->room;
                $room->is_available = 1;
                $room->save();
             //   $booking->delete(); // احذف الحجز إذا كنت لا تحتاجه
            }
        })->everyMinute();
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
