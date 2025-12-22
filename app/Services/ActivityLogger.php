<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(string $action, string $modelType, $modelId, string $description, $oldValues = null, $newValues = null)
    {
        $user = Auth::user();
        
        ActivityLog::create([
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $description,
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : 'System',
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
        ]);
    }
}
