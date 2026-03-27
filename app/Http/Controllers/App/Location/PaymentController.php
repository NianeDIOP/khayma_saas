<?php

namespace App\Http\Controllers\App\Location;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\RentalPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class PaymentController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = RentalPayment::forCompany($this->company()->id)
            ->with('rentalContract.rentalAsset', 'rentalContract.customer');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->whereHas('rentalContract', function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $payments = $query->orderBy('due_date')->paginate(20)->withQueryString();

        return inertia('App/Location/Payments/Index', [
            'payments' => $payments,
            'filters'  => $request->only(['search', 'status']),
        ]);
    }

    public function recordPayment(Request $request, RentalPayment $payment)
    {
        $validated = $request->validate([
            'amount_paid'  => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'method'       => ['required', 'string', 'in:cash,wave,om,card,bank_transfer,other'],
            'reference'    => ['nullable', 'string', 'max:255'],
            'notes'        => ['nullable', 'string'],
        ]);

        $newPaid = (float) $payment->amount_paid + (float) $validated['amount_paid'];
        $amount  = (float) $payment->amount;

        $status = 'partial';
        if ($newPaid >= $amount) {
            $status  = 'paid';
            $newPaid = $amount;
        }

        $payment->update([
            'amount_paid'  => $newPaid,
            'payment_date' => $validated['payment_date'],
            'method'       => $validated['method'],
            'reference'    => $validated['reference'] ?? $payment->reference,
            'notes'        => $validated['notes'] ?? $payment->notes,
            'status'       => $status,
        ]);

        return back()->with('success', 'Paiement enregistré.');
    }

    public function markOverdue()
    {
        $company = $this->company();

        $updated = RentalPayment::forCompany($company->id)
            ->where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        return back()->with('success', "{$updated} paiement(s) marqué(s) en retard.");
    }
}
