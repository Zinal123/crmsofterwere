<?php

namespace App\Services\Integration;

use App\Models\TallyConnection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class TallyClient
{
    public function post(TallyConnection $connection, string $xml, int $timeoutSeconds): TallyResponse
    {
        $url = sprintf('http://%s:%d', $connection->host, $connection->port);

        try {
            $response = Http::timeout($timeoutSeconds)->withBody($xml, 'text/xml')->post($url);
        } catch (ConnectionException $e) {
            return TallyResponse::networkFailure('Could not reach Tally server: '.$e->getMessage());
        }

        if ($response->failed()) {
            return TallyResponse::networkFailure('Tally server responded with HTTP '.$response->status().'.');
        }

        return TallyResponse::fromXml($response->body());
    }
}
