# Massimiliano Sparta — Sito personale

Sito personale con pagina "chi sono", stato degli esami, progetti e un form
di contatto funzionante; realizzato durante il corso di **Programmazione
Web e Mobile** (UniMe) come banco di prova per lo stack richiesto dal
progetto d'esame: HTML/CSS/JS lato client, PHP lato server, MySQL, servizio
web RESTful.

**Non è il progetto d'esame finale**, è nato come esercizio dopo la prima
lezione del corso e si è espanso via via, prima di scegliere il tema vero
e proprio della consegna.

## Cosa contiene

- **`index.html`**: la home: chi sono, tabelle con stato esami e
  competenze tecniche, elenco progetti, form di contatto.
- **`attivita.html`**: demo di gestione attività (to-do list), collegata
  a un'API REST completa (GET, POST, PUT, PATCH, DELETE).
- **Form contatti**: invia un messaggio via `POST` a un endpoint REST
  dedicato, con validazione sia lato client che lato server.

Due concetti mostrati in pratica:

- la differenza tra **PUT** (sostituzione completa della risorsa, richiede
  tutti i campi) e **PATCH** (modifica parziale, semantica "JSON Merge
  Patch", RFC 7396: i campi assenti dal body non vengono toccati, un
  campo `null` esplicito lo azzera);
- i codici di stato HTTP corretti per ogni operazione (`201` alla
  creazione, `204` alla cancellazione, `404` risorsa inesistente, `400`
  richiesta malformata, `405` metodo non supportato).

## Struttura

    ├── index.html            home / pagina personale
    ├── attivita.html         demo gestione attività
    ├── style.css             tema condiviso da tutte le pagine
    ├── app.js                fetch() per l'API attività
    ├── contatti.js           fetch() per il form contatti
    ├── schema.sql            schema MySQL completo (attivita + messaggi) + dati di esempio
    └── api/
        ├── config.php                connessione PDO
        ├── credentials.example.php   template delle credenziali (da copiare)
        ├── credentials.php           credenziali reali (NON tracciato da Git)
        ├── attivita.php              endpoint REST per le attività
        ├── messaggi.php              endpoint REST per il form contatti (solo POST)
        └── test.php                  script rapido per verificare la connessione al DB

## Come farlo partire in locale

1. Crea il database ed esegui lo schema (crea entrambe le tabelle):

       mysql -u root -p -e "CREATE DATABASE corso_web CHARACTER SET utf8mb4;"
       mysql -u root -p corso_web < schema.sql

2. Copia il template delle credenziali e inserisci i tuoi valori:

       cp api/credentials.example.php api/credentials.php

3. Avvia il server di sviluppo di PHP:

       php -S localhost:8000

4. Apri `http://localhost:8000` nel browser.

## Note di sicurezza

- Le credenziali del database vivono solo in `api/credentials.php`,
  escluso dal repository tramite `.gitignore`.
- `api/messaggi.php` accetta solo `POST`: niente endpoint pubblico che
  elenchi i messaggi ricevuti, perché conterrebbero dati di terze
  persone (chi scrive tramite il form), non solo miei.
