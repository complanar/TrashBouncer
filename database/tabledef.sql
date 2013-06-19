-- 
-- Table `trashbouncer_categories`
-- 

CREATE TABLE  `trashbouncer_categories` (
  `ham` int(10) unsigned NOT NULL DEFAULT '0',
  `spam` int(10) unsigned NOT NULL DEFAULT '0',
  `lang` char(2) NOT NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `trashbouncer_log`
-- 

CREATE TABLE  `trashbouncer_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `info` varchar(30) DEFAULT NULL,
  `text` text NOT NULL DEFAULT NULL,
  `cat` tinyint(4) NOT NULL DEFAULT NULL,
  `lang` char(2) NOT NULL DEFAULT NULL,
  `ip` varchar(64) NOT NULL DEFAULT NULL,
  `tstamp` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY  `cat`  (`cat`),
  KEY  `lang`  (`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `trashbouncer_tokens`
-- 

CREATE TABLE  `trashbouncer_tokens` (
  `token` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `lang` char(2) NOT NULL DEFAULT NULL,
  `ham` int(10) unsigned NOT NULL DEFAULT NULL,
  `spam` int(10) unsigned NOT NULL DEFAULT NULL,
  PRIMARY KEY  (`token`),
  KEY  `lang`  (`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `trashbouncer_specialtokens`
-- 

CREATE TABLE `tl_trashbouncer_specialtokens` (
  `token` varchar(60) NOT NULL default '',
  `type` char(1) NOT NULL default '',
  `lang` char(2) NOT NULL default '',
  KEY `token` (`token`),
  KEY `type` (`type`),
  KEY `lang` (`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
