<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var Company $company */
        $company = App::make('currentCompany');
        $company->load(['users', 'modules', 'subscriptions' => fn ($q) => $q->latest()->limit(1)]);

        $stats = [
            'users_count'       => $company->users()->count(),
            'modules_count'     => $company->modules()->count(),
            'subscription'      => $company->subscription_status,
            'trial_ends_at'     => $company->trial_ends_at?->toISOString(),
            'trial_days_left'   => $company->isOnTrial() ? (int) now()->diffInDays($company->trial_ends_at, false) : null,
            'active_plan'       => $company->subscriptions->first()?->plan?->name ?? 'Aucun',
        ];

        // ── KPIs métier ───────────────────────────────────────────
        $salesToday    = $company->sales()
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->sum('total');

        $expensesToday = $company->expenses()
            ->whereDate('date', today())
            ->sum('amount');

        $salesMonth    = $company->sales()
            ->where('status', 'completed')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total');

        $expensesMonth = $company->expenses()
            ->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->sum('amount');

        $kpis = [
            'sales_today'    => (float) $salesToday,
            'expenses_today' => (float) $expensesToday,
            'profit_today'   => (float) ($salesToday - $expensesToday),
            'sales_month'    => (float) $salesMonth,
            'expenses_month' => (float) $expensesMonth,
            'profit_month'   => (float) ($salesMonth - $expensesMonth),
        ];

        // ── Graphique : ventes 7 derniers jours ───────────────────
        $salesLast7 = $company->sales()
            ->where('status', 'completed')
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('SUM(total) as total'))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->toArray();

        // Fill all 7 days (including days with 0 sales)
        $chartDays   = [];
        $chartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $chartDays[]   = now()->subDays($i)->locale('fr')->isoFormat('ddd D');
            $chartValues[] = (float) ($salesLast7[$day] ?? 0);
        }

        // ── Graphique : ventes par mode de paiement (ce mois) ─────
        $paymentData = DB::table('payments')
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->where('sales.company_id', $company->id)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->select('payments.method', DB::raw('SUM(payments.amount) as total'))
            ->groupBy('payments.method')
            ->get();

        $payLabels  = $paymentData->pluck('method')->map(fn ($m) => match ($m) {
            'cash' => 'Cash', 'wave' => 'Wave', 'om' => 'Orange Money',
            'free' => 'Free Money', 'card' => 'Carte', default => ucfirst($m)
        })->toArray();
        $payValues  = $paymentData->pluck('total')->map(fn ($v) => (float) $v)->toArray();

        $recentUsers = $company->users()
            ->orderByPivot('joined_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($u) => [
                'id'        => $u->id,
                'name'      => $u->name,
                'email'     => $u->email,
                'role'      => $u->pivot->role,
                'joined_at' => $u->pivot->joined_at,
            ]);

        $activeModules = $company->modules()
            ->where('is_active', true)
            ->get()
            ->map(fn ($m) => [
                'id'   => $m->id,
                'name' => $m->name,
                'code' => $m->code,
                'icon' => $m->icon,
            ]);

        return inertia('App/Dashboard', [
            'stats'         => $stats,
            'kpis'          => $kpis,
            'chartDays'     => $chartDays,
            'chartValues'   => $chartValues,
            'payLabels'     => $payLabels,
            'payValues'     => $payValues,
            'recentUsers'   => $recentUsers,
            'activeModules' => $activeModules,
        ]);
    }
}
