// app.js — Frontend che dialoga con l'API REST in api/attivita.php
// Nota: in un progetto reale l'URL dell'API andrebbe reso configurabile
// (es. costante o file di config JS), qui è fissato per semplicità.
const URL_API = '/api/attivita.php';

const lista = document.getElementById('lista-attivita');
const form = document.getElementById('form-nuova-attivita');

// --- Caricamento iniziale (GET /api/attivita.php) ---
document.addEventListener('DOMContentLoaded', caricaAttivita);

async function caricaAttivita() {
    const risposta = await fetch(URL_API);
    const attivita = await risposta.json();

    lista.innerHTML = '';
    attivita.forEach(disegnaRiga);
}

function disegnaRiga(attivita) {
    const li = document.createElement('li');
    li.className = 'attivita' + (attivita.completata == 1 ? ' completata' : '');

    // Colonna sinistra: titolo + descrizione (se c'è)
    const testo = document.createElement('div');
    testo.className = 'testo';

    const titolo = document.createElement('span');
    titolo.className = 'titolo';
    titolo.textContent = attivita.titolo;

    testo.appendChild(titolo);

    // La descrizione la mostriamo SOLO se presente, sotto il titolo
    if (attivita.descrizione) {
        const descrizione = document.createElement('p');
        descrizione.className = 'descrizione';
        descrizione.textContent = attivita.descrizione;
        testo.appendChild(descrizione);
    }

    const scadenza = document.createElement('span');
    scadenza.className = 'scadenza';
    scadenza.textContent = attivita.scadenza ?? 'nessuna scadenza';

    const bottoneCompleta = document.createElement('button');
    bottoneCompleta.textContent = attivita.completata == 1 ? 'Riapri' : 'Completa';
    bottoneCompleta.onclick = () => alternaCompletata(attivita);

    // Bottone per aggiungere/modificare/rimuovere la DESCRIZIONE senza
    // cancellare e ricreare l'attività: PATCH con un solo campo.
    const bottoneDescrizione = document.createElement('button');
    bottoneDescrizione.textContent = 'Descrizione';
    bottoneDescrizione.onclick = () => modificaDescrizione(attivita);

    // Bottone per aggiungere/modificare/rimuovere la SCADENZA.
    const bottoneScadenza = document.createElement('button');
    bottoneScadenza.textContent = 'Scadenza';
    bottoneScadenza.onclick = () => modificaScadenza(attivita);

    const bottoneElimina = document.createElement('button');
    bottoneElimina.textContent = 'Elimina';
    bottoneElimina.onclick = () => eliminaAttivita(attivita.id);

    li.append(testo, scadenza, bottoneCompleta, bottoneDescrizione, bottoneScadenza, bottoneElimina);
    lista.appendChild(li);
}

// Chiede il testo con un prompt e manda un PATCH { descrizione: ... }.
// - Ok con testo      -> la imposta/aggiorna
// - Ok con campo vuoto -> la RIMUOVE (mandando null, come da RFC 7396)
// - Annulla           -> non tocca nulla
async function modificaDescrizione(attivita) {
    const valore = prompt(
        'Descrizione dell\'attività. Lascia vuoto per rimuoverla:',
        attivita.descrizione ?? ''
    );
    if (valore === null) return; // utente ha premuto Annulla

    const descrizione = valore.trim() === '' ? null : valore;

    await fetch(`${URL_API}?id=${attivita.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ descrizione }),
    });
    caricaAttivita();
}

// Chiede la data con un prompt e manda un PATCH { scadenza: ... }.
// - Ok con una data    -> la imposta/aggiorna
// - Ok con campo vuoto -> la RIMUOVE (mandando null, come da RFC 7396)
// - Annulla            -> non tocca nulla
async function modificaScadenza(attivita) {
    const valore = prompt(
        'Scadenza (formato YYYY-MM-DD). Lascia vuoto per rimuoverla:',
        attivita.scadenza ?? ''
    );
    if (valore === null) return; // utente ha premuto Annulla

    const scadenza = valore.trim() === '' ? null : valore.trim();

    await fetch(`${URL_API}?id=${attivita.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ scadenza }),
    });
    caricaAttivita();
}

// --- Creazione (POST /api/attivita.php) ---
form.addEventListener('submit', async (evento) => {
    evento.preventDefault();

    const nuovaAttivita = {
        titolo: document.getElementById('input-titolo').value,
        descrizione: document.getElementById('input-descrizione').value || null,
        scadenza: document.getElementById('input-scadenza').value || null,
    };

    await fetch(URL_API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(nuovaAttivita),
    });

    form.reset();
    caricaAttivita();
});

// --- Modifica parziale (PATCH /api/attivita.php?id=...) ---
// Usiamo PATCH e non PUT perché stiamo cambiando UN SOLO campo
// alla volta, non l'intera risorsa: è l'uso corretto della semantica
// "JSON Merge Patch" vista a lezione.
async function alternaCompletata(attivita) {
    await fetch(`${URL_API}?id=${attivita.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ completata: attivita.completata == 1 ? 0 : 1 }),
    });
    caricaAttivita();
}

// --- Eliminazione (DELETE /api/attivita.php?id=...) ---
async function eliminaAttivita(id) {
    await fetch(`${URL_API}?id=${id}`, { method: 'DELETE' });
    caricaAttivita();
}
