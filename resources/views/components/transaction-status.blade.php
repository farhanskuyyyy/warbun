@props(['status', 'label' => null])
@php($tone = in_array($status, ['pending','confirmed','preparing','ready','completed','cancelled','refunded','paid','partial','debt','failed','expired']) ? $status : 'unknown')
<span {{ $attributes->class(['transaction-status', 'status-tone-'.$tone]) }} data-transaction-status="{{ $status }}"><span class="transaction-status-dot" aria-hidden="true"></span><span>{{ $label ?? __('status.'.$status) }}</span></span>
