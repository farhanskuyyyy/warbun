@props(['name'])
<svg {{ $attributes->class(['ui-icon']) }} aria-hidden="true" focusable="false"><use href="{{ asset('icons/fontawesome-solid.svg') }}#{{ $name }}"></use></svg>
