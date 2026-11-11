import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// Perbaikan ikon penanda: Vite tidak menemukan gambar bawaan Leaflet secara otomatis.
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

const TILE_URL = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
const TILE_ATTR = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

// Peta dasar yang dipakai ulang di semua halaman (lokasi, dashboard, tracking).
export function baseMap(el, center = [10, 20], zoom = 2) {
    const map = L.map(el, { worldCopyJump: true }).setView(center, zoom);
    L.tileLayer(TILE_URL, { attribution: TILE_ATTR, maxZoom: 19 }).addTo(map);
    return map;
}

// 1) Peta untuk memilih koordinat di form lokasi.
function initPicker() {
    const el = document.getElementById('location-picker');
    if (!el) return;

    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const lat = parseFloat(latInput.value);
    const lng = parseFloat(lngInput.value);
    const hasPoint = !isNaN(lat) && !isNaN(lng);

    const map = baseMap(el, hasPoint ? [lat, lng] : [10, 20], hasPoint ? 6 : 2);
    let marker = hasPoint ? L.marker([lat, lng]).addTo(map) : null;

    const setPoint = (latlng) => {
        latInput.value = latlng.lat.toFixed(7);
        lngInput.value = latlng.lng.toFixed(7);
        if (marker) {
            marker.setLatLng(latlng);
        } else {
            marker = L.marker(latlng).addTo(map);
        }
    };

    // wrap(): kalau peta digeser ke "dunia kedua", longitude dikembalikan ke -180..180.
    map.on('click', (e) => setPoint(e.latlng.wrap()));

    // Kalau koordinat diketik manual, penanda ikut pindah.
    [latInput, lngInput].forEach((input) => {
        input.addEventListener('change', () => {
            const la = parseFloat(latInput.value);
            const ln = parseFloat(lngInput.value);
            if (!isNaN(la) && !isNaN(ln) && la >= -90 && la <= 90 && ln >= -180 && ln <= 180) {
                setPoint(L.latLng(la, ln));
                map.setView([la, ln], Math.max(map.getZoom(), 5));
            }
        });
    });
}

// 2) Peta ringkasan semua lokasi di halaman daftar.
function initOverview() {
    const el = document.getElementById('locations-overview');
    if (!el) return;

    const points = JSON.parse(el.dataset.points || '[]');
    const map = baseMap(el);
    const markers = [];

    points.forEach((p) => {
        const marker = L.marker([p.latitude, p.longitude]).addTo(map);

        // Isi popup dibuat lewat textContent (bukan innerHTML) supaya nama lokasi
        // yang berisi tag HTML tidak dijalankan sebagai kode (mencegah XSS).
        const box = document.createElement('div');
        const title = document.createElement('strong');
        title.textContent = p.name;
        const sub = document.createElement('div');
        sub.textContent = [p.city, p.country_code].filter(Boolean).join(', ') + ' - ' + p.type;
        box.append(title, sub);
        marker.bindPopup(box);

        markers.push(marker);
    });

    if (markers.length) {
        map.fitBounds(L.featureGroup(markers).getBounds(), { padding: [30, 30], maxZoom: 5 });
    }
}

// 3) Peta rute satu shipment: asal, tujuan, titik tracking, dan garis perjalanan.
function initShipmentMap() {
    const el = document.getElementById('shipment-map');
    if (!el) return;

    const data = JSON.parse(el.dataset.map);
    const map = baseMap(el);

    // Popup dibuat lewat textContent supaya teks dari database tidak dijalankan sebagai HTML.
    const popup = (lines) => {
        const box = document.createElement('div');
        lines.filter(Boolean).forEach((text, i) => {
            const row = document.createElement('div');
            if (i === 0) row.style.fontWeight = '600';
            row.textContent = text;
            box.append(row);
        });
        return box;
    };

    const origin = L.latLng(data.origin.lat, data.origin.lng);
    const destination = L.latLng(data.destination.lat, data.destination.lng);
    const traveled = data.points.map((p) => L.latLng(p.lat, p.lng));

    L.circleMarker(origin, { radius: 8, color: '#15803d', fillColor: '#22c55e', fillOpacity: 1 })
        .bindPopup(popup(['Asal', data.origin.name])).addTo(map);
    L.circleMarker(destination, { radius: 8, color: '#b91c1c', fillColor: '#ef4444', fillOpacity: 1 })
        .bindPopup(popup(['Tujuan', data.destination.name])).addTo(map);

    // Garis biru = perjalanan yang sudah terjadi.
    if (traveled.length > 1) {
        L.polyline(traveled, { color: '#1d4ed8', weight: 3 }).addTo(map);
    }

    // Garis putus-putus = sisa perjalanan (garis lurus perkiraan, bukan rute sebenarnya).
    const from = traveled.length ? traveled[traveled.length - 1] : origin;
    L.polyline([from, destination], { color: '#64748b', weight: 2, dashArray: '6 8' }).addTo(map);

    data.points.forEach((p, i) => {
        const isLast = i === data.points.length - 1;
        const color = p.exception ? '#b91c1c' : '#1d4ed8';
        L.circleMarker([p.lat, p.lng], { radius: isLast ? 7 : 5, color, fillColor: color, fillOpacity: isLast ? 1 : 0.6 })
            .bindPopup(popup([p.type, p.time, p.place])).addTo(map);
    });

    map.fitBounds(L.latLngBounds([origin, destination, ...traveled]), { padding: [30, 30], maxZoom: 8 });
}

function init() {
    initPicker();
    initOverview();
    initShipmentMap();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}