<?php

namespace App\Http\Controllers\App\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $company  = $this->company();
        $dateFrom = $request->input('date_from', today()->toDateString());
        $dateTo   = $request->input('date_to', today()->toDateString());

        $baseQuery = fn () => $company->orders()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        // Total sales
        $totalSales = $baseQuery()->sum('total');
        $orderCount = $baseQuery()->count();

        // Sales by service
        $salesByService = $baseQuery()
            ->select('service_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('service_id')
            ->with('service')
            ->get()
            ->map(fn ($row) => [
                'service' => $row->service?->name ?? 'Sans service',
                'count'   => $row->count,
                'total'   => $row->total,
            ]);

        // Sales by type
        $salesByType = $baseQuery()
            ->select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('type')
            ->get();

        // Sales by payment method
        $salesByPayment = $baseQuery()
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')
            ->get();

        // Top dishes
        $topDishes = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('dishes', 'dishes.id', '=', 'order_items.dish_id')
            ->where('orders.company_id', $company->id)
            ->where('orders.status', '!=', 'cancelled')
            ->whereBetween('orders.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->select('dishes.name', DB::raw('SUM(order_items.quantity) as qty'), DB::raw('SUM(order_items.total) as revenue'))
            ->groupBy('dishes.id', 'dishes.name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        // Cancelled orders
        $cancelledCount = $company->orders()
            ->where('status', 'cancelled')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->count();

        // Expenses for the period (reuse generic expenses)
        $totalExpenses = $company->expenses()
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->sum('amount');

        $netProfit = $totalSales - $totalExpenses;

        // ── Daily chart data ──────────────────────────────────────
        $dailyRaw = $company->orders()
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('SUM(total) as total'))
            ->groupBy('day')->orderBy('day')
            ->pluck('total', 'day')->toArray();

        $chartLabels = [];
        $chartValues = [];
        $cur = \Carbon\Carbon::parse($dateFrom);
        $end = \Carbon\Carbon::parse($dateTo);
        while ($cur->lte($end)) {
            $day = $cur->toDateString();
            $chartLabels[] = $cur->locale('fr')->isoFormat('D MMM');
            $chartValues[] = (float) ($dailyRaw[$day] ?? 0);
            $cur->addDay();
        }

        return inertia('App/Restaurant/Reports/Index', [
            'stats' => [
                'total_sales'    => $totalSales,
                'order_count'    => $orderCount,
                'cancelled'      => $cancelledCount,
                'total_expenses' => $totalExpenses,
                'net_profit'     => $netProfit,
            ],
            'salesByService' => $salesByService,
            'salesByType'    => $salesByType,
            'salesByPayment' => $salesByPayment,
            'topDishes'      => $topDishes,
            'chartLabels'    => $chartLabels,
            'chartValues'    => $chartValues,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ],
        ]);
    }
}
