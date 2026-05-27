function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    // Forcer Leaflet à recalculer la taille de la carte
    setTimeout(function() {
        window.dispatchEvent(new Event('resize'));
    }, 100);
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}
function openDeleteModal(event) {
    event.preventDefault();
    document.getElementById('delete-account-modal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}
function closeDeleteModal() {
    document.getElementById('delete-account-modal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}


