<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Room;
use App\Models\Service;
use App\Models\User;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\In;

class StatisticsController extends Controller
{
    use GeneralTrait;
    public function index()
    {
        // Bookings
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        $allBookings = Booking::count();
        $dailyBookings = Booking::whereDate('created_at', $today)->count();
        $weeklyBookings = Booking::whereBetween('created_at', [$startOfWeek, now()])->count();
        $monthlyBookings = Booking::whereBetween('created_at', [$startOfMonth, now()])->count();
        $bookingsByRoomType = Booking::select('rooms.type', DB::raw('COUNT(*) as count'))
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->groupBy('rooms.type')
            ->pluck('count', 'rooms.type');

        // Users
        $totalUsers = User::count();

        // Rooms
        $totalRooms=Room::count();
        $availableRooms = Room::where('is_available', true)->count();
        $unavailableRooms = Room::where('is_available', false)->count();

        // Services
        $totalServices = Service::count();
        $availableServices = Service::where('is_available', true)->count();
        $unavailableServices = Service::where('is_available', false)->count();
        // Invoices
        $totalInvoices=Invoice::count();
        $paidInvoices = Invoice::where('payment_status', 'paid')->count();
        $unpaidInvoices = Invoice::where('payment_status', 'unpaid')->count();
        $partialInvoices = Invoice::where('payment_status', 'partial')->count();
        return response()->json([
            'bookings' => [
                'total' => $allBookings,
                'daily' => $dailyBookings,
                'weekly' => $weeklyBookings,
                'monthly' => $monthlyBookings,
                'bookingsByRoomType' => $bookingsByRoomType,
            ],
            'invoices'=>[
                'total' => $totalInvoices,
                'paid' => $paidInvoices,
                'unpaid' => $unpaidInvoices,
                'partial' => $partialInvoices,
            ],
            'users' => [
                'total' => $totalUsers,
            ],
            'rooms' => [
                'total' => $totalRooms,
                'available' => $availableRooms,
                'unavailable' => $unavailableRooms,
            ],
            'services' => [
                'total' => $totalServices,
                'available' => $availableServices,
                'unavailable' => $unavailableServices,
            ],
        ]);
    }
    public function getAllInvoicesWithItems(Request $request){
        $total=Invoice::with('items','user')->paginate($request->per_page??10);
        return $this->returnData('invoices', $total);
    }
    public function getPaidInvoicesWithItems($perPage = 10){
        $total=Invoice::with('items','user')->where('payment_status','paid')->paginate($perPage);
        return $this->returnData('invoices', $total);
    }
    public function getUnpaidInvoicesWithItems($perPage = 10){
        $total=Invoice::with('items','user')->where('payment_status','unpaid')->paginate($perPage);
        return $this->returnData('invoices', $total);
    }
    public function getPartialInvoicesWithItems($perPage = 10){
        $total=Invoice::with('items','user')->where('payment_status','partial')->paginate($perPage);
        return $this->returnData('invoices', $total);
    }
    public function revenueStatus()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        //total
        $total=Invoice::where('payment_status', 'paid')->sum('total_price');
        //daily Revenue
        $dailyRevenue = Invoice::where('payment_status', 'paid')
            ->whereDate('created_at', $today)
            ->sum('total_price');

        //weekly Revenue
        $weeklyRevenue = Invoice::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startOfWeek, now()])
            ->sum('total_price');

        //monthly revenue
        $monthlyRevenue = Invoice::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startOfMonth, now()])
            ->sum('total_price');

        return response()->json([
            'total' => $total,
            'daily_revenue' => $dailyRevenue,
            'weekly_revenue' => $weeklyRevenue,
            'monthly_revenue' => $monthlyRevenue,
        ]);
    }
}
