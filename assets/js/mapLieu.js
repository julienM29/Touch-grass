import { creerCarte } from './lieuMap.js';

const carteDiv = document.getElementById('carte');
creerCarte('carte', parseFloat(carteDiv.dataset.lat), parseFloat(carteDiv.dataset.lng));
