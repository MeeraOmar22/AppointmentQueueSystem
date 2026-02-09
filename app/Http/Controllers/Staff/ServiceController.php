<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::withCount('appointments')->latest()->get();
        return view('staff.services.index', compact('services'));
    }

    public function create()
    {
        return view('staff.services.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'status' => 'required|boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/services'), $filename);
            $data['image'] = 'uploads/services/' . $filename;
        }

        // Set estimated_duration from duration_minutes for backward compatibility
        $data['estimated_duration'] = $data['duration_minutes'];

        $service = Service::create($data);

        ActivityLogger::log('created', 'Service', $service->id, "Created service: {$data['name']}", null, $service->toArray());

        return redirect('/staff/services')->with('success', 'Service added successfully.');
    }

    public function edit($id)
    {
        $service = Service::findOrFail($id);
        return view('staff.services.edit', compact('service'));
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $oldValues = $service->toArray();

        // For AJAX requests with only status, allow partial update
        if ($request->expectsJson() && $request->has('status') && !$request->has('name')) {
            $data = $request->validate([
                'status' => 'required|boolean',
            ]);
            
            $service->update($data);
            
            ActivityLogger::log('updated', 'Service', $service->id, "Updated service status: {$service->name}", $oldValues, $service->fresh()->only(['status']));
            
            return response()->json(['message' => 'Service updated successfully.']);
        }

        // Full update validation
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'status' => 'required|boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($service->image && file_exists(public_path($service->image))) {
                unlink(public_path($service->image));
            }
            
            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/services'), $filename);
            $data['image'] = 'uploads/services/' . $filename;
        }

        // Set estimated_duration from duration_minutes for backward compatibility
        $data['estimated_duration'] = $data['duration_minutes'];

        $service->update($data);

        ActivityLogger::log('updated', 'Service', $service->id, "Updated service: {$service->name}", $oldValues, $service->fresh()->toArray());

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Service updated successfully.']);
        }

        return redirect('/staff/services')->with('success', 'Service updated successfully.');
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $serviceName = $service->name;
        
        ActivityLogger::log('deleted', 'Service', $id, "Deleted service: {$serviceName}", $service->toArray(), null);
        
        // Soft delete instead of hard delete
        $service->delete();

        return redirect('/staff/services')->with('success', 'Service deleted successfully and moved to Past Records.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:services,id',
        ]);

        $all = Service::whereIn('id', $validated['ids'])->get();
        $blocked = $all->filter(fn($s) => $s->appointments()->exists());
        $deletable = $all->reject(fn($s) => $s->appointments()->exists());

        if ($deletable->isNotEmpty()) {
            $before = $deletable->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'price' => $s->price,
                'estimated_duration' => $s->estimated_duration,
            ])->values()->all();
            $names = array_map(fn($d) => $d['name'], $before);
            ActivityLogger::log('deleted', 'Service', null, 'Bulk deleted '.count($before).' services: '.implode(', ', $names), ['items' => $before], null);
            Service::whereIn('id', $deletable->pluck('id'))->delete();
        }

        $messages = [];
        if ($deletable->isNotEmpty()) {
            $messages['success'] = $deletable->count().' service(s) deleted successfully.';
        }
        if ($blocked->isNotEmpty()) {
            $messages['error'] = 'Could not delete the following service(s) because they have appointment records: '.$blocked->pluck('name')->implode(', ').'.';
        }

        return redirect('/staff/services')->with($messages);
    }

    /**
     * Get services statistics (API endpoint)
     */
    public function stats()
    {
        $services = Service::all();

        return response()->json([
            'data' => $services->map(function (Service $service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'price' => $service->price,
                    'duration' => $service->duration_minutes,
                    'status' => $service->status ?? 1,
                ];
            }),
        ]);
    }
}

