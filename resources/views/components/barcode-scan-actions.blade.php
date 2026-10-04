@props(['target', 'submit' => '', 'disabled' => false])
<div class="barcode-scan-actions" role="group" aria-label="{{ __('Barcode scan method') }}">
    <button type="button" class="button-secondary" data-barcode-focus="{{ $target }}" @disabled($disabled)><x-icon name="barcode" />{{ __('Hardware scanner') }}</button>
    <button type="button" class="button-secondary" data-camera-target="{{ $target }}" data-camera-submit="{{ $submit }}" @disabled($disabled)><x-icon name="camera" />{{ __('Scan with camera') }}</button>
</div>
