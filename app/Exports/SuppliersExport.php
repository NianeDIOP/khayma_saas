<?php

namespace App\Exports;

use App\Models\Supplier;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SuppliersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        $company = App::make('currentCompany');

        return Supplier::where('company_id', $company->id)->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Nom',
            'Téléphone',
            'Email',
            'Adresse',
            'NINEA',
            'RIB',
            'Note',
            'Solde dû',
        ];
    }

    public function map($supplier): array
    {
        return [
            $supplier->name,
            $supplier->phone ?? '',
            $supplier->email ?? '',
            $supplier->address ?? '',
            $supplier->ninea ?? '',
            $supplier->rib ?? '',
            $supplier->notes ?? '',
            $supplier->outstanding_balance,
        ];
    }
}
