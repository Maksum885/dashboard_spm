<?php

use App\Models\RoomCamera;
use App\Models\TestingRoom;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_cameras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('testing_room_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('slot');
            $table->string('name');
            $table->string('rtsp_host');
            $table->unsignedSmallInteger('rtsp_port')->default(554);
            $table->string('rtsp_path')->default('/Streaming/Channels/102');
            $table->string('rtsp_username')->nullable();
            $table->string('rtsp_password')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();

            $table->unique(['testing_room_id', 'slot']);
        });

        foreach (TestingRoom::query()->pluck('id') as $roomId) {
            foreach ([1 => 'Camera 1', 2 => 'Camera 2'] as $slot => $name) {
                RoomCamera::firstOrCreate(
                    ['testing_room_id' => $roomId, 'slot' => $slot],
                    [
                        'name' => $name,
                        'rtsp_host' => '192.168.1.64',
                        'rtsp_port' => 554,
                        'rtsp_path' => '/Streaming/Channels/102',
                        'rtsp_username' => 'admin',
                        'rtsp_password' => 'changeme',
                        'is_enabled' => false,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('room_cameras');
    }
};
