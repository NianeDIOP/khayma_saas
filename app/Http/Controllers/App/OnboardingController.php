<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Company;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    public function index()
    {
        /** @var Company $company */
        $company = App::make('currentCompany');

        return inertia('App/Onboarding', [
            'company' => [
                'name'    => $company->name,
                'email'   => $company->email,
                'phone'   => $company->phone,
                'address' => $company->address,
                'sector'  => $company->sector,
                'ninea'   => $company->ninea,
                'logo_url' => $company->logo_url,
            ],
            'steps' => $this->computeSteps($company),
        ]);
    }

    public function update(Request $request)
    {
        /** @var Company $company */
        $company = App::make('currentCompany');

        $validated = $request->validate([
            'email'   => ['nullable', 'email', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'sector'  => ['nullable', 'string', 'max:60'],
            'ninea'   => ['nullable', 'string', 'max:30'],
        ]);

        $company->update($validated);

        $user = auth()->user();

        if ($user && !empty($user->email)) {
            Mail::to($user->email)->queue(new WelcomeMail($user, $company->fresh()));
        }

        if ($user && !empty($company->phone)) {
            app(SmsService::class)->send(
                $company->phone,
                'Bienvenue sur Khayma. Votre entreprise ' . $company->name . ' est configuree.'
            );
        }

        return back()->with('success', 'Informations mises à jour.');
    }

    private function computeSteps(Company $company): array
    {
        return [
            ['label' => 'Profil entreprise',  'done' => filled($company->email) && filled($company->phone)],
            ['label' => 'Adresse',            'done' => filled($company->address)],
            ['label' => 'Secteur d\'activité', 'done' => filled($company->sector)],
            ['label' => 'Module activé',       'done' => $company->modules()->exists()],
        ];
    }
}
