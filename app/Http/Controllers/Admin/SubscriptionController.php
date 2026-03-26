<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::with(['company', 'plan', 'module'])
            ->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $query->whereHas('company', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($period = $request->input('period')) {
            $query->where('billing_period', $period);
        }

        $subscriptions = $query->paginate(20)->withQueryString();

        $stats = [
            'total'    => Subscription::count(),
            'active'   => Subscription::where('status', 'active')->count(),
            'revenue'  => Subscription::where('status', 'active')->sum('amount_paid'),
        ];

        return inertia('Admin/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'filters'       => $request->only('search', 'status', 'period'),
            'stats'         => $stats,
        ]);
    }

    public function export(Request $request)
    {
        $query = Subscription::with(['company', 'plan', 'module'])
            ->orderByDesc('created_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $subscriptions = $query->get();

        $csv = "ID,Entreprise,Plan,Module,Statut,Période,Montant,Date début,Date fin,Référence\n";
        foreach ($subscriptions as $s) {
            $csv .= implode(',', [
                $s->id,
                '"' . str_replace('"', '""', $s->company->name ?? '') . '"',
                '"' . ($s->plan->name ?? '') . '"',
                '"' . ($s->module->name ?? '') . '"',
                $s->status,
                $s->billing_period,
                $s->amount_paid,
                $s->starts_at?->format('Y-m-d'),
                $s->ends_at?->format('Y-m-d'),
                '"' . ($s->payment_reference ?? '') . '"',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="abonnements_' . date('Y-m-d') . '.csv"',
        ]);
    }
}
