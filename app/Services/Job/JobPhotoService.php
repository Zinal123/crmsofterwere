<?php

namespace App\Services\Job;

use App\Models\Job;
use App\Models\JobPhoto;
use App\Models\User;
use App\Repositories\Contracts\JobPhotoRepositoryInterface;
use App\Support\Geo\HaversineDistance;
use App\Support\Geocoding\NominatimGeocoder;
use App\Support\ImageCompressor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class JobPhotoService
{
    /**
     * A photo taken further than this from the machine's registered
     * coordinates gets flagged (surfaced to staff), not rejected - the
     * machine's own coordinates could be stale, or a Worker might
     * legitimately be documenting something a short distance away.
     */
    private const GEOFENCE_THRESHOLD_METERS = 150;

    public function __construct(
        private JobPhotoRepositoryInterface $repository,
        private JobAuditLogger $auditLogger,
        private ImageCompressor $compressor,
        private NominatimGeocoder $geocoder,
    ) {
    }

    public function upload(Job $job, User $uploader, UploadedFile $file, ?float $lat, ?float $lng, string $stage = 'general'): JobPhoto
    {
        if ($job->status !== 'in_progress') {
            throw new \InvalidArgumentException('Proof photos can only be uploaded while a job is in progress.');
        }

        // GPS is mandatory: a proof photo with no location is no longer
        // meaningful proof-of-work. Being outside the machine's geofence is
        // a separate, non-blocking concern - see below.
        if ($lat === null || $lng === null) {
            throw new \InvalidArgumentException('Location is required to upload a proof photo. Please allow location access and try again.');
        }

        $diskPath = 'job-photos/' . $job->id . '/' . uniqid('photo_', true) . '.jpg';
        $this->compressor->compress($file, $diskPath);

        // Tamper-evident hash of the actual stored bytes (post-compression),
        // not the original upload - this is what a later reader can verify
        // the file against, not a promise the compression step was lossless.
        $contentHash = hash('sha256', Storage::disk('public')->get($diskPath));

        $mapLink = "https://www.google.com/maps?q={$lat},{$lng}";
        $address = $this->geocoder->reverseGeocode($lat, $lng);

        [$locationFlagged, $distanceMeters] = $this->checkGeofence($job, $lat, $lng);

        $photo = $this->repository->create([
            'job_id' => $job->id,
            'uploaded_by' => $uploader->id,
            'path' => $diskPath,
            'content_hash' => $contentHash,
            'stage' => $stage,
            'latitude' => $lat,
            'longitude' => $lng,
            'location_captured' => true,
            'location_flagged' => $locationFlagged,
            'distance_from_machine_meters' => $distanceMeters,
            'map_link' => $mapLink,
            'address' => $address,
            'captured_at' => now(),
        ]);

        $description = $locationFlagged
            ? "{$uploader->name} uploaded a proof photo (⚠ outside expected radius of the machine)."
            : "{$uploader->name} uploaded a proof photo (location captured).";
        $this->auditLogger->log($job, $uploader, 'photo_uploaded', $description);

        return $photo;
    }

    /**
     * @return array{0: bool, 1: float|null} [location_flagged, distance_meters]
     */
    private function checkGeofence(Job $job, float $lat, float $lng): array
    {
        $machine = $job->machine;

        if ($machine === null || $machine->latitude === null || $machine->longitude === null) {
            // Nothing registered to compare against - can't flag what we
            // can't verify either way.
            return [false, null];
        }

        $distance = HaversineDistance::meters($lat, $lng, (float) $machine->latitude, (float) $machine->longitude);

        return [$distance > self::GEOFENCE_THRESHOLD_METERS, round($distance, 2)];
    }
}
