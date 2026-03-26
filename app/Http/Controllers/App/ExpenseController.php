<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ExpenseController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = $this->company()->expenses()->with(['expenseCategory', 'supplier']);

        if ($search = $request->input('search')) {
            $query->where('description', 'like', "%{$search}%");
        }

        if ($categoryId = $request->input('expense_category_id')) {
            $query->where('expense_category_id', $categoryId);
        }

        $expenses           = $query->orderByDesc('date')->paginate(20)->withQueryString();
        $expenseCategories  = $this->company()->expenseCategories()->orderBy('name')->get(['id', 'name']);

        return inertia('App/Expenses/Index', [
            'expenses'          => $expenses,
            'expenseCategories' => $expenseCategories,
            'filters'           => $request->only(['search', 'expense_category_id']),
        ]);
    }

    public function create()
    {
        return inertia('App/Expenses/Form', [
            'expense'           => null,
            'expenseCategories' => $this->company()->expenseCategories()->orderBy('name')->get(['id', 'name']),
            'suppliers'         => $this->company()->suppliers()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated              = $request->validate($this->rules());
        $validated['user_id']   = auth()->id();

        $this->company()->expenses()->create($validated);

        return redirect()->route('app.expenses.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Dépense enregistrée.');
    }

    public function show(int $id)
    {
        $expense = $this->company()->expenses()->with(['expenseCategory', 'supplier', 'user'])->findOrFail($id);

        return inertia('App/Expenses/Show', ['expense' => $expense]);
    }

    public function edit(int $id)
    {
        $expense = $this->company()->expenses()->findOrFail($id);

        return inertia('App/Expenses/Form', [
            'expense'           => $expense,
            'expenseCategories' => $this->company()->expenseCategories()->orderBy('name')->get(['id', 'name']),
            'suppliers'         => $this->company()->suppliers()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $expense   = $this->company()->expenses()->findOrFail($id);
        $validated = $request->validate($this->rules());
        $expense->update($validated);

        return redirect()->route('app.expenses.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Dépense mise à jour.');
    }

    public function destroy(int $id)
    {
        $expense = $this->company()->expenses()->findOrFail($id);
        $expense->delete();

        return redirect()->route('app.expenses.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Dépense supprimée.');
    }

    private function rules(): array
    {
        return [
            'expense_category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
            'amount'              => ['required', 'numeric', 'min:0.01'],
            'description'         => ['nullable', 'string', 'max:500'],
            'supplier_id'         => ['nullable', 'integer', 'exists:suppliers,id'],
            'date'                => ['required', 'date'],
        ];
    }
}
