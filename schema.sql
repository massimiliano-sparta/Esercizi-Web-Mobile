-- Schema completo del progetto: tabella "attivita" (demo REST) e tabella
-- "messaggi" (form contatti della home). Un solo file, un solo comando.
--
-- Uso: mysql -u corso_web -p corso_web < schema.sql

CREATE TABLE IF NOT EXISTS attivita (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titolo      VARCHAR(150) NOT NULL,
    descrizione TEXT NULL,             -- può essere assente/vuota
    completata  TINYINT(1) NOT NULL DEFAULT 0,
    scadenza    DATE NULL,             -- può essere assente/vuota
    creato_il   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Messaggi inviati dal form "Contatti" della home.
CREATE TABLE IF NOT EXISTS messaggi (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(100) NOT NULL,
    email       VARCHAR(190) NOT NULL,
    motivo      VARCHAR(30)  NOT NULL,
    preferenza  VARCHAR(10)  NOT NULL DEFAULT 'email',
    messaggio   TEXT         NOT NULL,
    inviato_il  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Un po' di dati iniziali per non partire da una tabella vuota
INSERT INTO attivita (titolo, descrizione, completata, scadenza) VALUES
('Studiare REST API', 'Rivedere differenza tra PUT e PATCH', 0, '2026-09-25'),
('Consegnare progetto Basi di Dati', NULL, 1, '2026-09-10'),
('Preparare esame Sistemi Operativi', 'Ripassare Silberschatz, capitoli su scheduling', 0, NULL);
