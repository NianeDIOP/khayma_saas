<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StorageService
{
    /**
     * Upload a file for a company.
     */
    public function upload(
        Company $company,
        UploadedFile $file,
        string $folder = 'general',
        string $disk = 'public'
    ): CompanyFile {
        $path = $file->store("companies/{$company->id}/{$folder}", $disk);

        return CompanyFile::create([
            'company_id'    => $company->id,
            'uploaded_by'   => auth()->id(),
            'original_name' => $file->getClientOriginalName(),
            'disk'          => $disk,
            'path'          => $path,
            'mime_type'     => $file->getMimeType(),
            'size'          => $file->getSize(),
            'folder'        => $folder,
        ]);
    }

    /**
     * Delete a company file from disk + DB.
     */
    public function delete(CompanyFile $file): bool
    {
        Storage::disk($file->disk)->delete($file->path);
        return $file->delete();
    }

    /**
     * Get total storage used by a company (bytes).
     */
    public function usageBytes(int $companyId): int
    {
        return (int) CompanyFile::forCompany($companyId)->sum('size');
    }

    /**
     * Check if a company can upload a file of given size.
     */
    public function canUpload(Company $company, int $fileSize): bool
    {
        $plan = $company->currentPlan();
        if (! $plan || ! $plan->max_storage_gb) {
            return true; // no limit
        }

        $limitBytes = $plan->max_storage_gb * 1073741824; // 1 GB = 1073741824 bytes
        $used       = $this->usageBytes($company->id);

        return ($used + $fileSize) <= $limitBytes;
    }
}
