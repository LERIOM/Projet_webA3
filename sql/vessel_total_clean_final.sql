/* ---------------------------------------------------------------------------
   0 — Réinitialisation de l’ancien schéma
--------------------------------------------------------------------------- */
    -- ancienne table à supprimer
DROP TABLE IF EXISTS position           CASCADE;
DROP TABLE IF EXISTS boat               CASCADE;
DROP TABLE IF EXISTS navigation_status  CASCADE;
DROP TABLE IF EXISTS vessel_total_stage;

/* ---------------------------------------------------------------------------
   1 — Table tampon (UNLOGGED) + import CSV
--------------------------------------------------------------------------- */
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
    status             DOUBLE PRECISION,    -- NAVSTAT AIS (accepts 0.0, 15.0…)
    length             DOUBLE PRECISION,
    width              DOUBLE PRECISION,
    draft              DOUBLE PRECISION,
    cargo              DOUBLE PRECISION,
    transceiver_class  TEXT,
    cluster_kmeans     INTEGER              -- ★ nouveau champ ★
);

COPY vessel_total_stage
FROM '/var/www/html/Projet_webA3/csv/data_base_150_with_clusters.csv'
DELIMITER ','
CSV HEADER
NULL 'NA';

/* ---------------------------------------------------------------------------
   2 — Tables définitives
--------------------------------------------------------------------------- */
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
    transceiver_class  TEXT,
    cluster_kmeans     INTEGER              -- ★ stocké dans BOAT ★
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
    id_status       INTEGER REFERENCES navigation_status(id_status),
    mmsi            BIGINT   REFERENCES boat(mmsi)
                       ON UPDATE CASCADE
                       ON DELETE CASCADE
);

/* ---------------------------------------------------------------------------
   3 — Remplissage des tables
--------------------------------------------------------------------------- */

/* 3.1 — BOAT : un seul enregistrement par MMSI (ligne la plus récente) */
INSERT INTO boat (mmsi, vessel_name, length, width, draft,
                  vessel_type, imo, call_sign, cargo,
                  transceiver_class, cluster_kmeans)
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
       transceiver_class,
       cluster_kmeans
FROM vessel_total_stage
WHERE mmsi IS NOT NULL
ORDER BY mmsi, base_date_time DESC;

/* 3.2 — NAVIGATION_STATUS : dictionnaire des codes AIS NAVSTAT */
INSERT INTO navigation_status (id_status)
SELECT DISTINCT status::INTEGER
FROM vessel_total_stage
WHERE status IS NOT NULL
ORDER BY status;

UPDATE navigation_status
SET description = CASE id_status
    WHEN 0  THEN 'Under way using engine'
    WHEN 1  THEN 'At anchor'
    WHEN 2  THEN 'Not under command'
    WHEN 3  THEN 'Restricted manoeuverability'
    WHEN 4  THEN 'Constrained by her draught'
    WHEN 5  THEN 'Moored'
    WHEN 6  THEN 'Aground'
    WHEN 7  THEN 'Engaged in Fishing'
    WHEN 8  THEN 'Under way sailing'
    WHEN 9  THEN 'Reserved for future amendment of Navigational Status for HSC'
    WHEN 10 THEN 'Reserved for future amendment of Navigational Status for WIG'
    WHEN 11 THEN 'Reserved for future use'
    WHEN 12 THEN 'Reserved for future use'
    WHEN 13 THEN 'Reserved for future use'
    WHEN 14 THEN 'AIS-SART is active'
    WHEN 15 THEN 'Not defined (default)'
END
WHERE id_status BETWEEN 0 AND 15;

/* 3.3 — POSITION : une ligne par observation, sans le cluster */
INSERT INTO position (base_date_time, lat, lon, sog, cog, heading,
                      id_status, mmsi)
SELECT base_date_time,
       lat,
       lon,
       sog,
       cog,
       heading,
       status::INTEGER AS id_status,
       mmsi
FROM vessel_total_stage
WHERE mmsi IS NOT NULL;
