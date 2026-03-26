<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SettingsController extends Controller
{
    public function index()
    {
        /** @var Company $company */
        $company = App::make('currentCompany');

        return inertia('App/Settings', [
            'company' => [
                'id'              => $company->id,
                'name'            => $company->name,
                'slug'            => $company->slug,
                'email'           => $company->email,
                'phone'           => $company->phone,
                'address'         => $company->address,
                'sector'          => $company->sector,
                'ninea'           => $company->ninea,
                'currency'        => $company->currency,
                'timezone'        => $company->timezone,
                'primary_color'   => $company->primary_color,
                'secondary_color' => $company->secondary_color,
            ],
        ]);
    }

    public function update(Request $request)
    {
        /** @var Company $company */
        $company = App::make('currentCompany');

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['nullable', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'address'         => ['nullable', 'string', 'max:500'],
            'sector'          => ['nullable', 'string', 'max:60'],
            'ninea'           => ['nullable', 'string', 'max:30'],
            'currency'        => ['required', 'string', 'size:3'],
            'timezone'        => ['required', 'string', 'max:60'],
            'primary_color'   => ['nullable', 'string', 'max:10'],
            'secondary_color' => ['nullable', 'string', 'max:10'],
        ]);

        $company->update($validated);

        return back()->with('success', 'Paramètres mis à jour.');
    }
}
