<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');
        
        if ($request->action) {
            $query->where('action', 'like', "%{$request->action}%");
        }
        
        if ($request->entity_type) {
            $query->where('entity_type', 'like', "%{$request->entity_type}%");
        }
        
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $logs = $query->latest()->paginate(30);
        
        return view('audit.index', compact('logs'));
    }

    public function show(AuditLog $auditLog)
    {
        return view('audit.show', compact('auditLog'));
    }
}
