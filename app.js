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

    const titolo = document.createElement('span');
    titolo.className = 'titolo';
    titolo.textContent = attivita.titolo;

    const scadenza = document.createElement('span');
    scadenza.className = 'scadenza';
    scadenza.textContent = attivita.scadenza ?? 'nessuna scadenza';

    const bottoneCompleta = document.createElement('button');
    bottoneCompleta.textContent = attivita.completata == 1 ? 'Riapri' : 'Completa';
    bottoneCompleta.onclick = () => alternaCompletata(attivita);

    const bottoneElimina = document.createElement('button');
    bottoneElimina.textContent = 'Elimina';
    bottoneElimina.onclick = () => eliminaAttivita(attivita.id);

    li.append(titolo, scadenza, bottoneCompleta, bottoneElimina);
    lista.appendChild(li);
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
// (completata), non l'intera risorsa: è l'uso corretto della semantica
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
