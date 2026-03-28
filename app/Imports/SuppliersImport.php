<?php

namespace App\Imports;

use App\Models\Supplier;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SuppliersImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $companyId;

    public function __construct()
    {
        $this->companyId = App::make('currentCompany')->id;
    }

    public function model(array $row)
    {
        return new Supplier([
            'company_id'          => $this->companyId,
            'name'                => $row['nom'],
            'phone'               => $row['telephone'] ?? null,
            'email'               => $row['email'] ?? null,
            'address'             => $row['adresse'] ?? null,
            'ninea'               => $row['ninea'] ?? null,
            'rib'                 => $row['rib'] ?? null,
            'notes'               => $row['note'] ?? null,
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
