<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = $this->company()->units()->with('baseUnit');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('symbol', 'like', "%{$search}%");
            });
        }

        $units = $query->orderBy('name')->paginate(20)->withQueryString();

        return inertia('App/Units/Index', [
            'units'   => $units,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        $baseUnits = $this->company()->units()->whereNull('base_unit_id')->orderBy('name')->get(['id', 'name', 'symbol']);

        return inertia('App/Units/Form', [
            'unit'      => null,
            'baseUnits' => $baseUnits,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $this->company()->units()->create($validated);

        return redirect()->route('app.units.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Unité créée avec succès.');
    }

    public function edit(int $id)
    {
        $unit      = $this->company()->units()->findOrFail($id);
        $baseUnits = $this->company()->units()
            ->whereNull('base_unit_id')
            ->where('id', '!=', $id)
            ->orderBy('name')
            ->get(['id', 'name', 'symbol']);

        return inertia('App/Units/Form', [
            'unit'      => $unit,
            'baseUnits' => $baseUnits,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $unit      = $this->company()->units()->findOrFail($id);
        $validated = $request->validate($this->rules($unit->id));
        $unit->update($validated);

        return redirect()->route('app.units.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Unité mise à jour.');
    }

    public function destroy(int $id)
    {
        $unit = $this->company()->units()->findOrFail($id);
        $unit->delete();

        return redirect()->route('app.units.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Unité supprimée.');
    }

    private function rules(?int $ignoreId = null): array
    {
        $companyId = $this->company()->id;

        return [
            'name'              => [
                'required', 'string', 'min:1', 'max:50',
                Rule::unique('units')->where('company_id', $companyId)->ignore($ignoreId),
            ],
            'symbol'            => ['required', 'string', 'max:10'],
            'base_unit_id'      => ['nullable', 'integer', 'exists:units,id'],
            'conversion_factor' => ['nullable', 'numeric', 'min:0.0001', 'max:9999999'],
        ];
    }
}
