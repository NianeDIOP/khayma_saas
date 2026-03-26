<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('companies')
            ->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->input('role') === 'admin') {
            $query->where('is_super_admin', true);
        }

        $users = $query->paginate(20)->withQueryString();

        return inertia('Admin/Users/Index', [
            'users'   => $users,
            'filters' => $request->only('search', 'role'),
        ]);
    }

    public function show(User $user)
    {
        $user->load(['companies' => fn ($q) => $q->withPivot('role', 'joined_at')]);

        return inertia('Admin/Users/Show', compact('user'));
    }

    public function toggleAdmin(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas modifier votre propre statut admin.');
        }

        $user->update(['is_super_admin' => !$user->is_super_admin]);

        return back()->with('success', 'Statut admin mis à jour.');
    }
}
