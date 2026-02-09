<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Dentist;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;

class DentistController extends Controller
{
    public function index()
    {
        $dentists = Dentist::withCount('appointments')->latest()->get();
        return view('staff.dentists.index', compact('dentists'));
    }

    public function create()
    {
        return view('staff.dentists.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'specialization' => 'nullable|string|max:255',
            'years_of_experience' => 'nullable|integer|min:0',
            'bio' => 'nullable|string',
            'email' => 'nullable|email|unique:dentists,email',
            'phone' => 'nullable|string|max:20',
            'twitter_url' => 'nullable|url',
            'facebook_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'status' => 'required|boolean',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('uploads/dentists'), $filename);
            $data['photo'] = 'uploads/dentists/' . $filename;
        }

        $dentist = Dentist::create($data);

        ActivityLogger::log('created', 'Dentist', $dentist->id, "Created dentist: {$data['name']}", null, $dentist->toArray());

        return redirect('/staff/dentists')->with('success', 'Dentist added successfully.');
    }

    public function edit($id)
    {
        $dentist = Dentist::findOrFail($id);
        return view('staff.dentists.edit', compact('dentist'));
    }

    public function update(Request $request, $id)
    {
        $dentist = Dentist::findOrFail($id);
        $oldValues = $dentist->toArray();

        // For AJAX requests with only status, allow partial update
        if ($request->expectsJson() && $request->has('status') && !$request->has('name')) {
            $data = $request->validate([
                'status' => 'required|boolean',
            ]);
            
            $dentist->update($data);
            
            ActivityLogger::log('updated', 'Dentist', $dentist->id, "Updated dentist status: {$dentist->name}", $oldValues, $dentist->fresh()->only(['status']));
            
            return response()->json(['message' => 'Dentist updated successfully.']);
        }

        // Full update validation
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'specialization' => 'nullable|string|max:255',
            'years_of_experience' => 'nullable|integer|min:0',
            'bio' => 'nullable|string',
            'email' => 'nullable|email|unique:dentists,email,' . $dentist->id,
            'phone' => 'nullable|string|max:20',
            'twitter_url' => 'nullable|url',
            'facebook_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'status' => 'required|boolean',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($dentist->photo && file_exists(public_path($dentist->photo))) {
                unlink(public_path($dentist->photo));
            }
            
            $photo = $request->file('photo');
            $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('uploads/dentists'), $filename);
            $data['photo'] = 'uploads/dentists/' . $filename;
        }

        $dentist->update($data);

        ActivityLogger::log('updated', 'Dentist', $dentist->id, "Updated dentist: {$dentist->name}", $oldValues, $dentist->fresh()->toArray());

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Dentist updated successfully.']);
        }

        return redirect('/staff/dentists')->with('success', 'Dentist updated successfully.');
    }

    public function deactivate(Request $request, $id)
    {
        $dentist = Dentist::findOrFail($id);
        $old = $dentist->toArray();

        // Set status to inactive (0/false)
        $dentist->update(['status' => false]);

        ActivityLogger::log(
            'updated',
            'Dentist',
            $dentist->id,
            "Deactivated dentist: {$dentist->name}",
            $old,
            $dentist->fresh()->toArray()
        );

        // Return JSON for AJAX requests, redirect for form submissions
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Dentist deactivated successfully.']);
        }

        return redirect('/staff/dentists')->with('success', 'Dentist deactivated successfully.');
    }

    public function destroy($id)
    {
        $dentist = Dentist::findOrFail($id);
        $dentistName = $dentist->name;
        
        ActivityLogger::log('deleted', 'Dentist', $id, "Deleted dentist: {$dentistName}", $dentist->toArray(), null);
        
        // Soft delete instead of hard delete
        $dentist->delete();

        return redirect('/staff/dentists')->with('success', 'Dentist deleted successfully and moved to Past Records.');
    }

    public function bulkDestroy(Request $request)
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'exists:dentists,id',
            ]);

            // Fetch dentists and split into deletable vs blocked (has appointments)
            $allDentists = Dentist::whereIn('id', $validated['ids'])->get();
            $blocked = $allDentists->filter(fn($d) => $d->appointments()->exists());
            $deletable = $allDentists->reject(fn($d) => $d->appointments()->exists());

            // If there are deletable dentists, log and delete them
            if ($deletable->isNotEmpty()) {
                $before = $deletable->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->name,
                    'email' => $d->email,
                    'phone' => $d->phone,
                    'specialization' => $d->specialization,
                ])->values()->all();

                $names = array_map(fn($d) => $d['name'], $before);
                $desc = "Bulk deleted ".count($before)." dentists: ".implode(', ', $names);

                ActivityLogger::log('deleted', 'Dentist', null, $desc, ['items' => $before], null);

                Dentist::whereIn('id', $deletable->pluck('id'))->delete();
            }

            // Prepare user feedback
            $messages = [];
            if ($deletable->isNotEmpty()) {
                $messages['success'] = $deletable->count()." dentist(s) deleted successfully.";
            }
            if ($blocked->isNotEmpty()) {
                $blockedNames = $blocked->pluck('name')->implode(', ');
                $messages['error'] = "Could not delete the following dentist(s) because they have appointment records: {$blockedNames}.";
            }

            return redirect()->route('staff.dentists.index')
                ->with($messages);
        } catch (\Exception $e) {
            \Log::error('Bulk delete error', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update dentist status (for resource configuration)
     * Allows marking dentists as available, busy, on_break, or off
     */
    public function updateStatus(Request $request, Dentist $dentist)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,busy,on_break,off',
        ]);

        $oldStatus = $dentist->status ?? 'unknown';
        $dentist->update(['status' => $validated['status']]);

        ActivityLogger::log(
            'updated',
            'Dentist',
            $dentist->id,
            "Status updated: {$oldStatus} → {$validated['status']}",
            ['old_status' => $oldStatus],
            ['status' => $validated['status']]
        );

        return back()->with('success', 'Dentist status updated successfully.');
    }

    /**
     * Get dentist availability statistics (API endpoint)
     * Returns real-time dentist availability for queue assignment
     */
    public function stats()
    {
        $dentists = Dentist::all();

        return response()->json([
            'data' => $dentists,
        ]);
    }
}
