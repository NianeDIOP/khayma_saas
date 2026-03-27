<?php

namespace App\Http\Controllers\App\Location;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\RentalAsset;
use App\Models\RentalContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CalendarController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $company = $this->company();

        $assets = $company->rentalAssets()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'status']);

        // Get contracts for calendar view (current & upcoming)
        $contracts = RentalContract::forCompany($company->id)
            ->whereIn('status', ['active', 'renewed'])
            ->with('rentalAsset:id,name,type', 'customer:id,name')
            ->get(['id', 'rental_asset_id', 'customer_id', 'reference', 'start_date', 'end_date', 'status']);

        // Alerts
        $expiringContracts = RentalContract::forCompany($company->id)
            ->where('status', 'active')
            ->where('end_date', '<=', now()->addDays(7))
            ->where('end_date', '>=', now())
            ->with('rentalAsset:id,name', 'customer:id,name')
            ->get();

        $overduePayments = $company->rentalPayments()
            ->where('status', 'overdue')
            ->with('rentalContract.customer:id,name', 'rentalContract.rentalAsset:id,name')
            ->limit(10)
            ->get();

        return inertia('App/Location/Calendar/Index', [
            'assets'             => $assets,
            'contracts'          => $contracts,
            'expiringContracts'  => $expiringContracts,
            'overduePayments'    => $overduePayments,
            'filters'            => $request->only(['type']),
        ]);
    }
}
