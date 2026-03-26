<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

class ExpenseCategoryController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = $this->company()->expenseCategories()->withCount('expenses');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->orderBy('name')->paginate(20)->withQueryString();

        return inertia('App/ExpenseCategories/Index', [
            'categories' => $categories,
            'filters'    => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return inertia('App/ExpenseCategories/Form', ['category' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $this->company()->expenseCategories()->create($validated);

        return redirect()->route('app.expense-categories.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Catégorie de dépense créée.');
    }

    public function edit(int $id)
    {
        $category = $this->company()->expenseCategories()->findOrFail($id);

        return inertia('App/ExpenseCategories/Form', ['category' => $category]);
    }

    public function update(Request $request, int $id)
    {
        $category  = $this->company()->expenseCategories()->findOrFail($id);
        $validated = $request->validate($this->rules($category->id));
        $category->update($validated);

        return redirect()->route('app.expense-categories.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(int $id)
    {
        $category = $this->company()->expenseCategories()->findOrFail($id);
        $category->delete();

        return redirect()->route('app.expense-categories.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Catégorie supprimée.');
    }

    private function rules(?int $ignoreId = null): array
    {
        $companyId = $this->company()->id;

        return [
            'name' => [
                'required', 'string', 'min:2', 'max:100',
                Rule::unique('expense_categories')->where('company_id', $companyId)->ignore($ignoreId),
            ],
        ];
    }
}
