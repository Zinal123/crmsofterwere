<?php

namespace Database\Seeders;

use App\Models\TicketProblemType;
use Illuminate\Database\Seeder;

class TicketProblemTypeSeeder extends Seeder
{
    /**
     * Starter list - admin-editable afterward via the Problem Types master
     * screen, so this doesn't need to be the final word on what's covered.
     */
    public const PROBLEM_TYPES = [
        'electrical' => [
            'Power Supply Failure',
            'Wiring Fault',
            'Motor Not Running',
            'Control Panel Error',
            'PLC Fault',
            'Sensor Fault',
            'Short Circuit',
            'Cable Damage',
        ],
        'mechanical' => [
            'Cutting Head Misalignment',
            'Nozzle Damage',
            'Belt / Gear Wear',
            'Rail / Guide Wear',
            'Lubrication Issue',
            'Bearing Failure',
            'Unusual Vibration / Noise',
            'Frame / Structural Issue',
        ],
    ];

    public function run(): void
    {
        foreach (self::PROBLEM_TYPES as $category => $names) {
            foreach ($names as $name) {
                TicketProblemType::firstOrCreate(['category' => $category, 'name' => $name]);
            }
        }
    }
}
