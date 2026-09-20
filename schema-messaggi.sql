-- Tabella per i messaggi inviati dal form "Contatti" della home.
-- Da eseguire UNA VOLTA, dopo aver già creato il database corso_web:
--
--   mysql -u corso_web -p corso_web < schema-messaggi.sql

CREATE TABLE IF NOT EXISTS messaggi (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(100) NOT NULL,
    email       VARCHAR(190) NOT NULL,
    motivo      VARCHAR(30)  NOT NULL,
    preferenza  VARCHAR(10)  NOT NULL DEFAULT 'email',
    messaggio   TEXT         NOT NULL,
    inviato_il  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
