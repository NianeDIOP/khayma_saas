<?php

namespace App\Http\Controllers\App\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class DishController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = $this->company()->dishes()->with('category');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('restaurant_category_id', $categoryId);
        }

        if ($request->has('is_additional')) {
            $query->where('is_additional', $request->boolean('is_additional'));
        }

        $dishes = $query->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString();

        $categories = $this->company()->restaurantCategories()
            ->where('is_active', true)->orderBy('sort_order')->get(['id', 'name']);

        return inertia('App/Restaurant/Dishes/Index', [
            'dishes'     => $dishes,
            'categories' => $categories,
            'filters'    => $request->only(['search', 'category_id', 'is_additional']),
        ]);
    }

    public function create()
    {
        $categories = $this->company()->restaurantCategories()
            ->where('is_active', true)->orderBy('sort_order')->get(['id', 'name']);

        return inertia('App/Restaurant/Dishes/Form', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $this->company()->dishes()->create($validated);

        return redirect()->route('app.restaurant.dishes.index')
            ->with('success', 'Plat créé.');
    }

    public function edit(Dish $dish)
    {
        $categories = $this->company()->restaurantCategories()
            ->where('is_active', true)->orderBy('sort_order')->get(['id', 'name']);

        return inertia('App/Restaurant/Dishes/Form', [
            'dish'       => $dish,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Dish $dish)
    {
        $validated = $request->validate($this->rules());
        $dish->update($validated);

        return redirect()->route('app.restaurant.dishes.index')
            ->with('success', 'Plat modifié.');
    }

    public function destroy(Dish $dish)
    {
        $dish->delete();

        return redirect()->route('app.restaurant.dishes.index')
            ->with('success', 'Plat supprimé.');
    }

    private function rules(): array
    {
        return [
            'restaurant_category_id' => ['required', 'integer', 'exists:restaurant_categories,id'],
            'name'                   => ['required', 'string', 'max:255'],
            'description'            => ['nullable', 'string', 'max:1000'],
            'price'                  => ['required', 'numeric', 'min:0'],
            'image_url'              => ['nullable', 'string', 'max:500'],
            'is_available'           => ['nullable', 'boolean'],
            'available_morning'      => ['nullable', 'boolean'],
            'available_noon'         => ['nullable', 'boolean'],
            'available_evening'      => ['nullable', 'boolean'],
            'is_additional'          => ['nullable', 'boolean'],
            'promo_price'            => ['nullable', 'numeric', 'min:0'],
            'promo_start'            => ['nullable', 'date'],
            'promo_end'              => ['nullable', 'date', 'after_or_equal:promo_start'],
            'sort_order'             => ['nullable', 'integer', 'min:0'],
        ];
    }
}
