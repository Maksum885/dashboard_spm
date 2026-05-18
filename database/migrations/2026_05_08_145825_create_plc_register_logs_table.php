<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plc_register_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plc_device_id')->nullable()->constrained()->onDelete('no action');
            $table->foreignId('testing_room_id')->nullable()->constrained()->onDelete('no action');
            $table->foreignId('control_room_id')->nullable()->constrained()->onDelete('no action');

            // Register info
            $table->integer('register_address');       // 40001, 40002, dst
            $table->string('register_name');           // "mode_maintenance", "alarm_alert"
            $table->string('register_description');    // "Panel sedang di mode maintenance"

            // Nilai perubahan
            $table->integer('old_value')->nullable();  // Nilai sebelumnya
            $table->integer('new_value');              // Nilai baru
            $table->string('decoded_value')->nullable(); // Misal: "true", "1.5 bar"
            $table->enum('change_type', [
                'status_change',    // Bit/boolean berubah
                'value_change',     // Angka berubah (pressure dll)
                'alarm_triggered',  // Alarm aktif (emergency on)
                'alarm_cleared',    // Alarm selesai (emergency off)
                'maintenance_on',   // Masuk maintenance
                'maintenance_off',  // Keluar maintenance
                'testing_started',  // Testing dimulai
                'testing_stopped',  // Testing selesai
                'roof_moving',      // Roof bergerak
                'door_locked',      // Pintu dikunci
            ])->default('status_change');

            $table->timestamp('occurred_at');          // Waktu kejadian di PLC
            $table->timestamps();

            // Index untuk query cepat per room & per waktu
            $table->index(['testing_room_id', 'occurred_at']);
            $table->index(['control_room_id', 'occurred_at']);
            $table->index(['register_address', 'occurred_at']);
            $table->index('change_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plc_register_logs');
    }
};
