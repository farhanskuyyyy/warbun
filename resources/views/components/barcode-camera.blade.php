<dialog id="barcodeCameraDialog" class="pos-customer-dialog barcode-camera-dialog" aria-labelledby="barcodeCameraTitle" aria-describedby="barcodeCameraHelp">
    <div class="pos-customer-heading"><h2 id="barcodeCameraTitle">{{ __('Scan with camera') }}</h2><button id="closeBarcodeCamera" type="button" class="button-secondary" aria-label="{{ __('Close camera') }}"><x-icon name="xmark" /></button></div>
    <p id="barcodeCameraHelp">{{ __('Allow camera access, then point at the packaging barcode. Scanning is automatic; no capture button needed. One scan per opening.') }}</p>
    <div class="barcode-camera-preview"><video id="barcodeCameraVideo" autoplay muted playsinline aria-label="{{ __('Camera preview') }}"></video><span class="barcode-camera-guide" aria-hidden="true"></span></div>
    <p id="barcodeCameraStatus" role="status" aria-live="polite"></p><p id="barcodeCameraError" role="alert" class="text-red-700"></p>
    <label class="field">{{ __('Camera') }}<select id="barcodeCameraDevice" disabled><option value="">{{ __('Back camera preferred') }}</option></select></label>
    <label class="barcode-camera-mirror"><input id="barcodeCameraMirror" type="checkbox">{{ __('Mirror camera preview') }}</label>
    <div class="barcode-camera-footer"><button id="retryBarcodeCamera" type="button" class="button-secondary">{{ __('Retry camera') }}</button><button id="cancelBarcodeCamera" type="button" class="button-primary">{{ __('Return to scanner field') }}</button></div>
</dialog>
<script id="barcode-camera-config" type="application/json">{!! json_encode([
    'permission'=>__('Waiting for camera permission...'), 'scanning'=>__('Scanning automatically. Keep the entire barcode sharp and visible.'),
    'guidance'=>__('Not read yet. Move the package slightly farther away until the bars look sharp, keep it flat, and avoid glare. Try the back camera or enter the barcode manually.'),
    'insecure'=>__('Camera scanning needs HTTPS or localhost. Use the hardware scanner or type the barcode on this connection.'),
    'unsupported'=>__('This browser cannot access a camera. Use the hardware scanner or type the barcode.'),
    'denied'=>__('Camera permission was denied. Allow camera access in browser settings, then retry.'),
    'missing'=>__('No camera was found. Connect a camera or use the hardware scanner.'),
    'busy'=>__('Camera is unavailable or being used by another app. Close that app and retry.'),
    'failed'=>__('Unable to start the camera. Retry or use the scanner field.'),
    'invalid'=>__('This barcode is longer than 50 characters. Use a product barcode.'),
    'found'=>__('Barcode captured. Check the field before saving.'), 'camera'=>__('Camera'),
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
