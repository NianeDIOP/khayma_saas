<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private const KEYS = [
        'app_name',
        'default_currency',
        'trial_duration_days',
        'grace_period_days',
        'data_retention_days',
        'maintenance_mode',
        'support_email',
    ];

    public function index()
    {
        $settings = PlatformSetting::getMany(self::KEYS);

        // Defaults
        $defaults = [
            'app_name'            => 'Khayma',
            'default_currency'    => 'XOF',
            'trial_duration_days' => '7',
            'grace_period_days'   => '3',
            'data_retention_days' => '90',
            'maintenance_mode'    => '0',
            'support_email'       => '',
        ];

        foreach ($defaults as $key => $default) {
            $settings[$key] = $settings[$key] ?? $default;
        }

        return inertia('Admin/Settings/Index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name'            => 'required|string|max:100',
            'default_currency'    => 'required|string|max:10',
            'trial_duration_days' => 'required|integer|min:1|max:90',
            'grace_period_days'   => 'required|integer|min:1|max:30',
            'data_retention_days' => 'required|integer|min:30|max:365',
            'maintenance_mode'    => 'required|in:0,1',
            'support_email'       => 'nullable|email|max:255',
        ]);

        foreach ($validated as $key => $value) {
            PlatformSetting::set($key, (string) $value);
        }

        return back()->with('success', 'Paramètres enregistrés.');
    }
}
