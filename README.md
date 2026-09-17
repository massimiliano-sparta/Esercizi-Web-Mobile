# Esercizio REST — Programmazione Web e Mobile

Piccola demo realizzata dopo la prima lezione del corso di **Programmazione
Web e Mobile** (UniMe), per prendere confidenza con lo stack richiesto dal
progetto d'esame prima di scegliere il tema vero e proprio.

**Non è il progetto d'esame** — è solo un esempio di riferimento su:
HTML/CSS/JS lato client, PHP lato server, MySQL, e un servizio web RESTful
con tutti i verbi HTTP principali (GET, POST, PUT, PATCH, DELETE).

## Cosa fa

Una to-do list ("attività") con titolo, descrizione, stato di completamento
e scadenza. Serve a mostrare in pratica due cose viste a lezione:

- la differenza tra **PUT** (sostituzione completa della risorsa, richiede
  tutti i campi) e **PATCH** (modifica parziale, secondo la semantica
  "JSON Merge Patch", RFC 7396: i campi assenti dal body non vengono
  toccati, un campo `null` esplicito lo azzera);
- i codici di stato HTTP corretti per ogni operazione (`201` alla
  creazione, `204` alla cancellazione, `404` quando la risorsa non
  esiste, `400` per richieste malformate).

## Struttura

```
├── index.html          interfaccia
├── style.css
├── app.js              chiamate fetch() all'API
├── schema.sql           schema MySQL + dati di esempio
└── api/
    ├── config.php              connessione PDO
    ├── credentials.example.php template delle credenziali (da copiare)
    ├── credentials.php         credenziali reali (NON tracciato da Git)
    ├── attivita.php            endpoint REST
    └── test.php                script rapido per verificare la connessione al DB
```

## Come farlo partire in locale

1. Crea il database ed esegui lo schema:
   ```
   mysql -u root -p -e "CREATE DATABASE corso_web CHARACTER SET utf8mb4;"
   mysql -u root -p corso_web < schema.sql
   ```
2. Copia il template delle credenziali e inserisci i tuoi valori:
   ```
   cp api/credentials.example.php api/credentials.php
   ```
3. Avvia il server di sviluppo di PHP:
   ```
   php -S localhost:8000
   ```
4. Apri `http://localhost:8000` nel browser.

## Note

Progetto realizzato con il supporto di Claude (Anthropic) durante lo studio
personale, come da indicazioni del corso: usato come tutor/revisore di
codice, non per generare la soluzione da consegnare senza capirla.
