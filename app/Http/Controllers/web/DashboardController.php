<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Halaman utama — token API (Sanctum) bisa di-flash sekali setelah login web.
     */
    public function index(Request $request)
    {
        return view('shared.dashboard');
    }
}
