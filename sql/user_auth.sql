--
-- Table structure for table `user_auth`
--

DROP TABLE IF EXISTS `user_auth`;
CREATE TABLE `user_auth` (
  `id` bigint(20) NOT NULL auto_increment,
  `user_id` bigint(20) NOT NULL default '0',
  `username` varchar(64) NOT NULL default '',
  `password` varchar(40) NOT NULL default '',
  `valid_from` date default NULL,
  `valid_to` date default NULL,
  `valid` set('n','y') NOT NULL default 'n',
  `level` int(4) NOT NULL default '20',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `credentials` (`username`,`password`),
  KEY `id` (`user_id`),
  KEY `level` (`level`),
  KEY `valid_from` (`valid_from`,`valid_to`)
) ENGINE=MyISAM AUTO_INCREMENT=891 DEFAULT CHARSET=latin1;

--
-- Data for table `user_auth`
--

INSERT INTO `user_auth` VALUES (9,9,'demo',SHA1('demo'),'2000-01-01 00:00:00',NULL,'y',20);

