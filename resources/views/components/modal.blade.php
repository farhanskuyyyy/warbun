@props(['name','show'=>false,'maxWidth'=>'2xl'])
<dialog class="warbun-dialog" data-modal-name="{{ $name }}" @if($show) data-initial-open @endif aria-label="{{ $attributes->get('aria-label', __($name)) }}">{{ $slot }}</dialog>
