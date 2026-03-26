<?php

namespace App\Http\Controllers\App\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\RestaurantCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CategoryController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index()
    {
        $categories = $this->company()
            ->restaurantCategories()
            ->withCount('dishes')
            ->orderBy('sort_order')
            ->get();

        return inertia('App/Restaurant/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return inertia('App/Restaurant/Categories/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $this->company()->restaurantCategories()->create($validated);

        return redirect()->route('app.restaurant.categories.index')
            ->with('success', 'Catégorie créée.');
    }

    public function edit(RestaurantCategory $category)
    {
        return inertia('App/Restaurant/Categories/Form', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, RestaurantCategory $category)
    {
        $validated = $request->validate($this->rules());
        $category->update($validated);

        return redirect()->route('app.restaurant.categories.index')
            ->with('success', 'Catégorie modifiée.');
    }

    public function destroy(RestaurantCategory $category)
    {
        $category->delete();

        return redirect()->route('app.restaurant.categories.index')
            ->with('success', 'Catégorie supprimée.');
    }

    private function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active'  => ['nullable', 'boolean'],
        ];
    }
}
