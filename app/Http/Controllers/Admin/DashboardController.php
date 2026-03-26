<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Module;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'companies_total'   => Company::withTrashed()->count(),
            'companies_active'  => Company::where('subscription_status', 'active')->count(),
            'companies_trial'   => Company::where('subscription_status', 'trial')->count(),
            'companies_expired' => Company::whereIn('subscription_status', ['expired', 'suspended', 'cancelled'])->count(),
            'users_total'       => User::count(),
            'users_admin'       => User::where('is_super_admin', true)->count(),
            'mrr'               => Subscription::where('status', 'active')->sum('amount_paid'),
            'new_this_week'     => Company::where('created_at', '>=', now()->startOfWeek())->count(),
        ];

        // Modules populaires
        $popularModules = Module::withCount('companies')
            ->orderByDesc('companies_count')
            ->limit(5)
            ->get()
            ->map(fn ($m) => ['name' => $m->name, 'count' => $m->companies_count]);

        // Abonnements récents
        $recentSubscriptions = Subscription::with(['company', 'plan'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Entreprises récentes
        $recentCompanies = Company::with(['users' => fn ($q) => $q->wherePivot('role', 'owner')])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Conversion trial → payant
        $totalTrial = Company::withTrashed()->where('subscription_status', '!=', 'trial')->count();
        $converted = Subscription::where('status', 'active')->distinct('company_id')->count('company_id');
        $conversionRate = $totalTrial > 0 ? round(($converted / $totalTrial) * 100, 1) : 0;

        $stats['conversion_rate'] = $conversionRate;

        return inertia('Admin/Dashboard', [
            'stats'               => $stats,
            'popularModules'      => $popularModules,
            'recentSubscriptions' => $recentSubscriptions,
            'recentCompanies'     => $recentCompanies,
        ]);
    }
}
