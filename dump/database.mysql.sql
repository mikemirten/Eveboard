CREATE TABLE `kills` (
	`kill_id`      int unsigned NOT NULL PRIMARY KEY,
	`character_id` int unsigned NOT NULL,
	`corp_id`      int unsigned NOT NULL,
	`alliance_id`  int unsigned NOT NULL,
	`faction_id`   int unsigned NOT NULL,
	`item_id`      int unsigned NOT NULL COMMENT 'Destroyed item',
	`system_id`    int unsigned NOT NULL,
	`moon_id`      int unsigned NOT NULL,
	`committed`    timestamp NOT NULL,
	`damage_taken` bigint unsigned
);

CREATE TABLE `involved` (
	`part_id`      int unsigned NOT NULL PRIMARY KEY auto_increment,
	`kill_id`      int unsigned NOT NULL,
	`character_id` int unsigned NOT NULL,
	`alliance_id`  int unsigned NOT NULL,
	`corp_id`      int unsigned NOT NULL,
	`faction_id`   int unsigned NOT NULL,
	`ship_id`      int unsigned NOT NULL,
	`weapon_id`    int unsigned NOT NULL,
	`damage_done`  bigint NOT NULL DEFAULT 0,
	`final_blow`   boolean NOT NULL DEFAULT false,
	KEY `kill` (`kill_id`)
);

CREATE TABLE `lost_items` (
	`kill_id`       int unsigned NOT NULL,
	`item_id`       int unsigned NOT NULL,
	`flag`          int unsigned NOT NULL DEFAULT 0,
	`qty_destroyed` int unsigned NOT NULL DEFAULT 0,
	`qty_dropped`   int unsigned NOT NULL DEFAULT 0,	
	PRIMARY KEY (`kill_id`, `item_id`, `flag`)
);

CREATE TABLE `items` (
	`item_id` int unsigned NOT NULL PRIMARY KEY,
	`title`   varchar(256) NOT NULL
);

CREATE TABLE `players` (
	`player_id` int unsigned NOT NULL PRIMARY KEY,
	`corp_id`   int unsigned,
	`name`      varchar(256) NOT NULL,
	KEY `corp` (`corp_id`)
);

CREATE TABLE `alliances` (
	`alliance_id` int unsigned NOT NULL PRIMARY KEY,
	`title`       varchar(256) NOT NULL
);

CREATE TABLE `corps` (
	`corp_id`     int unsigned NOT NULL PRIMARY KEY,
	`alliance_id` int unsigned,
	`title`       varchar(256) NOT NULL,
	KEY `alliance` (`alliance_id`)
);