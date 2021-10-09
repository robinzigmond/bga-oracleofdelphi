
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- TheOracleOfDelphi implementation : © Robin Zigmond (robinzig@hotmail.com)
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

CREATE TABLE IF NOT EXISTS `card` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` varchar(16) NOT NULL,
  `card_type_arg` int(11) NOT NULL,
  `card_location` varchar(16) NOT NULL,
  `card_location_arg` int(11) NOT NULL,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `map_hex` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `x_coord` INT(8) NOT NULL,
    `y_coord` INT(8) NOT NULL,
    `type` ENUM(
        'water',
        'zeus',
        'island',
        'temple',
        'monster',
        'land',
        'offering',
        'city',
        'statue_red_blue_pink',
        'statue_green_yellow_pink',
        'statue_black_blue_yellow',
        'statue_red_pink_black',
        'statue_green_blue_black',
        'statue_red_green_yellow'
    ) NOT NULL,
    `color` ENUM('pink', 'blue', 'yellow', 'green', 'red', 'black') NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `player_dice` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `color` ENUM('pink', 'blue', 'yellow', 'green', 'red', 'black') NOT NULL,
  `player_id` INT(10) UNSIGNED NOT NULL,
  `used` TINYINT(1) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `token` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `type` ENUM('zeus', 'monster', 'shrine', 'statue', 'offering', 'ship', 'island') NOT NULL,
    `color` ENUM('pink', 'blue', 'yellow', 'green', 'red', 'black') NULL DEFAULT NULL,
    `location_x` INT(10) NULL DEFAULT NULL,
    `location_y` INT(10) NULL DEFAULT NULL,
    `player_id` INT(10) UNSIGNED NULL DEFAULT NULL,
    `status` INT(4) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

ALTER TABLE `player`
    ADD `ship_location_x` INT(10) UNSIGNED NOT NULL,
    ADD `ship_location_y` INT(10) UNSIGNED NOT NULL,
    ADD `shields` INT(8) UNSIGNED NOT NULL DEFAULT 0,
    ADD `favors` INT(8) UNSIGNED NOT NULL DEFAULT 0,
    ADD `poseidon` INT(4) UNSIGNED NOT NULL DEFAULT 0,
    ADD `artemis` INT(4) UNSIGNED NOT NULL DEFAULT 0,
    ADD `ares` INT(4) UNSIGNED NOT NULL DEFAULT 0,
    ADD `apollon` INT(4) UNSIGNED NOT NULL DEFAULT 0,
    ADD `aphrodite` INT(4) UNSIGNED NOT NULL DEFAULT 0,
    ADD `hermes` INT(4) UNSIGNED NOT NULL DEFAULT 0,
    ADD `oracle_used` ENUM('pink', 'blue', 'yellow', 'green', 'red', 'black') NULL DEFAULT NULL,
    ADD `apollo_used` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;
