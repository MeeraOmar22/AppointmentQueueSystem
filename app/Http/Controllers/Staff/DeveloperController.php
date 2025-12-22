<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DeveloperController extends Controller
{
    private const DEVELOPER_PASSWORD = 'dev2025'; // Change this to your preferred password

    public function login()
    {
        // If already authenticated, redirect to dashboard
        if (Session::get('developer_authenticated') === true) {
            return redirect('/staff/developer/dashboard');
        }

        return view('staff.developer.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if ($request->password === self::DEVELOPER_PASSWORD) {
            Session::put('developer_authenticated', true);
            return redirect('/staff/developer/dashboard')->with('success', 'Developer access granted!');
        }

        return back()->withErrors(['password' => 'Invalid developer password.'])->withInput();
    }

    public function dashboard()
    {
        // Check authentication
        if (Session::get('developer_authenticated') !== true) {
            return redirect('/staff/developer')->withErrors(['auth' => 'Please authenticate first.']);
        }

        return view('staff.developer.dashboard');
    }

    public function logout()
    {
        Session::forget('developer_authenticated');
        return redirect('/staff/developer')->with('success', 'Logged out from developer mode.');
    }

    public function apiTest()
    {
        // Check authentication
        if (Session::get('developer_authenticated') !== true) {
            return redirect('/staff/developer');
        }

        return view('staff.developer.api-test');
    }
}
