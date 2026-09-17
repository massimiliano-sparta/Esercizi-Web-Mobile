<?php
/**
 * config.php
 * -----------
 * Centralizza la connessione al database tramite PDO.
 * PDO (PHP Data Objects) ci piace perché supporta i "prepared statement",
 * che ci proteggono dalla SQL injection: i valori vengono inviati al DB
 * separatamente dalla query, non concatenati dentro la stringa SQL.
 */

// Le credenziali vere e proprie vivono in credentials.php, che NON è
// tracciato da Git (vedi .gitignore). Se questo file manca, copia
// credentials.example.php e compilalo con i tuoi valori.
require_once __DIR__ . '/credentials.php';

function getConnessioneDb(): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    $opzioni = [
        // Le query fallite lanciano eccezioni invece di fallire senza dire niente
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // I risultati arrivano come array associativi (['titolo' => ...])
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Usa i prepared statement reali del driver, non l'emulazione di PHP
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, DB_USER, DB_PASS, $opzioni);
    } catch (PDOException $e) {
        // Non esporre mai il messaggio d'errore originale al client:
        // potrebbe rivelare dettagli sulla struttura del database.
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['errore' => 'Connessione al database fallita']);
        exit;
    }
}
