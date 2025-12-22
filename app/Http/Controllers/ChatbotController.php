<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Dentist;

class ChatbotController extends Controller
{
    public function index()
    {
        session()->forget('chat_step');
        session()->forget('chat_data');

        return view('chat.index', [
            'services' => Service::all(),
            'dentists' => Dentist::all(),
        ]);
    }

    public function handle(Request $request)
    {
        // Initialize step & data
        $step = session('chat_step', 'name');
        $data = session('chat_data', []);

        $input = trim($request->input('value'));

        // Save input based on current step
        switch ($step) {
            case 'name':
                $data['patient_name'] = $input;
                $step = 'phone';
                break;

            case 'phone':
                $data['patient_phone'] = $input;
                $step = 'service';
                break;

            case 'service':
            $serviceId = $this->parseService($input);

            if (!$serviceId) {
                return response()->json([
                    'done' => false,
                    'next' => 'service',
                    'message' => 'I could not recognize the service. Please try again.'
                ]);
            }

            $data['service_id'] = $serviceId;
            $step = 'dentist';
            break;


            case 'dentist':
                $data['dentist_id'] = $input;
                $step = 'date';
                break;

            case 'date':
                $data['appointment_date'] = $this->parseDate($input);
                $step = 'time';
                break;


            case 'time':
                $data['appointment_time'] = $this->parseTime($input);
                $step = 'confirm';
                break;


            case 'confirm':

                $this->validateData($data);

                $available = $this->isDentistAvailable(
                    $data['dentist_id'],
                    $data['appointment_date'],
                    $data['appointment_time'],
                    $data['service_id']
                );

                if (!$available) {
                    return response()->json([
                        'done' => false,
                        'next' => 'time',
                        'message' => 'Selected time is not available. Please choose another time.'
                    ]);
                }

                Appointment::create($data);

                session()->forget('chat_step');
                session()->forget('chat_data');

                return response()->json([
                    'done' => true,
                    'message' => 'Your appointment has been successfully booked.'
                ]);

        }

        session([
            'chat_step' => $step,
            'chat_data' => $data
        ]);

        return response()->json([
            'done' => false,
            'next' => $step
        ]);
    }

    private function validateData(array $data)
    {
        foreach ([
            'patient_name',
            'patient_phone',
            'service_id',
            'dentist_id',
            'appointment_date',
            'appointment_time'
        ] as $field) {
            if (empty($data[$field])) {
                abort(400, "Missing $field");
            }
        }
    }

        private function parseDate($input)
    {
        $input = strtolower($input);

        if ($input === 'today') {
            return now()->toDateString();
        }

        if ($input === 'tomorrow') {
            return now()->addDay()->toDateString();
        }

        return $input; // assume YYYY-MM-DD
    }

    private function parseTime($input)
    {
        $input = strtolower($input);

        if ($input === 'now') {
            return now()->format('H:i');
        }

        if (str_contains($input, 'morning')) {
            return '10:00';
        }

        if (str_contains($input, 'afternoon')) {
            return '14:00';
        }

        if (str_contains($input, 'evening')) {
            return '17:00';
        }

        return $input; // assume HH:MM
    }

    private function parseService($input)
    {
        $input = strtolower($input);

        return \App\Models\Service::whereRaw(
            'LOWER(service_name) LIKE ?', ['%' . $input . '%']
        )->first()?->id;
    }

    private function isDentistAvailable($dentistId, $date, $time, $serviceId)
    {
        $service = \App\Models\Service::find($serviceId);
        $duration = $service->estimated_duration;

        $start = \Carbon\Carbon::parse("$date $time");
        $end   = (clone $start)->addMinutes($duration);

        return !\App\Models\Appointment::where('dentist_id', $dentistId)
            ->where('appointment_date', $date)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('appointment_time', [
                    $start->format('H:i'),
                    $end->format('H:i')
                ]);
            })
            ->exists();
    }


}
