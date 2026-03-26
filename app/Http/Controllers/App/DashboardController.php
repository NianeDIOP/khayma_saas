<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var Company $company */
        $company = App::make('currentCompany');
        $company->load(['users', 'modules', 'subscriptions' => fn ($q) => $q->latest()->limit(1)]);

        $stats = [
            'users_count'       => $company->users()->count(),
            'modules_count'     => $company->modules()->count(),
            'subscription'      => $company->subscription_status,
            'trial_ends_at'     => $company->trial_ends_at?->toISOString(),
            'trial_days_left'   => $company->isOnTrial() ? (int) now()->diffInDays($company->trial_ends_at, false) : null,
            'active_plan'       => $company->subscriptions->first()?->plan?->name ?? 'Aucun',
        ];

        $recentUsers = $company->users()
            ->orderByPivot('joined_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($u) => [
                'id'        => $u->id,
                'name'      => $u->name,
                'email'     => $u->email,
                'role'      => $u->pivot->role,
                'joined_at' => $u->pivot->joined_at,
            ]);

        $activeModules = $company->modules()
            ->where('is_active', true)
            ->get()
            ->map(fn ($m) => [
                'id'   => $m->id,
                'name' => $m->name,
                'code' => $m->code,
                'icon' => $m->icon,
            ]);

        return inertia('App/Dashboard', [
            'stats'         => $stats,
            'recentUsers'   => $recentUsers,
            'activeModules' => $activeModules,
        ]);
    }
}
