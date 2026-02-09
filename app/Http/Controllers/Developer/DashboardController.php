<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the developer dashboard
     */
    public function index()
    {
        // Developer only
        if (auth()->user()->role !== 'developer') {
            abort(403, 'Unauthorized');
        }
        
        // Get statistics
        $totalLogs = ActivityLog::count();
        $logsToday = ActivityLog::whereDate('created_at', today())->count();
        $logTypes = ActivityLog::select('action')
            ->distinct()
            ->pluck('action');

        // Get recent activity logs
        $recentLogs = ActivityLog::latest()
            ->paginate(25);

        return view('developer.dashboard.index', compact(
            'totalLogs',
            'logsToday',
            'logTypes',
            'recentLogs'
        ));
    }

    /**
     * Show activity logs
     */
    public function activityLogs(Request $request)
    {
        $query = ActivityLog::query();

        // Filter by action type
        if ($request->action_type) {
            $query->where('action', $request->action_type);
        }

        // Filter by model type
        if ($request->model_type) {
            $query->where('model_type', $request->model_type);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('user_id', 'like', "%{$search}%")
                  ->orWhere('model_id', 'like', "%{$search}%");
            });
        }

        $logs = $query->latest()->paginate(50);
        $actionTypes = ActivityLog::select('action')->distinct()->pluck('action');
        $modelTypes = ActivityLog::select('model_type')->distinct()->pluck('model_type');

        return view('developer.dashboard.activity-logs', compact(
            'logs',
            'actionTypes',
            'modelTypes'
        ));
    }

    /**
     * Show log details
     */
    public function logDetails($id)
    {
        $log = ActivityLog::findOrFail($id);

        return view('developer.dashboard.log-details', compact('log'));
    }

    /**
     * Show API testing tool
     */
    public function apiTest()
    {
        return view('developer.tools.api-test');
    }

    /**
     * Show system info
     * MEDIUM-002 FIX: Limit sensitive system information disclosure
     * Only show non-sensitive configuration details
     */
    public function systemInfo()
    {
        // HIGH-008 FIX: Consistent developer role check via middleware only
        // This controller is already protected by route middleware
        // No additional inline checks needed
        
        $info = [
            // Application info (safe)
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'laravel_version' => app()->version(),
            'php_version' => phpversion(),
            
            // Database info (safe - general only)
            'database' => config('database.default'),
            
            // Omit for security:
            // - database.connections.mysql.host (MEDIUM-002 FIX)
            // - database.connections.mysql.user (MEDIUM-002 FIX)
            // - database.connections.mysql.password (MEDIUM-002 FIX)
            // - API keys or secrets
            // - file paths leading to app root
        ];

        return view('developer.tools.system-info', compact('info'));
    }

    /**
     * Show database tools
     */
    public function databaseTools()
    {
        return view('developer.tools.database');
    }
}
