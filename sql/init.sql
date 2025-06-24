SELECT
    mmsi,
    vessel_name,
    length,
    width,
    draft
FROM boat
WHERE length = (
    SELECT MAX(length)
    FROM boat
);