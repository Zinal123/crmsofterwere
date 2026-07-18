<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageCompressor
{
    public function compress(UploadedFile $file, string $destinationDiskPath, int $maxDimension = 1600, int $quality = 75): void
    {
        $image = $this->readImage($file->getRealPath(), $file->getMimeType());

        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(1, $maxDimension / max($width, $height));

        if ($scale < 1) {
            $newWidth = (int) round($width * $scale);
            $newHeight = (int) round($height * $scale);
            $resized = imagescale($image, $newWidth, $newHeight);
            imagedestroy($image);
            $image = $resized;
        }

        ob_start();
        imagejpeg($image, null, $quality);
        $contents = ob_get_clean();
        imagedestroy($image);

        Storage::disk('public')->put($destinationDiskPath, $contents);
    }

    private function readImage(string $path, ?string $mimeType)
    {
        return match ($mimeType) {
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            default => imagecreatefromjpeg($path),
        };
    }
}
