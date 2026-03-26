<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with(['user', 'company'])
            ->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('model_type', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        $logs = $query->paginate(30)->withQueryString();

        $actions = AuditLog::distinct()->pluck('action')->sort()->values();

        return inertia('Admin/AuditLogs/Index', [
            'logs'    => $logs,
            'filters' => $request->only('search', 'action'),
            'actions' => $actions,
        ]);
    }
}
