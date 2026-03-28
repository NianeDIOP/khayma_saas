<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Exports\ProductsExport;
use App\Exports\CustomersExport;
use App\Exports\SuppliersExport;
use App\Imports\ProductsImport;
use App\Imports\CustomersImport;
use App\Imports\SuppliersImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportImportController extends Controller
{
    // ── Exports ──────────────────────────────────────────

    public function exportProducts()
    {
        return Excel::download(new ProductsExport(), 'produits.xlsx');
    }

    public function exportCustomers()
    {
        return Excel::download(new CustomersExport(), 'clients.xlsx');
    }

    public function exportSuppliers()
    {
        return Excel::download(new SuppliersExport(), 'fournisseurs.xlsx');
    }

    // ── Imports ──────────────────────────────────────────

    public function importProducts(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        try {
            Excel::import(new ProductsImport(), $request->file('file'));
            return back()->with('success', 'Produits importés avec succès.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return back()->withErrors(['file' => 'Erreurs de validation dans le fichier.'])->with('import_failures', $e->failures());
        }
    }

    public function importCustomers(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        try {
            Excel::import(new CustomersImport(), $request->file('file'));
            return back()->with('success', 'Clients importés avec succès.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return back()->withErrors(['file' => 'Erreurs de validation dans le fichier.'])->with('import_failures', $e->failures());
        }
    }

    public function importSuppliers(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        try {
            Excel::import(new SuppliersImport(), $request->file('file'));
            return back()->with('success', 'Fournisseurs importés avec succès.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return back()->withErrors(['file' => 'Erreurs de validation dans le fichier.'])->with('import_failures', $e->failures());
        }
    }
}
