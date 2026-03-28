<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $companyId;
    protected $categories;
    protected $units;

    public function __construct()
    {
        $company = App::make('currentCompany');
        $this->companyId = $company->id;
        $this->categories = Category::where('company_id', $this->companyId)->pluck('id', 'name');
        $this->units = Unit::where('company_id', $this->companyId)->pluck('id', 'name');
    }

    public function model(array $row)
    {
        return new Product([
            'company_id'      => $this->companyId,
            'name'            => $row['nom'],
            'category_id'     => $this->categories[$row['categorie'] ?? ''] ?? null,
            'unit_id'         => $this->units[$row['unite'] ?? ''] ?? null,
            'purchase_price'  => $row['prix_achat'] ?? 0,
            'selling_price'   => $row['prix_vente'] ?? 0,
            'barcode'         => $row['code_barres'] ?? null,
            'min_stock_alert' => $row['stock_alerte'] ?? 0,
            'is_active'       => ($row['actif'] ?? 'Oui') === 'Oui',
        ]);
    }

    public function rules(): array
    {
        return [
            'nom'        => 'required|string|max:255',
            'prix_vente' => 'required|numeric|min:0',
        ];
    }
}
