<?php

namespace App\Http\Controllers\App\Boutique;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

class VariantController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = ProductVariant::forCompany($this->company()->id)
            ->with('product');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }

        $variants = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $products = $this->company()->products()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return inertia('App/Boutique/Variants/Index', [
            'variants' => $variants,
            'products' => $products,
            'filters'  => $request->only(['search', 'product_id']),
        ]);
    }

    public function create()
    {
        $products = $this->company()->products()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'selling_price', 'purchase_price']);

        return inertia('App/Boutique/Variants/Form', [
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $company = $this->company();

        $validated = $request->validate([
            'product_id'             => ['required', 'integer', 'exists:products,id'],
            'name'                   => ['required', 'string', 'max:255'],
            'sku'                    => ['nullable', 'string', 'max:100', Rule::unique('product_variants')->where('company_id', $company->id)],
            'barcode'                => ['nullable', 'string', 'max:100', Rule::unique('product_variants')->where('company_id', $company->id)],
            'price_override'         => ['nullable', 'numeric', 'min:0'],
            'purchase_price_override' => ['nullable', 'numeric', 'min:0'],
            'attributes'             => ['nullable', 'array'],
            'is_active'              => ['boolean'],
        ]);

        $validated['company_id'] = $company->id;

        ProductVariant::create($validated);

        return redirect()->route('app.boutique.variants.index')
            ->with('success', 'Variante créée avec succès.');
    }

    public function edit(ProductVariant $variant)
    {
        $products = $this->company()->products()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'selling_price', 'purchase_price']);

        return inertia('App/Boutique/Variants/Form', [
            'variant'  => $variant,
            'products' => $products,
        ]);
    }

    public function update(Request $request, ProductVariant $variant)
    {
        $company = $this->company();

        $validated = $request->validate([
            'product_id'             => ['required', 'integer', 'exists:products,id'],
            'name'                   => ['required', 'string', 'max:255'],
            'sku'                    => ['nullable', 'string', 'max:100', Rule::unique('product_variants')->where('company_id', $company->id)->ignore($variant->id)],
            'barcode'                => ['nullable', 'string', 'max:100', Rule::unique('product_variants')->where('company_id', $company->id)->ignore($variant->id)],
            'price_override'         => ['nullable', 'numeric', 'min:0'],
            'purchase_price_override' => ['nullable', 'numeric', 'min:0'],
            'attributes'             => ['nullable', 'array'],
            'is_active'              => ['boolean'],
        ]);

        $variant->update($validated);

        return redirect()->route('app.boutique.variants.index')
            ->with('success', 'Variante mise à jour.');
    }

    public function destroy(ProductVariant $variant)
    {
        $variant->delete();

        return redirect()->route('app.boutique.variants.index')
            ->with('success', 'Variante supprimée.');
    }
}
