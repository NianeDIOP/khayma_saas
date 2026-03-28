<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Mail\LowStockAlertMail;
use App\Models\Company;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class StockController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = StockItem::where('stock_items.company_id', $this->company()->id)
            ->join('products', 'stock_items.product_id', '=', 'products.id')
            ->join('depots', 'stock_items.depot_id', '=', 'depots.id')
            ->select('stock_items.*', 'products.name as product_name', 'products.barcode', 'products.min_stock_alert', 'products.selling_price', 'depots.name as depot_name');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('products.barcode', 'like', "%{$search}%");
            });
        }

        if ($depotId = $request->input('depot_id')) {
            $query->where('stock_items.depot_id', $depotId);
        }

        $stockItems = $query->orderBy('products.name')->paginate(20)->withQueryString();
        $depots     = $this->company()->depots()->orderBy('name')->get(['id', 'name']);

        return inertia('App/Stock/Index', [
            'stockItems' => $stockItems,
            'depots'     => $depots,
            'filters'    => $request->only(['search', 'depot_id']),
        ]);
    }

    public function movements(Request $request)
    {
        $query = StockMovement::where('stock_movements.company_id', $this->company()->id)
            ->join('products', 'stock_movements.product_id', '=', 'products.id')
            ->join('depots', 'stock_movements.depot_id', '=', 'depots.id')
            ->select('stock_movements.*', 'products.name as product_name', 'depots.name as depot_name');

        if ($type = $request->input('type')) {
            $query->where('stock_movements.type', $type);
        }

        if ($search = $request->input('search')) {
            $query->where('products.name', 'like', "%{$search}%");
        }

        $movements = $query->orderByDesc('stock_movements.created_at')->paginate(20)->withQueryString();

        return inertia('App/Stock/Movements', [
            'movements' => $movements,
            'filters'   => $request->only(['search', 'type']),
        ]);
    }

    public function createMovement()
    {
        $products = $this->company()->products()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'barcode']);
        $depots   = $this->company()->depots()->orderBy('name')->get(['id', 'name']);

        return inertia('App/Stock/MovementForm', [
            'products' => $products,
            'depots'   => $depots,
        ]);
    }

    public function storeMovement(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'depot_id'   => ['required', 'integer', 'exists:depots,id'],
            'type'       => ['required', 'in:in,out,adjustment,loss'],
            'quantity'   => ['required', 'numeric', 'min:0.01'],
            'unit_cost'  => ['nullable', 'numeric', 'min:0'],
            'reference'  => ['nullable', 'string', 'max:100'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        $company = $this->company();

        DB::transaction(function () use ($validated, $company) {
            $validated['company_id'] = $company->id;
            $validated['user_id']    = auth()->id();

            StockMovement::create($validated);

            $stockItem = StockItem::firstOrCreate(
                ['product_id' => $validated['product_id'], 'depot_id' => $validated['depot_id']],
                ['company_id' => $company->id, 'quantity' => 0]
            );

            if (in_array($validated['type'], ['in', 'adjustment'])) {
                $stockItem->increment('quantity', $validated['quantity']);
            } else {
                $stockItem->decrement('quantity', $validated['quantity']);
            }
        });

        // Check low-stock products for this company and notify owner if needed.
        $lowStock = StockItem::query()
            ->where('stock_items.company_id', $company->id)
            ->join('products', 'stock_items.product_id', '=', 'products.id')
            ->whereNotNull('products.min_stock_alert')
            ->where('products.min_stock_alert', '>', 0)
            ->whereColumn('stock_items.quantity', '<=', 'products.min_stock_alert')
            ->select('products.name', 'stock_items.quantity', 'products.min_stock_alert')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'name'      => $r->name,
                'quantity'  => (int) $r->quantity,
                'threshold' => (int) $r->min_stock_alert,
            ])
            ->values()
            ->all();

        if (!empty($lowStock)) {
            $owner = $company->users()->wherePivot('role', 'owner')->first();
            if ($owner && !empty($owner->email)) {
                Mail::to($owner->email)->queue(new LowStockAlertMail($company->name, $owner->name, $lowStock));
            }
            if ($owner && !empty($owner->phone)) {
                app(SmsService::class)->send($owner->phone, 'Khayma: alerte stock bas dans ' . $company->name . '.');
            }
        }

        return redirect()->route('app.stock.movements', ['_tenant' => $company->slug])
                         ->with('success', 'Mouvement de stock enregistré.');
    }
}
