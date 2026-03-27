<?php

namespace App\Http\Controllers\App\Boutique;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Models\LoyaltyConfig;
use App\Models\LoyaltyTransaction;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\VariantStockItem;
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
            ->with(['category', 'variants' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($company) {
                $promo = $product->promotions()->active()->first();
                $product->promo_price = $promo ? $promo->getDiscountedPrice($product->selling_price) : null;
                $product->promo_label = $promo ? $promo->name : null;

                // Stock from selected depot (default)
                $defaultDepot = $company->depots()->where('is_default', true)->first();
                if ($defaultDepot) {
                    $stockItem = StockItem::where('product_id', $product->id)
                        ->where('depot_id', $defaultDepot->id)->first();
                    $product->stock_qty = $stockItem ? $stockItem->quantity : 0;
                }

                return $product;
            });

        $customers = $company->customers()->orderBy('name')->get(['id', 'name', 'phone', 'loyalty_points']);
        $depots    = $company->depots()->orderBy('name')->get(['id', 'name', 'is_default']);
        $categories = $company->categories()->where('module', 'general')->whereNull('parent_id')->with('children')->orderBy('name')->get(['id', 'name', 'parent_id']);
        $loyaltyConfig = LoyaltyConfig::where('company_id', $company->id)->where('is_active', true)->first();

        return inertia('App/Boutique/Pos/Index', [
            'products'      => $products,
            'customers'     => $customers,
            'depots'        => $depots,
            'categories'    => $categories,
            'loyaltyConfig' => $loyaltyConfig,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'             => ['nullable', 'integer', 'exists:customers,id'],
            'depot_id'                => ['nullable', 'integer', 'exists:depots,id'],
            'discount_amount'         => ['nullable', 'numeric', 'min:0'],
            'tax_amount'              => ['nullable', 'numeric', 'min:0'],
            'notes'                   => ['nullable', 'string', 'max:1000'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.product_id'      => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id'      => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity'        => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'      => ['required', 'numeric', 'min:0'],
            'items.*.discount'        => ['nullable', 'numeric', 'min:0'],
            'payment_method'          => ['required', 'in:cash,wave,om,card,other,credit'],
            'use_loyalty_points'      => ['nullable', 'integer', 'min:0'],
        ]);

        $company = $this->company();

        $sale = DB::transaction(function () use ($validated, $company) {
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $discount = $item['discount'] ?? 0;
                $subtotal += ($item['unit_price'] * $item['quantity']) - $discount;
            }

            $discountAmount  = $validated['discount_amount'] ?? 0;
            $taxAmount       = $validated['tax_amount'] ?? 0;
            $loyaltyDiscount = 0;
            $loyaltyUsed     = 0;

            // Loyalty points redemption
            if (!empty($validated['use_loyalty_points']) && !empty($validated['customer_id'])) {
                $customer = Customer::find($validated['customer_id']);
                $config   = LoyaltyConfig::where('company_id', $company->id)->where('is_active', true)->first();

                if ($config && $customer && $customer->loyalty_points >= $validated['use_loyalty_points']) {
                    $loyaltyUsed     = $validated['use_loyalty_points'];
                    $loyaltyDiscount = $config->calculateRedemptionValue($loyaltyUsed);
                }
            }

            $total = max(0, $subtotal - $discountAmount + $taxAmount - $loyaltyDiscount);

            $depotId = $validated['depot_id'] ?? $company->depots()->where('is_default', true)->value('id');

            $sale = Sale::create([
                'company_id'            => $company->id,
                'customer_id'           => $validated['customer_id'] ?? null,
                'user_id'               => auth()->id(),
                'depot_id'              => $depotId,
                'reference'             => 'POS-' . strtoupper(uniqid()),
                'type'                  => 'counter',
                'status'                => 'completed',
                'subtotal'              => $subtotal,
                'discount_amount'       => $discountAmount,
                'tax_amount'            => $taxAmount,
                'total'                 => $total,
                'payment_status'        => $validated['payment_method'] === 'credit' ? 'unpaid' : 'paid',
                'notes'                 => $validated['notes'] ?? null,
                'loyalty_points_earned' => 0,
                'loyalty_points_used'   => $loyaltyUsed,
                'loyalty_discount'      => $loyaltyDiscount,
            ]);

            foreach ($validated['items'] as $item) {
                $discount  = $item['discount'] ?? 0;
                $lineTotal = ($item['unit_price'] * $item['quantity']) - $discount;

                SaleItem::create([
                    'sale_id'            => $sale->id,
                    'company_id'         => $company->id,
                    'product_id'         => $item['product_id'],
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'quantity'           => $item['quantity'],
                    'unit_price'         => $item['unit_price'],
                    'discount'           => $discount,
                    'total'              => $lineTotal,
                ]);

                // Decrement stock
                if ($depotId) {
                    if (!empty($item['variant_id'])) {
                        $vsi = VariantStockItem::firstOrCreate(
                            ['product_variant_id' => $item['variant_id'], 'depot_id' => $depotId],
                            ['company_id' => $company->id, 'quantity' => 0]
                        );
                        $vsi->decrement('quantity', $item['quantity']);
                    }

                    $stockItem = StockItem::firstOrCreate(
                        ['product_id' => $item['product_id'], 'depot_id' => $depotId],
                        ['company_id' => $company->id, 'quantity' => 0]
                    );
                    $stockItem->decrement('quantity', $item['quantity']);

                    StockMovement::create([
                        'company_id'         => $company->id,
                        'product_id'         => $item['product_id'],
                        'product_variant_id' => $item['variant_id'] ?? null,
                        'depot_id'           => $depotId,
                        'type'               => 'out',
                        'quantity'           => $item['quantity'],
                        'unit_cost'          => $item['unit_price'],
                        'reference'          => $sale->reference,
                        'user_id'            => auth()->id(),
                    ]);
                }
            }

            // Payment (unless credit)
            if ($validated['payment_method'] !== 'credit') {
                Payment::create([
                    'company_id' => $company->id,
                    'sale_id'    => $sale->id,
                    'amount'     => $total,
                    'method'     => $validated['payment_method'],
                ]);
            } else {
                // Add to customer outstanding balance
                if ($sale->customer_id) {
                    Customer::where('id', $sale->customer_id)
                        ->increment('outstanding_balance', $total);
                }
            }

            // Loyalty: debit used points
            if ($loyaltyUsed > 0) {
                $customer = Customer::find($validated['customer_id']);
                $customer->decrement('loyalty_points', $loyaltyUsed);

                LoyaltyTransaction::create([
                    'company_id'     => $company->id,
                    'customer_id'    => $customer->id,
                    'sale_id'        => $sale->id,
                    'type'           => 'redeem',
                    'points'         => $loyaltyUsed,
                    'monetary_value' => $loyaltyDiscount,
                    'description'    => "Utilisation de {$loyaltyUsed} points sur vente {$sale->reference}",
                ]);
            }

            // Loyalty: earn points
            if (!empty($validated['customer_id'])) {
                $config = LoyaltyConfig::where('company_id', $company->id)->where('is_active', true)->first();
                if ($config) {
                    $earned = $config->calculatePoints($total);
                    if ($earned > 0) {
                        $sale->update(['loyalty_points_earned' => $earned]);

                        Customer::where('id', $validated['customer_id'])
                            ->increment('loyalty_points', $earned);

                        LoyaltyTransaction::create([
                            'company_id'     => $company->id,
                            'customer_id'    => $validated['customer_id'],
                            'sale_id'        => $sale->id,
                            'type'           => 'earn',
                            'points'         => $earned,
                            'monetary_value' => null,
                            'description'    => "Gain de {$earned} points sur vente {$sale->reference}",
                        ]);
                    }
                }
            }

            return $sale;
        });

        return redirect()->route('app.boutique.pos.index')
            ->with('success', "Vente {$sale->reference} enregistrée !");
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['items.product', 'customer', 'payments', 'user']);

        return inertia('App/Boutique/Pos/Receipt', [
            'sale' => $sale,
        ]);
    }
}
