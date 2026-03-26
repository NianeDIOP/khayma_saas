<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = Module::withCount('companies')
            ->orderBy('name')
            ->get();

        return inertia('Admin/Modules/Index', compact('modules'));
    }

    public function create()
    {
        return inertia('Admin/Modules/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|min:2|max:100',
            'code'             => 'required|string|max:50|unique:modules,code',
            'description'      => 'nullable|string|max:500',
            'icon'             => 'nullable|string|max:100',
            'installation_fee' => 'required|integer|min:0',
            'is_active'        => 'nullable|boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        Module::create($validated);

        return redirect()->route('admin.modules.index')->with('success', 'Module créé.');
    }

    public function edit(Module $module)
    {
        $module->loadCount('companies');

        return inertia('Admin/Modules/Form', compact('module'));
    }

    public function update(Request $request, Module $module)
    {
        $validated = $request->validate([
            'name'             => 'required|string|min:2|max:100',
            'code'             => 'required|string|max:50|unique:modules,code,' . $module->id,
            'description'      => 'nullable|string|max:500',
            'icon'             => 'nullable|string|max:100',
            'installation_fee' => 'required|integer|min:0',
            'is_active'        => 'nullable|boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;
        $module->update($validated);

        return redirect()->route('admin.modules.index')->with('success', 'Module mis à jour.');
    }

    public function toggle(Module $module)
    {
        $module->update(['is_active' => !$module->is_active]);

        return back()->with('success', 'Statut du module mis à jour.');
    }
}
