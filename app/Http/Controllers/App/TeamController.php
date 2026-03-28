<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Company;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    private function authorizeOwner(): void
    {
        $user = auth()->user();
        $role = $user->roleInCompany($this->company());
        if (!in_array($role, ['owner']) && !$user->is_super_admin) {
            abort(403, 'Seul le propriétaire peut gérer l\'équipe.');
        }
    }

    private function roleDefaults(): array
    {
        return [
            'manager'    => ['dashboard','customers','suppliers','products','categories','units','depots','stock','sales','expenses',
                             'restaurant.pos','restaurant.orders','restaurant.dishes','restaurant.categories','restaurant.services','restaurant.cash','restaurant.reports',
                             'quinc.pos','quinc.quotes','quinc.purchase-orders','quinc.supplier-payments','quinc.supplier-returns','quinc.credits','quinc.inventories','quinc.reports',
                             'boutique.pos','boutique.variants','boutique.promotions','boutique.loyalty','boutique.transfers','boutique.reports',
                             'location.assets','location.contracts','location.payments','location.calendar','location.reports'],
            'caissier'   => ['dashboard','customers','sales','restaurant.pos','restaurant.orders','restaurant.cash','quinc.pos','quinc.credits','boutique.pos','location.payments'],
            'magasinier' => ['dashboard','suppliers','products','categories','units','depots','stock','quinc.inventories','boutique.transfers'],
        ];
    }

    private function allPermissions(): array
    {
        return [
            ['group' => 'Général',        'items' => [
                ['key' => 'dashboard',  'label' => 'Tableau de bord'],
                ['key' => 'customers',  'label' => 'Clients'],
                ['key' => 'suppliers',  'label' => 'Fournisseurs'],
                ['key' => 'products',   'label' => 'Produits'],
                ['key' => 'categories', 'label' => 'Catégories'],
                ['key' => 'units',      'label' => 'Unités'],
                ['key' => 'depots',     'label' => 'Dépôts'],
                ['key' => 'stock',      'label' => 'Stock'],
                ['key' => 'sales',      'label' => 'Ventes'],
                ['key' => 'expenses',   'label' => 'Dépenses'],
            ]],
            ['group' => 'Restaurant', 'module' => 'restaurant', 'items' => [
                ['key' => 'restaurant.pos',        'label' => 'POS Commandes'],
                ['key' => 'restaurant.orders',     'label' => 'Commandes'],
                ['key' => 'restaurant.dishes',     'label' => 'Plats'],
                ['key' => 'restaurant.categories', 'label' => 'Catégories Menu'],
                ['key' => 'restaurant.services',   'label' => 'Services'],
                ['key' => 'restaurant.cash',       'label' => 'Caisse'],
                ['key' => 'restaurant.reports',    'label' => 'Rapports'],
            ]],
            ['group' => 'Quincaillerie', 'module' => 'quincaillerie', 'items' => [
                ['key' => 'quinc.pos',              'label' => 'Caisse POS'],
                ['key' => 'quinc.quotes',           'label' => 'Devis'],
                ['key' => 'quinc.purchase-orders',  'label' => 'Bons de commande'],
                ['key' => 'quinc.supplier-payments', 'label' => 'Paiements fournisseur'],
                ['key' => 'quinc.supplier-returns',  'label' => 'Retours fournisseur'],
                ['key' => 'quinc.credits',           'label' => 'Crédits clients'],
                ['key' => 'quinc.inventories',       'label' => 'Inventaires'],
                ['key' => 'quinc.reports',           'label' => 'Rapports'],
            ]],
            ['group' => 'Boutique', 'module' => 'boutique', 'items' => [
                ['key' => 'boutique.pos',        'label' => 'Caisse POS'],
                ['key' => 'boutique.variants',   'label' => 'Variantes'],
                ['key' => 'boutique.promotions', 'label' => 'Promotions'],
                ['key' => 'boutique.loyalty',    'label' => 'Fidélité'],
                ['key' => 'boutique.transfers',  'label' => 'Transferts'],
                ['key' => 'boutique.reports',    'label' => 'Rapports'],
            ]],
            ['group' => 'Location', 'module' => 'location', 'items' => [
                ['key' => 'location.assets',    'label' => 'Biens'],
                ['key' => 'location.contracts', 'label' => 'Contrats'],
                ['key' => 'location.payments',  'label' => 'Paiements'],
                ['key' => 'location.calendar',  'label' => 'Calendrier'],
                ['key' => 'location.reports',   'label' => 'Rapports'],
            ]],
        ];
    }

    public function index()
    {
        $company = $this->company();

        $members = $company->users()
            ->orderByPivot('joined_at', 'desc')
            ->get()
            ->map(fn (User $u) => [
                'id'          => $u->id,
                'name'        => $u->name,
                'email'       => $u->email,
                'phone'       => $u->phone,
                'role'        => $u->pivot->role,
                'permissions' => is_string($u->pivot->permissions) ? json_decode($u->pivot->permissions, true) : ($u->pivot->permissions ?? []),
                'joined_at'   => $u->pivot->joined_at,
            ]);

        return inertia('App/Team/Index', [
            'members' => $members,
            'roles'   => ['owner', 'manager', 'caissier', 'magasinier'],
        ]);
    }

    public function create()
    {
        $this->authorizeOwner();

        return inertia('App/Team/Form', [
            'member'            => null,
            'roles'             => ['manager', 'caissier', 'magasinier'],
            'allPermissions'    => $this->allPermissions(),
            'activeModuleCodes' => $this->company()->modules()->pluck('code')->toArray(),
            'roleDefaults'      => $this->roleDefaults(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeOwner();
        $company = $this->company();

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'password'      => ['required', 'string', 'min:8'],
            'role'          => ['required', Rule::in(['manager', 'caissier', 'magasinier'])],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        // Check if user with this email already exists
        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            // Check if already in this company
            if ($company->users()->where('user_id', $user->id)->exists()) {
                return back()->withErrors(['email' => 'Cet utilisateur est déjà membre de l\'entreprise.']);
            }
        } else {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'phone'    => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
            ]);
        }

        $company->users()->attach($user->id, [
            'role'        => $validated['role'],
            'permissions' => json_encode($validated['permissions'] ?? []),
            'joined_at'   => now(),
            'invited_by'  => auth()->id(),
        ]);

        // Assign Spatie role
        if (!$user->hasRole($validated['role'])) {
            $user->assignRole($validated['role']);
        }

        if (!empty($user->email)) {
            Mail::to($user->email)->queue(new WelcomeMail($user, $company));
        }

        if (!empty($user->phone)) {
            app(SmsService::class)->send(
                $user->phone,
                'Khayma: vous avez ete ajoute a ' . $company->name . ' en tant que ' . $validated['role'] . '.'
            );
        }

        return redirect()
            ->route('app.team.index', ['_tenant' => $company->slug])
            ->with('success', "{$user->name} a été ajouté à l'équipe.");
    }

    public function edit(int $userId)
    {
        $this->authorizeOwner();
        $company = $this->company();

        $user = $company->users()->where('user_id', $userId)->firstOrFail();

        // Cannot edit the owner
        if ($user->pivot->role === 'owner') {
            return back()->with('error', 'Impossible de modifier le propriétaire.');
        }

        return inertia('App/Team/Form', [
            'member' => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'phone'       => $user->phone,
                'role'        => $user->pivot->role,
                'permissions' => is_string($user->pivot->permissions) ? json_decode($user->pivot->permissions, true) : ($user->pivot->permissions ?? []),
            ],
            'roles'             => ['manager', 'caissier', 'magasinier'],
            'allPermissions'    => $this->allPermissions(),
            'activeModuleCodes' => $this->company()->modules()->pluck('code')->toArray(),
            'roleDefaults'      => $this->roleDefaults(),
        ]);
    }

    public function update(Request $request, int $userId)
    {
        $this->authorizeOwner();
        $company = $this->company();

        $user = $company->users()->where('user_id', $userId)->firstOrFail();

        if ($user->pivot->role === 'owner') {
            return back()->with('error', 'Impossible de modifier le propriétaire.');
        }

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'password'      => ['nullable', 'string', 'min:8'],
            'role'          => ['required', Rule::in(['manager', 'caissier', 'magasinier'])],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $user->update([
            'name'  => $validated['name'],
            'phone' => $validated['phone'] ?? $user->phone,
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Update pivot role + permissions
        $company->users()->updateExistingPivot($user->id, [
            'role'        => $validated['role'],
            'permissions' => json_encode($validated['permissions'] ?? []),
        ]);

        // Sync Spatie role
        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('app.team.index', ['_tenant' => $company->slug])
            ->with('success', "Profil de {$user->name} mis à jour.");
    }

    public function destroy(int $userId)
    {
        $this->authorizeOwner();
        $company = $this->company();

        $user = $company->users()->where('user_id', $userId)->firstOrFail();

        if ($user->pivot->role === 'owner') {
            return back()->with('error', 'Impossible de retirer le propriétaire.');
        }

        $company->users()->detach($userId);

        return redirect()
            ->route('app.team.index', ['_tenant' => $company->slug])
            ->with('success', "{$user->name} a été retiré de l'équipe.");
    }
}
