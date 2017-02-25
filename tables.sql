CREATE TABLE `skindesignjxh2017_reginfo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` char(15) NOT NULL,
  `nickname` varchar(32) NOT NULL,
  `qq` varchar(32) NOT NULL,
  `email` varchar(64) NOT NULL,
  `telnumber` varchar(32) NOT NULL,
  `from` tinyint(1) NOT NULL DEFAULT '0',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `skindesignjxh2017_upload` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `regid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `skin_name` varchar(32) NOT NULL,
  `short_name` varchar(16) NOT NULL,
  `intro` varchar(255) NOT NULL,
  `author_name` varchar(32) NOT NULL,
  `pickey9` varchar(255) NOT NULL,
  `pickey26` varchar(255) NOT NULL,
  `digest` tinyint(1) NOT NULL DEFAULT '0',
  `checked` tinyint(1) NOT NULL DEFAULT '0',
  `hit` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `grade` tinyint(1) NOT NULL DEFAULT '0',
  `listorder` mediumint(8) NOT NULL DEFAULT '0',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `regid` (`regid`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `skindesignjxh2017_user` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `passportid` varchar(64) NOT NULL,
  `ucuid` int(10) unsigned NOT NULL DEFAULT '0',
  `nickname` varchar(32) NOT NULL,
  `gender` tinyint(1) NOT NULL DEFAULT '0',
  `regip` char(15) NOT NULL,
  `ips` varchar(64) NOT NULL,
  `salt` char(8) NOT NULL,
  `headimgurl` varchar(255) NOT NULL,
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `passportid` (`passportid`),
  KEY `ucuid` (`ucuid`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
