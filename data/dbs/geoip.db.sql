CREATE TABLE geoblocks (
    startIpNum NUMERIC UNIQUE,
    endIpNum NUMERIC UNIQUE,
    locId INTEGER REFERENCES geolocation(locID)
, idx INTEGER);
CREATE TABLE geolocation (
    locId INTEGER PRIMARY KEY,
    country TEXT,
    region TEXT,
    city TEXT,
    postalCode TEXT,
    latitude NUMERIC,
    longitude NUMERIC,
    metroCode TEXT,
    areaCode TEXT
);
CREATE INDEX geoidx ON geoblocks(idx);
