<a class="brand workspace-brand" href="{{ auth()->user()->can('dashboard.view') ? route('dashboard') : route('landing') }}">warbun<span>{{ __('Store workspace') }}</span></a>
<nav class="workspace-navigation" aria-label="{{ __('Main navigation') }}">
@php
    $groups = [
        'Overview' => [['dashboard','Dashboard','dashboard.view']],
        'Operations' => [['pos.index','POS / Cashier','pos.access'],['pos.history','Sales History','sales.view'],['orders.monitor','Order monitoring','orders.view'],['orders.index','Orders','orders.view'],['payments.index','Payments','payments.view'],['refunds.index','Returns / Refunds','payments.view'],['shifts.index','Cashier Shifts','pos.access']],
        'Catalog & stock' => [['products.index','Products','products.view'],['shelves.index','Product placement','products.view'],['inventory.index','Inventory','inventory.view'],['opnames.index','Stock Opname','inventory.opname']],
        'Customers' => [['customers.index','Customers','customers.view'],['debt.index','Debt','debt.view']],
        'Management' => [['users.index','Users & Roles','users.view'],['reports.index','Reports','reports.view'],['settings.index','Settings','settings.view'],['audit.index','Audit Log','audit.view']],
    ];
    $masters = [['categories.index','Categories'],['product-types.index','Product Types'],['brands.index','Brands'],['units.index','Units'],['suppliers.index','Suppliers']];
    $icons = [
        'shelves.index' => 'layer-group',
        'orders.monitor' => 'clipboard-list',
        'dashboard' => 'gauge-high', 'pos.index' => 'cash-register', 'pos.history' => 'receipt',
        'orders.index' => 'bag-shopping', 'payments.index' => 'credit-card', 'refunds.index' => 'rotate-left',
        'shifts.index' => 'clock', 'products.index' => 'box', 'inventory.index' => 'boxes-stacked',
        'opnames.index' => 'clipboard-check', 'customers.index' => 'users', 'debt.index' => 'file-invoice-dollar',
        'users.index' => 'user-shield', 'reports.index' => 'chart-column', 'settings.index' => 'gear',
        'audit.index' => 'clipboard-list', 'categories.index' => 'tags', 'product-types.index' => 'shapes',
        'brands.index' => 'tag', 'units.index' => 'ruler', 'suppliers.index' => 'truck',
    ];
@endphp
@foreach($groups as $group => $links)
@if(collect($links)->contains(fn($link) => auth()->user()->can($link[2])))
<section class="nav-group"><h2>{{ __($group) }}</h2>
@foreach($links as [$destination,$label,$permission])
@can($permission)
@php($active = $destination === 'pos.index' ? request()->routeIs('pos.index','pos.checkout') : ($destination === 'pos.history' ? request()->routeIs('pos.history','pos.receipt') : request()->routeIs(explode('.', $destination)[0].'.*', $destination)))
@if(str_starts_with($destination, 'orders.'))@php($active = $destination === 'orders.monitor' ? request()->routeIs('orders.monitor','orders.delivery-status') : request()->routeIs('orders.index','orders.show','orders.update-status','orders.payment'))@endif
<a class="nav-link {{ $active ? 'is-active' : '' }}" href="{{ route($destination) }}" @if($active) aria-current="page" @endif><x-icon :name="$icons[$destination]" /><span>{{ __($label) }}</span></a>
@endcan
@endforeach
@if($group === 'Catalog & stock')@can('products.view')
<details class="master-navigation" @if(request()->routeIs('categories.*','product-types.*','brands.*','units.*','suppliers.*')) open @endif><summary><x-icon name="layer-group" /><span>{{ __('Master data') }}</span><x-icon name="chevron-down" class="disclosure-icon" /></summary><div>@foreach($masters as [$destination,$label])<a class="nav-link {{ request()->routeIs(explode('.', $destination)[0].'.*') ? 'is-active' : '' }}" href="{{ route($destination) }}" @if(request()->routeIs(explode('.', $destination)[0].'.*')) aria-current="page" @endif><x-icon :name="$icons[$destination]" /><span>{{ __($label) }}</span></a>@endforeach</div></details>
@endcan @endif
</section>@endif
@endforeach
</nav>
<div class="sidebar-bottom"><span class="status-dot" aria-hidden="true"></span>{{ auth()->user()->name }}</div>
