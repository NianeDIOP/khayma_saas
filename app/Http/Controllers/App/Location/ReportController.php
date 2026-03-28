<?php

namespace App\Http\Controllers\App\Location;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\RentalAsset;
use App\Models\RentalContract;
use App\Models\RentalPayment;
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
        $endDateFull = $endDate . ' 23:59:59';

        // Asset stats
        $totalAssets     = $company->rentalAssets()->where('is_active', true)->count();
        $availableAssets = $company->rentalAssets()->where('is_active', true)->where('status', 'available')->count();
        $rentedAssets    = $company->rentalAssets()->where('is_active', true)->where('status', 'rented')->count();
        $maintenanceAssets = $company->rentalAssets()->where('is_active', true)->where('status', 'maintenance')->count();

        // Assets by type
        $assetsByType = $company->rentalAssets()
            ->where('is_active', true)
            ->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get();

        // Revenue (payments received in period)
        $totalRevenue = RentalPayment::forCompany($company->id)
            ->whereIn('status', ['paid', 'partial'])
            ->whereBetween('payment_date', [$startDate, $endDateFull])
            ->sum('amount_paid');

        // Expected vs received
        $expectedPayments = RentalPayment::forCompany($company->id)
            ->whereBetween('due_date', [$startDate, $endDateFull])
            ->sum('amount');

        $receivedPayments = RentalPayment::forCompany($company->id)
            ->whereBetween('due_date', [$startDate, $endDateFull])
            ->sum('amount_paid');

        // Revenue by asset type
        $revenueByType = DB::table('rental_payments')
            ->join('rental_contracts', 'rental_payments.rental_contract_id', '=', 'rental_contracts.id')
            ->join('rental_assets', 'rental_contracts.rental_asset_id', '=', 'rental_assets.id')
            ->where('rental_payments.company_id', $company->id)
            ->whereIn('rental_payments.status', ['paid', 'partial'])
            ->whereBetween('rental_payments.payment_date', [$startDate, $endDateFull])
            ->select('rental_assets.type', DB::raw('SUM(rental_payments.amount_paid) as revenue'))
            ->groupBy('rental_assets.type')
            ->get();

        // Contract stats
        $activeContracts    = RentalContract::forCompany($company->id)->where('status', 'active')->count();
        $completedContracts = RentalContract::forCompany($company->id)->where('status', 'completed')->count();
        $overdueContracts   = RentalContract::forCompany($company->id)->where('status', 'overdue')->count();
        $renewedContracts   = RentalContract::forCompany($company->id)->where('status', 'renewed')->count();

        // Outstanding debts
        $totalDebts = RentalPayment::forCompany($company->id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->selectRaw('COALESCE(SUM(amount - amount_paid), 0) as debt')
            ->value('debt');

        // Top debtors
        $topDebtors = DB::table('rental_payments')
            ->join('rental_contracts', 'rental_payments.rental_contract_id', '=', 'rental_contracts.id')
            ->join('customers', 'rental_contracts.customer_id', '=', 'customers.id')
            ->where('rental_payments.company_id', $company->id)
            ->whereIn('rental_payments.status', ['pending', 'partial', 'overdue'])
            ->select('customers.name', DB::raw('SUM(rental_payments.amount - rental_payments.amount_paid) as debt'))
            ->groupBy('customers.name')
            ->orderByDesc('debt')
            ->limit(10)
            ->get();

        // Occupancy rate (rented / total active assets)
        $occupancyRate = $totalAssets > 0 ? round(($rentedAssets / $totalAssets) * 100, 1) : 0;

        // ── Monthly revenue chart ─────────────────────────────────
        $driver     = DB::getDriverName();
        $monthExpr  = $driver === 'pgsql'
            ? DB::raw("TO_CHAR(payment_date, 'YYYY-MM') as month")
            : DB::raw("strftime('%Y-%m', payment_date) as month");

        $monthlyRaw = RentalPayment::forCompany($company->id)
            ->whereIn('status', ['paid', 'partial'])
            ->whereBetween('payment_date', [$startDate, $endDate . ' 23:59:59'])
            ->select($monthExpr, DB::raw('SUM(amount_paid) as total'))
            ->groupBy('month')->orderBy('month')
            ->pluck('total', 'month')->toArray();

        $chartLabels = [];
        $chartValues = [];
        $cur = \Carbon\Carbon::parse($startDate)->startOfMonth();
        $endM = \Carbon\Carbon::parse($endDate);
        while ($cur->lte($endM)) {
            $key = $cur->format('Y-m');
            $chartLabels[] = $cur->locale('fr')->isoFormat('MMM YY');
            $chartValues[] = (float) ($monthlyRaw[$key] ?? 0);
            $cur->addMonth();
        }

        return inertia('App/Location/Reports/Index', [
            'totalAssets'        => $totalAssets,
            'availableAssets'    => $availableAssets,
            'rentedAssets'       => $rentedAssets,
            'maintenanceAssets'  => $maintenanceAssets,
            'assetsByType'       => $assetsByType,
            'totalRevenue'       => $totalRevenue,
            'expectedPayments'   => $expectedPayments,
            'receivedPayments'   => $receivedPayments,
            'revenueByType'      => $revenueByType,
            'activeContracts'    => $activeContracts,
            'completedContracts' => $completedContracts,
            'overdueContracts'   => $overdueContracts,
            'renewedContracts'   => $renewedContracts,
            'totalDebts'         => $totalDebts,
            'topDebtors'         => $topDebtors,
            'occupancyRate'      => $occupancyRate,
            'chartLabels'        => $chartLabels,
            'chartValues'        => $chartValues,
            'filters'            => [
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ],
        ]);
    }
}
