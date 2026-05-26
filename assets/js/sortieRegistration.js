document.addEventListener('submit', async (event) => {
    const form = event.target.closest('.js-sortie-event_registration-form');

    if (!form) {
        return;
    }

    event.preventDefault();

    const block = form.closest('[data-sortie-event_registration-block]');

    if (!block) {
        return;
    }

    const submitButton = form.querySelector('button[type="submit"]');

    if (submitButton) {
        submitButton.disabled = true;
    }

    try {
        const response = await fetch(form.action, {
            method: form.method,
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        updateRegistrationMessage(block, data);
        updateRegistrationCounter(block, data);

        if (data.success) {
            updateRegistrationAction(block, data);
        }
    } catch (error) {
        console.error('Erreur pendant l’inscription à la sortie.', error);

        showTechnicalError(block);
    } finally {
        if (submitButton) {
            submitButton.disabled = false;
        }
    }
});

function updateRegistrationMessage(block, data) {
    const message = block.querySelector('[data-sortie-event_registration-message]');

    if (!message) {
        return;
    }

    message.textContent = data.message;
    message.classList.remove('hidden', 'text-green-600', 'text-red-600');
    message.classList.add(data.success ? 'text-green-600' : 'text-red-600');
}

function updateRegistrationCounter(block, data) {
    const counter = block.querySelector('[data-sortie-event_registration-counter]');

    if (!counter) {
        return;
    }

    counter.textContent = `${data.nbParticipantsInscrits} / ${data.nbInscriptionsMax} participant(s)`;
}

function updateRegistrationAction(block, data) {
    const action = block.querySelector('[data-sortie-event_registration-action]');

    if (!action) {
        return;
    }

    if (data.registered) {
        action.innerHTML = createUnregisterForm(block);
        return;
    }

    if (data.complete) {
        action.innerHTML = '<p>Cette sortie est complète.</p>';
        return;
    }

    if (!data.inscriptionsOpen) {
        action.innerHTML = '<p>Les inscriptions ne sont pas ouvertes.</p>';
        return;
    }

    action.innerHTML = createRegisterForm(block);
}

function createRegisterForm(block) {
    const registerUrl = block.dataset.registerUrl;
    const registerToken = block.dataset.registerToken;

    return `
        <form method="post"
              action="${registerUrl}"
              class="js-sortie-event_registration-form">
            <input type="hidden" name="_token" value="${registerToken}">
            <button type="submit" class="btn">S'inscrire</button>
        </form>
    `;
}

function createUnregisterForm(block) {
    const unregisterUrl = block.dataset.unregisterUrl;
    const unregisterToken = block.dataset.unregisterToken;

    return `
        <form method="post"
              action="${unregisterUrl}"
              class="js-sortie-event_registration-form">
            <input type="hidden" name="_token" value="${unregisterToken}">
            <button type="submit" class="btn">Se désinscrire</button>
        </form>
    `;
}

function showTechnicalError(block) {
    const message = block.querySelector('[data-sortie-event_registration-message]');

    if (!message) {
        return;
    }

    message.textContent = 'Une erreur est survenue. Veuillez réessayer.';
    message.classList.remove('hidden', 'text-green-600');
    message.classList.add('text-red-600');
}
