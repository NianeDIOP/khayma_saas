<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('subscriptions')
            ->orderBy('price_monthly')
            ->get();

        return inertia('Admin/Plans/Index', compact('plans'));
    }

    public function create()
    {
        return inertia('Admin/Plans/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                   => 'required|string|min:2|max:100',
            'code'                   => 'required|string|max:50|unique:plans,code',
            'max_products'           => 'required|integer|min:0',
            'max_users'              => 'required|integer|min:1',
            'max_storage_gb'         => 'required|integer|min:1',
            'max_transactions_month' => 'required|integer|min:0',
            'api_rate_limit'         => 'required|integer|min:0',
            'price_monthly'          => 'required|integer|min:0',
            'price_quarterly'        => 'required|integer|min:0',
            'price_yearly'           => 'required|integer|min:0',
            'is_active'              => 'nullable|boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        Plan::create($validated);

        return redirect()->route('admin.plans.index')->with('success', 'Plan créé.');
    }

    public function edit(Plan $plan)
    {
        return inertia('Admin/Plans/Form', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name'                   => 'required|string|min:2|max:100',
            'code'                   => 'required|string|max:50|unique:plans,code,' . $plan->id,
            'max_products'           => 'required|integer|min:0',
            'max_users'              => 'required|integer|min:1',
            'max_storage_gb'         => 'required|integer|min:1',
            'max_transactions_month' => 'required|integer|min:0',
            'api_rate_limit'         => 'required|integer|min:0',
            'price_monthly'          => 'required|integer|min:0',
            'price_quarterly'        => 'required|integer|min:0',
            'price_yearly'           => 'required|integer|min:0',
            'is_active'              => 'nullable|boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;
        $plan->update($validated);

        return redirect()->route('admin.plans.index')->with('success', 'Plan mis à jour.');
    }

    public function destroy(Plan $plan)
    {
        if ($plan->subscriptions()->where('status', 'active')->exists()) {
            return back()->with('error', 'Impossible de supprimer un plan avec des abonnements actifs.');
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')->with('success', 'Plan supprimé.');
    }
}
