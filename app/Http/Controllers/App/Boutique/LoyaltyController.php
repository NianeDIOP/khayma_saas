<?php

namespace App\Http\Controllers\App\Boutique;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\LoyaltyConfig;
use App\Models\LoyaltyTier;
use App\Models\LoyaltyTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LoyaltyController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $company = $this->company();
        $config  = LoyaltyConfig::firstOrCreate(
            ['company_id' => $company->id],
            [
                'points_per_amount'    => 1,
                'amount_per_point'     => 1000,
                'redemption_threshold' => 100,
                'redemption_value'     => 2000,
                'is_active'            => false,
            ]
        );

        $query = LoyaltyTransaction::forCompany($company->id)->with('customer', 'sale');

        if ($search = $request->input('search')) {
            $query->whereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $transactions = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Top customers by points
        $topCustomers = $company->customers()
            ->where('loyalty_points', '>', 0)
            ->orderByDesc('loyalty_points')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'loyalty_points']);

        // Stats
        $totalEarned   = LoyaltyTransaction::forCompany($company->id)->where('type', 'earn')->sum('points');
        $totalRedeemed = LoyaltyTransaction::forCompany($company->id)->where('type', 'redeem')->sum('points');

        return inertia('App/Boutique/Loyalty/Index', [
            'config'        => $config,
            'transactions'  => $transactions,
            'topCustomers'  => $topCustomers,
            'totalEarned'   => $totalEarned,
            'totalRedeemed' => $totalRedeemed,
            'tiers'         => LoyaltyTier::where('company_id', $company->id)->orderBy('min_points')->get(),
            'filters'       => $request->only(['search', 'type']),
        ]);
    }

    public function updateConfig(Request $request)
    {
        $validated = $request->validate([
            'points_per_amount'    => ['required', 'integer', 'min:1'],
            'amount_per_point'     => ['required', 'numeric', 'min:1'],
            'redemption_threshold' => ['required', 'integer', 'min:1'],
            'redemption_value'     => ['required', 'numeric', 'min:1'],
            'is_active'            => ['boolean'],
        ]);

        LoyaltyConfig::updateOrCreate(
            ['company_id' => $this->company()->id],
            $validated
        );

        return redirect()->route('app.boutique.loyalty.index')
            ->with('success', 'Configuration fidélité mise à jour.');
    }

    // ── Tier Management ───────────────────────────────────────

    public function storeTier(Request $request)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:50'],
            'min_points'       => ['required', 'integer', 'min:0'],
            'bonus_multiplier' => ['required', 'numeric', 'min:1', 'max:10'],
        ]);

        $company = $this->company();
        $maxSort = LoyaltyTier::where('company_id', $company->id)->max('sort_order') ?? 0;

        LoyaltyTier::create([
            'company_id'       => $company->id,
            'name'             => $validated['name'],
            'min_points'       => $validated['min_points'],
            'bonus_multiplier' => $validated['bonus_multiplier'],
            'sort_order'       => $maxSort + 1,
        ]);

        return redirect()->route('app.boutique.loyalty.index')
            ->with('success', "Palier « {$validated['name']} » créé.");
    }

    public function destroyTier(LoyaltyTier $tier)
    {
        if ((int) $tier->company_id !== (int) $this->company()->id) {
            abort(403);
        }

        $tier->delete();

        return redirect()->route('app.boutique.loyalty.index')
            ->with('success', 'Palier supprimé.');
    }
}
