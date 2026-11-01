@props(['name', 'show' => false, 'maxWidth' => '2xl'])
@php($width = ['sm'=>'384px','md'=>'448px','lg'=>'512px','xl'=>'576px','2xl'=>'672px'][$maxWidth] ?? '672px')
<dialog
    class="warbun-dialog"
    style="--dialog-width: {{ $width }}"
    aria-label="{{ $attributes->get('aria-label', __($name)) }}"
    x-data="{ open() { if (!$el.open) $el.showModal() }, close() { $el.close() } }"
    x-init="@if($show) $nextTick(() => open()) @endif"
    x-on:open-modal.window="if ($event.detail === @js($name)) open()"
    x-on:close-modal.window="if ($event.detail === @js($name)) close()"
    x-on:close.stop="if ($event.target !== $el) close()"
    x-on:click="if ($event.target === $el) { const bounds = $el.getBoundingClientRect(); if ($event.clientX < bounds.left || $event.clientX > bounds.right || $event.clientY < bounds.top || $event.clientY > bounds.bottom) close() }"
>{{ $slot }}</dialog>
