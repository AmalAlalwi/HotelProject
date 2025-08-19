<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BookingService;
use Illuminate\Support\Carbon;

class UpdateServiceAvailability extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:update-availability';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates service availability based on booking check-in dates.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $bookings = BookingService::where('check_in_date', '<=', $today)
                                ->whereHas('service', function ($query) {
                                    $query->where('is_available', true);
                                })
                                ->get();

        foreach ($bookings as $booking) {
            $service = $booking->service;
            if ($service) {
                $service->update(['is_available' => false]);
                $this->info("Service {$service->id} marked as unavailable.");
            }
        }

        $this->info('Service availability updated successfully.');
    }
}

