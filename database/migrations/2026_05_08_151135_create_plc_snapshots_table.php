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
        Schema::create('plc_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plc_device_id')->nullable()->constrained()->onDelete('no action');
            $table->foreignId('testing_room_id')->nullable()->constrained()->onDelete('no action');

            // Simpan semua nilai register sebagai JSON
            $table->json('register_values');
            /*
            Contoh isi JSON:
            {
                "40001": { "name": "tekanan_masuk", "value": 0, "raw": 0 },
                "40002": { "name": "alarm_alert",   "value": true, "raw": 1 },
                "40003": { "name": "mode_maintenance", "value": false, "raw": 0 },
                ...
                "40011": { "name": "pressure_1", "value": 1.5, "raw": 15000 },
                "40012": { "name": "pressure_2", "value": 2.1, "raw": 21000 }
            }
            */

            $table->enum('poll_status', ['success', 'timeout', 'error'])->default('success');
            $table->string('error_message')->nullable();
            $table->timestamp('polled_at');

            $table->index(['testing_room_id', 'polled_at']);
            // Simpan per 5 detik, bisa banyak data — pertimbangkan partitioning/pruning
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plc_snapshots');
    }
};
