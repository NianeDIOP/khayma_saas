<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;

class LegalPageController extends Controller
{
    private const KEYS = [
        'legal_privacy'  => 'Politique de confidentialité',
        'legal_terms'    => 'Conditions Générales d\'Utilisation (CGU)',
        'legal_sales'    => 'Conditions Générales de Vente (CGV)',
        'legal_mentions' => 'Mentions légales',
        'legal_cookies'  => 'Politique de cookies',
    ];

    public function index()
    {
        $pages = PlatformSetting::getMany(array_keys(self::KEYS));

        $items = [];
        foreach (self::KEYS as $key => $label) {
            $items[] = [
                'key'   => $key,
                'label' => $label,
                'value' => $pages[$key] ?? '',
            ];
        }

        return inertia('Admin/LegalPages/Index', ['pages' => $items]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'pages'         => 'required|array',
            'pages.*.key'   => 'required|string|in:' . implode(',', array_keys(self::KEYS)),
            'pages.*.value' => 'nullable|string|max:100000',
        ]);

        foreach ($validated['pages'] as $page) {
            PlatformSetting::set($page['key'], $page['value'] ?? '');
        }

        return back()->with('success', 'Pages légales enregistrées.');
    }
}
