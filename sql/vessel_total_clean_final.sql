DROP TABLE IF EXISTS vessel_total_clean_final;
/* 0. — Nettoyage de l’ancien schéma
-----------------------------------*/
DROP TABLE IF EXISTS position           CASCADE;
DROP TABLE IF EXISTS boat               CASCADE;
DROP TABLE IF EXISTS navigation_status  CASCADE;   -- « status » est un mot un peu ambigu
DROP TABLE IF EXISTS vessel_total_stage;

/* 1. — Table tampon pour l’import CSV
--------------------------------------*/
CREATE UNLOGGED TABLE vessel_total_stage (
    id                 INTEGER,
    mmsi               BIGINT,
    base_date_time     TIMESTAMP,
    lat                DOUBLE PRECISION,
    lon                DOUBLE PRECISION,
    sog                DOUBLE PRECISION,
    cog                DOUBLE PRECISION,
    heading            DOUBLE PRECISION,
    vessel_name        TEXT,
    imo                TEXT,
    call_sign          TEXT,
    vessel_type        INTEGER,
    status             INTEGER,             -- colonne NAVSTAT du message AIS
    length             DOUBLE PRECISION,
    width              DOUBLE PRECISION,
    draft              DOUBLE PRECISION,
    cargo              DOUBLE PRECISION,
    transceiver_class  TEXT
);

COPY vessel_total_stage
FROM '/var/www/html/Projet_webA3/csv/vessel-total-clean-final.csv'
DELIMITER ','
CSV HEADER
NULL 'NA';

/* 2. — Tables définitives du modèle
------------------------------------*/
CREATE TABLE boat (
    mmsi               BIGINT PRIMARY KEY,
    vessel_name        TEXT,
    length             DOUBLE PRECISION,
    width              DOUBLE PRECISION,
    draft              DOUBLE PRECISION,
    vessel_type        INTEGER,
    imo                TEXT,
    call_sign          TEXT,
    cargo              DOUBLE PRECISION,
    transceiver_class  TEXT
);

CREATE TABLE navigation_status (
    id_status   INTEGER PRIMARY KEY,
    description TEXT
);

CREATE TABLE position (
    id_position     SERIAL PRIMARY KEY,
    base_date_time  TIMESTAMP      NOT NULL,
    lat             DOUBLE PRECISION,
    lon             DOUBLE PRECISION,
    sog             DOUBLE PRECISION,
    cog             DOUBLE PRECISION,
    heading         DOUBLE PRECISION,
    id_status       INTEGER        REFERENCES navigation_status(id_status),
    mmsi            BIGINT         REFERENCES boat(mmsi)
                       ON UPDATE CASCADE
                       ON DELETE CASCADE
);

/* 3. — Remplissage des tables
-----------------------------*/

/* 3.1  Bateaux : un enregistrement unique par MMSI            */
INSERT INTO boat (mmsi, vessel_name, length, width, draft,
                  vessel_type, imo, call_sign, cargo, transceiver_class)
SELECT DISTINCT ON (mmsi)
       mmsi,
       vessel_name,
       length,
       width,
       draft,
       vessel_type,
       imo,
       call_sign,
       cargo,
       transceiver_class
FROM vessel_total_stage
WHERE mmsi IS NOT NULL
ORDER BY mmsi, base_date_time DESC;

/* 3.2  Statuts de navigation (NAVSTAT)                        */
INSERT INTO navigation_status (id_status)
SELECT DISTINCT status
FROM vessel_total_stage
WHERE status IS NOT NULL
ORDER BY status;

/* Vous pouvez ensuite mettre à jour la colonne description
   manuellement ou via un UPDATE ... CASE ... pour documenter
   le sens de chaque code AIS (0 = Under way using engine, etc.) */

/* 3.3  Positions                                              */
INSERT INTO position (base_date_time, lat, lon, sog, cog, heading,
                      id_status, mmsi)
SELECT base_date_time,
       lat,
       lon,
       sog,
       cog,
       heading,
       status        AS id_status,
       mmsi
FROM vessel_total_stage
WHERE mmsi IS NOT NULL;

/* 4. — Nettoyage facultatif                                   */
-- DROP TABLE vessel_total_stage;


