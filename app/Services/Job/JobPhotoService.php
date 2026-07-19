<?php

namespace App\Services\Job;

use App\Models\Job;
use App\Models\JobPhoto;
use App\Models\User;
use App\Repositories\Contracts\JobPhotoRepositoryInterface;
use App\Support\Geocoding\NominatimGeocoder;
use App\Support\ImageCompressor;
use Illuminate\Http\UploadedFile;

class JobPhotoService
{
    public function __construct(
        private JobPhotoRepositoryInterface $repository,
        private JobAuditLogger $auditLogger,
        private ImageCompressor $compressor,
        private NominatimGeocoder $geocoder,
    ) {
    }

    public function upload(Job $job, User $uploader, UploadedFile $file, ?float $lat, ?float $lng): JobPhoto
    {
        if ($job->status !== 'in_progress') {
            throw new \InvalidArgumentException('Proof photos can only be uploaded while a job is in progress.');
        }

        $diskPath = 'job-photos/' . $job->id . '/' . uniqid('photo_', true) . '.jpg';
        $this->compressor->compress($file, $diskPath);

        $locationCaptured = $lat !== null && $lng !== null;
        $mapLink = $locationCaptured ? "https://www.google.com/maps?q={$lat},{$lng}" : null;
        $address = $locationCaptured ? $this->geocoder->reverseGeocode($lat, $lng) : null;

        $photo = $this->repository->create([
            'job_id' => $job->id,
            'uploaded_by' => $uploader->id,
            'path' => $diskPath,
            'latitude' => $lat,
            'longitude' => $lng,
            'location_captured' => $locationCaptured,
            'map_link' => $mapLink,
            'address' => $address,
            'captured_at' => now(),
        ]);

        $description = $locationCaptured
            ? "{$uploader->name} uploaded a proof photo (location captured)."
            : "{$uploader->name} uploaded a proof photo (location NOT captured).";
        $this->auditLogger->log($job, $uploader, 'photo_uploaded', $description);

        return $photo;
    }
}
