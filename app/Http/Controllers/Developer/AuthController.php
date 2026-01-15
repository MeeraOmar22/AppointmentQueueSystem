<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Show the developer login form
     */
    public function showLoginForm()
    {
        // If already authenticated as developer, redirect to dashboard
        if (Auth::check() && Auth::user()->role === 'developer') {
            return redirect('/developer/dashboard');
        }

        return view('developer.auth.login');
    }

    /**
     * Handle developer login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // Attempt to authenticate
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'developer',
        ], $request->remember ?? false)) {
            return redirect('/developer/dashboard')->with('success', 'Developer access granted!');
        }

        return back()
            ->withErrors(['email' => 'Invalid credentials or you do not have developer access.'])
            ->withInput($request->only('email'));
    }

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
