<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
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
        $company = $this->company();

        // ── Period resolution ──────────────────────────────────────
        $period = $request->input('period', 'month');

        [$dateFrom, $dateTo] = match ($period) {
            'today'  => [today()->toDateString(), today()->toDateString()],
            'week'   => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'year'   => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            'custom' => [
                $request->input('date_from', now()->startOfMonth()->toDateString()),
                $request->input('date_to',   now()->toDateString()),
            ],
            default  => [now()->startOfMonth()->toDateString(), now()->toDateString()], // month
        };

        $dtFrom = $dateFrom . ' 00:00:00';
        $dtTo   = $dateTo   . ' 23:59:59';

        // ── Sales KPIs ─────────────────────────────────────────────
        $salesQuery = fn () => $company->sales()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dtFrom, $dtTo]);

        $totalSales  = (float) $salesQuery()->sum('total');
        $totalOrders = $salesQuery()->count();
        $avgBasket   = $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0;

        // ── Expenses KPIs ──────────────────────────────────────────
        $totalExpenses = (float) $company->expenses()
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->sum('amount');

        $netProfit = $totalSales - $totalExpenses;

        // ── Sales by payment method ────────────────────────────────
        $salesByPayment = DB::table('payments')
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->where('sales.company_id', $company->id)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$dtFrom, $dtTo])
            ->select('payments.method', DB::raw('COUNT(*) as count'), DB::raw('SUM(payments.amount) as total'))
            ->groupBy('payments.method')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'method' => $r->method,
                'label'  => match ($r->method) {
                    'cash' => 'Cash', 'wave' => 'Wave', 'om' => 'Orange Money',
                    'free' => 'Free Money', 'card' => 'Carte', default => ucfirst($r->method)
                },
                'count'  => $r->count,
                'total'  => (float) $r->total,
            ]);

        // ── Daily trend chart ─────────────────────────────────────
        $dailySales = $company->sales()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dtFrom, $dtTo])
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('SUM(total) as total'))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $dailyExpenses = $company->expenses()
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->select('date', DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        // Build continuous date range for chart
        $chartLabels   = [];
        $chartSales    = [];
        $chartExpenses = [];

        $current = \Carbon\Carbon::parse($dateFrom);
        $end     = \Carbon\Carbon::parse($dateTo);
        $diffDays = $current->diffInDays($end);

        // If range > 60 days, group by week; > 365 by month
        if ($diffDays > 365) {
            // Monthly grouping
            $monthlySales    = $company->sales()->where('status', 'completed')
                ->whereBetween('created_at', [$dtFrom, $dtTo])
                ->select(DB::raw("TO_CHAR(created_at, 'YYYY-MM') as period"), DB::raw('SUM(total) as total'))
                ->groupBy('period')->orderBy('period')->pluck('total', 'period')->toArray();
            $monthlyExpenses = $company->expenses()->whereBetween('date', [$dateFrom, $dateTo])
                ->select(DB::raw("TO_CHAR(date, 'YYYY-MM') as period"), DB::raw('SUM(amount) as total'))
                ->groupBy('period')->orderBy('period')->pluck('total', 'period')->toArray();

            $cur = \Carbon\Carbon::parse($dateFrom)->startOfMonth();
            while ($cur->lte($end)) {
                $key = $cur->format('Y-m');
                $chartLabels[]   = $cur->locale('fr')->isoFormat('MMM YY');
                $chartSales[]    = (float) ($monthlySales[$key]    ?? 0);
                $chartExpenses[] = (float) ($monthlyExpenses[$key] ?? 0);
                $cur->addMonth();
            }
        } elseif ($diffDays > 60) {
            // Weekly grouping
            $cur = \Carbon\Carbon::parse($dateFrom)->startOfWeek();
            while ($cur->lte($end)) {
                $weekEnd = $cur->copy()->endOfWeek();
                $wLabel  = $cur->locale('fr')->isoFormat('D MMM');
                $chartLabels[]   = $wLabel;
                $chartSales[]    = (float) $company->sales()->where('status', 'completed')
                    ->whereBetween('created_at', [$cur->toDateTimeString(), $weekEnd->toDateTimeString()])
                    ->sum('total');
                $chartExpenses[] = (float) $company->expenses()
                    ->whereBetween('date', [$cur->toDateString(), $weekEnd->toDateString()])
                    ->sum('amount');
                $cur->addWeek();
            }
        } else {
            // Daily
            while ($current->lte($end)) {
                $day = $current->toDateString();
                $chartLabels[]   = $current->locale('fr')->isoFormat('D MMM');
                $chartSales[]    = (float) ($dailySales[$day]    ?? 0);
                $chartExpenses[] = (float) ($dailyExpenses[$day] ?? 0);
                $current->addDay();
            }
        }

        // ── Top products ──────────────────────────────────────────
        $topProducts = DB::table('sale_items')
            ->join('sales',    'sale_items.sale_id',    '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.company_id', $company->id)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$dtFrom, $dtTo])
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as qty'), DB::raw('SUM(sale_items.total) as revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // ── Expenses by category ──────────────────────────────────
        $expensesByCategory = $company->expenses()
            ->with('expenseCategory')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->select('expense_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_category_id')
            ->get()
            ->map(fn ($r) => [
                'category' => $r->expenseCategory?->name ?? 'Non catégorisé',
                'total'    => (float) $r->total,
            ])
            ->sortByDesc('total')
            ->values();

        // ── Customer debts (top 10) ─────────────────────────────
        $customerDebts = $company->customers()
            ->where('outstanding_balance', '>', 0)
            ->orderByDesc('outstanding_balance')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'outstanding_balance']);

        return inertia('App/Reports/Overview', [
            'kpis' => [
                'total_sales'    => $totalSales,
                'total_orders'   => $totalOrders,
                'avg_basket'     => $avgBasket,
                'total_expenses' => $totalExpenses,
                'net_profit'     => $netProfit,
            ],
            'salesByPayment'     => $salesByPayment,
            'topProducts'        => $topProducts,
            'expensesByCategory' => $expensesByCategory,
            'customerDebts'      => $customerDebts,
            'chartLabels'        => $chartLabels,
            'chartSales'         => $chartSales,
            'chartExpenses'      => $chartExpenses,
            'filters' => [
                'period'    => $period,
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ],
        ]);
    }
}
