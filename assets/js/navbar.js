document.addEventListener('DOMContentLoaded', () => {
    const burger = document.getElementById('burger-btn');
    const dropdown = document.getElementById('nav-dropdown');

    burger.addEventListener('click', () => {
        dropdown.classList.toggle('hidden');
        burger.toggleAttribute('data-open');
    });
});
