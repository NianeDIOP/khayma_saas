<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Quote;
use App\Models\RentalContract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;

class PdfController extends Controller
{
    public function sale(Sale $sale)
    {
        $company = App::make('currentCompany');
        abort_if($sale->company_id !== $company->id, 404);

        $sale->load(['items.product', 'customer', 'user']);

        $pdf = Pdf::loadView('pdf.sale', [
            'sale'    => $sale,
            'company' => $company,
        ])->setPaper('a4');

        return $pdf->stream("facture-{$sale->reference}.pdf");
    }

    public function quote(Quote $quote)
    {
        $company = App::make('currentCompany');
        abort_if($quote->company_id !== $company->id, 404);

        $quote->load(['items.product', 'customer', 'user']);

        $pdf = Pdf::loadView('pdf.quote', [
            'quote'   => $quote,
            'company' => $company,
        ])->setPaper('a4');

        return $pdf->stream("devis-{$quote->reference}.pdf");
    }

    public function contract(RentalContract $contract)
    {
        $company = App::make('currentCompany');
        abort_if($contract->company_id !== $company->id, 404);

        $contract->load(['rentalAsset', 'customer', 'payments']);

        $pdf = Pdf::loadView('pdf.contract', [
            'contract' => $contract,
            'company'  => $company,
        ])->setPaper('a4');

        return $pdf->stream("contrat-{$contract->reference}.pdf");
    }
}
