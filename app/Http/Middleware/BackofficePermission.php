<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BackofficePermission
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->is_active, 403);
        $name = $request->route()->getName();
        $map = [
            'pos.customers' => 'customers.create', 'orders.monitor' => 'orders.view', 'orders.delivery-status' => 'orders.update',
            'opnames.index' => 'inventory.opname', 'opnames.store' => 'inventory.opname', 'opnames.approve' => 'inventory.approve-adjustment', 'refunds.index' => 'payments.view', 'refunds.store' => 'sales.refund', 'shifts.index' => 'pos.access', 'debt.correction' => 'debt.view',
            'dashboard' => 'dashboard.view', 'pos.index' => 'pos.access', 'pos.products' => 'pos.access', 'pos.barcode' => 'pos.access', 'pos.process-sale' => 'sales.create',
            'pos.receipt' => 'sales.view', 'pos.history' => 'sales.view', 'pos.open-shift' => 'pos.access', 'pos.close-shift' => 'pos.access',
            'inventory.index' => 'inventory.view', 'inventory.stock-in' => 'inventory.stock-in', 'inventory.stock-out' => 'inventory.stock-out', 'inventory.adjust' => 'inventory.adjust',
            'debt.index' => 'debt.view', 'debt.dashboard' => 'debt.view', 'debt.create' => 'debt.create', 'debt.store' => 'debt.create', 'debt.payment' => 'debt.pay',
            'orders.payment' => 'payments.create', 'orders.index' => 'orders.view', 'orders.show' => 'orders.view', 'orders.update-status' => 'orders.update',
            'payments.index' => 'payments.view', 'payments.show' => 'payments.view', 'payments.create' => 'payments.create', 'payments.store' => 'payments.create',
            'payments.confirm' => 'payments.correct', 'payments.refund' => 'payments.refund', 'audit.index' => 'audit.view', 'audit.show' => 'audit.view',
        ];
        $permission = $map[$name] ?? null;
        if (! $permission && str_starts_with($name, 'reports.')) {
            $permission = $name === 'reports.index' ? 'reports.view' : $name;
        }
        if (! $permission) {
            [$resource,$action] = explode('.', $name, 2);
            $resource = in_array($resource, ['categories', 'product-types', 'brands', 'units', 'suppliers']) ? 'products' : $resource;
            $action = match ($action) {
                'index','show' => 'view','create','store' => 'create','edit','update' => 'update','destroy' => 'delete',default => $action
            };
            $permission = $resource.'.'.($resource === 'products' && $action === 'delete' && str_starts_with($name, 'products.') ? 'archive' : $action);
        }
        abort_unless($request->user()->can($permission), 403);

        return $next($request);
    }
}
