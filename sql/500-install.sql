

-- --------------------------------------------------------
--
-- Table structure for table `plugin`
--
DROP TABLE IF EXISTS `plugin`;
CREATE TABLE IF NOT EXISTS `plugin` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL,
  `version` VARCHAR(9) NOT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`)
) ENGINE=InnoDB;


-- --------------------------------------------------------
--
-- Table structure for table `pluginData`
--
DROP TABLE IF EXISTS `pluginData`;
CREATE TABLE IF NOT EXISTS `pluginData` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` VARCHAR(64) NOT NULL DEFAULT '',
  `key` VARCHAR(64) NOT NULL DEFAULT '',
  `value` TEXT,
  PRIMARY KEY (`id`),
  KEY `name` (`key`),
  KEY `group` (`group`),
  UNIQUE `group_2` (`group`, `key`)
) ENGINE=InnoDB;

