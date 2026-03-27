<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
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

    public function index()
    {
        $company = $this->company();

        $members = $company->users()
            ->orderByPivot('joined_at', 'desc')
            ->get()
            ->map(fn (User $u) => [
                'id'        => $u->id,
                'name'      => $u->name,
                'email'     => $u->email,
                'phone'     => $u->phone,
                'role'      => $u->pivot->role,
                'joined_at' => $u->pivot->joined_at,
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
            'member' => null,
            'roles'  => ['manager', 'caissier', 'magasinier'],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeOwner();
        $company = $this->company();

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['required', Rule::in(['manager', 'caissier', 'magasinier'])],
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
            'role'       => $validated['role'],
            'joined_at'  => now(),
            'invited_by' => auth()->id(),
        ]);

        // Assign Spatie role
        if (!$user->hasRole($validated['role'])) {
            $user->assignRole($validated['role']);
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
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role'  => $user->pivot->role,
            ],
            'roles' => ['manager', 'caissier', 'magasinier'],
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
            'name'     => ['required', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8'],
            'role'     => ['required', Rule::in(['manager', 'caissier', 'magasinier'])],
        ]);

        $user->update([
            'name'  => $validated['name'],
            'phone' => $validated['phone'] ?? $user->phone,
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Update pivot role
        $company->users()->updateExistingPivot($user->id, [
            'role' => $validated['role'],
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
