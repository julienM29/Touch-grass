document.addEventListener('DOMContentLoaded', () => {
    const filterSelect = document.querySelector('[data-filter-submit]');

    if (!filterSelect) {
        return;
    }

    filterSelect.addEventListener('change', () => {
        filterSelect.form.submit();
    });
});
