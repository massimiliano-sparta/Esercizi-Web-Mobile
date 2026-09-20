<?php
/**
 * messaggi.php — Endpoint REST per la risorsa "messaggi di contatto"
 * =================================================================
 *   POST /api/messaggi.php        -> salva un nuovo messaggio di contatto
 *
 * Volutamente NON esiste un GET che elenca i messaggi: sono dati di
 * terze persone (chi scrive tramite il form), non solo tuoi. Un
 * endpoint pubblico che li espone in lettura sarebbe un data leak,
 * non una feature. Per consultarli, interroga il database direttamente
 * (mysql CLI, un client grafico, ecc.), da un contesto che non è
 * raggiungibile pubblicamente da chiunque conosca l'URL.
 *
 * Il form della pagina personale (index.html) lo usa via fetch() in JSON.
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

$pdo = getConnessioneDb();
$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'POST':
        gestisciPost($pdo);
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['errore' => 'Metodo non supportato']);
}

function gestisciPost(PDO $pdo): void
{
    $dati = leggiCorpoJson();

    // --- Validazione ---
    $nome       = trim($dati['nome'] ?? '');
    $email      = trim($dati['email'] ?? '');
    $motivo     = trim($dati['motivo'] ?? '');
    $preferenza = trim($dati['preferenza'] ?? 'email');
    $messaggio  = trim($dati['messaggio'] ?? '');

    if ($nome === '' || $messaggio === '') {
        http_response_code(400);
        echo json_encode(['errore' => 'Nome e messaggio sono obbligatori']);
        return;
    }

    // Validazione dell'email con il filtro dedicato di PHP
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['errore' => 'Indirizzo email non valido']);
        return;
    }

    // Il motivo deve essere uno di quelli previsti dal form
    $motiviConsentiti = ['collaborazione', 'ripetizioni', 'altro'];
    if (!in_array($motivo, $motiviConsentiti, true)) {
        http_response_code(400);
        echo json_encode(['errore' => 'Motivo del contatto non valido']);
        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO messaggi (nome, email, motivo, preferenza, messaggio)
         VALUES (:nome, :email, :motivo, :preferenza, :messaggio)'
    );
    $stmt->execute([
        'nome'       => $nome,
        'email'      => $email,
        'motivo'     => $motivo,
        'preferenza' => $preferenza,
        'messaggio'  => $messaggio,
    ]);

    http_response_code(201); // Created
    echo json_encode([
        'ok'      => true,
        'messaggio' => 'Messaggio inviato, grazie ' . $nome . '!',
    ]);
}

/** Legge e decodifica il corpo della richiesta come JSON. */
function leggiCorpoJson(): array
{
    $corpo = file_get_contents('php://input');
    $dati = json_decode($corpo, true);
    return is_array($dati) ? $dati : [];
}
