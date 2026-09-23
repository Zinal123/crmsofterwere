<?php

namespace App\Services\Integration;

use App\Models\TallyConnection;
use Illuminate\Support\Facades\Http;

class TallyConnectionService
{
    private const TIMEOUT_SECONDS = 5;

    /**
     * Send a lightweight "list of companies" request and judge success by
     * whether the configured company name comes back in Tally's response.
     * The exact request/response shape (including how Security Control
     * credentials are actually authenticated) needs live verification
     * against the real server - see
     * docs/superpowers/specs/2026-09-23-tally-direct-connection-design.md.
     */
    public function testConnection(TallyConnection $connection): void
    {
        $xml = $this->buildListCompaniesRequest($connection);
        $url = sprintf('http://%s:%d', $connection->host, $connection->port);

        try {
            $httpResponse = Http::timeout(self::TIMEOUT_SECONDS)->withBody($xml, 'text/xml')->post($url);
        } catch (\Throwable $e) {
            $this->markFailed($connection, 'Could not reach Tally server: '.$e->getMessage());

            return;
        }

        if ($httpResponse->failed()) {
            $this->markFailed($connection, 'Tally server responded with HTTP '.$httpResponse->status().'.');

            return;
        }

        if (! str_contains(strtolower($httpResponse->body()), strtolower($connection->company_name))) {
            $this->markFailed($connection, "Tally did not return company \"{$connection->company_name}\" - check the company name and credentials.");

            return;
        }

        $connection->update([
            'status' => 'connected',
            'last_checked_at' => now(),
            'last_error' => null,
        ]);
    }

    private function markFailed(TallyConnection $connection, string $message): void
    {
        $connection->update([
            'status' => 'failed',
            'last_checked_at' => now(),
            'last_error' => $message,
        ]);
    }

    private function buildListCompaniesRequest(TallyConnection $connection): string
    {
        $dom = new \DOMDocument('1.0', 'utf-8');

        $envelope = $dom->createElement('ENVELOPE');
        $dom->appendChild($envelope);

        $header = $dom->createElement('HEADER');
        $header->appendChild($dom->createElement('TALLYREQUEST', 'Export'));
        $envelope->appendChild($header);

        $requestDesc = $dom->createElement('REQUESTDESC');
        $requestDesc->appendChild($dom->createElement('REPORTNAME', 'List of Companies'));

        $staticVariables = $dom->createElement('STATICVARIABLES');
        $staticVariables->appendChild($dom->createElement('SVCURRENTUSER', htmlspecialchars($connection->username, ENT_XML1)));
        $staticVariables->appendChild($dom->createElement('SVCURRENTUSERPASSWORD', htmlspecialchars($connection->password, ENT_XML1)));
        $requestDesc->appendChild($staticVariables);

        $exportData = $dom->createElement('EXPORTDATA');
        $exportData->appendChild($requestDesc);

        $body = $dom->createElement('BODY');
        $body->appendChild($exportData);
        $envelope->appendChild($body);

        return $dom->saveXML();
    }
}
