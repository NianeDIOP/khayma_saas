<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        $company = App::make('currentCompany');

        return Product::where('company_id', $company->id)
            ->with(['category', 'unit'])
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Nom',
            'Catégorie',
            'Unité',
            'Prix achat',
            'Prix vente',
            'Code-barres',
            'Stock alerte',
            'Actif',
        ];
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->category?->name ?? '',
            $product->unit?->name ?? '',
            $product->purchase_price,
            $product->selling_price,
            $product->barcode ?? '',
            $product->min_stock_alert,
            $product->is_active ? 'Oui' : 'Non',
        ];
    }
}
