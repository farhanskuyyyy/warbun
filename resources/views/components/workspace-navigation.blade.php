<a class="brand workspace-brand" href="{{ auth()->user()->can('dashboard.view') ? route('dashboard') : route('landing') }}">warbun<span>{{ __('Store workspace') }}</span></a>
<nav class="workspace-navigation" aria-label="{{ __('Main navigation') }}">
@php
    $groups = [
        'Overview' => [['dashboard','Dashboard','dashboard.view']],
        'Operations' => [['pos.index','POS / Cashier','pos.access'],['pos.history','Sales History','sales.view'],['orders.index','Orders','orders.view'],['payments.index','Payments','payments.view'],['refunds.index','Returns / Refunds','payments.view'],['shifts.index','Cashier Shifts','pos.access']],
        'Catalog & stock' => [['products.index','Products','products.view'],['inventory.index','Inventory','inventory.view'],['opnames.index','Stock Opname','inventory.opname']],
        'Customers' => [['customers.index','Customers','customers.view'],['debt.index','Debt','debt.view']],
        'Management' => [['users.index','Users & Roles','users.view'],['reports.index','Reports','reports.view'],['settings.index','Settings','settings.view'],['audit.index','Audit Log','audit.view']],
    ];
    $masters = [['categories.index','Categories'],['product-types.index','Product Types'],['brands.index','Brands'],['units.index','Units'],['suppliers.index','Suppliers']];
@endphp
@foreach($groups as $group => $links)
@if(collect($links)->contains(fn($link) => auth()->user()->can($link[2])))
<section class="nav-group"><h2>{{ __($group) }}</h2>
@foreach($links as [$destination,$label,$permission])
@can($permission)
@php($active = $destination === 'pos.index' ? request()->routeIs('pos.index','pos.checkout') : ($destination === 'pos.history' ? request()->routeIs('pos.history','pos.receipt') : request()->routeIs(explode('.', $destination)[0].'.*', $destination)))
<a class="nav-link {{ $active ? 'is-active' : '' }}" href="{{ route($destination) }}" @if($active) aria-current="page" @endif><span class="nav-marker" aria-hidden="true"></span>{{ __($label) }}</a>
@endcan
@endforeach
@if($group === 'Catalog & stock')@can('products.view')
<details class="master-navigation" @if(request()->routeIs('categories.*','product-types.*','brands.*','units.*','suppliers.*')) open @endif><summary>{{ __('Master data') }}</summary><div>@foreach($masters as [$destination,$label])<a class="nav-link {{ request()->routeIs(explode('.', $destination)[0].'.*') ? 'is-active' : '' }}" href="{{ route($destination) }}" @if(request()->routeIs(explode('.', $destination)[0].'.*')) aria-current="page" @endif>{{ __($label) }}</a>@endforeach</div></details>
@endcan @endif
</section>@endif
@endforeach
</nav>
<div class="sidebar-bottom"><span class="status-dot" aria-hidden="true"></span>{{ auth()->user()->name }}</div>
