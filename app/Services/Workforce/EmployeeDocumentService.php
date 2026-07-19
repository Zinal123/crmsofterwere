<?php

namespace App\Services\Workforce;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use App\Repositories\Contracts\EmployeeDocumentRepositoryInterface;
use App\Support\ImageCompressor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentService
{
    public function __construct(
        private EmployeeDocumentRepositoryInterface $repository,
        private ImageCompressor $compressor,
    ) {
    }

    public function upload(Employee $employee, User $uploader, UploadedFile $file, string $documentType, ?string $documentNumber): EmployeeDocument
    {
        $isImage = str_starts_with($file->getMimeType(), 'image/');
        $extension = $isImage ? 'jpg' : $file->getClientOriginalExtension();
        $diskPath = 'employee-documents/' . $employee->id . '/' . uniqid('doc_', true) . '.' . $extension;

        if ($isImage) {
            $this->compressor->compress($file, $diskPath);
        } else {
            Storage::disk('public')->putFileAs(dirname($diskPath), $file, basename($diskPath));
        }

        return $this->repository->create([
            'employee_id' => $employee->id,
            'document_type' => $documentType,
            'document_number' => $documentNumber,
            'path' => $diskPath,
            'uploaded_by' => $uploader->id,
        ]);
    }
}
