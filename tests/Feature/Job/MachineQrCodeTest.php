<?php

namespace Tests\Feature\Job;

use App\Support\MachineQrCode;
use PHPUnit\Framework\TestCase;

class MachineQrCodeTest extends TestCase
{
    public function test_token_and_parse_round_trip(): void
    {
        $this->assertSame('OMT-MACHINE-42', MachineQrCode::token(42));
        $this->assertSame(42, MachineQrCode::parseId('OMT-MACHINE-42'));
    }

    public function test_parse_rejects_a_scan_of_something_else(): void
    {
        $this->assertNull(MachineQrCode::parseId('https://example.com'));
        $this->assertNull(MachineQrCode::parseId('OMT-MACHINE-'));
        $this->assertNull(MachineQrCode::parseId('OMT-MACHINE-abc'));
        $this->assertNull(MachineQrCode::parseId(''));
    }
}
