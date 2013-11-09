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
	`final_blow`   boolean NOT NULL DEFAULT false
);

CREATE TABLE `destroyed_items` (
	`kill_id` int unsigned NOT NULL,
	`item_id` int unsigned NOT NULL,
	`flags`   int unsigned NOT NULL DEFAULT 0 COMMENT 'in cargo, copy...',
	PRIMARY KEY (`kill_id`, `item_id`)
);

CREATE TABLE `players` (
	`player_id` int unsigned NOT NULL PRIMARY KEY,
	`corp_id`   int unsigned,
	`name`      varchar(256) NOT NULL
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

CREATE TABLE `systems` (
	`system_id` int unsigned NOT NULL PRIMARY KEY auto_increment,
	`title`     varchar(256) NOT NULL,
	UNIQUE KEY `name` (`title`)
);

CREATE TABLE `moons` (
	`moon_id`   int unsigned NOT NULL PRIMARY KEY auto_increment,
	`system_id` int unsigned NOT NULL,
	`title`     varchar(256) NOT NULL,
	UNIQUE KEY `system_moon` (`system_id`, `title`)
);

CREATE TABLE `items` (
	`item_id` int unsigned NOT NULL PRIMARY KEY auto_increment,
	`title`   varchar(256) NOT NULL,
	UNIQUE KEY `title` (`title`)
);