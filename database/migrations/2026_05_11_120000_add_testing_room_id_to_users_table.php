<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // SQL Server: avoid SET NULL + other FKs causing “multiple cascade paths”.
            $table->foreignId('testing_room_id')
                ->nullable()
                ->after('control_room_id')
                ->constrained('testing_rooms')
                ->noActionOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('testing_room_id');
        });
    }
};
