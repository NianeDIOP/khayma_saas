<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;

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
        ];

        return inertia('Admin/Dashboard', compact('stats'));
    }
}
