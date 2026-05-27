import { creerCarte, rechercheNominatim, geocoderAdresse, reverseGeocode } from './lieuMap.js';

let carteInstance = null;

function remplirChamps(nom, rue, villeNom, villeCP) {
    document.getElementById('modal-lieu-nom').value       = nom ?? '';
    document.getElementById('modal-lieu-rue').value       = rue ?? '';
    document.getElementById('modal-lieu-ville-nom').value = villeNom ?? '';
    document.getElementById('modal-lieu-ville-cp').value  = villeCP ?? '';
}

function mettreAJourCoords(lat, lng) {
    document.getElementById('modal-lieu-latitude').value  = lat;
    document.getElementById('modal-lieu-longitude').value = lng;
}

function reinitialiserCarte(lat, lng) {
    if (carteInstance) carteInstance.carte.remove();
    carteInstance = creerCarte('modal-lieu-carte', lat, lng, {
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

// Init carte après ouverture de la modal
setTimeout(() => {
    reinitialiserCarte(48.1173, -1.6778);
    window.dispatchEvent(new Event('resize'));
}, 100);

// Geocodage auto
async function geocoder() {
    const rue   = document.getElementById('modal-lieu-rue').value.trim();
    const ville = document.getElementById('modal-lieu-ville-nom').value.trim();
    if (!rue || !ville) return;

    const coords = await geocoderAdresse(rue, ville);
    if (coords) {
        mettreAJourCoords(coords.lat, coords.lng);
        reinitialiserCarte(parseFloat(coords.lat), parseFloat(coords.lng));
    }
}

document.getElementById('modal-lieu-rue').addEventListener('blur', geocoder);
document.getElementById('modal-lieu-ville-nom').addEventListener('blur', geocoder);

// Recherche Nominatim
const rechercheInput   = document.getElementById('modal-lieu-recherche');
const suggestionsListe = document.getElementById('modal-lieu-suggestions');
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

// Sauvegarde via API
document.getElementById('modal-lieu-btn-save').addEventListener('click', async function() {
    const nom      = document.getElementById('modal-lieu-nom').value.trim();
    const rue      = document.getElementById('modal-lieu-rue').value.trim();
    const villeNom = document.getElementById('modal-lieu-ville-nom').value.trim();
    const villeCP  = document.getElementById('modal-lieu-ville-cp').value.trim();
    const lat      = document.getElementById('modal-lieu-latitude').value;
    const lng      = document.getElementById('modal-lieu-longitude').value;

    if (!nom || !rue || !villeNom || !villeCP) {
        alert('Veuillez remplir tous les champs.');
        return;
    }

    try {
        const response = await fetch('/lieu/api/create', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nom, rue, villeNom, villeCodePostal: villeCP, latitude: lat, longitude: lng })
        });

        const data = await response.json();

        if (response.status === 409 || response.ok) {
            ajouterEtSelectionnerLieu(data.lieuId, data.lieuNom);
            closeModal('modal-create-lieu');
        } else {
            alert(data.error ?? 'Une erreur est survenue.');
        }
    } catch(e) {
        console.error('Erreur :', e);
        alert('Une erreur est survenue.');
    }
});

function ajouterEtSelectionnerLieu(id, nom) {
    const select = document.getElementById('sortie_lieu');
    if (![...select.options].find(o => o.value === id)) {
        select.appendChild(new Option(nom, id));
    }
    select.value = id;
}
