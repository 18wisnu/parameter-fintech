<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'owner') {
            abort(403);
        }

        // Activity Logs
        $activityLogs = ActivityLog::with('user')
            ->latest()
            ->limit(500)
            ->get();

        // Error Logs (Laravel Log)
        $errorLogs = $this->getLastLogs(500);

        return view('admin.logs.index', compact('activityLogs', 'errorLogs'));
    }

    private function getLastLogs($limit = 500)
    {
        $logFile = storage_path('logs/laravel.log');
        if (!File::exists($logFile)) {
            return [];
        }

        $content = File::get($logFile);
        $lines = explode("\n", $content);
        $lastLines = array_slice($lines, -$limit);
        
        $logs = [];
        $currentLog = null;

        foreach ($lastLines as $line) {
            if (preg_match('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', $line)) {
                if ($currentLog) {
                    $logs[] = $currentLog;
                }
                $currentLog = $line;
            } else {
                if ($currentLog) {
                    $currentLog .= "\n" . $line;
                }
            }
        }
        if ($currentLog) {
            $logs[] = $currentLog;
        }

        return array_reverse($logs);
    }
}
