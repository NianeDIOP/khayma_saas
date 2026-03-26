<?php

namespace App\Http\Controllers\App\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = $this->company()->orders()->with(['items.dish', 'service', 'user']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($date = $request->input('date')) {
            $query->whereDate('created_at', $date);
        }

        $orders = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return inertia('App/Restaurant/Orders/Index', [
            'orders'  => $orders,
            'filters' => $request->only(['search', 'type', 'status', 'date']),
        ]);
    }

    public function create()
    {
        $company = $this->company();

        $dishes = $company->dishes()
            ->where('is_available', true)
            ->with('category')
            ->orderBy('sort_order')
            ->get();

        $services = $company->services()->where('is_active', true)->get();

        $openSession = $company->cashSessions()
            ->whereNull('closed_at')
            ->first();

        return inertia('App/Restaurant/Orders/POS', [
            'dishes'      => $dishes,
            'services'    => $services,
            'openSession' => $openSession,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'             => ['required', 'in:table,takeaway,delivery'],
            'table_number'     => ['nullable', 'required_if:type,table', 'string', 'max:20'],
            'delivery_address' => ['nullable', 'required_if:type,delivery', 'string', 'max:500'],
            'delivery_person'  => ['nullable', 'string', 'max:255'],
            'customer_name'    => ['nullable', 'string', 'max:255'],
            'service_id'       => ['nullable', 'integer', 'exists:services,id'],
            'discount_amount'  => ['nullable', 'numeric', 'min:0'],
            'payment_method'   => ['required', 'in:cash,wave,om,card,other'],
            'payment_status'   => ['required', 'in:paid,pending'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.dish_id'  => ['required', 'integer', 'exists:dishes,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes'    => ['nullable', 'string', 'max:255'],
        ]);

        $company = $this->company();

        $order = DB::transaction(function () use ($validated, $company) {
            $subtotal = 0;

            // Calculate subtotal from items
            $itemsData = [];
            foreach ($validated['items'] as $item) {
                $dish = $company->dishes()->findOrFail($item['dish_id']);
                $unitPrice = $dish->effective_price;
                $total = $unitPrice * $item['quantity'];
                $subtotal += $total;

                $itemsData[] = [
                    'dish_id'    => $item['dish_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total'      => $total,
                    'notes'      => $item['notes'] ?? null,
                ];
            }

            $discount = $validated['discount_amount'] ?? 0;
            $grandTotal = max(0, $subtotal - $discount);

            // Get open cash session
            $openSession = $company->cashSessions()
                ->whereNull('closed_at')
                ->first();

            $order = $company->orders()->create([
                'reference'       => Order::generateReference($company->id),
                'type'            => $validated['type'],
                'table_number'    => $validated['table_number'] ?? null,
                'delivery_address' => $validated['delivery_address'] ?? null,
                'delivery_person' => $validated['delivery_person'] ?? null,
                'customer_name'   => $validated['customer_name'] ?? null,
                'service_id'      => $validated['service_id'] ?? null,
                'cash_session_id' => $openSession?->id,
                'user_id'         => Auth::id(),
                'status'          => 'completed',
                'subtotal'        => $subtotal,
                'discount_amount' => $discount,
                'total'           => $grandTotal,
                'payment_method'  => $validated['payment_method'],
                'payment_status'  => $validated['payment_status'],
                'paid_at'         => $validated['payment_status'] === 'paid' ? now() : null,
                'notes'           => $validated['notes'] ?? null,
            ]);

            foreach ($itemsData as $itemData) {
                $order->items()->create($itemData);
            }

            return $order;
        });

        return redirect()->route('app.restaurant.orders.show', $order)
            ->with('success', 'Commande enregistrée.');
    }

    public function show(Order $order)
    {
        $order->load(['items.dish', 'service', 'user']);

        return inertia('App/Restaurant/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function cancel(Request $request, Order $order)
    {
        $validated = $request->validate([
            'cancel_reason' => ['required', 'string', 'max:500'],
        ]);

        $order->update([
            'status'        => 'cancelled',
            'cancel_reason' => $validated['cancel_reason'],
        ]);

        return redirect()->route('app.restaurant.orders.index')
            ->with('success', 'Commande annulée.');
    }
}
