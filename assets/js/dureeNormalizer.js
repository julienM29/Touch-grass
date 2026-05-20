const days    = document.getElementById('sortie_duree_days');
const hours   = document.getElementById('sortie_duree_hours');
const minutes = document.getElementById('sortie_duree_minutes');

if (days && hours && minutes) {
    [days, hours, minutes].forEach(field => {
        field.addEventListener('change', normalize);
    });
}

function normalize() {
    let totalMinutes = ((parseInt(days.value)    || 0) * 24 * 60)
        + ((parseInt(hours.value)   || 0) * 60)
        +  (parseInt(minutes.value) || 0);

    days.value    = Math.floor(totalMinutes / 1440);
    totalMinutes %= 1440;
    hours.value   = Math.floor(totalMinutes / 60);
    minutes.value = totalMinutes % 60;
}
