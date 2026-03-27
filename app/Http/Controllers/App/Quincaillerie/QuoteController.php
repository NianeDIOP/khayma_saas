<?php

namespace App\Http\Controllers\App\Quincaillerie;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Quote;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = $this->company()->quotes()->with(['customer', 'user']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $quotes = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return inertia('App/Quincaillerie/Quotes/Index', [
            'quotes'  => $quotes,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        $company = $this->company();

        return inertia('App/Quincaillerie/Quotes/Form', [
            'quote'     => null,
            'products'  => $company->products()->where('is_active', true)->with('unit')->orderBy('name')->get(),
            'customers' => $company->customers()->orderBy('name')->get(['id', 'name']),
            'units'     => $company->units()->orderBy('name')->get(['id', 'name', 'symbol']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'           => ['nullable', 'integer', 'exists:customers,id'],
            'discount_amount'       => ['nullable', 'numeric', 'min:0'],
            'notes'                 => ['nullable', 'string', 'max:2000'],
            'valid_until'           => ['nullable', 'date', 'after:today'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'integer', 'exists:products,id'],
            'items.*.unit_id'       => ['nullable', 'integer', 'exists:units,id'],
            'items.*.quantity'      => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'    => ['required', 'numeric', 'min:0'],
            'items.*.discount'      => ['nullable', 'numeric', 'min:0'],
        ]);

        $company = $this->company();

        $quote = DB::transaction(function () use ($validated, $company) {
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $discount = $item['discount'] ?? 0;
                $subtotal += ($item['unit_price'] * $item['quantity']) - $discount;
            }

            $discountAmount = $validated['discount_amount'] ?? 0;
            $total = $subtotal - $discountAmount;

            $quote = $company->quotes()->create([
                'customer_id'     => $validated['customer_id'] ?? null,
                'user_id'         => auth()->id(),
                'reference'       => Quote::generateReference($company->id),
                'status'          => 'draft',
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'total'           => $total,
                'notes'           => $validated['notes'] ?? null,
                'valid_until'     => $validated['valid_until'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $discount = $item['discount'] ?? 0;
                $lineTotal = ($item['unit_price'] * $item['quantity']) - $discount;

                $quote->items()->create([
                    'product_id' => $item['product_id'],
                    'unit_id'    => $item['unit_id'] ?? null,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount'   => $discount,
                    'total'      => $lineTotal,
                ]);
            }

            return $quote;
        });

        return redirect()->route('app.quincaillerie.quotes.show', $quote)
            ->with('success', 'Devis créé.');
    }

    public function show(Quote $quote)
    {
        $quote->load(['customer', 'user', 'items.product', 'items.unit', 'convertedSale']);

        return inertia('App/Quincaillerie/Quotes/Show', [
            'quote' => $quote,
        ]);
    }

    public function edit(Quote $quote)
    {
        if (!in_array($quote->status, ['draft', 'sent'])) {
            return back()->with('error', 'Ce devis ne peut plus être modifié.');
        }

        $company = $this->company();
        $quote->load('items');

        return inertia('App/Quincaillerie/Quotes/Form', [
            'quote'     => $quote,
            'products'  => $company->products()->where('is_active', true)->with('unit')->orderBy('name')->get(),
            'customers' => $company->customers()->orderBy('name')->get(['id', 'name']),
            'units'     => $company->units()->orderBy('name')->get(['id', 'name', 'symbol']),
        ]);
    }

    public function update(Request $request, Quote $quote)
    {
        if (!in_array($quote->status, ['draft', 'sent'])) {
            return back()->with('error', 'Ce devis ne peut plus être modifié.');
        }

        $validated = $request->validate([
            'customer_id'           => ['nullable', 'integer', 'exists:customers,id'],
            'discount_amount'       => ['nullable', 'numeric', 'min:0'],
            'notes'                 => ['nullable', 'string', 'max:2000'],
            'valid_until'           => ['nullable', 'date'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'integer', 'exists:products,id'],
            'items.*.unit_id'       => ['nullable', 'integer', 'exists:units,id'],
            'items.*.quantity'      => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'    => ['required', 'numeric', 'min:0'],
            'items.*.discount'      => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $quote) {
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $discount = $item['discount'] ?? 0;
                $subtotal += ($item['unit_price'] * $item['quantity']) - $discount;
            }

            $discountAmount = $validated['discount_amount'] ?? 0;
            $total = $subtotal - $discountAmount;

            $quote->update([
                'customer_id'     => $validated['customer_id'] ?? null,
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'total'           => $total,
                'notes'           => $validated['notes'] ?? null,
                'valid_until'     => $validated['valid_until'] ?? null,
            ]);

            $quote->items()->delete();

            foreach ($validated['items'] as $item) {
                $discount = $item['discount'] ?? 0;
                $lineTotal = ($item['unit_price'] * $item['quantity']) - $discount;

                $quote->items()->create([
                    'product_id' => $item['product_id'],
                    'unit_id'    => $item['unit_id'] ?? null,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount'   => $discount,
                    'total'      => $lineTotal,
                ]);
            }
        });

        return redirect()->route('app.quincaillerie.quotes.show', $quote)
            ->with('success', 'Devis mis à jour.');
    }

    public function updateStatus(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:sent,accepted,rejected'],
        ]);

        $allowed = [
            'draft'    => ['sent'],
            'sent'     => ['accepted', 'rejected'],
        ];

        if (!in_array($validated['status'], $allowed[$quote->status] ?? [])) {
            return back()->with('error', 'Changement de statut non autorisé.');
        }

        $quote->update(['status' => $validated['status']]);

        return back()->with('success', 'Statut du devis mis à jour.');
    }

    public function convert(Quote $quote)
    {
        if ($quote->status !== 'accepted') {
            return back()->with('error', 'Seul un devis accepté peut être converti.');
        }

        $company = $this->company();

        $sale = DB::transaction(function () use ($quote, $company) {
            $sale = Sale::create([
                'company_id'      => $company->id,
                'customer_id'     => $quote->customer_id,
                'user_id'         => auth()->id(),
                'reference'       => 'VNT-' . strtoupper(uniqid()),
                'type'            => 'counter',
                'status'          => 'completed',
                'subtotal'        => $quote->subtotal,
                'discount_amount' => $quote->discount_amount,
                'tax_amount'      => 0,
                'total'           => $quote->total,
                'payment_status'  => 'unpaid',
            ]);

            foreach ($quote->items as $item) {
                SaleItem::create([
                    'company_id' => $company->id,
                    'sale_id'    => $sale->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount'   => $item->discount,
                    'total'      => $item->total,
                ]);

                // Decrease stock
                $stockItem = StockItem::where('product_id', $item->product_id)
                    ->whereHas('depot', fn ($q) => $q->where('company_id', $company->id))
                    ->first();

                if ($stockItem) {
                    $stockItem->decrement('quantity', $item->quantity);

                    StockMovement::create([
                        'company_id' => $company->id,
                        'product_id' => $item->product_id,
                        'depot_id'   => $stockItem->depot_id,
                        'type'       => 'sale',
                        'quantity'   => -$item->quantity,
                        'unit_cost'  => $item->unit_price,
                        'reference'  => $sale->reference,
                        'user_id'    => auth()->id(),
                    ]);
                }
            }

            $quote->update([
                'status'            => 'converted',
                'converted_sale_id' => $sale->id,
            ]);

            return $sale;
        });

        return redirect()->route('app.quincaillerie.quotes.show', $quote)
            ->with('success', 'Devis converti en vente #' . $sale->reference);
    }

    public function destroy(Quote $quote)
    {
        if (!in_array($quote->status, ['draft'])) {
            return back()->with('error', 'Seul un brouillon peut être supprimé.');
        }

        $quote->items()->delete();
        $quote->delete();

        return redirect()->route('app.quincaillerie.quotes.index')
            ->with('success', 'Devis supprimé.');
    }
}
