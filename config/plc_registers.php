<?php

/**
 * English display labels for PLC registers (UI only).
 * Internal keys (register_name) and Modbus addresses are unchanged.
 */
return [
    'labels' => [
        'tekanan_masuk' => 'Inlet pressure before testing starts',
        'alarm_alert' => 'Alarm / emergency',
        'mode_maintenance' => 'Panel in maintenance mode',
        'roof_tertutup' => 'Roof closed',
        'roof_terbuka' => 'Roof open',
        'roof_bergerak_buka' => 'Roof moving (opening)',
        'roof_bergerak_tutup' => 'Roof moving (closing)',
        'pintu_terkunci' => 'Access door locked',
        'testing_dimulai' => 'Testing in progress',
        'pressure_1' => 'Pressure 1 (PSI)',
        'pressure_2' => 'Pressure 2 (PSI)',
    ],

    /** Legacy Indonesian text stored in older log rows */
    'legacy_descriptions' => [
        'Tekanan Masuk Sebelum Testing Dimulai' => 'Inlet pressure before testing starts',
        'Panel Sedang Di Mode Maintenance' => 'Panel in maintenance mode',
        'Roof Tertutup' => 'Roof closed',
        'Roof Terbuka' => 'Roof open',
        'Roof Bergerak Membuka' => 'Roof moving (opening)',
        'Roof Bergerak Menutup' => 'Roof moving (closing)',
        'Pintu Akses Sedang Terkunci' => 'Access door locked',
        'Testing Sedang Dimulai' => 'Testing in progress',
    ],
];
