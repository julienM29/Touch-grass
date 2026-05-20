import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.default.min.css';

const ts = new TomSelect('#lieu_form_ville', {
    placeholder: '-- Choisir une ville existante --',
});

const nouvelleVilleDiv = document.getElementById('nouvelle-ville');

function toggleNouvelleVille() {
    nouvelleVilleDiv.style.display = ts.getValue() === '' ? 'block' : 'none';
}

ts.on('change', toggleNouvelleVille);
toggleNouvelleVille();
