<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Debug: Log the current user's role
        if (auth()->check()) {
            \Log::info('User logged in', [
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'role' => auth()->user()->role,
            ]);
        }

        // Redirect developers to developer dashboard
        if (auth()->check() && auth()->user()->role === 'developer') {
            return redirect('/developer/dashboard');
        }

        // Redirect admins to admin system config
        if (auth()->check() && auth()->user()->role === 'admin') {
            return redirect('/staff/system-config');
        }

        // Redirect staff to appointments
        if (auth()->check() && auth()->user()->role === 'staff') {
            return redirect('/staff/appointments');
        }

        // For patients and other roles, show home page
        return view('home');
    }
}
