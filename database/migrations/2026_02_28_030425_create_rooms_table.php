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
        Schema::create("rooms", function (Blueprint $table) {
            $table->id();
            $table->string("room_code");      // cr1, cr2, pit1, tc1, dst
            $table->string("name");           // Control Room 1
            $table->string("section");        // Test Area, Phase, Bottom
            $table->string("type");           // control_room, test_pit, test_cell
            $table->string("status")->default("online"); // online, warning, alert
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
