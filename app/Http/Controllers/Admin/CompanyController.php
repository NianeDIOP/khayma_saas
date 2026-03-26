<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::withTrashed()
            ->with(['users' => fn ($q) => $q->wherePivot('role', 'owner')])
            ->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('subscription_status', $status);
        }

        $companies = $query->paginate(20)->withQueryString();

        return inertia('Admin/Companies/Index', [
            'companies' => $companies,
            'filters'   => $request->only('search', 'status'),
        ]);
    }

    public function show(Company $company)
    {
        $company->load([
            'users',
            'subscriptions' => fn ($q) => $q->orderByDesc('created_at')->limit(5),
        ]);

        return inertia('Admin/Companies/Show', compact('company'));
    }

    public function toggle(Company $company)
    {
        $company->update(['is_active' => ! $company->is_active]);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function updateSubscription(Request $request, Company $company)
    {
        $validated = $request->validate([
            'subscription_status' => ['required', Rule::in([
                'trial', 'active', 'grace_period', 'expired', 'suspended', 'cancelled',
            ])],
        ]);

        $company->update($validated);

        return back()->with('success', 'Abonnement mis à jour.');
    }
}
