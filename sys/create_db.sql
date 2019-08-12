BEGIN TRANSACTION;
CREATE TABLE "shortlinks" (
    `obj`       TEXT NOT NULL UNIQUE,
    `location`  TEXT,
    `del_key`   TEXT NOT NULL UNIQUE,
    `deleted`   INTEGER NOT NULL,
    `key_id`    INTEGER NOT NULL 
);
CREATE TABLE "files" (
    `obj`       TEXT NOT NULL UNIQUE,
    `del_key`   TEXT NOT NULL UNIQUE,
    `deleted`   INTEGER NOT NULL,
    `key_id`    INTEGER NOT NULL 
);
CREATE TABLE "apikeys" (
    `id`    INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `key`   TEXT NOT NULL UNIQUE,
    `valid` INTEGER NOT NULL,
    `name`  TEXT
);
COMMIT;
