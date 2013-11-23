--
-- Table structure for table `expense2_demo`
--

CREATE TABLE IF NOT EXISTS `expense2_demo` (
  `id` bigint(2) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `cost` float NOT NULL DEFAULT '0',
  `type` varchar(4) NOT NULL DEFAULT '',
  `prod` varchar(255) NOT NULL DEFAULT '',
  `other` float DEFAULT NULL,
  `currency` char(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `date` (`date`),
  FULLTEXT KEY `prod` (`prod`)
) ENGINE=MyISAM;
