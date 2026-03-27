<?php

namespace App\Http\Controllers\App\Quincaillerie;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\SupplierPayment;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SupplierPaymentController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = $this->company()->supplierPayments()->with(['supplier', 'purchaseOrder', 'user']);

        if ($supplierId = $request->input('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->where('date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->where('date', '<=', $dateTo);
        }

        $payments = $query->orderByDesc('date')->paginate(20)->withQueryString();

        $suppliers = $this->company()->suppliers()->orderBy('name')->get(['id', 'name', 'outstanding_balance']);

        return inertia('App/Quincaillerie/SupplierPayments/Index', [
            'payments'  => $payments,
            'suppliers' => $suppliers,
            'filters'   => $request->only(['supplier_id', 'date_from', 'date_to']),
        ]);
    }

    public function create()
    {
        $company = $this->company();

        return inertia('App/Quincaillerie/SupplierPayments/Form', [
            'payment'        => null,
            'suppliers'      => $company->suppliers()->orderBy('name')->get(['id', 'name', 'outstanding_balance']),
            'purchaseOrders' => $company->purchaseOrders()
                ->whereIn('status', ['sent', 'partial', 'received'])
                ->with('supplier:id,name')
                ->orderByDesc('created_at')
                ->get(['id', 'reference', 'supplier_id', 'total']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'       => ['required', 'integer', 'exists:suppliers,id'],
            'purchase_order_id' => ['nullable', 'integer', 'exists:purchase_orders,id'],
            'amount'            => ['required', 'numeric', 'min:0.01'],
            'method'            => ['required', 'in:cash,wave,om,bank,other'],
            'reference'         => ['nullable', 'string', 'max:100'],
            'notes'             => ['nullable', 'string', 'max:1000'],
            'date'              => ['required', 'date'],
        ]);

        $company = $this->company();

        $company->supplierPayments()->create([
            'supplier_id'       => $validated['supplier_id'],
            'purchase_order_id' => $validated['purchase_order_id'] ?? null,
            'user_id'           => auth()->id(),
            'amount'            => $validated['amount'],
            'method'            => $validated['method'],
            'reference'         => $validated['reference'] ?? null,
            'notes'             => $validated['notes'] ?? null,
            'date'              => $validated['date'],
        ]);

        // Update supplier outstanding balance
        $supplier = Supplier::find($validated['supplier_id']);
        $supplier->decrement('outstanding_balance', $validated['amount']);

        return redirect()->route('app.quincaillerie.supplier-payments.index')
            ->with('success', 'Paiement enregistré.');
    }
}
