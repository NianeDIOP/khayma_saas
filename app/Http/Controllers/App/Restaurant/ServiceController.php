<?php

namespace App\Http\Controllers\App\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ServiceController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index()
    {
        $services = $this->company()->services()
            ->withCount('orders')
            ->orderBy('start_time')
            ->get();

        return inertia('App/Restaurant/Services/Index', [
            'services' => $services,
        ]);
    }

    public function create()
    {
        return inertia('App/Restaurant/Services/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $this->company()->services()->create($validated);

        return redirect()->route('app.restaurant.services.index')
            ->with('success', 'Service créé.');
    }

    public function edit(Service $service)
    {
        return inertia('App/Restaurant/Services/Form', [
            'service' => $service,
        ]);
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate($this->rules());
        $service->update($validated);

        return redirect()->route('app.restaurant.services.index')
            ->with('success', 'Service modifié.');
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('app.restaurant.services.index')
            ->with('success', 'Service supprimé.');
    }

    private function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
            'is_active'  => ['nullable', 'boolean'],
        ];
    }
}
