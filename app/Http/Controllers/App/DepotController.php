<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Depot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class DepotController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = $this->company()->depots();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $depots = $query->orderBy('name')->paginate(20)->withQueryString();

        return inertia('App/Depots/Index', [
            'depots'  => $depots,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return inertia('App/Depots/Form', ['depot' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        if (!empty($validated['is_default'])) {
            $this->company()->depots()->update(['is_default' => false]);
        }

        $this->company()->depots()->create($validated);

        return redirect()->route('app.depots.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Dépôt créé avec succès.');
    }

    public function edit(int $id)
    {
        $depot = $this->company()->depots()->findOrFail($id);

        return inertia('App/Depots/Form', ['depot' => $depot]);
    }

    public function update(Request $request, int $id)
    {
        $depot     = $this->company()->depots()->findOrFail($id);
        $validated = $request->validate($this->rules());

        if (!empty($validated['is_default'])) {
            $this->company()->depots()->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $depot->update($validated);

        return redirect()->route('app.depots.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Dépôt mis à jour.');
    }

    public function destroy(int $id)
    {
        $depot = $this->company()->depots()->findOrFail($id);
        $depot->delete();

        return redirect()->route('app.depots.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Dépôt supprimé.');
    }

    private function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'min:2', 'max:100'],
            'address'    => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
