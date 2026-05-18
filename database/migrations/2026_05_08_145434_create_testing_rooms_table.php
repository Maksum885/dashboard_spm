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
        Schema::create('testing_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('control_room_id')->constrained()->cascadeOnDelete();
            $table->string('name');           // "Test Cell 1", "Test Pit 1"
            $table->string('code')->unique(); // "TC1", "TP1"
            $table->enum('type', ['cell', 'pit']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testing_rooms');
    }
};
