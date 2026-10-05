@extends('layouts.app')
@section('title', __('Audit Log'))
@section('header', __('Audit Log'))

@section('content')
<div class="space-y-4">
    <!-- Filters -->
    <div class="bg-white rounded-xl p-4 border border-gray-100">
        <form method="GET" class="flex gap-2 flex-wrap">
            <input type="text" name="action" value="{{ request('action') }}" placeholder="Action (e.g., product, sale)" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <input type="text" name="entity_type" value="{{ request('entity_type') }}" placeholder="{{ __('Entity type') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <select name="user_id" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">{{ __('All Users') }}</option>
                @foreach(\App\Models\User::all() as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">{{ __('Filter') }}</button>
        </form>
    </div>

    <!-- Log Table -->
    <div class="bg-white rounded-xl border border-gray-100 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Time') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('User') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Action') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('Entity') }}</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">{{ __('ID') }}</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-600">{{ __('Details') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="border-t border-gray-100 hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500">{{ $log->created_at->translatedFormat('d M Y H:i:s') }}</td>
                    <td class="px-4 py-3">{{ $log->user?->name ?? 'System' }}</td>
                    <td class="px-4 py-3">
                        @php
                            $actionColor = match(true) {
                                str_contains($log->action, 'created') => 'bg-green-100 text-green-700',
                                str_contains($log->action, 'updated') => 'bg-blue-100 text-blue-700',
                                str_contains($log->action, 'deleted') => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700'
                            };
                        @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $actionColor }}">{{ $log->action }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ class_basename($log->entity_type ?? '-') }}</td>
                    <td class="px-4 py-3">{{ $log->entity_id ?? '-' }}</td>
                    <td class="px-4 py-3 text-right">
                        <x-crud-action action="view" :href="route('audit.show', $log)" />
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('No audit logs found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex justify-center">{{ $logs->withQueryString()->links() }}</div>
</div>
@endsection
