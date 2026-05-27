const sortieFilter = document.querySelector('#sortie-filter-form');

if (sortieFilter) {
    sortieFilter.addEventListener('input', () => {
        sendFilterRequest();
        }
    )
}

async function sendFilterRequest() {
    const wordData = document.querySelector('#filter_form_word').value;
    const dateMinData = document.querySelector('#filter_form_dateMin').value;
    const dateMaxData = document.querySelector('#filter_form_dateMax').value;
    const siteData = document.querySelector('#filter_form_site').value;
    const organisateurData = document.querySelector('#filter_form_organisateur').checked;
    const registeredData = document.querySelector('#filter_form_registered').checked;
    const notRegisteredData = document.querySelector('#filter_form_notRegistered').checked;
    const finishedData = document.querySelector('#filter_form_finished').checked;

    const filters = {
        word: wordData,
        dateMin: dateMinData || null,
        dateMax: dateMaxData || null,
        site: siteData || null,
        organisateur: organisateurData,
        registered: registeredData,
        notRegistered: notRegisteredData,
        finished: finishedData
    };

    const response = await fetch('/api/sortie-filtered', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(filters),
        });

    const data = await response.json();

    console.log(data);
}
