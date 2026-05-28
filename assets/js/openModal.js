function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    // Forcer Leaflet à recalculer la taille de la carte
    setTimeout(function() {
        window.dispatchEvent(new Event('resize'));
    }, 100);
}

async function openSortieModal(sortieId) {
    const modalId = `modal-${sortieId}`;
    let modal = document.getElementById(modalId);

    if (!modal) {
        const response = await fetch(`/api/sortie/${sortieId}/modal`);

        if (!response.ok) {
            return;
        }

        const modalHtml = await response.text();

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        modal = document.getElementById(modalId);
    }

    if (modal) {
        openModal(modalId);
    }
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


