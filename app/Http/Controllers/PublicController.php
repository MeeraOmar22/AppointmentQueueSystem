<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Dentist;
use App\Models\OperatingHour;
use App\Models\User;

class PublicController extends Controller
{
    public function home()
    {
        return view('public.home', [
            'operatingHours' => OperatingHour::orderBy('day_of_week')->orderBy('start_time')->get()
        ]);
    }

    public function about()
    {
        return view('public.about', [
            'operatingHours' => OperatingHour::orderBy('day_of_week')->orderBy('start_time')->get()
        ]);
    }

    public function services()
    {
        return view('public.services', [
            'services' => Service::where('status', 1)->get(),
            'operatingHours' => OperatingHour::orderBy('day_of_week')->orderBy('start_time')->get()
        ]);
    }

    public function dentists()
    {
        return view('public.dentists', [
            'dentists' => Dentist::where('status', 1)->get(),
            'operatingHours' => OperatingHour::orderBy('day_of_week')->orderBy('start_time')->get()
        ]);
    }

    public function contact()
    {
        $staff = User::where('role', 'staff')
            ->where(function($q) {
                // show only those marked visible if column exists
                try {
                    $q->where('public_visible', true);
                } catch (\Throwable $e) {}
            })
            ->orderBy('name')
            ->get();
        return view('public.contact', [
            'operatingHours' => OperatingHour::orderBy('day_of_week')->orderBy('start_time')->get(),
            'staff' => $staff,
        ]);
    }

    public function hours()
    {
        $hours = OperatingHour::orderByRaw("FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');
        
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        return view('public.hours', compact('hours', 'daysOfWeek'));
    }
}
