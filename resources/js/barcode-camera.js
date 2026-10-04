const dialog = document.getElementById('barcodeCameraDialog');
if (dialog) {
    const labels = JSON.parse(document.getElementById('barcode-camera-config').textContent);
    let video = document.getElementById('barcodeCameraVideo');
    const status = document.getElementById('barcodeCameraStatus');
    const error = document.getElementById('barcodeCameraError');
    const devices = document.getElementById('barcodeCameraDevice');
    const retry = document.getElementById('retryBarcodeCamera');
    const mirror = document.getElementById('barcodeCameraMirror');
    const previewContainer = dialog.querySelector('.barcode-camera-preview');
    const mirrorKey = 'warbun.camera.mirror';
    try { mirror.checked = localStorage.getItem(mirrorKey) === 'true'; } catch {}
    previewContainer.classList.toggle('is-mirrored', mirror.checked);
    mirror.addEventListener('change', () => {
        previewContainer.classList.toggle('is-mirrored', mirror.checked);
        try { localStorage.setItem(mirrorKey, String(mirror.checked)); } catch {}
    });
    let version = 0;
    let stream;
    let controls;
    let guidanceTimer;
    let opener;
    let returnFocus;

    function stop() {
        version++;
        clearTimeout(guidanceTimer);
        controls?.stop();
        controls = null;
        stream?.getTracks().forEach(track => track.stop());
        stream = null;
        video.pause();
        video.srcObject = null;
    }

    function finish() {
        stop();
        if (dialog.open) dialog.close();
    }

    function messageFor(exception) {
        if (['NotAllowedError', 'SecurityError'].includes(exception.name)) return labels.denied;
        if (['NotFoundError', 'OverconstrainedError'].includes(exception.name)) return labels.missing;
        if (['NotReadableError', 'AbortError'].includes(exception.name)) return labels.busy;
        return labels.failed;
    }

    async function start(deviceId = '') {
        stop();
        const attempt = version;
        // A delayed decoder must not clear the preview of a newer scan.
        const preview = video.cloneNode(false);
        preview.muted = true;
        video.replaceWith(preview);
        video = preview;
        let detected = false;
        status.textContent = labels.permission;
        error.textContent = '';
        devices.disabled = true;
        retry.disabled = true;
        try {
            if (!window.isSecureContext) throw Object.assign(new Error(), {cameraMessage:labels.insecure});
            if (!navigator.mediaDevices?.getUserMedia) throw Object.assign(new Error(), {cameraMessage:labels.unsupported});
            const acquired = await navigator.mediaDevices.getUserMedia({audio:false, video:{...(deviceId ? {deviceId:{exact:deviceId}} : {facingMode:{ideal:'environment'}}), width:{ideal:1920}, height:{ideal:1080}}});
            if (attempt !== version || !dialog.open) { acquired.getTracks().forEach(track=>track.stop()); return; }
            stream = acquired;
            const track = acquired.getVideoTracks()[0];
            try {
                if (track.getCapabilities?.().focusMode?.includes('continuous')) {
                    await track.applyConstraints({advanced:[{focusMode:'continuous'}]});
                }
            } catch {}
            if (attempt !== version || !dialog.open) return;
            const [{BrowserMultiFormatOneDReader}, {default:DecodeHintType}] = await Promise.all([
                import('@zxing/browser'), import('@zxing/library/esm/core/DecodeHintType'),
            ]);
            if (attempt !== version || !dialog.open) return;
            const hints = new Map([[DecodeHintType.TRY_HARDER, true]]);
            const reader = new BrowserMultiFormatOneDReader(hints, {delayBetweenScanAttempts:300, delayBetweenScanSuccess:500});
            const scanning = await reader.decodeFromStream(acquired, preview, (result, failure, scanner) => {
                if (!result || detected || attempt !== version || !dialog.open) return;
                const barcode = result.getText().trim();
                if (!barcode || barcode.length > 50) { error.textContent = labels.invalid; return; }
                const input = document.getElementById(opener.dataset.cameraTarget);
                if (!input || input.disabled) { finish(); return; }
                detected = true;
                scanner.stop();
                const submit = opener.dataset.cameraSubmit;
                returnFocus = input;
                finish();
                input.value = barcode;
                input.dispatchEvent(new Event('input', {bubbles:true}));
                input.focus();
                if (submit) document.getElementById(submit)?.requestSubmit();
                else {
                    const feedback = document.getElementById('barcodeCameraFeedback');
                    if (feedback) feedback.textContent = labels.found;
                }
            });
            if (attempt !== version || !dialog.open) { scanning.stop(); return; }
            controls = scanning;
            status.textContent = labels.scanning;
            guidanceTimer = setTimeout(() => {
                if (attempt === version && dialog.open && !detected) status.textContent = labels.guidance;
            }, 6000);
            const cameras = navigator.mediaDevices.enumerateDevices ? await navigator.mediaDevices.enumerateDevices().catch(()=>[]) : [];
            if (attempt !== version || !dialog.open) return;
            const current = acquired.getVideoTracks()[0]?.getSettings().deviceId;
            devices.replaceChildren();
            cameras.filter(device=>device.kind === 'videoinput').forEach((device, index)=>devices.add(new Option(device.label || labels.camera + ' ' + (index+1), device.deviceId)));
            if (current) devices.value = current;
            devices.disabled = devices.options.length < 2;
        } catch (exception) {
            if (attempt !== version || !dialog.open) return;
            stop();
            status.textContent = '';
            error.textContent = exception.cameraMessage || messageFor(exception);
        } finally {
            if (dialog.open && (attempt === version || error.textContent)) retry.disabled = false;
        }
    }

    document.querySelectorAll('[data-camera-target]').forEach(button=>button.addEventListener('click', ()=> {
        if (button.disabled || document.getElementById(button.dataset.cameraTarget)?.disabled) return;
        opener = button;
        returnFocus = null;
        devices.replaceChildren(new Option(labels.camera, ''));
        dialog.showModal();
        start();
    }));
    document.querySelectorAll('[data-barcode-focus]').forEach(button=>button.addEventListener('click', ()=>document.getElementById(button.dataset.barcodeFocus)?.focus()));
    retry.addEventListener('click', ()=>start(devices.value));
    devices.addEventListener('change', ()=>start(devices.value));
    document.getElementById('closeBarcodeCamera').addEventListener('click', finish);
    document.getElementById('cancelBarcodeCamera').addEventListener('click', ()=> {
        returnFocus = document.getElementById(opener?.dataset.cameraTarget);
        finish();
    });
    dialog.addEventListener('cancel', finish);
    dialog.addEventListener('close', ()=> { stop(); (returnFocus || opener)?.focus(); });
    document.addEventListener('visibilitychange', ()=> { if (document.hidden && dialog.open) finish(); });
    window.addEventListener('pagehide', finish);
}
