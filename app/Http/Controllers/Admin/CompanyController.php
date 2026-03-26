<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Module;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::withTrashed()
            ->with(['users' => fn ($q) => $q->wherePivot('role', 'owner')])
            ->withCount('modules')
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

    public function create()
    {
        $plans = Plan::active()->orderBy('price_monthly')->get();
        $modules = Module::active()->orderBy('name')->get();

        return inertia('Admin/Companies/Create', compact('plans', 'modules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|min:2|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'sector'  => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'owner_name'  => 'required|string|min:2|max:255',
            'owner_email' => 'required|email|max:255|unique:users,email',
            'subscription_status' => 'required|' . Rule::in(['trial', 'active']),
            'module_id' => 'nullable|exists:modules,id',
        ]);

        $slug = Str::slug($validated['name']);
        $base = $slug;
        $i = 1;
        while (Company::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $company = Company::create([
            'name'                => $validated['name'],
            'slug'                => $slug,
            'email'               => $validated['email'],
            'phone'               => $validated['phone'] ?? null,
            'sector'              => $validated['sector'] ?? null,
            'address'             => $validated['address'] ?? null,
            'subscription_status' => $validated['subscription_status'],
            'trial_ends_at'       => $validated['subscription_status'] === 'trial' ? now()->addDays(7) : null,
            'is_active'           => true,
        ]);

        $password = Str::random(12);
        $owner = User::create([
            'name'     => $validated['owner_name'],
            'email'    => $validated['owner_email'],
            'password' => Hash::make($password),
        ]);

        $company->users()->attach($owner->id, [
            'role'      => 'owner',
            'joined_at' => now(),
        ]);

        if (!empty($validated['module_id'])) {
            $company->modules()->attach($validated['module_id'], [
                'activated_at' => now(),
                'activated_by' => auth()->id(),
            ]);
        }

        return redirect()->route('admin.companies.show', $company)
            ->with('success', "Entreprise créée. Mot de passe owner : {$password}");
    }

    public function show(Company $company)
    {
        $company->load([
            'users',
            'modules',
            'subscriptions' => fn ($q) => $q->with(['plan', 'module'])->orderByDesc('created_at')->limit(10),
        ]);

        $plans = Plan::active()->orderBy('price_monthly')->get();
        $modules = Module::active()->orderBy('name')->get();

        return inertia('Admin/Companies/Show', compact('company', 'plans', 'modules'));
    }

    public function edit(Company $company)
    {
        return inertia('Admin/Companies/Edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name'    => 'required|string|min:2|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'sector'  => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'ninea'   => 'nullable|string|max:50',
        ]);

        $company->update($validated);

        return redirect()->route('admin.companies.show', $company)->with('success', 'Entreprise mise à jour.');
    }

    public function destroy(Company $company)
    {
        $company->update(['is_active' => false, 'subscription_status' => 'cancelled']);
        $company->delete();

        return redirect()->route('admin.companies.index')->with('success', 'Entreprise supprimée.');
    }

    public function toggle(Company $company)
    {
        $company->update(['is_active' => !$company->is_active]);

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

    public function extendTrial(Request $request, Company $company)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:90',
        ]);

        $base = $company->trial_ends_at && $company->trial_ends_at->isFuture()
            ? $company->trial_ends_at
            : now();

        $company->update([
            'trial_ends_at'       => $base->addDays($validated['days']),
            'subscription_status' => 'trial',
        ]);

        return back()->with('success', "Essai prolongé de {$validated['days']} jours.");
    }

    public function syncModules(Request $request, Company $company)
    {
        $validated = $request->validate([
            'module_ids'   => 'nullable|array',
            'module_ids.*' => 'exists:modules,id',
        ]);

        $moduleIds = $validated['module_ids'] ?? [];
        $syncData = [];
        foreach ($moduleIds as $id) {
            $syncData[$id] = [
                'activated_at' => now(),
                'activated_by' => auth()->id(),
            ];
        }

        $company->modules()->sync($syncData);

        return back()->with('success', 'Modules mis à jour.');
    }

    public function resetPassword(Company $company)
    {
        $owner = $company->users()->wherePivot('role', 'owner')->first();

        if (!$owner) {
            return back()->with('error', 'Aucun propriétaire trouvé.');
        }

        $password = Str::random(12);
        $owner->update(['password' => Hash::make($password)]);

        return back()->with('success', "Mot de passe réinitialisé : {$password}");
    }
}
