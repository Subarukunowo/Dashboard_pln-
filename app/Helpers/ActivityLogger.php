<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(string $action, string $description, ?array $metadata = null, ?Request $request = null)
    {
        $req = $request ?? request();
        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => $action,
            'description' => $description,
            'ip_address'  => $req->ip(),
            'user_agent'  => $req->userAgent(),
            'metadata'    => $metadata,
        ]);
    }
}