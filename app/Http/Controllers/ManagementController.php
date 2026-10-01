<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ManagementController extends Controller
{
    public function users(Request $request)
    {
        $users = User::with('roles')->when($request->search, fn ($q) => $q->where('name', 'like', '%'.$request->search.'%'))->orderBy('name')->paginate(20);
        $roles = Role::with('permissions')->get();
        $permissions = Permission::orderBy('name')->get();

        return view('management.users', compact('users', 'roles', 'permissions'));
    }

    public function storeUser(Request $request)
    {
        $v = $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email', 'password' => 'required|string|min:12', 'role' => 'required|exists:roles,name']);
        abort_unless(auth()->user()->can('permissions.assign'), 403);
        $this->authorizeRole(Role::findByName($v['role']));
        DB::transaction(function () use ($v) {
            $user = User::create($v);
            $user->assignRole($v['role']);
            AuditLog::log('user.created', $user, null, $user->only(['name', 'email']));
        });

        return back()->with('success', __('Staff created'));
    }

    public function updateUser(Request $request, User $user)
    {
        $v = $request->validate(['role' => 'required|exists:roles,name', 'is_active' => 'required|boolean']);
        abort_unless(auth()->user()->can('permissions.assign'), 403);
        abort_if($user->id === auth()->id(), 422);
        if ($user->is_active !== $request->boolean('is_active')) {
            abort_unless(auth()->user()->can('users.disable'), 403);
        }
        foreach ($user->roles as $currentRole) {
            $this->authorizeRole($currentRole);
        }
        $this->authorizeRole(Role::findByName($v['role']));
        DB::transaction(function () use ($v, $user) {
            $user = User::lockForUpdate()->findOrFail($user->id);
            $old = $user->only(['is_active']);
            $old['roles'] = $user->getRoleNames()->all();
            $user->syncRoles([$v['role']]);
            $user->update(['is_active' => $v['is_active']]);
            AuditLog::log('user.permissions_updated', $user, $old, ['is_active' => $user->is_active, 'role' => $v['role']]);
        });

        return back()->with('success', __('Staff updated'));
    }

    public function role(Request $request, Role $role)
    {
        $v = $request->validate(['permissions' => 'sometimes|array', 'permissions.*' => 'required|distinct|exists:permissions,name']);
        abort_unless(auth()->user()->can('permissions.assign'), 403);
        abort_if(in_array($role->name, ['super-admin', 'owner']), 422);
        $this->authorizeRole($role);
        foreach ($v['permissions'] ?? [] as $permission) {
            abort_unless(auth()->user()->can($permission), 403);
        }
        DB::transaction(function () use ($role, $v) {
            $old = $role->permissions->pluck('name')->all();
            $role->syncPermissions($v['permissions'] ?? []);
            AuditLog::log('role.updated', $role, ['permissions' => $old], $v);
        });

        return back()->with('success', __('Role permissions updated'));
    }

    private function authorizeRole(Role $role): void
    {
        if ($role->name === 'super-admin') {
            abort_unless(auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('owner'), 403);
        }
        foreach ($role->permissions as $permission) {
            abort_unless(auth()->user()->can($permission->name), 403);
        }
    }

    public function settings()
    {
        $settings = DB::table('store_settings')->pluck('value', 'key');

        return view('management.settings', compact('settings'));
    }

    public function saveSettings(Request $request)
    {
        $v = $request->validate(['store_name' => 'required|string|max:120', 'debt_terms' => 'required|integer|min:1|max:365', 'shipping_cost' => 'required|decimal:0,2|min:0']);
        DB::transaction(function () use ($v) {
            $old = DB::table('store_settings')->pluck('value', 'key')->all();
            foreach ($v as $key => $value) {
                DB::table('store_settings')->updateOrInsert(['key' => $key], ['value' => (string) $value, 'created_at' => now(), 'updated_at' => now()]);
            }AuditLog::log('settings.updated', null, $old, $v);
        });

        return back()->with('success', __('Settings saved'));
    }
}
