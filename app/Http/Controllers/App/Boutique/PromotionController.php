<?php

namespace App\Http\Controllers\App\Boutique;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class PromotionController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = Promotion::forCompany($this->company()->id)->with('product');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->input('active_only')) {
            $query->active();
        }

        $promotions = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return inertia('App/Boutique/Promotions/Index', [
            'promotions' => $promotions,
            'filters'    => $request->only(['search', 'active_only']),
        ]);
    }

    public function create()
    {
        $products = $this->company()->products()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'selling_price']);

        return inertia('App/Boutique/Promotions/Form', [
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'name'       => ['required', 'string', 'max:255'],
            'type'       => ['required', 'in:percentage,fixed'],
            'value'      => ['required', 'numeric', 'min:0.01'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'is_active'  => ['boolean'],
        ]);

        $validated['company_id'] = $this->company()->id;

        Promotion::create($validated);

        return redirect()->route('app.boutique.promotions.index')
            ->with('success', 'Promotion créée avec succès.');
    }

    public function edit(Promotion $promotion)
    {
        $products = $this->company()->products()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'selling_price']);

        return inertia('App/Boutique/Promotions/Form', [
            'promotion' => $promotion,
            'products'  => $products,
        ]);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'name'       => ['required', 'string', 'max:255'],
            'type'       => ['required', 'in:percentage,fixed'],
            'value'      => ['required', 'numeric', 'min:0.01'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'is_active'  => ['boolean'],
        ]);

        $promotion->update($validated);

        return redirect()->route('app.boutique.promotions.index')
            ->with('success', 'Promotion mise à jour.');
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();

        return redirect()->route('app.boutique.promotions.index')
            ->with('success', 'Promotion supprimée.');
    }
}
