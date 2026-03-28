<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CustomersImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $companyId;

    public function __construct()
    {
        $this->companyId = App::make('currentCompany')->id;
    }

    public function model(array $row)
    {
        return new Customer([
            'company_id'          => $this->companyId,
            'name'                => $row['nom'],
            'phone'               => $row['telephone'] ?? null,
            'email'               => $row['email'] ?? null,
            'address'             => $row['adresse'] ?? null,
            'nif'                 => $row['nif'] ?? null,
            'category'            => $row['categorie'] ?? null,
            'loyalty_points'      => 0,
            'outstanding_balance' => 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
        ];
    }
}
