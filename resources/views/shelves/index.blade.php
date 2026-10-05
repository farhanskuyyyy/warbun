@extends('layouts.app')
@section('title', __('Product placement'))
@section('header', __('Product placement'))
@section('content')
<div class="monitor-heading"><div><h2>{{ __('Find it on the shelf') }}</h2><p>{{ __('Assign a main shelf and position for each product. Cashiers and order pickers see the same location.') }}</p></div><a class="button-secondary" href="{{ route('pos.index') }}"><x-icon name="cash-register" />{{ __('Open cashier') }}</a></div>
<section class="panel shelf-overview" aria-labelledby="shelvesHeading">
    <div class="shelf-heading"><h2 id="shelvesHeading">{{ __('Shelves') }}</h2><span>{{ __('Numbered overview, not a floor plan.') }}</span></div>
    <nav class="shelf-map" aria-label="{{ __('Filter by shelf') }}">
        <a class="shelf-tile" href="{{ route('shelves.index', ['search'=>request('search')]) }}" @if(!request('shelf')) aria-current="page" @endif><x-icon name="boxes-stacked" /><strong>{{ __('All products') }}</strong><span>{{ __('All locations') }}</span></a>
        @foreach($shelves as $shelf)<a class="shelf-tile" href="{{ route('shelves.index', ['shelf'=>$shelf->id,'search'=>request('search')]) }}" @if((string)request('shelf') === (string)$shelf->id) aria-current="page" @endif><span class="shelf-number">{{ $shelf->number }}</span><strong>{{ $shelf->name }}</strong><span>{{ $shelf->products_count }} {{ __('products') }}</span></a>@endforeach
        <a class="shelf-tile" href="{{ route('shelves.index', ['shelf'=>'unassigned','search'=>request('search')]) }}" @if(request('shelf') === 'unassigned') aria-current="page" @endif><x-icon name="box" /><strong>{{ __('Unassigned') }}</strong><span>{{ $unassigned }} {{ __('products') }}</span></a>
    </nav>
    @can('products.update')
    <details class="shelf-settings" @if($shelves->isEmpty()) open @endif><summary>{{ __('Manage shelves') }}</summary>
        <form action="{{ route('shelves.store') }}" method="POST" class="shelf-editor">@csrf<label class="field">{{ __('Shelf number') }}<input name="number" type="number" min="1" max="9999" required value="{{ old('number', ($shelves->max('number') ?? 0) + 1) }}"></label><label class="field">{{ __('Shelf name') }}<input name="name" maxlength="100" required placeholder="{{ __('e.g. Drinks near the entrance') }}" value="{{ old('name') }}"></label><button class="button-primary">{{ __('Add shelf') }}</button></form>
        @foreach($shelves as $shelf)<details class="shelf-settings"><summary>{{ $shelf->label }}</summary><form action="{{ route('shelves.update',$shelf) }}" method="POST" class="shelf-editor">@csrf @method('PUT')<label class="field">{{ __('Shelf number') }}<input name="number" type="number" min="1" max="9999" required value="{{ $shelf->number }}"></label><label class="field">{{ __('Shelf name') }}<input name="name" maxlength="100" required value="{{ $shelf->name }}"></label><button class="button-secondary">{{ __('Save shelf') }}</button></form><form method="POST" action="{{ route('shelves.destroy',$shelf) }}" class="mt-3">@csrf @method('DELETE')<x-crud-action action="delete" :label="__('Delete empty shelf')" :disabled="$shelf->products_count > 0" /></form></details>@endforeach
    </details>
    @endcan
</section>
<form method="GET" class="shelf-search"><input type="hidden" name="shelf" value="{{ request('shelf') }}"><label class="field">{{ __('Search product or barcode') }}<input name="search" type="search" maxlength="100" value="{{ request('search') }}"></label><button class="button-primary">{{ __('Search') }}</button><a class="button-secondary" href="{{ route('shelves.index') }}">{{ __('Clear filters') }}</a></form>
<div class="shelf-product-list">
@forelse($products as $product)
<article class="panel shelf-product" data-placement-product="{{ $product->id }}"><div><h3>{{ $product->name }}</h3><p>{{ $product->sku }} · {{ $product->category?->name }} · {{ $product->trashed() ? __('Archived') : ($product->is_active ? __('Active') : __('Inactive')) }}</p><p class="product-location"><x-icon name="layer-group" />{{ $product->location_label }}</p></div>
@can('products.update')<details @if((string)old('placement_product') === (string)$product->id) open @endif><summary>{{ __('Set location') }}</summary><form method="POST" action="{{ route('shelves.place',$product) }}" class="shelf-placement-form">@csrf @method('PUT')<input type="hidden" name="placement_product" value="{{ $product->id }}"><label class="field">{{ __('Shelf') }}<select name="shelf_id"><option value="">{{ __('Unassigned') }}</option>@foreach($shelves as $shelf)<option value="{{ $shelf->id }}" @selected((string)(old('placement_product') == $product->id ? old('shelf_id') : $product->shelf_id) === (string)$shelf->id)>{{ $shelf->label }}</option>@endforeach</select></label><label class="field">{{ __('Position on shelf') }}<input name="shelf_position" maxlength="100" value="{{ old('placement_product') == $product->id ? old('shelf_position') : $product->shelf_position }}" placeholder="{{ __('e.g. Top row, left') }}"></label><button class="button-primary">{{ __('Save location') }}</button></form></details>@endcan
</article>
@empty<div class="panel"><h2>{{ __('No products found') }}</h2><p>{{ __('Choose another shelf or clear your search.') }}</p></div>@endforelse
</div><div class="mt-6">{{ $products->links() }}</div>
@endsection
