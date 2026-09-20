// contatti.js — Collega il form "Contatti" della home all'endpoint
// POST /api/messaggi.php, inviando i dati come JSON (come fa app.js
// per le attività). Va incluso in index.html, dopo </body> oppure con
// <script src="contatti.js" defer></script> nel <head>.

const formContatti = document.querySelector('.contact-form');

if (formContatti) {
    formContatti.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const bottone = formContatti.querySelector('button[type="submit"]');
        bottone.disabled = true;
        bottone.textContent = 'Invio in corso…';

        const dati = {
            nome: document.getElementById('nome').value,
            email: document.getElementById('email').value,
            motivo: document.getElementById('motivo').value,
            preferenza: formContatti.querySelector('input[name="preferenza"]:checked').value,
            messaggio: document.getElementById('messaggio').value,
        };

        try {
            const risposta = await fetch('/api/messaggi.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dati),
            });

            const esito = await risposta.json();

            if (!risposta.ok) {
                throw new Error(esito.errore || "Errore durante l'invio");
            }

            mostraEsito('ok', esito.messaggio);
            formContatti.reset();
        } catch (errore) {
            mostraEsito('errore', errore.message);
        } finally {
            bottone.disabled = false;
            bottone.textContent = 'Invia messaggio';
        }
    });
}

// Mostra un messaggio di conferma/errore sopra il form
function mostraEsito(tipo, testo) {
    let avviso = document.querySelector('.esito-form');

    if (!avviso) {
        avviso = document.createElement('p');
        avviso.className = 'esito-form';
        formContatti.parentElement.insertBefore(avviso, formContatti);
    }

    avviso.textContent = testo;
    avviso.dataset.tipo = tipo; // il colore lo decidiamo via CSS
}
