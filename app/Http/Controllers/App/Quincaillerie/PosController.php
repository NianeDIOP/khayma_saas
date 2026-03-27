<?php

namespace App\Http\Controllers\App\Quincaillerie;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index()
    {
        $company = $this->company();

        $products = $company->products()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($company) {
                $defaultDepot = $company->depots()->where('is_default', true)->first();
                if ($defaultDepot) {
                    $stockItem = StockItem::where('product_id', $product->id)
                        ->where('depot_id', $defaultDepot->id)->first();
                    $product->stock_qty = $stockItem ? $stockItem->quantity : 0;
                }
                return $product;
            });

        $customers  = $company->customers()->orderBy('name')->get(['id', 'name', 'phone', 'outstanding_balance']);
        $depots     = $company->depots()->orderBy('name')->get(['id', 'name', 'is_default']);
        $categories = $company->categories()
            ->where('module', 'general')
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        return inertia('App/Quincaillerie/Pos/Index', [
            'products'   => $products,
            'customers'  => $customers,
            'depots'     => $depots,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'         => ['nullable', 'integer', 'exists:customers,id'],
            'depot_id'            => ['nullable', 'integer', 'exists:depots,id'],
            'discount_amount'     => ['nullable', 'numeric', 'min:0'],
            'tax_amount'          => ['nullable', 'numeric', 'min:0'],
            'notes'               => ['nullable', 'string', 'max:1000'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.product_id'  => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
            'items.*.discount'    => ['nullable', 'numeric', 'min:0'],
            'payment_method'      => ['required', 'in:cash,wave,om,card,other,credit'],
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
            $total = max(0, $subtotal - $discountAmount + $taxAmount);

            $depotId = $validated['depot_id'] ?? $company->depots()->where('is_default', true)->value('id');

            $sale = Sale::create([
                'company_id'      => $company->id,
                'customer_id'     => $validated['customer_id'] ?? null,
                'user_id'         => auth()->id(),
                'depot_id'        => $depotId,
                'reference'       => 'QNC-' . strtoupper(uniqid()),
                'type'            => 'counter',
                'status'          => 'completed',
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount'      => $taxAmount,
                'total'           => $total,
                'payment_status'  => $validated['payment_method'] === 'credit' ? 'unpaid' : 'paid',
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

                if ($depotId) {
                    $stockItem = StockItem::firstOrCreate(
                        ['product_id' => $item['product_id'], 'depot_id' => $depotId],
                        ['company_id' => $company->id, 'quantity' => 0]
                    );
                    $stockItem->decrement('quantity', $item['quantity']);

                    StockMovement::create([
                        'company_id' => $company->id,
                        'product_id' => $item['product_id'],
                        'depot_id'   => $depotId,
                        'type'       => 'out',
                        'quantity'   => $item['quantity'],
                        'unit_cost'  => $item['unit_price'],
                        'reference'  => $sale->reference,
                        'user_id'    => auth()->id(),
                    ]);
                }
            }

            if ($validated['payment_method'] !== 'credit') {
                Payment::create([
                    'company_id' => $company->id,
                    'sale_id'    => $sale->id,
                    'amount'     => $total,
                    'method'     => $validated['payment_method'],
                ]);
            } else {
                if ($sale->customer_id) {
                    Customer::where('id', $sale->customer_id)
                        ->increment('outstanding_balance', $total);
                }
            }

            return $sale;
        });

        return redirect()
            ->route('app.quincaillerie.pos.receipt', ['sale' => $sale->id, '_tenant' => $company->slug])
            ->with('success', 'Vente enregistrée.');
    }

    public function receipt(int $saleId)
    {
        $sale = $this->company()->sales()
            ->with(['customer', 'items.product', 'payments', 'user'])
            ->findOrFail($saleId);

        return inertia('App/Quincaillerie/Pos/Receipt', ['sale' => $sale]);
    }
}
