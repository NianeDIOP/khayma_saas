<?php

namespace App\Http\Controllers\App\Quincaillerie;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Sale;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CreditController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $company = $this->company();

        $query = Sale::forCompany($company->id)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->with(['customer', 'payments']);

        if ($customerId = $request->input('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        $credits = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Summary per customer
        $customerDebts = Sale::forCompany($company->id)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('customer_id')
            ->selectRaw('customer_id, SUM(total) as total_debt, SUM(COALESCE((SELECT SUM(amount) FROM payments WHERE payments.sale_id = sales.id), 0)) as total_paid')
            ->groupBy('customer_id')
            ->with('customer:id,name,phone')
            ->get()
            ->map(function ($row) {
                return [
                    'customer'   => $row->customer,
                    'total_debt' => $row->total_debt,
                    'total_paid' => $row->total_paid,
                    'remaining'  => $row->total_debt - $row->total_paid,
                ];
            });

        $customers = $company->customers()->orderBy('name')->get(['id', 'name']);

        return inertia('App/Quincaillerie/Credits/Index', [
            'credits'       => $credits,
            'customerDebts' => $customerDebts,
            'customers'     => $customers,
            'filters'       => $request->only(['customer_id']),
        ]);
    }

    public function addPayment(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'amount'  => ['required', 'numeric', 'min:0.01'],
            'method'  => ['required', 'in:cash,wave,om,card,other'],
            'notes'   => ['nullable', 'string', 'max:500'],
        ]);

        Payment::create([
            'company_id' => $this->company()->id,
            'sale_id'    => $sale->id,
            'amount'     => $validated['amount'],
            'method'     => $validated['method'],
            'notes'      => $validated['notes'] ?? null,
        ]);

        // Update payment status
        $totalPaid = $sale->payments()->sum('amount');
        $sale->update([
            'payment_status' => $totalPaid >= $sale->total ? 'paid' : 'partial',
        ]);

        return back()->with('success', 'Paiement enregistré.');
    }
}
