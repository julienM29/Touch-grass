import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.default.min.css';

// API communes
const input = document.getElementById('recherche-ville');
const suggestions = document.getElementById('liste-suggestions');

input.addEventListener('input', function() {
    const saisie = input.value;

    if (saisie.length < 2) {
        suggestions.innerHTML = '';
        return;
    }

    const url = `https://geo.api.gouv.fr/communes?nom=${saisie}&fields=nom,codesPostaux&limit=8`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            suggestions.innerHTML = ''; // vider la liste précédente

            data.forEach(function(commune) {
                const li = document.createElement('li');
                li.textContent = commune.nom + ' (' + commune.codesPostaux[0] + ')';

                li.addEventListener('click', function () {
                    // On remplit l'input avec la ville choisie
                    input.value = commune.nom;

                    // On cherche si la ville existe déjà en BDD
                    const selectElement = document.getElementById('lieu_form_ville');
                    const tomSelectInstance = selectElement.tomselect;
                    const options = [...selectElement.options];
                    const optionExistante = options.find(function (option) {
                        return option.text.toLowerCase() === commune.nom.toLowerCase();
                    });

                    if (optionExistante) {
                        // On utilise l'API Tom Select pour sélectionner la valeur si en BDD
                        tomSelectInstance.setValue(optionExistante.value);
                        console.log('Ville existante trouvée, id :', optionExistante.value);

                        // On vide les champs nouvelleVille pour ne pas créer de doublon
                        document.getElementById('lieu_form_nouvelleVille_nom').value = '';
                        document.getElementById('lieu_form_nouvelleVille_codePostal').value = '';
                    } else {
                        // On créé la ville si elle n'existe pas
                        tomSelectInstance.clear();
                        document.getElementById('lieu_form_nouvelleVille_nom').value = commune.nom;
                        document.getElementById('lieu_form_nouvelleVille_codePostal').value = commune.codesPostaux[0];
                        console.log('Nouvelle ville créée :', commune.nom);
                    }

                    // vider la liste
                    suggestions.innerHTML = '';
                });

                suggestions.appendChild(li);
            });
        });
});



// Affichage dynamique si on entre une nouvelle ville non présente dans la BDD
const ts = new TomSelect('#lieu_form_ville', {
    placeholder: '-- Choisir une ville existante --',
});

const nouvelleVilleDiv = document.getElementById('nouvelle-ville');

function toggleNouvelleVille() {
    nouvelleVilleDiv.style.display = ts.getValue() === '' ? 'block' : 'none';
}

ts.on('change', toggleNouvelleVille);
toggleNouvelleVille();
