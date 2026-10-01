<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'products.view', 'products.create', 'products.update', 'products.archive', 'products.delete',
            'inventory.view', 'inventory.stock-in', 'inventory.stock-out', 'inventory.adjust', 'inventory.opname', 'inventory.approve-adjustment',
            'pos.access', 'sales.view', 'sales.create', 'sales.cancel', 'sales.refund', 'sales.override-price', 'sales.override-discount',
            'orders.view', 'orders.create', 'orders.update', 'orders.confirm', 'orders.cancel', 'orders.complete',
            'payments.view', 'payments.create', 'payments.refund', 'payments.correct',
            'customers.view', 'customers.create', 'customers.update', 'customers.block', 'customers.view-debt',
            'debt.view', 'debt.create', 'debt.pay', 'debt.adjust', 'debt.write-off', 'debt.override-credit-limit',
            'users.view', 'users.create', 'users.update', 'users.disable',
            'roles.view', 'roles.create', 'roles.update',
            'permissions.view', 'permissions.assign',
            'reports.view', 'reports.sales', 'reports.inventory', 'reports.payments', 'reports.debt', 'reports.staff',
            'audit.view', 'settings.view', 'settings.update',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        $owner = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner->givePermissionTo(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->givePermissionTo(['dashboard.view', 'products.view', 'products.create', 'products.update', 'products.archive', 'inventory.view', 'inventory.stock-in', 'inventory.stock-out', 'inventory.adjust', 'inventory.opname', 'sales.view', 'pos.access', 'orders.view', 'orders.update', 'orders.confirm', 'orders.complete', 'payments.view', 'customers.view', 'customers.create', 'customers.update', 'debt.view', 'debt.pay', 'reports.view', 'reports.sales', 'reports.inventory', 'reports.payments', 'reports.debt', 'reports.staff', 'users.view', 'audit.view']);

        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $cashier->givePermissionTo(['dashboard.view', 'products.view', 'inventory.view', 'pos.access', 'sales.view', 'sales.create', 'customers.view', 'customers.create', 'debt.view', 'debt.create', 'debt.pay', 'payments.view', 'payments.create', 'orders.view', 'orders.update']);

        $customer = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        $customer->syncPermissions([]);

        User::firstOrCreate(['email' => 'admin@warbun.local'], ['name' => 'Admin', 'password' => bcrypt('password')])->assignRole('super-admin');
        User::firstOrCreate(['email' => 'owner@warbun.local'], ['name' => 'Owner', 'password' => bcrypt('password')])->assignRole('owner');
        User::firstOrCreate(['email' => 'manager@warbun.local'], ['name' => 'Manager', 'password' => bcrypt('password')])->assignRole('manager');
        User::firstOrCreate(['email' => 'kasir@warbun.local'], ['name' => 'Kasir', 'password' => bcrypt('password')])->assignRole('cashier');
    }
}
