<?php

namespace App\Http\Controllers;

use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

class AdminActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminActivityLog::with('actor')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where('summary', 'like', "%{$keyword}%");
        }

        $logs = $query->paginate(30)->withQueryString();

        return view('admin.activity-logs.index', compact('logs'));
    }
}
