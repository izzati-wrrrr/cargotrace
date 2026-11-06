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

function init() {
    initPicker();
    initOverview();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}