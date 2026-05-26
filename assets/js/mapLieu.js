import L from 'leaflet';

const carteDiv = document.getElementById('carte');
const lat = parseFloat(carteDiv.dataset.lat);
const lng = parseFloat(carteDiv.dataset.lng);

const carte = L.map('carte', {
    dragging: true,      // désactive le déplacement de la carte
    zoomControl: true,   // désactive les boutons zoom
    scrollWheelZoom: true, // désactive le zoom à la molette
    doubleClickZoom: true, // désactive le zoom au double clic
    touchZoom: true,     // désactive le zoom tactile
}).setView([lat, lng], 16);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(carte);

L.marker([lat, lng]).addTo(carte);
