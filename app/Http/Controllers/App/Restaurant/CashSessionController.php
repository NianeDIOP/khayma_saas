<?php

namespace App\Http\Controllers\App\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\CashSession;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class CashSessionController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index()
    {
        $sessions = $this->company()->cashSessions()
            ->with(['service', 'user'])
            ->orderByDesc('opened_at')
            ->paginate(20);

        $openSession = $this->company()->cashSessions()
            ->whereNull('closed_at')
            ->with('service')
            ->first();

        return inertia('App/Restaurant/CashSessions/Index', [
            'sessions'    => $sessions,
            'openSession' => $openSession,
        ]);
    }

    public function open(Request $request)
    {
        $validated = $request->validate([
            'service_id'     => ['nullable', 'integer', 'exists:services,id'],
            'opening_amount' => ['required', 'numeric', 'min:0'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        // Check if there's already an open session
        $existing = $this->company()->cashSessions()->whereNull('closed_at')->first();
        if ($existing) {
            return back()->withErrors(['session' => 'Une caisse est déjà ouverte. Fermez-la d\'abord.']);
        }

        $this->company()->cashSessions()->create([
            'service_id'     => $validated['service_id'] ?? null,
            'user_id'        => Auth::id(),
            'opened_at'      => now(),
            'opening_amount' => $validated['opening_amount'],
            'notes'          => $validated['notes'] ?? null,
        ]);

        return redirect()->route('app.restaurant.cash-sessions.index')
            ->with('success', 'Caisse ouverte.');
    }

    public function close(Request $request, CashSession $cashSession)
    {
        if ($cashSession->closed_at) {
            return back()->withErrors(['session' => 'Cette caisse est déjà fermée.']);
        }

        $validated = $request->validate([
            'closing_amount' => ['required', 'numeric', 'min:0'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        // Calculate expected amount: opening + orders paid during session
        $orderTotal = $cashSession->orders()
            ->where('payment_status', 'paid')
            ->sum('total');

        $cashSession->update([
            'closed_at'       => now(),
            'closing_amount'  => $validated['closing_amount'],
            'expected_amount' => $cashSession->opening_amount + $orderTotal,
            'notes'           => $validated['notes'] ?? $cashSession->notes,
        ]);

        return redirect()->route('app.restaurant.cash-sessions.index')
            ->with('success', 'Caisse fermée.');
    }
}
