<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $users   = User::orderBy('name')->get();
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        $query = ActivityLog::with('user')->orderByDesc('created_at');

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }
        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if (!$request->hasAny(['user_id','action','date_from','date_to'])) {
            // Default: hari ini
            $query->whereDate('created_at', today());
        }

        $logs  = $query->limit(500)->get();
        $total = $query->toBase()->getCountForPagination();

        return view('activity-logs.index', compact('logs', 'users', 'actions', 'total'));
    }
}
