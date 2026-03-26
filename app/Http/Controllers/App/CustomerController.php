<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = $this->company()->customers();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        $customers = $query->orderBy('name')->paginate(20)->withQueryString();

        return inertia('App/Customers/Index', [
            'customers' => $customers,
            'filters'   => $request->only(['search', 'category']),
        ]);
    }

    public function create()
    {
        return inertia('App/Customers/Form', [
            'customer' => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $this->company()->customers()->create($validated);

        return redirect()->route('app.customers.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Client créé avec succès.');
    }

    public function show(int $id)
    {
        $customer = $this->company()->customers()->findOrFail($id);

        return inertia('App/Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function edit(int $id)
    {
        $customer = $this->company()->customers()->findOrFail($id);

        return inertia('App/Customers/Form', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $customer  = $this->company()->customers()->findOrFail($id);
        $validated = $request->validate($this->rules($customer->id));

        $customer->update($validated);

        return redirect()->route('app.customers.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Client mis à jour.');
    }

    public function destroy(int $id)
    {
        $customer = $this->company()->customers()->findOrFail($id);
        $customer->delete();

        return redirect()->route('app.customers.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Client supprimé.');
    }

    private function rules(?int $ignoreId = null): array
    {
        $companyId = $this->company()->id;

        return [
            'name'     => ['required', 'string', 'min:2', 'max:100'],
            'phone'    => ['required', 'string', 'max:20'],
            'email'    => ['nullable', 'email', 'max:255'],
            'address'  => ['nullable', 'string', 'max:255'],
            'nif'      => [
                'nullable', 'string', 'max:50',
                Rule::unique('customers')->where('company_id', $companyId)->ignore($ignoreId),
            ],
            'category' => ['nullable', 'in:normal,vip,professional'],
        ];
    }
}
