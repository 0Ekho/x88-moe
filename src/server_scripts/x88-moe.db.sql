BEGIN TRANSACTION;
CREATE TABLE "shortlinks" (
	`item_key`	TEXT NOT NULL UNIQUE,
	`location`	TEXT,
	`delete_key`	TEXT NOT NULL UNIQUE,
	`deleted`	INTEGER NOT NULL
);
CREATE TABLE "files" (
	`item_key`	TEXT NOT NULL UNIQUE,
	`extension`	TEXT,
	`delete_key`	TEXT NOT NULL UNIQUE,
	`deleted`	INTEGER NOT NULL
);
CREATE TABLE "apikeys" (
	`id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	`apikey`	TEXT NOT NULL UNIQUE,
	`valid`	INTEGER NOT NULL,
	`name`	TEXT
);
COMMIT;
