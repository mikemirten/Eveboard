CREATE TABLE `kills` (
	`kill_id`      int unsigned NOT NULL PRIMARY KEY auto_increment,
	`player_id`    int unsigned,
	`alliance_id`  int unsigned,
	`corp_id`      int unsigned,
	`faction_id`   int unsigned,
	`item_id`      int unsigned NOT NULL COMMENT 'Destroyed item',
	`system_id`    int unsigned NOT NULL,
	`moon_id`      int unsigned,
	`committed`    timestamp NOT NULL,
	`security`     int signed,
	`damage_taken` bigint unsigned,
	`mail_source`  text NOT NULL COMMENT 'GZipped',
	`kill_hash`    varchar(32) NOT NULL,
	UNIQUE KEY `hash` (`kill_hash`)
);

CREATE TABLE `involved` (
	`kill_id`     int unsigned NOT NULL,
	`player_id`   int unsigned NOT NULL,
	`alliance_id` int unsigned,
	`corp_id`     int unsigned,
	`faction_id`  int unsigned,
	`ship_id`     int unsigned NOT NULL,
	`weapon_id`   int unsigned,
	`damage_done` bigint unsiged,
	UNIQUE KEY `involved_player` (`kill_id`, `player_id`)
);

CREATE TABLE `destroyed_items` (
	`kill_id` int unsigned NOT NULL,
	`item_id` int unsigned NOT NULL,
	`flags`   int unsigned NOT NULL DEFAULT 0 COMMENT 'in cargo, copy...',
	UNIQUE KEY `destroyed item` (`kill_id`, `item_id`)
);

CREATE TABLE `players` (
	`player_id`   int unsigned NOT NULL PRIMARY KEY auto_increment,
	`corp_id`     int unsigned,
	`name` varchar(256) NOT NULL,
	UNIQUE KEY `name` (`name`)
);

CREATE TABLE `alliances` (
	`alliance_id` int unsigned NOT NULL PRIMARY KEY auto_increment,
	`title`       varchar(256) NOT NULL,
	UNIQUE KEY `name` (`title`)
);

CREATE TABLE `corps` (
	`corp_id`     int unsigned NOT NULL PRIMARY KEY auto_increment,
	`alliance_id` int unsigned,
	`title`       varchar(256) NOT NULL,
	KEY `alliance` (`alliance_id`),
	UNIQUE KEY `name` (`title`)
);

CREATE TABLE `systems` (
	`system_id` int unsigned NOT NULL PRIMARY KEY auto_increment,
	`title`     varchar(256) NOT NULL,
	UNIQUE KEY `name` (`title`)
);

CREATE TABLE `moons` (
	`moon_id`   int unsigned NOT NULL PRIMARY KEY auto_increment,,
	`system_id` int unsigned NOT NULL,
	`title`     varchar(256) NOT NULL,
	UNIQUE KEY `system_moon` (`system_id`, `title`)
);