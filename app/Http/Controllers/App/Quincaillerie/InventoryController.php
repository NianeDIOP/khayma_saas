<?php

namespace App\Http\Controllers\App\Quincaillerie;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Inventory;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = $this->company()->inventories()->with(['depot', 'user']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $inventories = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return inertia('App/Quincaillerie/Inventories/Index', [
            'inventories' => $inventories,
            'depots'      => $this->company()->depots()->orderBy('name')->get(['id', 'name']),
            'filters'     => $request->only(['status']),
        ]);
    }

    public function create(Request $request)
    {
        $company = $this->company();
        $depot = $company->depots()->first();

        if (!$depot) {
            return back()->with('error', 'Aucun dépôt configuré.');
        }

        $requestDepotId = $request->input('depot_id', $depot->id);
        $selectedDepot = $company->depots()->findOrFail($requestDepotId);

        // Get all products with their current stock in this depot
        $products = $company->products()->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($selectedDepot) {
                $stockItem = StockItem::where('product_id', $product->id)
                    ->where('depot_id', $selectedDepot->id)
                    ->first();

                return [
                    'id'              => $product->id,
                    'name'            => $product->name,
                    'barcode'         => $product->barcode,
                    'system_quantity' => $stockItem ? $stockItem->quantity : 0,
                ];
            });

        return inertia('App/Quincaillerie/Inventories/Process', [
            'products' => $products,
            'depots'   => $company->depots()->orderBy('name')->get(['id', 'name']),
            'depot_id' => $selectedDepot->id,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'depot_id'                       => ['required', 'integer', 'exists:depots,id'],
            'notes'                          => ['nullable', 'string', 'max:2000'],
            'lines'                          => ['required', 'array', 'min:1'],
            'lines.*.product_id'             => ['required', 'integer', 'exists:products,id'],
            'lines.*.system_quantity'        => ['required', 'numeric', 'min:0'],
            'lines.*.physical_quantity'      => ['required', 'numeric', 'min:0'],
        ]);

        $company = $this->company();

        $inventory = DB::transaction(function () use ($validated, $company) {
            $inventory = $company->inventories()->create([
                'depot_id'  => $validated['depot_id'],
                'user_id'   => auth()->id(),
                'reference' => Inventory::generateReference($company->id),
                'status'    => 'in_progress',
                'notes'     => $validated['notes'] ?? null,
            ]);

            foreach ($validated['lines'] as $line) {
                $gap = $line['physical_quantity'] - $line['system_quantity'];

                $inventory->lines()->create([
                    'product_id'        => $line['product_id'],
                    'system_quantity'    => $line['system_quantity'],
                    'physical_quantity'  => $line['physical_quantity'],
                    'gap'               => $gap,
                ]);
            }

            return $inventory;
        });

        return redirect()->route('app.quincaillerie.inventories.show', $inventory)
            ->with('success', 'Inventaire créé.');
    }

    public function show(Inventory $inventory)
    {
        $inventory->load(['depot', 'user', 'lines.product']);

        return inertia('App/Quincaillerie/Inventories/Show', [
            'inventory' => $inventory,
        ]);
    }

    public function validate(Inventory $inventory)
    {
        if ($inventory->status !== 'in_progress') {
            return back()->with('error', 'Cet inventaire est déjà validé.');
        }

        $company = $this->company();

        DB::transaction(function () use ($inventory, $company) {
            foreach ($inventory->lines as $line) {
                if ($line->gap == 0) continue;

                $stockItem = StockItem::where('product_id', $line->product_id)
                    ->where('depot_id', $inventory->depot_id)
                    ->first();

                if ($stockItem) {
                    $stockItem->update(['quantity' => $line->physical_quantity]);

                    StockMovement::create([
                        'company_id' => $company->id,
                        'product_id' => $line->product_id,
                        'depot_id'   => $inventory->depot_id,
                        'type'       => 'adjustment',
                        'quantity'   => $line->gap,
                        'unit_cost'  => 0,
                        'reference'  => $inventory->reference,
                        'notes'      => 'Ajustement inventaire (écart: ' . $line->gap . ')',
                        'user_id'    => auth()->id(),
                    ]);
                }
            }

            $inventory->update([
                'status'       => 'validated',
                'validated_at' => now(),
            ]);
        });

        return back()->with('success', 'Inventaire validé et stock ajusté.');
    }
}
