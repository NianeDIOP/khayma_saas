<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CustomersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        $company = App::make('currentCompany');

        return Customer::where('company_id', $company->id)->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Nom',
            'Téléphone',
            'Email',
            'Adresse',
            'NIF',
            'Catégorie',
            'Points fidélité',
            'Solde dû',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->name,
            $customer->phone ?? '',
            $customer->email ?? '',
            $customer->address ?? '',
            $customer->nif ?? '',
            $customer->category ?? '',
            $customer->loyalty_points,
            $customer->outstanding_balance,
        ];
    }
}
