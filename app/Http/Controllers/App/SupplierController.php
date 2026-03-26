<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = $this->company()->suppliers();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('name')->paginate(20)->withQueryString();

        return inertia('App/Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters'   => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return inertia('App/Suppliers/Form', [
            'supplier' => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $this->company()->suppliers()->create($validated);

        return redirect()->route('app.suppliers.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Fournisseur créé avec succès.');
    }

    public function show(int $id)
    {
        $supplier = $this->company()->suppliers()->findOrFail($id);

        return inertia('App/Suppliers/Show', [
            'supplier' => $supplier,
        ]);
    }

    public function edit(int $id)
    {
        $supplier = $this->company()->suppliers()->findOrFail($id);

        return inertia('App/Suppliers/Form', [
            'supplier' => $supplier,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $supplier  = $this->company()->suppliers()->findOrFail($id);
        $validated = $request->validate($this->rules($supplier->id));

        $supplier->update($validated);

        return redirect()->route('app.suppliers.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Fournisseur mis à jour.');
    }

    public function destroy(int $id)
    {
        $supplier = $this->company()->suppliers()->findOrFail($id);
        $supplier->delete();

        return redirect()->route('app.suppliers.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Fournisseur supprimé.');
    }

    private function rules(?int $ignoreId = null): array
    {
        $companyId = $this->company()->id;

        return [
            'name'    => ['required', 'string', 'min:2', 'max:100'],
            'phone'   => ['required', 'string', 'max:20'],
            'email'   => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'ninea'   => [
                'nullable', 'string', 'max:50',
                Rule::unique('suppliers')->where('company_id', $companyId)->ignore($ignoreId),
            ],
            'rib'     => ['nullable', 'string', 'max:100'],
            'rating'  => ['nullable', 'numeric', 'min:0', 'max:5'],
            'notes'   => ['nullable', 'string', 'max:1000'],
        ];
    }
}
