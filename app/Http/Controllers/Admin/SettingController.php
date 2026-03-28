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
        // Email / SMTP
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_from_address',
        'mail_from_name',
        // SMS
        'sms_provider',
        'sms_api_url',
        'sms_api_token',
        'sms_from',
        // PayDunya
        'paydunya_mode',
        'paydunya_env',
        'paydunya_master_key',
        'paydunya_private_key',
        'paydunya_token',
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
            // Email / SMTP
            'mail_mailer'         => 'log',
            'mail_host'           => '',
            'mail_port'           => '587',
            'mail_username'       => '',
            'mail_password'       => '',
            'mail_from_address'   => '',
            'mail_from_name'      => 'Khayma',
            // SMS
            'sms_provider'        => 'log',
            'sms_api_url'         => '',
            'sms_api_token'       => '',
            'sms_from'            => 'KHAYMA',
            // PayDunya
            'paydunya_mode'        => 'log',
            'paydunya_env'         => 'test',
            'paydunya_master_key'  => '',
            'paydunya_private_key' => '',
            'paydunya_token'       => '',
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
            // Email / SMTP
            'mail_mailer'         => 'required|in:log,smtp,sendmail',
            'mail_host'           => 'nullable|string|max:255',
            'mail_port'           => 'nullable|integer|min:1|max:65535',
            'mail_username'       => 'nullable|string|max:255',
            'mail_password'       => 'nullable|string|max:255',
            'mail_from_address'   => 'nullable|email|max:255',
            'mail_from_name'      => 'nullable|string|max:100',
            // SMS
            'sms_provider'        => 'required|in:log,fake,api',
            'sms_api_url'         => 'nullable|url|max:500',
            'sms_api_token'       => 'nullable|string|max:255',
            'sms_from'            => 'nullable|string|max:20',
            // PayDunya
            'paydunya_mode'        => 'required|in:log,fake,api',
            'paydunya_env'         => 'required|in:test,live',
            'paydunya_master_key'  => 'nullable|string|max:255',
            'paydunya_private_key' => 'nullable|string|max:255',
            'paydunya_token'       => 'nullable|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            PlatformSetting::set($key, (string) $value);
        }

        return back()->with('success', 'Paramètres enregistrés.');
    }
}
