<?php

namespace App\Support\Geocoding;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class NominatimGeocoder
{
    public function __construct(private ?Client $client = null)
    {
        $this->client ??= new Client(['timeout' => 3]);
    }

    public function reverseGeocode(float $lat, float $lng): ?string
    {
        try {
            $response = $this->client->get('https://nominatim.openstreetmap.org/reverse', [
                'query' => ['lat' => $lat, 'lon' => $lng, 'format' => 'json'],
                'headers' => ['User-Agent' => config('app.name') . ' JobTracking/1.0'],
            ]);

            $data = json_decode((string) $response->getBody(), true);

            return $data['display_name'] ?? null;
        } catch (\Throwable $e) {
            Log::warning('Reverse geocode failed', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
