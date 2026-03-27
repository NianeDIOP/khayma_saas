<?php

namespace App\Http\Controllers\App\Location;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\RentalAsset;
use App\Models\RentalContract;
use App\Models\RentalPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $query = RentalContract::forCompany($this->company()->id)
            ->with('rentalAsset', 'customer');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('rentalAsset', fn ($a) => $a->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $contracts = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return inertia('App/Location/Contracts/Index', [
            'contracts' => $contracts,
            'filters'   => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        $company = $this->company();

        $assets    = $company->rentalAssets()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'type', 'daily_rate', 'monthly_rate', 'status']);
        $customers = $company->customers()->orderBy('name')->get(['id', 'name', 'phone']);

        return inertia('App/Location/Contracts/Form', [
            'assets'    => $assets,
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rental_asset_id'   => ['required', 'integer', 'exists:rental_assets,id'],
            'customer_id'       => ['required', 'integer', 'exists:customers,id'],
            'start_date'        => ['required', 'date'],
            'end_date'          => ['required', 'date', 'after:start_date'],
            'total_amount'      => ['required', 'numeric', 'min:0'],
            'deposit_amount'    => ['nullable', 'numeric', 'min:0'],
            'payment_frequency' => ['required', 'in:daily,monthly,quarterly,yearly,one_time'],
            'conditions'        => ['nullable', 'string'],
            'inspection_start'  => ['nullable', 'array'],
            'notes'             => ['nullable', 'string'],
        ]);

        $company = $this->company();

        // Generate unique reference
        $count = RentalContract::forCompany($company->id)->count() + 1;
        $validated['reference']  = 'LOC-' . str_pad($count, 5, '0', STR_PAD_LEFT);
        $validated['company_id'] = $company->id;
        $validated['user_id']    = auth()->id();
        $validated['status']     = 'active';

        DB::transaction(function () use ($validated) {
            $contract = RentalContract::create($validated);

            // Update asset status to rented
            RentalAsset::where('id', $validated['rental_asset_id'])
                ->update(['status' => 'rented']);

            // Generate payment schedule
            $this->generatePaymentSchedule($contract);
        });

        return redirect()->route('app.location.contracts.index')
            ->with('success', 'Contrat créé avec succès.');
    }

    public function show(RentalContract $contract)
    {
        $contract->load('rentalAsset', 'customer', 'user', 'payments', 'renewedFrom');

        return inertia('App/Location/Contracts/Show', [
            'contract' => $contract,
        ]);
    }

    public function updateStatus(Request $request, RentalContract $contract)
    {
        $validated = $request->validate([
            'status'         => ['required', 'in:active,completed,overdue,cancelled'],
            'inspection_end' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($contract, $validated) {
            $contract->update($validated);

            // If completed or cancelled, free the asset
            if (in_array($validated['status'], ['completed', 'cancelled'])) {
                $contract->rentalAsset()->update(['status' => 'available']);
            }
        });

        return back()->with('success', 'Statut du contrat mis à jour.');
    }

    public function renew(Request $request, RentalContract $contract)
    {
        $validated = $request->validate([
            'start_date'        => ['required', 'date'],
            'end_date'          => ['required', 'date', 'after:start_date'],
            'total_amount'      => ['required', 'numeric', 'min:0'],
            'deposit_amount'    => ['nullable', 'numeric', 'min:0'],
            'payment_frequency' => ['required', 'in:daily,monthly,quarterly,yearly,one_time'],
            'conditions'        => ['nullable', 'string'],
            'notes'             => ['nullable', 'string'],
        ]);

        $company = $this->company();
        $count   = RentalContract::forCompany($company->id)->count() + 1;

        $newContract = null;

        DB::transaction(function () use ($contract, $validated, $company, $count, &$newContract) {
            // Mark old contract as renewed
            $contract->update(['status' => 'renewed']);

            // Create new contract
            $newContract = RentalContract::create(array_merge($validated, [
                'company_id'      => $company->id,
                'rental_asset_id' => $contract->rental_asset_id,
                'customer_id'     => $contract->customer_id,
                'user_id'         => auth()->id(),
                'reference'       => 'LOC-' . str_pad($count, 5, '0', STR_PAD_LEFT),
                'status'          => 'active',
                'renewed_from_id' => $contract->id,
            ]));

            $this->generatePaymentSchedule($newContract);
        });

        return redirect()->route('app.location.contracts.show', $newContract)
            ->with('success', 'Contrat renouvelé avec succès.');
    }

    public function returnDeposit(Request $request, RentalContract $contract)
    {
        $validated = $request->validate([
            'deposit_returned_amount' => ['required', 'numeric', 'min:0', 'max:' . $contract->deposit_amount],
        ]);

        $contract->update([
            'deposit_returned'        => true,
            'deposit_returned_amount' => $validated['deposit_returned_amount'],
        ]);

        return back()->with('success', 'Caution restituée.');
    }

    private function generatePaymentSchedule(RentalContract $contract): void
    {
        $start     = Carbon::parse($contract->start_date);
        $end       = Carbon::parse($contract->end_date);
        $frequency = $contract->payment_frequency;
        $total     = (float) $contract->total_amount;

        $dates = [];

        if ($frequency === 'one_time') {
            $dates[] = $start->copy();
        } elseif ($frequency === 'daily') {
            $current = $start->copy();
            while ($current->lte($end)) {
                $dates[] = $current->copy();
                $current->addDay();
            }
        } elseif ($frequency === 'monthly') {
            $current = $start->copy();
            while ($current->lte($end)) {
                $dates[] = $current->copy();
                $current->addMonth();
            }
        } elseif ($frequency === 'quarterly') {
            $current = $start->copy();
            while ($current->lte($end)) {
                $dates[] = $current->copy();
                $current->addMonths(3);
            }
        } elseif ($frequency === 'yearly') {
            $current = $start->copy();
            while ($current->lte($end)) {
                $dates[] = $current->copy();
                $current->addYear();
            }
        }

        if (count($dates) === 0) {
            $dates[] = $start->copy();
        }

        $amountPerPayment = round($total / count($dates), 2);

        foreach ($dates as $date) {
            RentalPayment::create([
                'company_id'         => $contract->company_id,
                'rental_contract_id' => $contract->id,
                'due_date'           => $date->toDateString(),
                'amount'             => $amountPerPayment,
                'status'             => 'pending',
            ]);
        }
    }
}
