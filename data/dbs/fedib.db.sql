CREATE TABLE fediverse_nodes (
    "node_id" INTEGER PRIMARY KEY NOT NULL,
    "node_uri" TEXT NOT NULL,
    "node_last_up" TEXT,
    "node_version" TEXT,
    "node_up" INTEGER,
    "node_theme" TEXT,
    "node_email" TEXT,
    "node_timezone" TEXT,
    "node_closed" TEXT,
    "node_inviteonly" TEXT,
    "node_private" TEXT,
    "node_textlimit" TEXT,
    "node_ip" TEXT,
    "node_country" TEXT,
    "node_city" TEXT,
    "node_latitude" TEXT,
    "node_longitude" TEXT
, "is_nsfw" INTEGER   DEFAULT (0));
