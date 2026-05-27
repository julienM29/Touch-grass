import L from 'leaflet';

// -------------------------------------------------------------------
// Fonction de base : initialise une carte Leaflet
// options.draggable : marqueur déplaçable (formulaire) ou non (lecture)
// options.onDragEnd : callback quand le marqueur est déplacé
// -------------------------------------------------------------------
export function creerCarte(divId, lat, lng, options = {}) {
    const carte = L.map(divId, {
        dragging: true,
        zoomControl: true,
        scrollWheelZoom: true,
        doubleClickZoom: true,
        touchZoom: true,
    }).setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(carte);

    const marqueur = L.marker([lat, lng], {
        draggable: options.draggable ?? false
    }).addTo(carte);

    if (options.draggable && options.onDragEnd) {
        marqueur.on('dragend', function() {
            const pos = marqueur.getLatLng();
            options.onDragEnd(pos.lat.toFixed(6), pos.lng.toFixed(6));
        });
    }

    return { carte, marqueur };
}

// -------------------------------------------------------------------
// Reverse geocoding
// -------------------------------------------------------------------
export async function reverseGeocode(lat, lng) {
    const res = await fetch(
        `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&addressdetails=1`,
        { headers: { 'Accept-Language': 'fr', 'User-Agent': 'TouchGrass/1.0' } }
    );
    return await res.json();
}

// -------------------------------------------------------------------
// Recherche Nominatim
// -------------------------------------------------------------------
export async function rechercheNominatim(saisie) {
    const res = await fetch(
        `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(saisie)}&format=json&addressdetails=1&limit=5&countrycodes=fr`,
        { headers: { 'Accept-Language': 'fr', 'User-Agent': 'TouchGrass/1.0' } }
    );
    return await res.json();
}

// -------------------------------------------------------------------
// Géocodage adresse → coordonnées
// -------------------------------------------------------------------
export async function geocoderAdresse(rue, ville) {
    const res = await fetch(
        `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(rue + ' ' + ville)}&format=json&limit=1&countrycodes=fr`,
        { headers: { 'Accept-Language': 'fr', 'User-Agent': 'TouchGrass/1.0' } }
    );
    const data = await res.json();
    if (data.length > 0) {
        return {
            lat: parseFloat(data[0].lat).toFixed(6),
            lng: parseFloat(data[0].lon).toFixed(6)
        };
    }
    return null;
}

// -------------------------------------------------------------------
// Initialisation des cartes dans les modals de détail (lecture seule)
// -------------------------------------------------------------------
export function initialiserCartesModals() {
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[onclick]');
        if (!btn) return;

        const match = btn.getAttribute('onclick').match(/openModal\('modal-lieu-(\d+)'\)/);
        if (!match) return;

        const lieuId  = match[1];
        const carteDiv = document.getElementById('carte-modal-' + lieuId);
        if (!carteDiv || carteDiv.dataset.initialized) return;

        const lat = parseFloat(carteDiv.dataset.lat);
        const lng = parseFloat(carteDiv.dataset.lng);
        creerCarte('carte-modal-' + lieuId, lat, lng);
        carteDiv.dataset.initialized = 'true';

        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 100);
    });
}
