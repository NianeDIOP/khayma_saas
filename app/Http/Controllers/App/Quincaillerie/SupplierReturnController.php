<?php

namespace App\Http\Controllers\App\Quincaillerie;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\SupplierReturn;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class SupplierReturnController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = $this->company()->supplierReturns()->with(['supplier', 'product', 'purchaseOrder', 'user']);

        if ($supplierId = $request->input('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $returns = $query->orderByDesc('date')->paginate(20)->withQueryString();

        return inertia('App/Quincaillerie/SupplierReturns/Index', [
            'returns'   => $returns,
            'suppliers' => $this->company()->suppliers()->orderBy('name')->get(['id', 'name']),
            'filters'   => $request->only(['supplier_id', 'status']),
        ]);
    }

    public function create()
    {
        $company = $this->company();

        return inertia('App/Quincaillerie/SupplierReturns/Form', [
            'return'         => null,
            'suppliers'      => $company->suppliers()->orderBy('name')->get(['id', 'name']),
            'products'       => $company->products()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'purchaseOrders' => $company->purchaseOrders()
                ->whereIn('status', ['received', 'partial'])
                ->with('supplier:id,name')
                ->orderByDesc('created_at')
                ->get(['id', 'reference', 'supplier_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'       => ['required', 'integer', 'exists:suppliers,id'],
            'purchase_order_id' => ['nullable', 'integer', 'exists:purchase_orders,id'],
            'product_id'        => ['required', 'integer', 'exists:products,id'],
            'quantity'          => ['required', 'numeric', 'min:0.01'],
            'reason'            => ['nullable', 'string', 'max:1000'],
            'date'              => ['required', 'date'],
        ]);

        $company = $this->company();

        DB::transaction(function () use ($validated, $company) {
            $company->supplierReturns()->create([
                'supplier_id'       => $validated['supplier_id'],
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'product_id'        => $validated['product_id'],
                'user_id'           => auth()->id(),
                'quantity'          => $validated['quantity'],
                'reason'            => $validated['reason'] ?? null,
                'status'            => 'pending',
                'date'              => $validated['date'],
            ]);

            // Decrease stock for the returned product
            $depot = $company->depots()->first();
            if ($depot) {
                $stockItem = StockItem::where('product_id', $validated['product_id'])
                    ->where('depot_id', $depot->id)
                    ->first();

                if ($stockItem) {
                    $stockItem->decrement('quantity', $validated['quantity']);

                    StockMovement::create([
                        'company_id' => $company->id,
                        'product_id' => $validated['product_id'],
                        'depot_id'   => $depot->id,
                        'type'       => 'return_supplier',
                        'quantity'   => -$validated['quantity'],
                        'unit_cost'  => 0,
                        'reference'  => 'Retour fournisseur',
                        'user_id'    => auth()->id(),
                    ]);
                }
            }
        });

        return redirect()->route('app.quincaillerie.supplier-returns.index')
            ->with('success', 'Retour enregistré.');
    }
}
