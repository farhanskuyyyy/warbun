@props(['action', 'href' => null, 'label' => null])
@php
    $icon = match ($action) {
        'view' => 'eye',
        'edit' => 'pen-to-square',
        'delete' => 'trash-can',
    };
    $label ??= __(match ($action) {
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
    });
@endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->class(['crud-action', 'crud-action-'.$action]) }} aria-label="{{ $label }}" title="{{ $label }}"><x-icon :name="$icon" /></a>
@else
    <button {{ $attributes->class(['crud-action', 'crud-action-'.$action])->merge(['type' => 'submit']) }} aria-label="{{ $label }}" title="{{ $label }}"><x-icon :name="$icon" /></button>
@endif
