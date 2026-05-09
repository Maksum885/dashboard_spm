<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\ControlRoom;
use App\Models\TestingRoom;
use App\Models\PlcDevice;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Control Rooms ──────────────────────────────────────────────────────
        $cr1 = ControlRoom::create(['name' => 'Control Room 1', 'code' => 'CR1', 'description' => 'Cell 1 - Cell 2 - Cell 3']);
        $cr2 = ControlRoom::create(['name' => 'Control Room 2', 'code' => 'CR2', 'description' => 'Cell 4 - Cell 5 - Pit 1 - Pit 2']);
        $cr3 = ControlRoom::create(['name' => 'Control Room 3', 'code' => 'CR3', 'description' => 'Pit 3 - Pit 4 - Pit 5 - Pit 6']);

        // ── Testing Rooms ──────────────────────────────────────────────────────
        $testingRooms = [
            // Control Room 1
            ['control_room_id' => $cr1->id, 'name' => 'Test Cell 1', 'code' => 'TC1', 'type' => 'cell'],
            ['control_room_id' => $cr1->id, 'name' => 'Test Cell 2', 'code' => 'TC2', 'type' => 'cell'],
            ['control_room_id' => $cr1->id, 'name' => 'Test Cell 3', 'code' => 'TC3', 'type' => 'cell'],
            // Control Room 2
            ['control_room_id' => $cr2->id, 'name' => 'Test Cell 4', 'code' => 'TC4', 'type' => 'cell'],
            ['control_room_id' => $cr2->id, 'name' => 'Test Cell 5', 'code' => 'TC5', 'type' => 'cell'],
            ['control_room_id' => $cr2->id, 'name' => 'Test Pit 1',  'code' => 'TP1', 'type' => 'pit'],
            ['control_room_id' => $cr2->id, 'name' => 'Test Pit 2',  'code' => 'TP2', 'type' => 'pit'],
            // Control Room 3
            ['control_room_id' => $cr3->id, 'name' => 'Test Pit 3',  'code' => 'TP3', 'type' => 'pit'],
            ['control_room_id' => $cr3->id, 'name' => 'Test Pit 4',  'code' => 'TP4', 'type' => 'pit'],
            ['control_room_id' => $cr3->id, 'name' => 'Test Pit 5',  'code' => 'TP5', 'type' => 'pit'],
            ['control_room_id' => $cr3->id, 'name' => 'Test Pit 6',  'code' => 'TP6', 'type' => 'pit'],
        ];

        foreach ($testingRooms as $i => $testingRoomData) {
            $testingRoom = TestingRoom::create($testingRoomData);

            // Buat PLC device untuk setiap test room
            PlcDevice::create([
                'testing_room_id' => $testingRoom->id,
                'name'         => "PLC {$testingRoomData['name']}",
                'ip_address'   => '192.168.1.' . (100 + $i + 1),
                'port'         => 502,
                'unit_id'      => 1,
                'is_enabled'   => true,
                'status'       => 'offline',
            ]);
        }

        // ── Users ──────────────────────────────────────────────────────────────
        // Super Admin
        User::create([
            'name'            => 'Administrator',
            'email'           => 'admin@spm-scada.com',
            'password'        => Hash::make('admin123'),
            'role'            => 'admin',
            'control_room_id' => null,
            'is_active'       => true,
        ]);

        // Operator per Control Room
        User::create([
            'name'            => 'Operator CR1',
            'email'           => 'operator1@spm-scada.com',
            'password'        => Hash::make('operator123'),
            'role'            => 'operator',
            'control_room_id' => $cr1->id,
            'is_active'       => true,
        ]);

        User::create([
            'name'            => 'Operator CR2',
            'email'           => 'operator2@spm-scada.com',
            'password'        => Hash::make('operator123'),
            'role'            => 'operator',
            'control_room_id' => $cr2->id,
            'is_active'       => true,
        ]);

        User::create([
            'name'            => 'Operator CR3',
            'email'           => 'operator3@spm-scada.com',
            'password'        => Hash::make('operator123'),
            'role'            => 'operator',
            'control_room_id' => $cr3->id,
            'is_active'       => true,
        ]);

        // Viewer
        User::create([
            'name'            => 'Viewer',
            'email'           => 'viewer@spm-scada.com',
            'password'        => Hash::make('viewer123'),
            'role'            => 'viewer',
            'control_room_id' => $cr1->id,
            'is_active'       => true,
        ]);

        $this->command->info('✓ Seed selesai!');
        $this->command->line('  Admin:     admin@spm-scada.com / admin123');
        $this->command->line('  Operator1: operator1@spm-scada.com / operator123');
        $this->command->line('  Operator2: operator2@spm-scada.com / operator123');
        $this->command->line('  Operator3: operator3@spm-scada.com / operator123');
    }
}
