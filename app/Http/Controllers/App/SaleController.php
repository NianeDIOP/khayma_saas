<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\Payment;
use App\Mail\InvoiceMail;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SaleController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = $this->company()->sales()->with('customer');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        $sales = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return inertia('App/Sales/Index', [
            'sales'   => $sales,
            'filters' => $request->only(['search', 'status', 'payment_status']),
        ]);
    }

    public function create()
    {
        $products  = $this->company()->products()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'selling_price', 'barcode']);
        $customers = $this->company()->customers()->orderBy('name')->get(['id', 'name']);

        return inertia('App/Sales/Create', [
            'products'  => $products,
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'        => ['nullable', 'integer', 'exists:customers,id'],
            'type'               => ['nullable', 'in:counter,delivery,table,takeaway'],
            'discount_amount'    => ['nullable', 'numeric', 'min:0'],
            'tax_amount'         => ['nullable', 'numeric', 'min:0'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount'   => ['nullable', 'numeric', 'min:0'],
            'payment_method'     => ['nullable', 'in:cash,wave,om,card,other'],
        ]);

        $company = $this->company();

        $sale = DB::transaction(function () use ($validated, $company) {
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $discount = $item['discount'] ?? 0;
                $subtotal += ($item['unit_price'] * $item['quantity']) - $discount;
            }

            $discountAmount = $validated['discount_amount'] ?? 0;
            $taxAmount      = $validated['tax_amount'] ?? 0;
            $total          = $subtotal - $discountAmount + $taxAmount;

            $sale = Sale::create([
                'company_id'      => $company->id,
                'customer_id'     => $validated['customer_id'] ?? null,
                'user_id'         => auth()->id(),
                'reference'       => 'VNT-' . strtoupper(uniqid()),
                'type'            => $validated['type'] ?? 'counter',
                'status'          => 'completed',
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount'      => $taxAmount,
                'total'           => $total,
                'payment_status'  => 'paid',
                'notes'           => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $discount  = $item['discount'] ?? 0;
                $lineTotal = ($item['unit_price'] * $item['quantity']) - $discount;

                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'company_id' => $company->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount'   => $discount,
                    'total'      => $lineTotal,
                ]);

                // Decrement stock from default depot
                $defaultDepot = $company->depots()->where('is_default', true)->first();
                if ($defaultDepot) {
                    $stockItem = StockItem::firstOrCreate(
                        ['product_id' => $item['product_id'], 'depot_id' => $defaultDepot->id],
                        ['company_id' => $company->id, 'quantity' => 0]
                    );
                    $stockItem->decrement('quantity', $item['quantity']);

                    StockMovement::create([
                        'company_id' => $company->id,
                        'product_id' => $item['product_id'],
                        'depot_id'   => $defaultDepot->id,
                        'type'       => 'out',
                        'quantity'   => $item['quantity'],
                        'unit_cost'  => $item['unit_price'],
                        'reference'  => $sale->reference,
                        'user_id'    => auth()->id(),
                    ]);
                }
            }

            Payment::create([
                'company_id' => $company->id,
                'sale_id'    => $sale->id,
                'amount'     => $total,
                'method'     => $validated['payment_method'] ?? 'cash',
            ]);

            return $sale;
        });

        $sale->loadMissing(['company', 'customer']);

        if (!empty($sale->customer?->email)) {
            Mail::to($sale->customer->email)->queue(new InvoiceMail($sale));
        }

        if (!empty($sale->customer?->phone)) {
            app(SmsService::class)->send(
                $sale->customer->phone,
                'Khayma: vente ' . $sale->reference . ' enregistree. Total: ' . number_format((float) $sale->total, 0, ',', ' ') . ' XOF.'
            );
        }

        return redirect()->route('app.sales.show', ['sale' => $sale->id, '_tenant' => $company->slug])
                         ->with('success', 'Vente enregistrée avec succès.');
    }

    public function show(int $id)
    {
        $sale = $this->company()->sales()
            ->with(['customer', 'items.product', 'payments', 'user'])
            ->findOrFail($id);

        return inertia('App/Sales/Show', ['sale' => $sale]);
    }

    public function destroy(int $id)
    {
        $sale = $this->company()->sales()->findOrFail($id);
        $sale->update(['status' => 'cancelled']);
        $sale->delete();

        return redirect()->route('app.sales.index', ['_tenant' => $this->company()->slug])
                         ->with('success', 'Vente annulée.');
    }
}
