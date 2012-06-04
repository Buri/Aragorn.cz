-- MySQL dump 10.13  Distrib 5.5.24, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: aragorn_cz
-- ------------------------------------------------------
-- Server version	5.5.24-1~dotdeb.1

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

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `aragorn_cz` /*!40100 DEFAULT CHARACTER SET utf8 */;

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
-- Table structure for table `chatrooms`
--

DROP TABLE IF EXISTS `chatrooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chatrooms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT 'Nová místnost',
  `type` enum('public','game') CHARACTER SET latin1 NOT NULL DEFAULT 'public',
  `description` text CHARACTER SET latin1,
  `max` int(11) NOT NULL DEFAULT '0',
  `password` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `creator` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatrooms`
--

LOCK TABLES `chatrooms` WRITE;
/*!40000 ALTER TABLE `chatrooms` DISABLE KEYS */;
INSERT INTO `chatrooms` VALUES (1,'Hospoda','','Hospoda prece musi byt',0,NULL,0),(2,'Dalsi mistnost','','Heslo: test, max 1 clovek',1,'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3',2),(3,'Elysium','game','Whatever you think?',0,NULL,0);
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
  PRIMARY KEY (`id`),
  KEY `fk_forum_posts_users1` (`author`),
  KEY `fk_forum_posts_forum_topic1` (`forum`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_posts`
--

LOCK TABLES `forum_posts` WRITE;
/*!40000 ALTER TABLE `forum_posts` DISABLE KEYS */;
INSERT INTO `forum_posts` VALUES (1,1,4,1338292434),(2,6,4,1338292516),(3,1,4,1338475010),(4,1,4,1338478025),(5,1,4,1338566129),(6,6,4,1338567032),(7,6,4,1338567117),(8,1,4,1338567286),(9,6,4,1338567458),(10,6,4,1338567550),(11,1,4,1338567638),(12,1,4,1338567840),(13,1,4,1338575024);
/*!40000 ALTER TABLE `forum_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_posts_data`
--

DROP TABLE IF EXISTS `forum_posts_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_posts_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `post` text CHARACTER SET latin1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_posts_data`
--

LOCK TABLES `forum_posts_data` WRITE;
/*!40000 ALTER TABLE `forum_posts_data` DISABLE KEYS */;
INSERT INTO `forum_posts_data` VALUES (1,'Test propagace.'),(2,'Tak jÃ¡ si taky nÄ›co pÅ™idÃ¡m.'),(3,'[cite=msg2]Tak jÃ¡ si taky nÄ›co pÅ™idÃ¡m.[/cite]\r\n[cite=msg2]Tak jÃ¡ si taky nÄ›co pÅ™idÃ¡m.[/cite]\r\n[cite=msg1]Test propagace.[/cite]\r\n[cite=msg1]Test propagace.[/cite]\r\n[cite=msg1]Test propagace.[/cite]\r\n[cite=msg1]Test propagace.[/cite]\r\n[cite=msg1]Test propagace.[/cite]\r\n[cite=msg2]Tak jÃ¡ si taky nÄ›co pÅ™idÃ¡m.[/cite]\r\n'),(4,'[cite=msg3]Buri - 31.05.2012 16:36[/cite]\r\n'),(5,'DalÅ¡Ã­ zprÃ¡va?'),(6,'hoja?'),(7,'test'),(8,'Fuck system.'),(9,'fuck int'),(10,'hoja'),(11,'fuck it all'),(12,'uÅ¾?'),(13,'[cite=msg1]Buri - 29.05.2012 13:53[/cite]\r\n[spoiler]adadasdsa[/spoiler]');
/*!40000 ALTER TABLE `forum_posts_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_survey`
--

DROP TABLE IF EXISTS `forum_survey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_survey` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `expires` int(11) DEFAULT NULL,
  `owner` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_survey`
--

LOCK TABLES `forum_survey` WRITE;
/*!40000 ALTER TABLE `forum_survey` DISABLE KEYS */;
/*!40000 ALTER TABLE `forum_survey` ENABLE KEYS */;
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
  `parent` int(10) NOT NULL,
  `urlfragment` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `options` int(10) unsigned NOT NULL DEFAULT '3',
  `created` int(10) unsigned NOT NULL,
  `sticky` tinyint(4) DEFAULT '0',
  `noticeboard` text,
  `postcount` int(10) unsigned DEFAULT '0',
  `lastpost` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `uqname` (`name`,`urlfragment`),
  KEY `adress` (`urlfragment`(64))
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_topic`
--

LOCK TABLES `forum_topic` WRITE;
/*!40000 ALTER TABLE `forum_topic` DISABLE KEYS */;
INSERT INTO `forum_topic` VALUES (1,'Aragorn.cz',0,'Vse tykajici se serveru',0,'server',1,1325522043,1,'Info o serveru.',13,13),(2,'Herna',0,'DRD...',0,'herna',3,1325522043,0,NULL,0,0),(3,'Larpy a jine aktivity',0,'a vse kolem',0,'larp',3,1325522043,0,NULL,0,0),(4,'Bug',0,'Hlaseni chyb',1,'server-bug',3,1325522043,1,NULL,13,13),(11,'NÃ¡pady',1,'NovÃ© forum',1,'napady',3,1326129929,0,NULL,0,0),(12,'Archiv',1,'NovÃ© forum',0,'archiv',11,1326130876,-1,NULL,0,0),(13,'Fantasy, sci-fi a gotika',1,'NovÃ© forum',0,'fantasy-sci-fi-a-gotika',3,1326142999,0,'NÃ¡stÄ›nka',0,0),(14,'Pokec',1,'NovÃ© forum',0,'pokec',3,1326143034,0,NULL,0,0),(15,'HlavnÃ­ fÃ³rum',1,'HlavnÃ­ forum',0,'hlavni-forum',3,1326143041,2,'Pravidlo tÅ™Ã­ T: Topik tu TnenÃ­',0,0),(16,'To-do list',1,'NovÃ© forum',1,'to-do-list',3,1326151690,0,NULL,0,0),(33,'Galerie',0,'Diskuze ke galerce',1,'galerie',1,0,1,NULL,0,NULL);
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
INSERT INTO `forum_visit` VALUES (15,1,1338565594,0,0),(4,1,1338575025,0,0),(1,1,1338567638,9,0),(4,6,1338567927,1,0),(11,1,1338292974,0,0),(16,1,1338292991,0,0),(33,1,1338385252,0,0);
/*!40000 ALTER TABLE `forum_visit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gallery`
--

DROP TABLE IF EXISTS `gallery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gallery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `file` varchar(100) DEFAULT NULL,
  `state` int(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gallery`
--

LOCK TABLES `gallery` WRITE;
/*!40000 ALTER TABLE `gallery` DISABLE KEYS */;
INSERT INTO `gallery` VALUES (1,1,1338391921,'Test','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque quam neque, tristique viverra accumsan varius, euismod ut arcu. In hac habitasse platea dictumst. Ut mollis feugiat lorem luctus aliquam. Aenean ut egestas eros. Cras venenatis hendrerit malesuada. Nam blandit ullamcorper metus vestibulum aliquam. In condimentum ornare dolor. Integer et est eu ipsum blandit porta at ut velit. Mauris sem ligula, fermentum in auctor quis, cursus sit amet orci. In pretium arcu vitae elit rutrum non porta eros rutrum. Aliquam in tellus eros, ut sollicitudin elit. Phasellus commodo tincidunt sem, in lobortis elit vestibulum ut. Integer eget libero diam, accumsan euismod nunc. Praesent nec varius nulla. Nam et est enim, et scelerisque felis. Vestibulum et sapien nibh, sed cursus nibh. ','h2ujwy87_fotografie1225.jpg',3);
/*!40000 ALTER TABLE `gallery` ENABLE KEYS */;
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
  `desc` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'guest',-1,NULL),(2,'member',-1,NULL),(3,'moderator',2,NULL),(4,'admin',3,NULL),(0,'root',-1,NULL);
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
-- Table structure for table `napadovnik_komentare`
--

DROP TABLE IF EXISTS `napadovnik_komentare`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `napadovnik_komentare` (
  `idnapadovnik_komentare` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `napad` int(11) DEFAULT NULL,
  `autor` int(11) DEFAULT NULL,
  `cas` int(11) DEFAULT NULL,
  `komentar` text,
  PRIMARY KEY (`idnapadovnik_komentare`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `napadovnik_komentare`
--

LOCK TABLES `napadovnik_komentare` WRITE;
/*!40000 ALTER TABLE `napadovnik_komentare` DISABLE KEYS */;
/*!40000 ALTER TABLE `napadovnik_komentare` ENABLE KEYS */;
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
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registration`
--

LOCK TABLES `registration` WRITE;
/*!40000 ALTER TABLE `registration` DISABLE KEYS */;
INSERT INTO `registration` VALUES (11,'fucker','19b58543c85b97c5498edfd89c11c3aa8cb5fe51',1338125479,'2859bfa56f5e3f3a53a93909437fa778','test@local.cz'),(12,'sumo','1a67b518e5aba50c80c422dcf7c778fb50de87ac',1338128741,'dc5d04653485600aec35d749a4833fb9','sumo@local.cz'),(13,'looser','fece317c38ca91d735f3232d5f57f1118f46a749',1338130292,'c8319ea4cbaf74abbf229172835b983c','bfu@ser.sd');
/*!40000 ALTER TABLE `registration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `table_sizes`
--

DROP TABLE IF EXISTS `table_sizes`;
/*!50001 DROP VIEW IF EXISTS `table_sizes`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `table_sizes` (
  `Table Name` varchar(64),
  `Quant of Rows` bigint(21) unsigned,
  `Total Size Kb` decimal(25,2)
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

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
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Buri',0),(2,'test',2),(3,'imhotep',2),(4,'Darwin',1),(5,'Chiisai',1),(0,'System',0),(6,'antitalent',2),(7,'dalÅ¡Ã­',2);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_preferences`
--

DROP TABLE IF EXISTS `users_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_preferences` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `preference` text CHARACTER SET latin1,
  `widgets` text CHARACTER SET latin1,
  `chatcolor` varchar(45) CHARACTER SET latin1 DEFAULT 'white',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_preferences`
--

LOCK TABLES `users_preferences` WRITE;
/*!40000 ALTER TABLE `users_preferences` DISABLE KEYS */;
INSERT INTO `users_preferences` VALUES (1,'','[\"sample\",\"news\",\"help\"]','#ffc000'),(2,'{\"chat\":{\"color\":\"purple\"}}','[]',NULL),(6,'{\'chat\':{\'color\':\'#0000ff\'}}',NULL,NULL);
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
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_profiles`
--

LOCK TABLES `users_profiles` WRITE;
/*!40000 ALTER TABLE `users_profiles` DISABLE KEYS */;
INSERT INTO `users_profiles` VALUES (1,'4ff88aaddbd209d8026924c2cc2836b408698823','buri.buster@gmail.com',1304864844,01338550710,'1_4fbcedde6964d.png','A4 under construction.',15,'buri'),(2,'86f7e437faa5a7fce15d1ddcb9eaeaea377667b8','test@aragorn.cz',1305046888,01338215072,'2_4fc38b008d9ec.png','Jsem tester!',15,'tester'),(3,'3c33150764403d4be7e7b49dcb9c348b37174f85','test3@aragorn.cz',1309265408,00000000000,'default.png',NULL,15,'imhotep'),(4,'8cb2237d0679ca88db6464eac60da96345513964','darw@centrum.cz',1310236356,00000000000,'default.png',NULL,15,'darwin'),(5,'d9c2f352e9968706b67559e010cb17156c7ff335','psrutova@noveranet.cz',1310326091,00000000000,'default.png','Miau',15,'chiisai'),(0,NULL,'system@aragorn.cz',1,00000000001,'system.png','Já vás vidím.',0,'system'),(6,'3c33150764403d4be7e7b49dcb9c348b37174f85','test@somewhere.domain',1326140875,01338565845,'default.png','Jak se mÃ¡m',15,'antitalent'),(7,'a2cb5f7bb85df88bafe16acc20224e2a181e7fa4','dalsi@idiot.com',1338130378,00000000000,'default.png',NULL,15,'dalsi');
/*!40000 ALTER TABLE `users_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `widgets`
--

DROP TABLE IF EXISTS `widgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `widgets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `location` varchar(45) DEFAULT NULL,
  `state` tinyint(4) DEFAULT '0',
  `rating` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `widgets`
--

LOCK TABLES `widgets` WRITE;
/*!40000 ALTER TABLE `widgets` DISABLE KEYS */;
INSERT INTO `widgets` VALUES (1,'news',2,NULL),(2,'sample',2,NULL),(3,'help',2,NULL);
/*!40000 ALTER TABLE `widgets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `widgets_reviews`
--

DROP TABLE IF EXISTS `widgets_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `widgets_reviews` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `widget` int(10) unsigned DEFAULT NULL,
  `user` int(10) unsigned DEFAULT NULL,
  `rating` int(10) unsigned DEFAULT NULL,
  `review` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `widgets_reviews`
--

LOCK TABLES `widgets_reviews` WRITE;
/*!40000 ALTER TABLE `widgets_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `widgets_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Current Database: `aragorn_cz`
--

USE `aragorn_cz`;

--
-- Final view structure for view `table_sizes`
--

/*!50001 DROP TABLE IF EXISTS `table_sizes`*/;
/*!50001 DROP VIEW IF EXISTS `table_sizes`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `table_sizes` AS select `information_schema`.`TABLES`.`TABLE_NAME` AS `Table Name`,`information_schema`.`TABLES`.`TABLE_ROWS` AS `Quant of Rows`,round(((`information_schema`.`TABLES`.`DATA_LENGTH` + `information_schema`.`TABLES`.`INDEX_LENGTH`) / 1024),2) AS `Total Size Kb` from `information_schema`.`TABLES` where (`information_schema`.`TABLES`.`TABLE_SCHEMA` = 'aragorn_cz') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-06-02 15:22:12
