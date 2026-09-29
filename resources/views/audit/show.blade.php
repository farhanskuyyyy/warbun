@extends('layouts.app')
@section('title', 'Audit Log Detail')
@section('header', 'Audit Log Detail')

@section('content')
<div class="max-w-2xl space-y-4">
    <div class="bg-white rounded-xl p-6 border border-gray-100">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold">Audit Log</h2>
                <p class="text-gray-500">{{ $auditLog->created_at->format('d M Y H:i:s') }}</p>
            </div>
            <a href="{{ route('audit.index') }}" class="px-3 py-1 bg-gray-100 text-gray-700 rounded text-sm">Back</a>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <p class="text-xs text-gray-500 uppercase">User</p>
                <p class="font-medium">{{ $auditLog->user?->name ?? 'System' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Action</p>
                <p class="font-medium">{{ $auditLog->action }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Entity Type</p>
                <p class="font-medium">{{ $auditLog->entity_type }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Entity ID</p>
                <p class="font-medium">{{ $auditLog->entity_id ?? '-' }}</p>
            </div>
        </div>

        @if($auditLog->ip_address)
            <div class="mb-4">
                <p class="text-xs text-gray-500 uppercase">IP Address</p>
                <p class="font-mono text-sm">{{ $auditLog->ip_address }}</p>
            </div>
        @endif

        @if($auditLog->old_values)
            <div class="mb-4">
                <p class="text-xs text-gray-500 uppercase mb-2">Old Values</p>
                <pre class="bg-gray-50 p-3 rounded-lg text-xs overflow-x-auto">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif

        @if($auditLog->new_values)
            <div>
                <p class="text-xs text-gray-500 uppercase mb-2">New Values</p>
                <pre class="bg-gray-50 p-3 rounded-lg text-xs overflow-x-auto">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif
    </div>
</div>
@endsection
