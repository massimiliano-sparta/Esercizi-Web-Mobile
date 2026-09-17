<?php
/**
 * attivita.php — Endpoint REST per la risorsa "attività"
 * ========================================================
 * Rotte gestite (tutte sullo stesso file, distinte dal verbo HTTP):
 *
 *   GET    /api/attivita.php            -> elenco di tutte le attività
 *   GET    /api/attivita.php?id=5       -> singola attività
 *   POST   /api/attivita.php            -> crea una nuova attività
 *   PUT    /api/attivita.php?id=5       -> sostituisce COMPLETAMENTE l'attività 5
 *   PATCH  /api/attivita.php?id=5       -> modifica PARZIALE dell'attività 5
 *   DELETE /api/attivita.php?id=5       -> elimina l'attività 5
 *
 * Nota su PUT vs PATCH (il punto degli appunti da chiarire):
 * - PUT è un rimpiazzo totale: il client deve mandare TUTTI i campi,
 *   quelli non inviati vengono considerati "non specificati" e qui li
 *   trattiamo come un errore, perché PUT è per definizione idempotente
 *   e completo.
 * - PATCH invece segue la logica di "JSON Merge Patch" (RFC 7396):
 *     -> un campo ASSENTE dal body = non lo tocco, resta com'era
 *     -> un campo presente con valore null = lo azzero/rimuovo (dove è
 *        consentito, cioè i campi NULLABLE come descrizione e scadenza)
 *     -> un campo presente con un valore = lo aggiorno con quel valore
 *   Quindi non è "i campi che non cambiano sono null": è il contrario,
 *   sono i campi ASSENTI a restare invariati, mentre null è un'istruzione
 *   esplicita di cancellazione.
 */

header('Content-Type: application/json; charset=utf8mb4');
require_once __DIR__ . '/config.php';

$pdo = getConnessioneDb();
$metodo = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

switch ($metodo) {
    case 'GET':
        if ($id !== null) {
            gestisciGetSingola($pdo, $id);
        } else {
            gestisciGetElenco($pdo);
        }
        break;

    case 'POST':
        gestisciPost($pdo);
        break;

    case 'PUT':
        gestisciPut($pdo, $id);
        break;

    case 'PATCH':
        gestisciPatch($pdo, $id);
        break;

    case 'DELETE':
        gestisciDelete($pdo, $id);
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['errore' => 'Metodo non supportato']);
}

// ------------------------------------------------------------------
// Funzioni di gestione, una per verbo HTTP
// ------------------------------------------------------------------

function gestisciGetElenco(PDO $pdo): void
{
    $stmt = $pdo->query('SELECT * FROM attivita ORDER BY id DESC');
    echo json_encode($stmt->fetchAll());
}

function gestisciGetSingola(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('SELECT * FROM attivita WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $riga = $stmt->fetch();

    if (!$riga) {
        http_response_code(404);
        echo json_encode(['errore' => 'Attività non trovata']);
        return;
    }
    echo json_encode($riga);
}

function gestisciPost(PDO $pdo): void
{
    $dati = leggiCorpoJson();

    // Validazione minima: il titolo è obbligatorio
    if (empty($dati['titolo'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['errore' => 'Il campo "titolo" è obbligatorio']);
        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO attivita (titolo, descrizione, completata, scadenza)
         VALUES (:titolo, :descrizione, :completata, :scadenza)'
    );
    $stmt->execute([
        'titolo'      => $dati['titolo'],
        'descrizione' => $dati['descrizione'] ?? null,
        'completata'  => !empty($dati['completata']) ? 1 : 0,
        'scadenza'    => $dati['scadenza'] ?? null,
    ]);

    $nuovoId = (int) $pdo->lastInsertId();
    http_response_code(201); // Created
    header('Location: /api/attivita.php?id=' . $nuovoId);
    gestisciGetSingola($pdo, $nuovoId);
}

function gestisciPut(PDO $pdo, ?int $id): void
{
    if ($id === null) {
        http_response_code(400);
        echo json_encode(['errore' => 'PUT richiede l\'id della risorsa da sostituire']);
        return;
    }

    $dati = leggiCorpoJson();
    $campiRichiesti = ['titolo', 'descrizione', 'completata', 'scadenza'];
    foreach ($campiRichiesti as $campo) {
        if (!array_key_exists($campo, $dati)) {
            http_response_code(400);
            echo json_encode([
                'errore' => "PUT richiede TUTTI i campi della risorsa. Manca: $campo",
            ]);
            return;
        }
    }

    $stmt = $pdo->prepare(
        'UPDATE attivita
         SET titolo = :titolo, descrizione = :descrizione,
             completata = :completata, scadenza = :scadenza
         WHERE id = :id'
    );
    $stmt->execute([
        'titolo'      => $dati['titolo'],
        'descrizione' => $dati['descrizione'],
        'completata'  => !empty($dati['completata']) ? 1 : 0,
        'scadenza'    => $dati['scadenza'],
        'id'          => $id,
    ]);

    if ($stmt->rowCount() === 0 && !esisteAttivita($pdo, $id)) {
        http_response_code(404);
        echo json_encode(['errore' => 'Attività non trovata']);
        return;
    }

    gestisciGetSingola($pdo, $id);
}

function gestisciPatch(PDO $pdo, ?int $id): void
{
    if ($id === null) {
        http_response_code(400);
        echo json_encode(['errore' => 'PATCH richiede l\'id della risorsa da modificare']);
        return;
    }

    if (!esisteAttivita($pdo, $id)) {
        http_response_code(404);
        echo json_encode(['errore' => 'Attività non trovata']);
        return;
    }

    $dati = leggiCorpoJson();

    // Costruiamo la query dinamicamente, includendo SOLO i campi
    // effettivamente presenti nel body (JSON Merge Patch, RFC 7396).
    // I campi assenti da $dati non vengono nemmeno menzionati nella SET.
    $campiConsentiti = ['titolo', 'descrizione', 'completata', 'scadenza'];
    $set = [];
    $parametri = ['id' => $id];

    foreach ($campiConsentiti as $campo) {
        if (array_key_exists($campo, $dati)) {
            $set[] = "$campo = :$campo";
            // Se il client manda esplicitamente null, lo scriviamo come NULL
            // (valido solo per descrizione e scadenza, che sono NULLABLE)
            $parametri[$campo] = $dati[$campo];
        }
    }

    if (empty($set)) {
        http_response_code(400);
        echo json_encode(['errore' => 'Nessun campo valido da aggiornare']);
        return;
    }

    $sql = 'UPDATE attivita SET ' . implode(', ', $set) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parametri);

    gestisciGetSingola($pdo, $id);
}

function gestisciDelete(PDO $pdo, ?int $id): void
{
    if ($id === null) {
        http_response_code(400);
        echo json_encode(['errore' => 'DELETE richiede l\'id della risorsa da eliminare']);
        return;
    }

    $stmt = $pdo->prepare('DELETE FROM attivita WHERE id = :id');
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['errore' => 'Attività non trovata']);
        return;
    }

    http_response_code(204); // No Content: cancellazione riuscita, nessun corpo da restituire
}

// ------------------------------------------------------------------
// Funzioni di utilità
// ------------------------------------------------------------------

/** Legge e decodifica il corpo della richiesta come JSON. */
function leggiCorpoJson(): array
{
    $corpo = file_get_contents('php://input');
    $dati = json_decode($corpo, true);
    return is_array($dati) ? $dati : [];
}

function esisteAttivita(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM attivita WHERE id = :id');
    $stmt->execute(['id' => $id]);
    return (bool) $stmt->fetchColumn();
}
