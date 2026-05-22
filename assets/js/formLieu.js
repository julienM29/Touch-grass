import L from 'leaflet';
// import 'leaflet/dist/leaflet.css';

// -------------------------------------------------------------------
// Recherche globale via Nominatim
// -------------------------------------------------------------------
const rechercheInput  = document.getElementById('recherche-globale');
const suggestionsListe = document.getElementById('liste-suggestions-globale');
let debounceTimer = null;

rechercheInput.addEventListener('input', function() {
    const saisie = rechercheInput.value.trim();

    if (saisie.length < 5) {
        suggestionsListe.innerHTML = '';
        return;
    }

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(function() {
        const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(saisie)}&format=json&addressdetails=1&limit=5&countrycodes=fr`;

        fetch(url, {
            headers: {
                'Accept-Language': 'fr',
                'User-Agent': 'TouchGrass/1.0'
            }
        })
            .then(response => response.json())
            .then(data => {
                suggestionsListe.innerHTML = '';

                if (data.length === 0) {
                    const li = document.createElement('li');
                    li.textContent = 'Aucun résultat trouvé, vous pouvez compléter les champs manuellement.';
                    li.style.color = '#888';
                    li.style.fontStyle = 'italic';
                    suggestionsListe.appendChild(li);
                    return;
                }

                data.forEach(function(result) {
                    const li = document.createElement('li');
                    li.textContent = result.display_name;

                    li.addEventListener('click', function() {
                        const addr = result.address;

                        // Préremplir les champs
                        document.getElementById('lieu_form_nom').value            = result.name;
                        document.getElementById('lieu_form_rue').value            = addr.road ?? '';
                        document.getElementById('lieu_form_villeNom').value       = addr.city ?? addr.town ?? addr.village ?? addr.hamlet ?? '';
                        document.getElementById('lieu_form_villeCodePostal').value = addr.postcode ?? '';
                        const lat = parseFloat(result.lat).toFixed(6);
                        const lng = parseFloat(result.lon).toFixed(6);
                        document.getElementById('lieu_form_latitude').value  = lat;
                        document.getElementById('lieu_form_longitude').value = lng;
                        initialiserCarte(parseFloat(lat), parseFloat(lng));

                        suggestionsListe.innerHTML = '';
                        rechercheInput.value = '';
                    });

                    suggestionsListe.appendChild(li);
                });
            });
    }, 1500);
});

// -------------------------------------------------------------------
// Géocodage automatique quand rue ou ville change manuellement
// -------------------------------------------------------------------
async function geocoder() {
    const rue   = document.getElementById('lieu_form_rue').value.trim();
    const ville = document.getElementById('lieu_form_villeNom').value.trim();

    if (!rue || !ville) return;

    // Réinitialiser pour forcer un nouveau géocodage
    document.getElementById('lieu_form_latitude').value  = '';
    document.getElementById('lieu_form_longitude').value = '';

    const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(rue + ' ' + ville)}&format=json&limit=1&countrycodes=fr`;

    try {
        const response = await fetch(url, {
            headers: {
                'Accept-Language': 'fr',
                'User-Agent': 'TouchGrass/1.0'
            }
        });
        const data = await response.json();

        if (data.length > 0) {
            const lat = parseFloat(data[0].lat).toFixed(6);
            const lng = parseFloat(data[0].lon).toFixed(6);
            document.getElementById('lieu_form_latitude').value  = lat;
            document.getElementById('lieu_form_longitude').value = lng;
            initialiserCarte(parseFloat(lat), parseFloat(lng));
        }
    } catch(e) {
        console.error('Erreur géocodage :', e);
    }
}

document.getElementById('lieu_form_rue').addEventListener('blur', geocoder);
document.getElementById('lieu_form_villeNom').addEventListener('blur', geocoder);


// -------------------------------------------------------------------
// Carte Leaflet
// -------------------------------------------------------------------
let carte = null;
let marqueur = null;

function initialiserCarte(lat, lng) {
    // Si la carte existe déjà on la supprime
    if (carte) {
        carte.remove();
    }

    carte = L.map('carte').setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(carte);

    // Marqueur déplaçable
    marqueur = L.marker([lat, lng], { draggable: true }).addTo(carte);

    // Mettre à jour lat/lng quand le marqueur est déplacé
    marqueur.on('dragend', async function() {
        const position = marqueur.getLatLng();
        const lat = position.lat.toFixed(6);
        const lng = position.lng.toFixed(6);

        // Mettre à jour les coordonnées
        document.getElementById('lieu_form_latitude').value  = lat;
        document.getElementById('lieu_form_longitude').value = lng;

        // Reverse geocoding
        const url = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json&addressdetails=1`;

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept-Language': 'fr',
                    'User-Agent': 'TouchGrass/1.0'
                }
            });
            const data = await response.json();

            if (data && data.address) {
                const addr = data.address;

                document.getElementById('lieu_form_nom').value             = data.name ?? '';
                document.getElementById('lieu_form_rue').value             = addr.road ?? '';
                document.getElementById('lieu_form_villeNom').value        = addr.city ?? addr.town ?? addr.village ?? addr.hamlet ?? '';
                document.getElementById('lieu_form_villeCodePostal').value = addr.postcode ?? '';
            }
        } catch(e) {
            console.error('Erreur reverse geocoding :', e);
        }
    });
}

// Carte affichée par défaut sur Rennes
initialiserCarte(48.1173, -1.6778);
