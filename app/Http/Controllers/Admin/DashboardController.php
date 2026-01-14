<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $canViewAll = $user->can('view global analytics');
        // $canViewAll = true;

        $fromDate = $request->input('from_date') 
            ? Carbon::parse($request->input('from_date'))->startOfDay()
            : Carbon::now()->subDays(7)->startOfDay();

        $toDate = $request->input('to_date') 
            ? Carbon::parse($request->input('to_date'))->endOfDay()
            : Carbon::now()->endOfDay();

        // Base query builder (filtered by permission)
        $bookingQuery = Booking::whereBetween('created_at', [$fromDate, $toDate]);

        if (!$canViewAll) {
            $bookingQuery->where('agent_id', $user->id);
        }

        $bookings = clone $bookingQuery;

        // Payments linked to filtered bookings
        $bookingIds = (clone $bookings)->pluck('id');
        $paymentQuery = Payment::whereIn('booking_id', $bookingIds);

        // Metrics
        $totalOrders = (clone $bookings)->count();
        $totalOrdersWOError = (clone $bookings)
            ->where('status', '!=', Booking::STATUS_ERROR)
            ->count();
        $issuedOrders = (clone $bookings)
            ->where('status', Booking::STATUS_ISSUED)
            ->count();
        $initialOrders = (clone $bookings)
            ->where('status', Booking::STATUS_INITIAL)
            ->count();
        $cancelOrders = (clone $bookings)
            ->where('status', Booking::STATUS_CANCEL)
            ->count();

        $totalRevenue = (clone $paymentQuery)->sum('base_price') ?? 0;
        $averageTicket = $issuedOrders > 0
            ? round($totalRevenue / $issuedOrders, 2)
            : 0;

        // Chart data
        $statusData = (clone $bookings)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $bookingsByAirline = (clone $bookings)
            ->select('airline', DB::raw('COUNT(*) as total'))
            ->groupBy('airline')
            ->orderByDesc('total')
            ->get();

        $revenueTrend = (clone $paymentQuery)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(base_price) as total'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        $recentOrders = (clone $bookings)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalOrdersWOError',
            'totalOrders',
            'issuedOrders',
            'initialOrders',
            'cancelOrders',
            'totalRevenue',
            'averageTicket',
            'statusData',
            'bookingsByAirline',
            'revenueTrend',
            'recentOrders',
            'fromDate',
            'toDate',
            'canViewAll'
        ));
    }

}