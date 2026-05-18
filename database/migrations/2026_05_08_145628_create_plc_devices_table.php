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
        Schema::create('plc_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('testing_room_id')->constrained()->cascadeOnDelete();
            $table->string('name');                    // "PLC Test Pit 1"
            $table->string('ip_address');              // "192.168.1.1"
            $table->integer('port')->default(502);
            $table->integer('unit_id')->default(1);
            $table->boolean('is_enabled')->default(true);
            $table->enum('status', ['online', 'offline', 'error'])->default('offline');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plc_devices');
    }
};
