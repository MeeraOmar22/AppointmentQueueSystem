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
        // Redirect developers to developer dashboard
        if (auth()->check() && auth()->user()->role === 'developer') {
            return redirect('/developer/dashboard');
        }

        // Redirect staff to appointments
        if (auth()->check() && in_array(auth()->user()->role, ['staff', 'admin'])) {
            return redirect('/staff/appointments');
        }

        return view('home');
    }
}
