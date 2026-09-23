<?php

namespace App\Services\Integration;

class TallyResponse
{
    public function __construct(
        public readonly bool $accepted,
        public readonly ?string $tallyVoucherId,
        public readonly ?string $errorMessage,
        public readonly ?string $rawBody = null,
    ) {
    }

    /**
     * Parse Tally's XML-import response. Tag names (CREATED/ERRORS/
     * LASTVCHID/LINEERROR/EXCEPTIONS) follow Tally's documented format but
     * are not yet confirmed against this project's real server - see
     * docs/superpowers/specs/2026-09-23-tally-direct-connection-design.md.
     */
    public static function fromXml(string $body): self
    {
        $xml = @simplexml_load_string($body);

        if ($xml === false) {
            return new self(accepted: false, tallyVoucherId: null, errorMessage: 'Tally returned a response that could not be parsed as XML.', rawBody: $body);
        }

        $errors = isset($xml->ERRORS) ? (int) $xml->ERRORS : 0;
        $created = isset($xml->CREATED) ? (int) $xml->CREATED : 0;

        if ($errors > 0 || $created < 1) {
            $message = isset($xml->LINEERROR)
                ? (string) $xml->LINEERROR
                : (isset($xml->EXCEPTIONS) ? (string) $xml->EXCEPTIONS : 'Tally rejected the voucher (no error detail returned).');

            return new self(accepted: false, tallyVoucherId: null, errorMessage: $message, rawBody: $body);
        }

        return new self(
            accepted: true,
            tallyVoucherId: isset($xml->LASTVCHID) ? (string) $xml->LASTVCHID : null,
            errorMessage: null,
            rawBody: $body,
        );
    }

    public static function networkFailure(string $message): self
    {
        return new self(accepted: false, tallyVoucherId: null, errorMessage: $message, rawBody: null);
    }
}
