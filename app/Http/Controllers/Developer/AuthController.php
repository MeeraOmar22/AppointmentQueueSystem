<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Handle developer logout
     */
    public function logout()
    {
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();

        return redirect('/')->with('success', 'You have been logged out.');
    }
}
