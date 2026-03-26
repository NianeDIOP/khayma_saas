<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = $this->company()->categories()->with('parent');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($module = $request->input('module')) {
            $query->where('module', $module);
        }

        $categories = $query->orderBy('name')->paginate(20)->withQueryString();

        return inertia('App/Categories/Index', [
            'categories' => $categories,
            'filters'    => $request->only(['search', 'module']),
        ]);
    }

    public function create()
    {
        $parents = $this->company()->categories()->whereNull('parent_id')->orderBy('name')->get(['id', 'name']);

        return inertia('App/Categories/Form', [
            'category' => null,
            'parents'  => $parents,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $this->company()->categories()->create($validated);

        return redirect()->route('app.categories.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(int $id)
    {
        $category = $this->company()->categories()->findOrFail($id);
        $parents  = $this->company()->categories()
            ->whereNull('parent_id')
            ->where('id', '!=', $id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return inertia('App/Categories/Form', [
            'category' => $category,
            'parents'  => $parents,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $category  = $this->company()->categories()->findOrFail($id);
        $validated = $request->validate($this->rules($category->id));
        $category->update($validated);

        return redirect()->route('app.categories.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(int $id)
    {
        $category = $this->company()->categories()->findOrFail($id);
        $category->delete();

        return redirect()->route('app.categories.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Catégorie supprimée.');
    }

    private function rules(?int $ignoreId = null): array
    {
        $companyId = $this->company()->id;

        return [
            'name'      => [
                'required', 'string', 'min:2', 'max:100',
                Rule::unique('categories')->where('company_id', $companyId)->where('module', request('module', 'general'))->ignore($ignoreId),
            ],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'module'    => ['nullable', 'string', 'max:50'],
        ];
    }
}
