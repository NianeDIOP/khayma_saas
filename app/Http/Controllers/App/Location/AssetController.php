<?php

namespace App\Http\Controllers\App\Location;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\RentalAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class AssetController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = RentalAsset::forCompany($this->company()->id);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $assets = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return inertia('App/Location/Assets/Index', [
            'assets'  => $assets,
            'filters' => $request->only(['search', 'type', 'status']),
        ]);
    }

    public function create()
    {
        return inertia('App/Location/Assets/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'type'             => ['required', 'in:vehicle,real_estate,equipment,other'],
            'daily_rate'       => ['nullable', 'numeric', 'min:0'],
            'monthly_rate'     => ['nullable', 'numeric', 'min:0'],
            'status'           => ['required', 'in:available,rented,maintenance,out_of_service'],
            'characteristics'  => ['nullable', 'array'],
            'images'           => ['nullable', 'array'],
            'documents'        => ['nullable', 'array'],
            'inspection_notes' => ['nullable', 'string'],
            'is_active'        => ['boolean'],
        ]);

        $validated['company_id'] = $this->company()->id;

        RentalAsset::create($validated);

        return redirect()->route('app.location.assets.index')
            ->with('success', 'Bien ajouté avec succès.');
    }

    public function show(RentalAsset $asset)
    {
        $asset->load(['contracts' => function ($q) {
            $q->with('customer', 'payments')->orderByDesc('start_date');
        }]);

        return inertia('App/Location/Assets/Show', [
            'asset' => $asset,
        ]);
    }

    public function edit(RentalAsset $asset)
    {
        return inertia('App/Location/Assets/Form', [
            'asset' => $asset,
        ]);
    }

    public function update(Request $request, RentalAsset $asset)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'type'             => ['required', 'in:vehicle,real_estate,equipment,other'],
            'daily_rate'       => ['nullable', 'numeric', 'min:0'],
            'monthly_rate'     => ['nullable', 'numeric', 'min:0'],
            'status'           => ['required', 'in:available,rented,maintenance,out_of_service'],
            'characteristics'  => ['nullable', 'array'],
            'images'           => ['nullable', 'array'],
            'documents'        => ['nullable', 'array'],
            'inspection_notes' => ['nullable', 'string'],
            'is_active'        => ['boolean'],
        ]);

        $asset->update($validated);

        return redirect()->route('app.location.assets.index')
            ->with('success', 'Bien mis à jour.');
    }

    public function destroy(RentalAsset $asset)
    {
        if ($asset->contracts()->where('status', 'active')->exists()) {
            return back()->with('error', 'Impossible de supprimer un bien avec un contrat actif.');
        }

        $asset->delete();

        return redirect()->route('app.location.assets.index')
            ->with('success', 'Bien supprimé.');
    }
}
