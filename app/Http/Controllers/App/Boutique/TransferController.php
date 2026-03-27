<?php

namespace App\Http\Controllers\App\Boutique;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\DepotTransfer;
use App\Models\DepotTransferItem;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\VariantStockItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = DepotTransfer::forCompany($this->company()->id)
            ->with(['fromDepot', 'toDepot', 'user']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $transfers = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return inertia('App/Boutique/Transfers/Index', [
            'transfers' => $transfers,
            'filters'   => $request->only(['status']),
        ]);
    }

    public function create()
    {
        $company  = $this->company();
        $depots   = $company->depots()->orderBy('name')->get(['id', 'name']);
        $products = $company->products()
            ->where('is_active', true)
            ->with(['variants' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get(['id', 'name']);

        return inertia('App/Boutique/Transfers/Form', [
            'depots'   => $depots,
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_depot_id'           => ['required', 'integer', 'exists:depots,id'],
            'to_depot_id'             => ['required', 'integer', 'exists:depots,id', 'different:from_depot_id'],
            'notes'                   => ['nullable', 'string', 'max:1000'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.product_id'      => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id'      => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity'        => ['required', 'numeric', 'min:0.01'],
        ]);

        $company = $this->company();

        $transfer = DB::transaction(function () use ($validated, $company) {
            $transfer = DepotTransfer::create([
                'company_id'   => $company->id,
                'from_depot_id' => $validated['from_depot_id'],
                'to_depot_id'  => $validated['to_depot_id'],
                'user_id'      => auth()->id(),
                'reference'    => 'TRF-' . strtoupper(uniqid()),
                'status'       => 'completed',
                'notes'        => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                DepotTransferItem::create([
                    'depot_transfer_id'  => $transfer->id,
                    'product_id'         => $item['product_id'],
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'quantity'           => $item['quantity'],
                ]);

                // Decrement from source depot
                $fromStock = StockItem::firstOrCreate(
                    ['product_id' => $item['product_id'], 'depot_id' => $validated['from_depot_id']],
                    ['company_id' => $company->id, 'quantity' => 0]
                );
                $fromStock->decrement('quantity', $item['quantity']);

                // Increment in destination depot
                $toStock = StockItem::firstOrCreate(
                    ['product_id' => $item['product_id'], 'depot_id' => $validated['to_depot_id']],
                    ['company_id' => $company->id, 'quantity' => 0]
                );
                $toStock->increment('quantity', $item['quantity']);

                // Variant stock if applicable
                if (!empty($item['variant_id'])) {
                    $fromVSI = VariantStockItem::firstOrCreate(
                        ['product_variant_id' => $item['variant_id'], 'depot_id' => $validated['from_depot_id']],
                        ['company_id' => $company->id, 'quantity' => 0]
                    );
                    $fromVSI->decrement('quantity', $item['quantity']);

                    $toVSI = VariantStockItem::firstOrCreate(
                        ['product_variant_id' => $item['variant_id'], 'depot_id' => $validated['to_depot_id']],
                        ['company_id' => $company->id, 'quantity' => 0]
                    );
                    $toVSI->increment('quantity', $item['quantity']);
                }

                // Log movements
                StockMovement::create([
                    'company_id' => $company->id,
                    'product_id' => $item['product_id'],
                    'depot_id'   => $validated['from_depot_id'],
                    'type'       => 'transfer',
                    'quantity'   => $item['quantity'],
                    'reference'  => $transfer->reference,
                    'notes'      => "Transfert vers " . ($transfer->toDepot->name ?? ''),
                    'user_id'    => auth()->id(),
                ]);

                StockMovement::create([
                    'company_id' => $company->id,
                    'product_id' => $item['product_id'],
                    'depot_id'   => $validated['to_depot_id'],
                    'type'       => 'transfer',
                    'quantity'   => $item['quantity'],
                    'reference'  => $transfer->reference,
                    'notes'      => "Transfert depuis " . ($transfer->fromDepot->name ?? ''),
                    'user_id'    => auth()->id(),
                ]);
            }

            return $transfer;
        });

        return redirect()->route('app.boutique.transfers.index')
            ->with('success', "Transfert {$transfer->reference} effectué.");
    }

    public function show(DepotTransfer $transfer)
    {
        $transfer->load(['fromDepot', 'toDepot', 'user', 'items.product', 'items.variant']);

        return inertia('App/Boutique/Transfers/Show', [
            'transfer' => $transfer,
        ]);
    }
}
