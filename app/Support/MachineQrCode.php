<?php

namespace App\Support;

class MachineQrCode
{
    private const PREFIX = 'OMT-MACHINE-';

    /**
     * The exact string encoded into a machine's printed QR code. Keep this
     * format in sync with the scanner's parse regex in jobs/create.blade.php
     * (OMT-MACHINE-(\d+)) - there's no shared code between PHP and the
     * vendored JS decoder, so both sides just need to agree on the format.
     */
    public static function token(int $machineId): string
    {
        return self::PREFIX . $machineId;
    }

    public static function parseId(string $scanned): ?int
    {
        if (! str_starts_with($scanned, self::PREFIX)) {
            return null;
        }

        $id = substr($scanned, strlen(self::PREFIX));

        return ctype_digit($id) ? (int) $id : null;
    }
}
