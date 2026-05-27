import { creerCarte, rechercheNominatim, geocoderAdresse, reverseGeocode } from './lieuMap.js';

let carteInstance = null;

function remplirChamps(nom, rue, villeNom, villeCP) {
    document.getElementById('lieu_form_nom').value             = nom ?? '';
    document.getElementById('lieu_form_rue').value             = rue ?? '';
    document.getElementById('lieu_form_villeNom').value        = villeNom ?? '';
    document.getElementById('lieu_form_villeCodePostal').value = villeCP ?? '';
}

function mettreAJourCoords(lat, lng) {
    document.getElementById('lieu_form_latitude').value  = lat;
    document.getElementById('lieu_form_longitude').value = lng;
}

function reinitialiserCarte(lat, lng) {
    if (carteInstance) carteInstance.carte.remove();
    carteInstance = creerCarte('carte', lat, lng, {
        draggable: true,
        onDragEnd: async (lat, lng) => {
            mettreAJourCoords(lat, lng);
            const data = await reverseGeocode(lat, lng);
            if (data?.address) {
                const addr = data.address;
                remplirChamps(
                    data.name,
                    addr.road,
                    addr.city ?? addr.town ?? addr.village ?? addr.hamlet,
                    addr.postcode
                );
            }
        }
    });
}

// Init carte
const carteDiv = document.getElementById('carte');
reinitialiserCarte(
    parseFloat(carteDiv.dataset.lat) || 48.1173,
    parseFloat(carteDiv.dataset.lng) || -1.6778
);

// Geocodage auto
async function geocoder() {
    const rue   = document.getElementById('lieu_form_rue').value.trim();
    const ville = document.getElementById('lieu_form_villeNom').value.trim();
    if (!rue || !ville) return;

    const coords = await geocoderAdresse(rue, ville);
    if (coords) {
        mettreAJourCoords(coords.lat, coords.lng);
        reinitialiserCarte(parseFloat(coords.lat), parseFloat(coords.lng));
    }
}

document.getElementById('lieu_form_rue').addEventListener('blur', geocoder);
document.getElementById('lieu_form_villeNom').addEventListener('blur', geocoder);

// Recherche Nominatim
const rechercheInput   = document.getElementById('recherche-globale');
const suggestionsListe = document.getElementById('liste-suggestions-globale');
let debounceTimer = null;

rechercheInput.addEventListener('input', function() {
    const saisie = rechercheInput.value.trim();
    if (saisie.length < 5) { suggestionsListe.innerHTML = ''; return; }

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(async function() {
        const data = await rechercheNominatim(saisie);
        suggestionsListe.innerHTML = '';

        if (data.length === 0) {
            const li = document.createElement('li');
            li.textContent = 'Aucun résultat, remplissez les champs manuellement.';
            li.style.color = '#888';
            suggestionsListe.appendChild(li);
            return;
        }

        data.forEach(function(result) {
            const li = document.createElement('li');
            li.textContent = result.display_name;
            li.addEventListener('click', function() {
                const addr = result.address;
                const lat  = parseFloat(result.lat).toFixed(6);
                const lng  = parseFloat(result.lon).toFixed(6);

                remplirChamps(
                    result.name,
                    addr.road,
                    addr.city ?? addr.town ?? addr.village ?? addr.hamlet,
                    addr.postcode
                );
                mettreAJourCoords(lat, lng);
                reinitialiserCarte(parseFloat(lat), parseFloat(lng));

                suggestionsListe.innerHTML = '';
                rechercheInput.value = '';
            });
            suggestionsListe.appendChild(li);
        });
    }, 1000);
});
