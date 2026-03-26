<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = $this->company()->products()->with(['category', 'unit']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $products   = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = $this->company()->categories()->orderBy('name')->get(['id', 'name']);

        return inertia('App/Products/Index', [
            'products'   => $products,
            'categories' => $categories,
            'filters'    => $request->only(['search', 'category_id', 'is_active']),
        ]);
    }

    public function create()
    {
        return inertia('App/Products/Form', [
            'product'    => null,
            'categories' => $this->company()->categories()->orderBy('name')->get(['id', 'name']),
            'units'      => $this->company()->units()->orderBy('name')->get(['id', 'name', 'symbol']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $this->company()->products()->create($validated);

        return redirect()->route('app.products.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Produit créé avec succès.');
    }

    public function show(int $id)
    {
        $product = $this->company()->products()->with(['category', 'unit', 'stockItems.depot'])->findOrFail($id);

        return inertia('App/Products/Show', [
            'product' => $product,
        ]);
    }

    public function edit(int $id)
    {
        $product = $this->company()->products()->findOrFail($id);

        return inertia('App/Products/Form', [
            'product'    => $product,
            'categories' => $this->company()->categories()->orderBy('name')->get(['id', 'name']),
            'units'      => $this->company()->units()->orderBy('name')->get(['id', 'name', 'symbol']),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $product   = $this->company()->products()->findOrFail($id);
        $validated = $request->validate($this->rules($product->id));
        $product->update($validated);

        return redirect()->route('app.products.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Produit mis à jour.');
    }

    public function destroy(int $id)
    {
        $product = $this->company()->products()->findOrFail($id);
        $product->delete();

        return redirect()->route('app.products.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Produit supprimé.');
    }

    private function rules(?int $ignoreId = null): array
    {
        $companyId = $this->company()->id;

        return [
            'name'            => ['required', 'string', 'min:2', 'max:150'],
            'description'     => ['nullable', 'string', 'max:1000'],
            'category_id'     => ['nullable', 'integer', 'exists:categories,id'],
            'unit_id'         => ['nullable', 'integer', 'exists:units,id'],
            'purchase_price'  => ['required', 'numeric', 'min:0'],
            'selling_price'   => ['required', 'numeric', 'min:0'],
            'barcode'         => [
                'nullable', 'string', 'max:100',
                Rule::unique('products')->where('company_id', $companyId)->ignore($ignoreId),
            ],
            'min_stock_alert' => ['nullable', 'integer', 'min:0'],
            'is_active'       => ['nullable', 'boolean'],
        ];
    }
}
