const dialog = document.getElementById('deliveryMapDialog');
if (dialog) {
    const config = JSON.parse(document.getElementById('delivery-map-config').textContent);
    const field = id => document.getElementById(id);
    let version = 0;
    let opener;
    let group;
    let map;
    let marker;
    let leaflet;
    let selected;
    let locating = false;
    let locationVersion = 0;

    function valid(latitude, longitude) {
        return latitude !== '' && longitude !== '' && Number.isFinite(Number(latitude)) && Number.isFinite(Number(longitude)) && Math.abs(Number(latitude)) <= 90 && Math.abs(Number(longitude)) <= 180;
    }
    function sync(group) {
        const latitude = group.querySelector('[data-point-latitude]').value;
        const longitude = group.querySelector('[data-point-longitude]').value;
        const present = valid(latitude, longitude);
        group.querySelector('[data-point-status]').textContent = present ? config.text.selected : config.text.empty;
        group.querySelector('[data-location-clear]').hidden = !present;
    }
    function write(group, point) {
        for (const [name, value] of [['latitude', point?.lat], ['longitude', point?.lng]]) {
            const input = group.querySelector('[data-point-' + name + ']');
            input.value = value === undefined ? '' : Number(value).toFixed(7);
            input.dispatchEvent(new Event('input', {bubbles:true}));
            input.dispatchEvent(new Event('change', {bubbles:true}));
        }
        sync(group);
    }
    function pick(point, fromLocation = false) {
        if (!fromLocation) {
            locationVersion++;
            locating = false;
            field('locateDeliveryMap').disabled = false;
        }
        if (field('deliveryMapError').textContent !== config.text.tilesFailed) field('deliveryMapError').textContent = '';
        const latitude = Math.max(-90, Math.min(90, point.lat));
        const longitude = ((point.lng + 180) % 360 + 360) % 360 - 180;
        selected = {lat:latitude, lng:longitude};
        if (marker) marker.setLatLng(selected);
        else {
            marker = leaflet.marker(selected, {
                draggable:true, title:config.text.pin, alt:config.text.pin,
                icon:leaflet.divIcon({className:'delivery-map-marker', html:'<span class="delivery-map-pin"></span>', iconSize:[44,44], iconAnchor:[22,22]}),
            }).addTo(map);
            marker.on('dragend', () => pick(marker.getLatLng()));
        }
        field('deliveryMapStatus').textContent = config.text.selected;
        field('confirmDeliveryMap').disabled = false;
    }
    async function open(button) {
        if (button.disabled) return;
        opener = button;
        group = document.querySelector('[data-delivery-point="' + button.dataset.locationOpen + '"]');
        if (!group || group.querySelector('[data-point-latitude]').disabled) return;
        map?.stop();
        marker?.remove(); marker = null; locating = false;
        const attempt = ++version;
        selected = null;
        field('deliveryMapError').textContent = '';
        field('deliveryMapStatus').textContent = config.text.loading;
        field('retryDeliveryMap').hidden = true;
        for (const id of ['locateDeliveryMap','centerDeliveryMap','confirmDeliveryMap']) field(id).disabled = true;
        if (!dialog.open) dialog.showModal();
        try {
            leaflet = await import('leaflet');
            if (attempt !== version || !dialog.open) return;
            if (!map) {
                map = leaflet.map('deliveryMapCanvas', {worldCopyJump:true, keyboard:true, attributionControl:false, zoomAnimation:false, fadeAnimation:false, markerZoomAnimation:false});
                leaflet.tileLayer(config.tiles, {maxZoom:19, attribution:config.attribution}).on('tileerror', () => {
                    if (dialog.open) {
                        field('deliveryMapError').textContent = config.text.tilesFailed;
                        field('retryDeliveryMap').hidden = false;
                    }
                }).addTo(map);
                map.on('click', event => pick(event.latlng));
            }
            const latitude = group.querySelector('[data-point-latitude]').value;
            const longitude = group.querySelector('[data-point-longitude]').value;
            const existing = valid(latitude, longitude);
            map.setView(existing ? [Number(latitude), Number(longitude)] : config.center, existing ? 17 : config.zoom);
            if (existing) pick({lat:Number(latitude), lng:Number(longitude)});
            else field('deliveryMapStatus').textContent = config.text.choose;
            field('locateDeliveryMap').disabled = false;
            field('centerDeliveryMap').disabled = false;
            map.invalidateSize();
        } catch {
            if (attempt !== version || !dialog.open) return;
            map?.remove(); map = null;
            field('deliveryMapStatus').textContent = '';
            field('deliveryMapError').textContent = config.text.failed;
            field('retryDeliveryMap').hidden = false;
        }
    }
    document.querySelectorAll('[data-delivery-point]').forEach(group => {
        sync(group);
        group.addEventListener('change', () => sync(group));
        group.addEventListener('deliverypoint:refresh', () => sync(group));
        group.querySelector('[data-location-clear]').addEventListener('click', event => {
            if (!event.currentTarget.disabled && !group.querySelector('[data-point-latitude]').disabled) write(group, null);
        });
    });
    document.querySelectorAll('[data-location-open]').forEach(button => button.addEventListener('click', () => open(button)));
    field('retryDeliveryMap').addEventListener('click', () => open(opener));
    field('centerDeliveryMap').addEventListener('click', () => { if (map) pick(map.getCenter()); });
    field('locateDeliveryMap').addEventListener('click', () => {
        if (!map || locating) return;
        field('deliveryMapError').textContent = '';
        if (!window.isSecureContext || !navigator.geolocation) {
            field('deliveryMapError').textContent = config.text.unsupported;
            return;
        }
        locating = true;
        const attempt = version;
        const locationAttempt = ++locationVersion;
        field('locateDeliveryMap').disabled = true;
        field('deliveryMapStatus').textContent = config.text.locating;
        const failed = error => {
            if (attempt !== version || locationAttempt !== locationVersion || !dialog.open) return;
            locating = false; field('locateDeliveryMap').disabled = false;
            field('deliveryMapStatus').textContent = selected ? config.text.selected : config.text.choose;
            field('deliveryMapError').textContent = error.code === 1 ? config.text.denied : (error.code === 3 ? config.text.timeout : config.text.unavailable);
        };
        try {
            navigator.geolocation.getCurrentPosition(position => {
                if (attempt !== version || locationAttempt !== locationVersion || !dialog.open) return;
                locating = false; field('locateDeliveryMap').disabled = false;
                pick({lat:position.coords.latitude, lng:position.coords.longitude}, true);
                map.setView(selected, 17);
                field('deliveryMapStatus').textContent = config.text.located;
            }, failed, {enableHighAccuracy:true, timeout:10000, maximumAge:0});
        } catch (error) { failed(error); }
    });
    field('confirmDeliveryMap').addEventListener('click', () => {
        if (!selected || !group || group.querySelector('[data-point-latitude]').disabled) return;
        write(group, selected);
        dialog.close();
    });
    for (const id of ['closeDeliveryMap','cancelDeliveryMap']) field(id).addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => {
        version++; locating = false; map?.stop();
        opener?.focus();
    });
    window.addEventListener('pagehide', () => { if (dialog.open) dialog.close(); });
}
