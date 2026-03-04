<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/rooms', [App\Http\Controllers\Api\RoomController::class, 'index']);