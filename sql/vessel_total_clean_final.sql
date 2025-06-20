
CREATE TABLE IF NOT EXISTS vessel_total_clean_final (
    id INTEGER,
    mmsi BIGINT,
    base_date_time TIMESTAMP,
    lat DOUBLE PRECISION,
    lon DOUBLE PRECISION,
    sog DOUBLE PRECISION,
    cog DOUBLE PRECISION,
    heading DOUBLE PRECISION,
    vessel_name TEXT,
    imo TEXT,
    call_sign TEXT,
    vessel_type INTEGER,
    status DOUBLE PRECISION,
    length DOUBLE PRECISION,
    width DOUBLE PRECISION,
    draft DOUBLE PRECISION,
    cargo DOUBLE PRECISION,
    transceiver_class TEXT
);

ALTER TABLE vessel_total_clean_final ADD PRIMARY KEY (id);

-- PostgreSQL bulk import
COPY vessel_total_clean_final FROM '../csv/vessel-total-clean-final.csv' DELIMITER ',' CSV HEADER;

-- LOAD DATA INFILE '/path/to/vessel-total-clean-final.csv'
-- INTO TABLE vessel_total_clean_final
-- FIELDS TERMINATED BY ',' ENCLOSED BY '"'
-- LINES TERMINATED BY '\n'
-- IGNORE 1 LINES;