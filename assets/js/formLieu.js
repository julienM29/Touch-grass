import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.default.min.css';

// -------------------------------------------------------------------
// Tom Select
// -------------------------------------------------------------------
const ts = new TomSelect('#lieu_form_ville', {
    placeholder: '-- Choisir une ville existante --',
});

const nouvelleVilleDiv = document.getElementById('nouvelle-ville');

function toggleNouvelleVille() {
    nouvelleVilleDiv.style.display = ts.getValue() === '' ? 'block' : 'none';
}

ts.on('change', toggleNouvelleVille);
toggleNouvelleVille();

// -------------------------------------------------------------------
// Fonction réutilisable : chercher et sélectionner une ville
// -------------------------------------------------------------------
function gererVille(villeNom, cp) {
    const selectElement = document.getElementById('lieu_form_ville');
    const tomSelectInstance = selectElement.tomselect;
    const options = [...selectElement.options];
    const optionExistante = options.find(function(option) {
        return option.text.toLowerCase() === villeNom.toLowerCase();
    });

    if (optionExistante) {
        tomSelectInstance.setValue(optionExistante.value);
        document.getElementById('lieu_form_nouvelleVille_nom').value        = '';
        document.getElementById('lieu_form_nouvelleVille_codePostal').value = '';
    } else {
        tomSelectInstance.clear();
        document.getElementById('lieu_form_nouvelleVille_nom').value        = villeNom;
        document.getElementById('lieu_form_nouvelleVille_codePostal').value = cp;
    }
}

// -------------------------------------------------------------------
// Recherche globale via Nominatim
// -------------------------------------------------------------------
const rechercheInput = document.getElementById('recherche-globale');
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

                // Aucun résultat
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

                        // Préremplir les champs du formulaire
                        document.getElementById('lieu_form_nom').value       = result.name;
                        document.getElementById('lieu_form_rue').value       = addr.road ?? '';
                        document.getElementById('lieu_form_latitude').value  = parseFloat(result.lat).toFixed(6);
                        document.getElementById('lieu_form_longitude').value = parseFloat(result.lon).toFixed(6);

                        // Ville
                        const villeNom = addr.city ?? addr.town ?? addr.village ?? addr.hamlet ?? '';
                        const cp       = addr.postcode ?? '';
                        gererVille(villeNom, cp);

                        // Vider la liste et le champ de recherche
                        suggestionsListe.innerHTML = '';
                        rechercheInput.value = '';
                    });

                    suggestionsListe.appendChild(li);
                });
            });
    }, 1000);
});

// -------------------------------------------------------------------
// Géocodage automatique
// -------------------------------------------------------------------
async function geocoder() {
    const rue   = document.getElementById('lieu_form_rue').value.trim();
    const ville = document.getElementById('lieu_form_nouvelleVille_nom').value.trim()
        || document.getElementById('lieu_form_ville').options[document.getElementById('lieu_form_ville').selectedIndex]?.text.trim();

    if (!rue || !ville) return;

    const latInput = document.getElementById('lieu_form_latitude');
    const lngInput = document.getElementById('lieu_form_longitude');

    if (latInput.value && lngInput.value) return;

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
            latInput.value = parseFloat(data[0].lat).toFixed(6);
            lngInput.value = parseFloat(data[0].lon).toFixed(6);
        }
    } catch(e) {
        console.error('Erreur géocodage :', e);
    }
}

// Déclencher quand la rue perd le focus
document.getElementById('lieu_form_rue').addEventListener('blur', geocoder);

// Déclencher quand la ville change
ts.on('change', function() {
    toggleNouvelleVille();
    geocoder();
});

document.getElementById('lieu_form_nouvelleVille_nom').addEventListener('blur', geocoder);
