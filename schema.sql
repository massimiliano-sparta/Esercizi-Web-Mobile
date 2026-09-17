-- Schema di esempio: gestione di una lista di "attività" (tipo to-do list,
-- ma con scadenza e descrizione, così ci sono abbastanza campi per mostrare
-- bene la differenza tra PUT e PATCH nella REST API).
--
-- Uso: mysql -u root -p corso_web < schema.sql

CREATE TABLE IF NOT EXISTS attivita (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titolo      VARCHAR(150) NOT NULL,
    descrizione TEXT NULL,             -- può essere assente/vuota
    completata  TINYINT(1) NOT NULL DEFAULT 0,
    scadenza    DATE NULL,             -- può essere assente/vuota
    creato_il   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Un po' di dati iniziali per non partire da una tabella vuota
INSERT INTO attivita (titolo, descrizione, completata, scadenza) VALUES
('Studiare REST API', 'Rivedere differenza tra PUT e PATCH', 0, '2026-09-25'),
('Consegnare progetto Basi di Dati', NULL, 1, '2026-09-10'),
('Preparare esame Sistemi Operativi', 'Ripassare Silberschatz, capitoli su scheduling', 0, NULL);
