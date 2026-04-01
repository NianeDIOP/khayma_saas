<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyFile;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class StorageController extends Controller
{
    private function company(): Company
    {
        return App::make('currentCompany');
    }

    public function index(Request $request)
    {
        $company = $this->company();

        $query = CompanyFile::forCompany($company->id)->with('uploader');

        if ($folder = $request->input('folder')) {
            $query->where('folder', $folder);
        }

        if ($search = $request->input('search')) {
            $query->where('original_name', 'like', "%{$search}%");
        }

        $files = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $service = new StorageService();
        $usedBytes = $service->usageBytes($company->id);
        $plan = $company->currentPlan();
        $limitGb = $plan?->max_storage_gb;
        $limitBytes = $limitGb ? $limitGb * 1073741824 : null;

        // List distinct folders
        $folders = CompanyFile::forCompany($company->id)
            ->distinct()
            ->pluck('folder')
            ->sort()
            ->values();

        return inertia('App/Storage/Index', [
            'files'      => $files,
            'folders'    => $folders,
            'usedBytes'  => $usedBytes,
            'limitBytes' => $limitBytes,
            'filters'    => $request->only(['folder', 'search']),
        ]);
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file'   => ['required', 'file', 'max:10240'], // 10 MB max
            'folder' => ['nullable', 'string', 'max:100'],
        ]);

        $company = $this->company();
        $file = $request->file('file');
        $folder = $validated['folder'] ?? 'general';

        $service = new StorageService();

        if (! $service->canUpload($company, $file->getSize())) {
            return back()->withErrors(['file' => 'Quota de stockage dépassé. Veuillez mettre à niveau votre plan.']);
        }

        $service->upload($company, $file, $folder);

        return redirect()->route('app.storage.index')
            ->with('success', 'Fichier téléversé avec succès.');
    }

    public function destroy(CompanyFile $file)
    {
        $company = $this->company();

        if ((int) $file->company_id !== (int) $company->id) {
            abort(403);
        }

        $service = new StorageService();
        $service->delete($file);

        return redirect()->route('app.storage.index')
            ->with('success', 'Fichier supprimé.');
    }
}
