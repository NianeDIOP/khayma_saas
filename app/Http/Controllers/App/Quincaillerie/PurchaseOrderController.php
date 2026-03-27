<?php

namespace App\Http\Controllers\App\Quincaillerie;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = $this->company()->purchaseOrders()->with(['supplier', 'user']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $orders = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return inertia('App/Quincaillerie/PurchaseOrders/Index', [
            'purchaseOrders' => $orders,
            'filters'        => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        $company = $this->company();

        return inertia('App/Quincaillerie/PurchaseOrders/Form', [
            'purchaseOrder' => null,
            'products'      => $company->products()->where('is_active', true)->with('unit')->orderBy('name')->get(),
            'suppliers'     => $company->suppliers()->orderBy('name')->get(['id', 'name']),
            'units'         => $company->units()->orderBy('name')->get(['id', 'name', 'symbol']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'           => ['required', 'integer', 'exists:suppliers,id'],
            'expected_date'         => ['nullable', 'date'],
            'notes'                 => ['nullable', 'string', 'max:2000'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'integer', 'exists:products,id'],
            'items.*.unit_id'       => ['nullable', 'integer', 'exists:units,id'],
            'items.*.quantity'      => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'    => ['required', 'numeric', 'min:0'],
        ]);

        $company = $this->company();

        $po = DB::transaction(function () use ($validated, $company) {
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['unit_price'] * $item['quantity'];
            }

            $po = $company->purchaseOrders()->create([
                'supplier_id'   => $validated['supplier_id'],
                'user_id'       => auth()->id(),
                'reference'     => PurchaseOrder::generateReference($company->id),
                'status'        => 'draft',
                'subtotal'      => $subtotal,
                'total'         => $subtotal,
                'notes'         => $validated['notes'] ?? null,
                'expected_date' => $validated['expected_date'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $po->items()->create([
                    'product_id' => $item['product_id'],
                    'unit_id'    => $item['unit_id'] ?? null,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total'      => $item['unit_price'] * $item['quantity'],
                ]);
            }

            return $po;
        });

        return redirect()->route('app.quincaillerie.purchase-orders.show', $po)
            ->with('success', 'Bon de commande créé.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'user', 'items.product', 'items.unit', 'supplierPayments']);

        return inertia('App/Quincaillerie/PurchaseOrders/Show', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['draft'])) {
            return back()->with('error', 'Ce bon de commande ne peut plus être modifié.');
        }

        $company = $this->company();
        $purchaseOrder->load('items');

        return inertia('App/Quincaillerie/PurchaseOrders/Form', [
            'purchaseOrder' => $purchaseOrder,
            'products'      => $company->products()->where('is_active', true)->with('unit')->orderBy('name')->get(),
            'suppliers'     => $company->suppliers()->orderBy('name')->get(['id', 'name']),
            'units'         => $company->units()->orderBy('name')->get(['id', 'name', 'symbol']),
        ]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->with('error', 'Ce bon de commande ne peut plus être modifié.');
        }

        $validated = $request->validate([
            'supplier_id'           => ['required', 'integer', 'exists:suppliers,id'],
            'expected_date'         => ['nullable', 'date'],
            'notes'                 => ['nullable', 'string', 'max:2000'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'integer', 'exists:products,id'],
            'items.*.unit_id'       => ['nullable', 'integer', 'exists:units,id'],
            'items.*.quantity'      => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'    => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $purchaseOrder) {
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['unit_price'] * $item['quantity'];
            }

            $purchaseOrder->update([
                'supplier_id'   => $validated['supplier_id'],
                'subtotal'      => $subtotal,
                'total'         => $subtotal,
                'notes'         => $validated['notes'] ?? null,
                'expected_date' => $validated['expected_date'] ?? null,
            ]);

            $purchaseOrder->items()->delete();

            foreach ($validated['items'] as $item) {
                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'unit_id'    => $item['unit_id'] ?? null,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total'      => $item['unit_price'] * $item['quantity'],
                ]);
            }
        });

        return redirect()->route('app.quincaillerie.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Bon de commande mis à jour.');
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:sent,cancelled'],
        ]);

        $allowed = [
            'draft' => ['sent', 'cancelled'],
            'sent'  => ['cancelled'],
        ];

        if (!in_array($validated['status'], $allowed[$purchaseOrder->status] ?? [])) {
            return back()->with('error', 'Changement de statut non autorisé.');
        }

        $purchaseOrder->update(['status' => $validated['status']]);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['sent', 'partial'])) {
            return back()->with('error', 'Ce bon de commande ne peut pas être réceptionné.');
        }

        $validated = $request->validate([
            'items'                        => ['required', 'array'],
            'items.*.purchase_order_item_id' => ['required', 'integer'],
            'items.*.received_qty'           => ['required', 'numeric', 'min:0'],
        ]);

        $company = $this->company();

        DB::transaction(function () use ($validated, $purchaseOrder, $company) {
            $allReceived = true;

            foreach ($validated['items'] as $received) {
                $poItem = $purchaseOrder->items()->findOrFail($received['purchase_order_item_id']);

                if ($received['received_qty'] <= 0) {
                    if ($poItem->received_quantity < $poItem->quantity) {
                        $allReceived = false;
                    }
                    continue;
                }

                $poItem->increment('received_quantity', $received['received_qty']);

                if ($poItem->fresh()->received_quantity < $poItem->quantity) {
                    $allReceived = false;
                }

                // Add stock
                $depot = $company->depots()->first();
                if ($depot) {
                    $stockItem = StockItem::firstOrCreate(
                        ['product_id' => $poItem->product_id, 'depot_id' => $depot->id],
                        ['quantity' => 0, 'avg_cost' => 0]
                    );

                    // PMP calculation
                    $oldTotal = $stockItem->quantity * $stockItem->avg_cost;
                    $newTotal = $received['received_qty'] * $poItem->unit_price;
                    $newQty = $stockItem->quantity + $received['received_qty'];
                    $newAvgCost = $newQty > 0 ? ($oldTotal + $newTotal) / $newQty : 0;

                    $stockItem->update([
                        'quantity' => $newQty,
                        'avg_cost' => $newAvgCost,
                    ]);

                    StockMovement::create([
                        'company_id' => $company->id,
                        'product_id' => $poItem->product_id,
                        'depot_id'   => $depot->id,
                        'type'       => 'purchase',
                        'quantity'   => $received['received_qty'],
                        'unit_cost'  => $poItem->unit_price,
                        'reference'  => $purchaseOrder->reference,
                        'user_id'    => auth()->id(),
                    ]);
                }
            }

            $purchaseOrder->update([
                'status'      => $allReceived ? 'received' : 'partial',
                'received_at' => $allReceived ? now() : $purchaseOrder->received_at,
            ]);
        });

        return back()->with('success', 'Réception enregistrée.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->with('error', 'Seul un brouillon peut être supprimé.');
        }

        $purchaseOrder->items()->delete();
        $purchaseOrder->delete();

        return redirect()->route('app.quincaillerie.purchase-orders.index')
            ->with('success', 'Bon de commande supprimé.');
    }
}
