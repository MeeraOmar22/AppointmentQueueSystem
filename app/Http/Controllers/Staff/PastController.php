<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Dentist;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class PastController extends Controller
{
    /**
     * Display past (deleted) dentists and staff
     */
    public function index()
    {
        $pastDentists = Dentist::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();
        
        $pastStaff = User::onlyTrashed()
            ->where('role', 'staff')
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('staff.past', compact('pastDentists', 'pastStaff'));
    }

    /**
     * Restore a deleted dentist
     */
    public function restoreDentist($id)
    {
        $dentist = Dentist::onlyTrashed()->findOrFail($id);
        $name = $dentist->name;

        $dentist->restore();

        ActivityLogger::log(
            'restored',
            'Dentist',
            $id,
            "Restored dentist: {$name}",
            $dentist->toArray(),
            null
        );

        return back()->with('success', "Dentist '{$name}' restored successfully");
    }

    /**
     * Permanently delete a deleted dentist
     */
    public function forceDeleteDentist($id)
    {
        $dentist = Dentist::onlyTrashed()->findOrFail($id);
        $name = $dentist->name;

        // Delete photo if exists
        if ($dentist->photo && file_exists(public_path($dentist->photo))) {
            @unlink(public_path($dentist->photo));
        }

        ActivityLogger::log(
            'permanently_deleted',
            'Dentist',
            $id,
            "Permanently deleted dentist: {$name}",
            $dentist->toArray(),
            null
        );

        $dentist->forceDelete();

        return back()->with('success', "Dentist '{$name}' permanently deleted");
    }

    /**
     * Restore a deleted staff member
     */
    public function restoreStaff($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $name = $user->name;

        $user->restore();

        ActivityLogger::log(
            'restored',
            'User',
            $id,
            "Restored staff '{$name}'",
            $user->only(['name', 'email', 'position', 'phone', 'photo', 'public_visible']),
            []
        );

        return back()->with('success', "Staff '{$name}' restored successfully");
    }

    /**
     * Permanently delete a deleted staff member
     */
    public function forceDeleteStaff($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $name = $user->name;

        // Delete photo if exists
        if ($user->photo && file_exists(public_path($user->photo))) {
            @unlink(public_path($user->photo));
        }

        ActivityLogger::log(
            'permanently_deleted',
            'User',
            $id,
            "Permanently deleted staff '{$name}'",
            $user->only(['name', 'email', 'position', 'phone', 'photo', 'public_visible']),
            []
        );

        $user->forceDelete();

        return back()->with('success', "Staff '{$name}' permanently deleted");
    }
}
