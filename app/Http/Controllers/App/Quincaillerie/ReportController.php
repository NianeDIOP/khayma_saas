<?php

namespace App\Http\Controllers\App\Quincaillerie;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\SupplierPayment;
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
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->input('date_to', now()->toDateString());

        // -- Sales summary --
        $salesQuery = Sale::forCompany($company->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        $totalSales    = (clone $salesQuery)->sum('total');
        $salesCount    = (clone $salesQuery)->count();
        $avgSale       = $salesCount > 0 ? $totalSales / $salesCount : 0;

        // -- Client debts --
        $clientDebts = Sale::forCompany($company->id)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->sum('total');

        $clientPaid = DB::table('payments')
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->where('sales.company_id', $company->id)
            ->whereIn('sales.payment_status', ['unpaid', 'partial'])
            ->sum('payments.amount');

        $clientDebtRemaining = $clientDebts - $clientPaid;

        // -- Supplier debts --
        $supplierDebts = $company->suppliers()->sum('outstanding_balance');

        // -- Pending quotes --
        $pendingQuotes = $company->quotes()
            ->whereIn('status', ['draft', 'sent'])
            ->count();

        $pendingQuotesTotal = $company->quotes()
            ->whereIn('status', ['draft', 'sent'])
            ->sum('total');

        // -- Top products --
        $topProducts = SaleItem::select('sale_items.product_id',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.total) as total_revenue'))
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.company_id', $company->id)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->with('product:id,name')
            ->get();

        // -- Sales by type --
        $salesByType = Sale::forCompany($company->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('type')
            ->get();

        // -- Sales by payment method --
        $salesByPayment = DB::table('payments')
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->where('sales.company_id', $company->id)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->select('payments.method', DB::raw('COUNT(*) as count'), DB::raw('SUM(payments.amount) as total'))
            ->groupBy('payments.method')
            ->get();

        // -- Stock movements summary --
        $stockMovements = StockMovement::forCompany($company->id)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(ABS(quantity)) as total_qty'))
            ->groupBy('type')
            ->get();

        return inertia('App/Quincaillerie/Reports/Index', [
            'stats' => [
                'total_sales'          => $totalSales,
                'sales_count'          => $salesCount,
                'avg_sale'             => round($avgSale, 0),
                'client_debt_remaining' => $clientDebtRemaining,
                'supplier_debts'       => $supplierDebts,
                'pending_quotes'       => $pendingQuotes,
                'pending_quotes_total' => $pendingQuotesTotal,
            ],
            'topProducts'     => $topProducts,
            'salesByType'     => $salesByType,
            'salesByPayment'  => $salesByPayment,
            'stockMovements'  => $stockMovements,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ],
        ]);
    }
}
