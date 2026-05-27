import L from 'leaflet';

function initialiserCarteModal(divId, lat, lng) {
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

    L.marker([lat, lng]).addTo(carte);
}

// Initialiser les cartes quand une modal s'ouvre
document.addEventListener('click', function(e) {
    const btn = e.target.closest('[onclick]');
    if (!btn) return;

    const match = btn.getAttribute('onclick').match(/openModal\('modal-lieu-(\d+)'\)/);
    if (!match) return;

    const lieuId = match[1];
    const carteDiv = document.getElementById('carte-modal-' + lieuId);
    if (!carteDiv || carteDiv.dataset.initialized) return;

    const lat = parseFloat(carteDiv.dataset.lat);
    const lng = parseFloat(carteDiv.dataset.lng);
    initialiserCarteModal('carte-modal-' + lieuId, lat, lng);

    // Marquer comme initialisée pour ne pas le refaire
    carteDiv.dataset.initialized = 'true';
});
