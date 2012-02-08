-- MySQL dump 10.13  Distrib 5.1.58, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: aragorn_cz
-- ------------------------------------------------------
-- Server version	5.1.58-1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `aragorn_cz`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `aragorn_cz` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_czech_ci */;

USE `aragorn_cz`;

--
-- Table structure for table `bank_transfer`
--

DROP TABLE IF EXISTS `bank_transfer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bank_transfer` (
  `from` int(11) NOT NULL,
  `to` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `bank_transfercol` int(11) NOT NULL,
  `comment` text COLLATE utf8_czech_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_transfer`
--

LOCK TABLES `bank_transfer` WRITE;
/*!40000 ALTER TABLE `bank_transfer` DISABLE KEYS */;
/*!40000 ALTER TABLE `bank_transfer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bans`
--

DROP TABLE IF EXISTS `bans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL,
  `author` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `expires` int(10) unsigned NOT NULL,
  `ip` varchar(255) CHARACTER SET latin1 NOT NULL,
  `reason` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bans`
--

LOCK TABLES `bans` WRITE;
/*!40000 ALTER TABLE `bans` DISABLE KEYS */;
INSERT INTO `bans` VALUES (1,3,2,429496725,4294967295,'192.168.56.2','Test');
/*!40000 ALTER TABLE `bans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar`
--

DROP TABLE IF EXISTS `calendar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `owner` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `begin` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `description` text COLLATE utf8_czech_ci,
  `public` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar`
--

LOCK TABLES `calendar` WRITE;
/*!40000 ALTER TABLE `calendar` DISABLE KEYS */;
/*!40000 ALTER TABLE `calendar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_attendant`
--

DROP TABLE IF EXISTS `calendar_attendant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_attendant` (
  `id` int(11) NOT NULL,
  `iduser` int(11) DEFAULT NULL,
  `idaction` int(11) DEFAULT NULL,
  `rsvp` int(11) DEFAULT NULL,
  `message` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_attendant`
--

LOCK TABLES `calendar_attendant` WRITE;
/*!40000 ALTER TABLE `calendar_attendant` DISABLE KEYS */;
/*!40000 ALTER TABLE `calendar_attendant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chatroom_occupants`
--

DROP TABLE IF EXISTS `chatroom_occupants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chatroom_occupants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idroom` int(10) unsigned DEFAULT NULL,
  `idusers` int(11) unsigned DEFAULT NULL,
  `activity` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MEMORY AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatroom_occupants`
--

LOCK TABLES `chatroom_occupants` WRITE;
/*!40000 ALTER TABLE `chatroom_occupants` DISABLE KEYS */;
/*!40000 ALTER TABLE `chatroom_occupants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chatrooms`
--

DROP TABLE IF EXISTS `chatrooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chatrooms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT 'Nova mistnost',
  `type` enum('public','game') NOT NULL DEFAULT 'public',
  `description` text,
  `max` int(11) NOT NULL DEFAULT '0',
  `password` varchar(255) DEFAULT NULL,
  `creator` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatrooms`
--

LOCK TABLES `chatrooms` WRITE;
/*!40000 ALTER TABLE `chatrooms` DISABLE KEYS */;
INSERT INTO `chatrooms` VALUES (1,'Hospoda','','Hospoda prece musi byt',0,NULL,2),(2,'Dalsi mistnost','','Heslo: test, max 1 clovek',1,'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3',2);
/*!40000 ALTER TABLE `chatrooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chronicles`
--

DROP TABLE IF EXISTS `chronicles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chronicles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `owner` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `date_created` int(11) DEFAULT NULL,
  `description` text,
  `board` text,
  `status` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_chronicles_users1` (`owner`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chronicles`
--

LOCK TABLES `chronicles` WRITE;
/*!40000 ALTER TABLE `chronicles` DISABLE KEYS */;
/*!40000 ALTER TABLE `chronicles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_moderator`
--

DROP TABLE IF EXISTS `forum_moderator`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_moderator` (
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `forumid` int(10) unsigned NOT NULL,
  `pk` int(11) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`pk`),
  UNIQUE KEY `pk_UNIQUE` (`pk`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_moderator`
--

LOCK TABLES `forum_moderator` WRITE;
/*!40000 ALTER TABLE `forum_moderator` DISABLE KEYS */;
INSERT INTO `forum_moderator` VALUES (6,15,1),(4,15,2);
/*!40000 ALTER TABLE `forum_moderator` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_posts`
--

DROP TABLE IF EXISTS `forum_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author` int(10) unsigned NOT NULL,
  `forum` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `post` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idforum_posts_UNIQUE` (`id`),
  KEY `fk_forum_posts_users1` (`author`),
  KEY `fk_forum_posts_forum_topic1` (`forum`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_posts`
--

LOCK TABLES `forum_posts` WRITE;
/*!40000 ALTER TABLE `forum_posts` DISABLE KEYS */;
INSERT INTO `forum_posts` VALUES (1,1,2,1325684139,'Hola?'),(2,2,2,1325703108,'NÄ›jakÃ¡ dalÅ¡Ã­ zprÃ¡va?'),(3,4,2,1325703115,'NÄ›jakÃ¡ dalÅ¡Ã­ zprÃ¡va?'),(4,5,2,1325707324,'NÄ›co by to chtÄ›lo...'),(5,1,2,1325876348,'[b][/b]'),(6,1,2,1325945095,'[cite=msg5][b][/b][/cite]\n'),(7,1,4,1326046298,'Diskuze'),(8,1,13,1326148091,'TestovacÃ­ zprÃ¡va'),(16,1,16,1326151714,'ZabezpeÄenÃ­ mazÃ¡nÃ­ pÅ™Ã­spÄ›vkÅ¯'),(17,1,16,1326151736,'Editace pÅ™Ã­spÄ›vkÅ¯\nReportovÃ¡nÃ­ pÅ™Ã­spÄ›vkÅ¯'),(19,1,15,1326287547,'Test?'),(31,6,15,1328709627,'Hola?'),(21,1,15,1326292411,'NÄ›jakÃ¡ odpovÄ›Ä.');
/*!40000 ALTER TABLE `forum_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_topic`
--

DROP TABLE IF EXISTS `forum_topic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_topic` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET latin1 DEFAULT NULL,
  `owner` int(11) unsigned NOT NULL,
  `description` text CHARACTER SET latin1,
  `parent` int(10) unsigned zerofill NOT NULL,
  `urlfragment` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `options` int(10) unsigned NOT NULL DEFAULT '3',
  `created` int(10) unsigned NOT NULL,
  `sticky` tinyint(4) DEFAULT '0',
  `noticeboard` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `uqname` (`name`,`urlfragment`),
  KEY `adress` (`urlfragment`(64))
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_topic`
--

LOCK TABLES `forum_topic` WRITE;
/*!40000 ALTER TABLE `forum_topic` DISABLE KEYS */;
INSERT INTO `forum_topic` VALUES (1,'Aragorn.cz',0,'Vse tykajici se serveru',0000000000,'server',1,1325522043,1,'Info o serveru.'),(2,'Herna',0,'DRD...',0000000000,'herna',3,1325522043,0,NULL),(3,'Larpy a jine aktivity',0,'a vse kolem',0000000000,'larp',3,1325522043,0,NULL),(4,'Bug',0,'Hlaseni chyb',0000000001,'server-bug',3,1325522043,1,NULL),(11,'NÃ¡pady',1,'NovÃ© forum',0000000001,'napady',3,1326129929,0,NULL),(12,'Archiv',1,'NovÃ© forum',0000000000,'archiv',3,1326130876,-1,NULL),(13,'Fantasy, sci-fi a gotika',1,'NovÃ© forum',0000000000,'fantasy-sci-fi-a-gotika',3,1326142999,0,NULL),(14,'Pokec',1,'NovÃ© forum',0000000000,'pokec',3,1326143034,0,NULL),(15,'HlavnÃ­ fÃ³rum',1,'NovÃ© forum',0000000000,'hlavni-forum',3,1326143041,2,'Pravidlo tri T: Topic Tu Tneni'),(16,'To-do list',1,'NovÃ© forum',0000000001,'to-do-list',3,1326151690,0,NULL);
/*!40000 ALTER TABLE `forum_topic` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_visit`
--

DROP TABLE IF EXISTS `forum_visit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_visit` (
  `idforum` int(10) unsigned NOT NULL,
  `iduser` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `unread` int(11) DEFAULT '0',
  `bookmark` int(1) DEFAULT '0',
  UNIQUE KEY `uniqcomb` (`idforum`,`iduser`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_visit`
--

LOCK TABLES `forum_visit` WRITE;
/*!40000 ALTER TABLE `forum_visit` DISABLE KEYS */;
INSERT INTO `forum_visit` VALUES (15,1,1328709682,0,0),(15,2,1326305796,2,0),(13,2,1326307797,0,0),(13,1,1328720019,0,0),(17,1,1326306977,1,0),(20,1,1326307267,0,0),(17,2,1326307221,0,0),(12,2,1326307794,0,0),(1,2,1326307815,0,0),(16,2,1326307806,0,0),(11,2,1326307810,0,0),(4,2,1326307814,0,0),(12,1,1328705236,0,0),(2,1,1328705258,0,0),(1,1,1328718642,0,0),(4,1,1328705205,0,0),(14,1,1328705242,0,0),(26,1,1328705267,0,0),(16,1,1328705307,0,0),(15,6,1328713742,0,0),(1,6,1328710008,0,0),(4,6,1328709880,0,0),(11,6,1328709999,0,0),(13,6,1328710078,0,0);
/*!40000 ALTER TABLE `forum_visit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'guest',-1),(2,'member',-1),(3,'moderator',2),(4,'admin',3),(0,'root',-1);
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mail`
--

DROP TABLE IF EXISTS `mail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from` int(11) DEFAULT NULL,
  `to` int(11) DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_mail_users1` (`from`),
  KEY `fk_mail_users2` (`to`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mail`
--

LOCK TABLES `mail` WRITE;
/*!40000 ALTER TABLE `mail` DISABLE KEYS */;
/*!40000 ALTER TABLE `mail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mail_data`
--

DROP TABLE IF EXISTS `mail_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `data` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mail_data`
--

LOCK TABLES `mail_data` WRITE;
/*!40000 ALTER TABLE `mail_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `mail_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resource` varchar(255) DEFAULT NULL,
  `operation` varchar(255) DEFAULT NULL,
  `target` int(11) DEFAULT NULL,
  `type` enum('user','group') DEFAULT 'user',
  `value` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'test',NULL,1,'group',1),(2,'test','write',0,'user',0),(3,'admin',NULL,4,'group',1),(4,'chat',NULL,4,'group',1),(5,'discussion',NULL,2,'group',1);
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registration`
--

DROP TABLE IF EXISTS `registration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registration` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `token` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `mail` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idregistration_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registration`
--

LOCK TABLES `registration` WRITE;
/*!40000 ALTER TABLE `registration` DISABLE KEYS */;
/*!40000 ALTER TABLE `registration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `groupid` smallint(5) unsigned NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idusers_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Buri',0),(2,'test',2),(3,'imhotep',2),(4,'Darwin',1),(5,'Chiisai',1),(0,'System',0),(6,'antitalent',2);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_activity`
--

DROP TABLE IF EXISTS `users_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_activity` (
  `id` int(11) NOT NULL,
  `activity` int(11) DEFAULT NULL,
  `online` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_activity`
--

LOCK TABLES `users_activity` WRITE;
/*!40000 ALTER TABLE `users_activity` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_preferences`
--

DROP TABLE IF EXISTS `users_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_preferences` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `preference` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_preferences`
--

LOCK TABLES `users_preferences` WRITE;
/*!40000 ALTER TABLE `users_preferences` DISABLE KEYS */;
INSERT INTO `users_preferences` VALUES (1,'{\"chat\":{\"color\":\"#ffc000\"},\"signature\":\"Kiss my ass\"}'),(2,'{\"chat\":{\"color\":\"purple\"}}');
/*!40000 ALTER TABLE `users_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_profiles`
--

DROP TABLE IF EXISTS `users_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_profiles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `password` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `mail` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `created` int(11) unsigned DEFAULT NULL,
  `login` int(11) unsigned zerofill DEFAULT NULL,
  `icon` varchar(70) COLLATE utf8_czech_ci DEFAULT 'default.png',
  `status` text COLLATE utf8_czech_ci,
  `bank` int(11) DEFAULT '15',
  `urlfragment` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `urlfragment` (`urlfragment`),
  KEY `mail` (`mail`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_profiles`
--

LOCK TABLES `users_profiles` WRITE;
/*!40000 ALTER TABLE `users_profiles` DISABLE KEYS */;
INSERT INTO `users_profiles` VALUES (1,'4ff88aaddbd209d8026924c2cc2836b408698823','buri.buster@gmail.com',1304864844,01328705847,'buri.jpg','A4 under construction.',15,'buri'),(2,'86f7e437faa5a7fce15d1ddcb9eaeaea377667b8','test@aragorn.cz',1305046888,01326378015,'default.png','Jsem tester!',15,'tester'),(3,'3c33150764403d4be7e7b49dcb9c348b37174f85','test3@aragorn.cz',1309265408,00000000000,'default.png',NULL,15,'imhotep'),(4,'8cb2237d0679ca88db6464eac60da96345513964','darw@centrum.cz',1310236356,00000000000,'default.png',NULL,15,'darwin'),(5,'d9c2f352e9968706b67559e010cb17156c7ff335','psrutova@noveranet.cz',1310326091,00000000000,'default.png','Miau',15,'chiisai'),(0,NULL,'system@aragorn.cz',1,00000000001,'system.png','Já vás vidím.',0,'system'),(6,'3c33150764403d4be7e7b49dcb9c348b37174f85','test@somewhere.domain',1326140875,01328709539,'default.png','Jak se mÃ¡m',15,'antitalent');
/*!40000 ALTER TABLE `users_profiles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-02-08 17:56:20
