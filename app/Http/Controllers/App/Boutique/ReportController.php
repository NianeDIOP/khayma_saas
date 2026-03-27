<?php

namespace App\Http\Controllers\App\Boutique;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockItem;
use App\Models\StockMovement;
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
        $company   = $this->company();
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->toDateString());

        // Sales KPIs
        $salesQuery = $company->sales()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);

        $totalSales    = $salesQuery->sum('total');
        $totalOrders   = $salesQuery->count();
        $avgBasket     = $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0;

        // Net profit (selling_price - purchase_price)
        $profit = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.company_id', $company->id)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->selectRaw('COALESCE(SUM(sale_items.total - (products.purchase_price * sale_items.quantity)), 0) as net_profit')
            ->value('net_profit');

        // Sales by payment method
        $salesByPayment = DB::table('payments')
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->where('sales.company_id', $company->id)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->select('payments.method', DB::raw('SUM(payments.amount) as total'))
            ->groupBy('payments.method')
            ->get();

        // Top products
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.company_id', $company->id)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as qty'), DB::raw('SUM(sale_items.total) as revenue'))
            ->groupBy('products.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // Sales by category
        $salesByCategory = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('sales.company_id', $company->id)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->select('categories.name', DB::raw('SUM(sale_items.total) as revenue'))
            ->groupBy('categories.name')
            ->orderByDesc('revenue')
            ->get();

        // Stock alerts
        $stockAlerts = $company->products()
            ->where('is_active', true)
            ->where('min_stock_alert', '>', 0)
            ->get()
            ->filter(function ($product) use ($company) {
                $total = StockItem::where('product_id', $product->id)
                    ->whereIn('depot_id', $company->depots()->pluck('id'))
                    ->sum('quantity');
                $product->total_stock = $total;
                return $total <= $product->min_stock_alert;
            })
            ->values();

        // Customer debts
        $customerDebts = $company->customers()
            ->where('outstanding_balance', '>', 0)
            ->orderByDesc('outstanding_balance')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'outstanding_balance']);

        // Loyalty stats
        $loyaltyEarned  = LoyaltyTransaction::forCompany($company->id)
            ->where('type', 'earn')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->sum('points');
        $loyaltyRedeemed = LoyaltyTransaction::forCompany($company->id)
            ->where('type', 'redeem')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->sum('points');

        // Stock by depot
        $stockByDepot = $company->depots()->get()->map(function ($depot) {
            $totalValue = DB::table('stock_items')
                ->join('products', 'stock_items.product_id', '=', 'products.id')
                ->where('stock_items.depot_id', $depot->id)
                ->where('stock_items.quantity', '>', 0)
                ->selectRaw('COALESCE(SUM(stock_items.quantity * products.selling_price), 0) as value')
                ->value('value');

            $totalItems = StockItem::where('depot_id', $depot->id)->where('quantity', '>', 0)->count();

            return [
                'name'       => $depot->name,
                'items'      => $totalItems,
                'value'      => $totalValue,
            ];
        });

        return inertia('App/Boutique/Reports/Index', [
            'totalSales'      => $totalSales,
            'totalOrders'     => $totalOrders,
            'avgBasket'       => $avgBasket,
            'netProfit'       => $profit,
            'salesByPayment'  => $salesByPayment,
            'topProducts'     => $topProducts,
            'salesByCategory' => $salesByCategory,
            'stockAlerts'     => $stockAlerts,
            'customerDebts'   => $customerDebts,
            'loyaltyEarned'   => $loyaltyEarned,
            'loyaltyRedeemed' => $loyaltyRedeemed,
            'stockByDepot'    => $stockByDepot,
            'filters'         => [
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ],
        ]);
    }
}
