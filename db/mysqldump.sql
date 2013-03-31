-- MySQL dump 10.13  Distrib 5.5.30, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: aragorn_cz
-- ------------------------------------------------------
-- Server version	5.5.30-1~dotdeb.0

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
  `owner` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `begin` int(11) unsigned DEFAULT NULL,
  `end` int(11) unsigned DEFAULT NULL,
  `capacity` varchar(250) DEFAULT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `limits` varchar(255) DEFAULT NULL,
  `price` varchar(255) DEFAULT NULL,
  `public` tinyint(4) NOT NULL,
  `repeating` char(1) CHARACTER SET utf8 COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='repeat -  Daily, Weakly, Monthly, Yearly';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar`
--

LOCK TABLES `calendar` WRITE;
/*!40000 ALTER TABLE `calendar` DISABLE KEYS */;
INSERT INTO `calendar` VALUES (1,1,'Sraz v Äajce',1339081200,2339099200,'','Rychlosraz pro pÃ¡r odvÃ¡Å¾livcÅ¯, kteÅ™Ã­ se v nedÄ›li nudÃ­?\r\n\r\nKde: Shangri-la\r\nKdy: NedÄ›le, 10. 6. od 14 nebo od 16h (hlasujte)\r\nS kÃ½m: viz invite list','Praha, I.P. Pavlova','','',1,''),(2,1,'Pivnice',1363129200,1371326400,'','NÄ›co by to chtÄ›lo. Bla bla bla','','','',1,'');
/*!40000 ALTER TABLE `calendar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_attendant`
--

DROP TABLE IF EXISTS `calendar_attendant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar_attendant` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `iduser` int(11) unsigned NOT NULL,
  `idaction` int(11) unsigned NOT NULL,
  `rsvp` char(1) COLLATE utf8_czech_ci NOT NULL DEFAULT 'M',
  `message` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `moderator` bit(1) DEFAULT b'0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq` (`iduser`,`idaction`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_attendant`
--

LOCK TABLES `calendar_attendant` WRITE;
/*!40000 ALTER TABLE `calendar_attendant` DISABLE KEYS */;
INSERT INTO `calendar_attendant` VALUES (1,1,1,'y',NULL,'\0');
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
) ENGINE=MyISAM AUTO_INCREMENT=67 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_posts`
--

LOCK TABLES `forum_posts` WRITE;
/*!40000 ALTER TABLE `forum_posts` DISABLE KEYS */;
INSERT INTO `forum_posts` VALUES (5,1,15,1342204067),(2,6,13,1342198993),(6,1,15,1342204080),(39,1,15,1342540646),(35,1,15,1342538840),(37,6,15,1342539757),(36,1,15,1342539472),(38,1,15,1342539828),(40,1,15,1342540654),(41,1,15,1342540661),(42,1,15,1342540669),(43,1,15,1342540685),(44,1,15,1342540693),(45,1,15,1342540702),(46,1,15,1342540711),(47,1,15,1342541171),(48,1,15,1342541195),(49,1,15,1342541253),(50,1,15,1342542694),(51,1,15,1342542832),(52,1,15,1342542850),(53,1,15,1342542873),(54,1,15,1342543082),(55,1,15,1342543099),(56,1,15,1342543457),(57,1,15,1342543710),(58,1,15,1342543745),(59,1,15,1342546069),(60,1,14,1342612369),(62,1,15,1343407995),(64,1,15,1362498500),(66,1,35,1362498726);
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
  PRIMARY KEY (`id`),
  FULLTEXT KEY `full` (`post`)
) ENGINE=MyISAM AUTO_INCREMENT=67 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_posts_data`
--

LOCK TABLES `forum_posts_data` WRITE;
/*!40000 ALTER TABLE `forum_posts_data` DISABLE KEYS */;
INSERT INTO `forum_posts_data` VALUES (6,'[cite=msg5]Buri - 13.07.2012 20:27[/cite]\r\nZajÃ­mavost.'),(2,'[cite=msg1]Buri - 13.07.2012 19:02[/cite]\r\n'),(5,'NÄ›jakÃ½ ÃºÅ¾asnÃ½ pÅ™Ã­spÄ›vÄ›k.'),(40,'dSADASD'),(35,'NovÃ¡ zprÃ¡va'),(36,'Hehm'),(37,'fuck my life'),(38,'dasdddsa'),(39,'dasdS'),(41,'dadsad'),(42,'fdsklfjsda'),(43,'sdjfla hdls \r\n'),(44,'fsdfdsaf'),(45,'dasdaSDAd'),(46,'cdsdyfs a fsf ads'),(47,'test'),(48,'test'),(49,'test'),(50,'dsadad'),(51,'dsadsad'),(52,'dsadasda'),(53,'dasdasdad'),(54,'Hehe'),(55,'AjaxovÃ© odesÃ­lÃ¡nÃ­ pÅ™Ã­spÄ›vkÅ¯. :D'),(56,'Hehe'),(57,'dafuq?'),(58,'NÄ›hnÄ›.'),(59,'[cite=msg58]Buri - 17.07.2012 18:49[/cite]\n'),(60,'test'),(62,'[mp3=http://vampy.aragorn.cz/i/Skylar_Grey_-_Coming_Home.mp3][/mp3]'),(64,'Hola hej.'),(66,'Kaboom chaa');
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
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `uqname` (`name`,`urlfragment`),
  KEY `adress` (`urlfragment`(64))
) ENGINE=MyISAM AUTO_INCREMENT=58 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_topic`
--

LOCK TABLES `forum_topic` WRITE;
/*!40000 ALTER TABLE `forum_topic` DISABLE KEYS */;
INSERT INTO `forum_topic` VALUES (1,'Aragorn.cz',0,'Vse tykajici se serveru',0,'server',1,1325522043,1,'Info o serveru.',0,NULL,28),(2,'Herna',0,'DRD...',0,'herna',11,1325522043,0,NULL,0,NULL,41),(3,'Larpy a jine aktivity',0,'a vse kolem',0,'larp',3,1325522043,0,NULL,1,NULL,52),(4,'Bug',0,'Hlaseni chyb',1,'server-bug',3,1325522043,1,NULL,0,NULL,3),(11,'NÃ¡pady',1,'NovÃ© forum',1,'napady',3,1326129929,0,NULL,0,NULL,1),(12,'Archiv',1,'NovÃ© forum',0,'archiv',11,1326130876,-1,NULL,0,NULL,11),(13,'Fantasy, sci-fi a gotika',1,'NovÃ© forum',0,'fantasy-sci-fi-a-gotika',3,1326142999,0,'NÃ¡stÄ›nka',2,2,8),(14,'Pokec',1,'NovÃ© forum',0,'pokec',3,1326143034,0,NULL,14,60,47),(15,'HlavnÃ­ fÃ³rum',1,'HlavnÃ­ forum',0,'hlavni-forum',3,1326143041,2,'Pravidlo tÅ™Ã­ T: Topic Tu TnenÃ­.',33,5,405),(16,'To-do list',1,'NovÃ© forum',1,'to-do-list',3,1326151690,0,NULL,0,NULL,0),(33,'Galerie',0,'Diskuze ke galerce',1,'galerie',1,0,1,NULL,0,NULL,0),(35,'Sraz v Äajce',1,'Diskuze k akci Sraz v Äajce',-1,'calendar-forum-1',3,1339078624,0,NULL,7,66,53),(57,'Pivnice',1,'Diskuze k akci Pivnice',-1,'calendar-forum-2',3,1362499211,0,NULL,0,NULL,2),(54,'Sub',1,'NovÃ© forum',2,'herna:sub',3,1342298548,0,NULL,0,NULL,9);
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
INSERT INTO `forum_visit` VALUES (15,1,1363360175,0,0),(0,1,1363346942,0,0),(13,1,1362499813,0,0),(3,1,1343409663,0,0),(14,1,1343407879,0,0),(2,1,1343409638,0,0),(54,1,1342612395,0,0),(12,1,1342618228,0,0),(1,1,1342617972,0,0),(4,1,1342614112,0,0),(35,1,1362499064,0,0),(57,1,1362499280,0,0);
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
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `uniq` (`resource`,`operation`,`target`,`type`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'test',NULL,1,'group',1),(2,'test','write',0,'user',0),(3,'admin',NULL,4,'group',1),(4,'chat',NULL,4,'group',1),(5,'discussion',NULL,2,'group',1),(6,'forum-thread-14',NULL,2,'group',1),(7,'forum-thread-14','admin',2,'group',0);
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
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registration`
--

LOCK TABLES `registration` WRITE;
/*!40000 ALTER TABLE `registration` DISABLE KEYS */;
INSERT INTO `registration` VALUES (11,'fucker','19b58543c85b97c5498edfd89c11c3aa8cb5fe51',1338125479,'2859bfa56f5e3f3a53a93909437fa778','test@local.cz'),(12,'sumo','1a67b518e5aba50c80c422dcf7c778fb50de87ac',1338128741,'dc5d04653485600aec35d749a4833fb9','sumo@local.cz'),(13,'looser','fece317c38ca91d735f3232d5f57f1118f46a749',1338130292,'c8319ea4cbaf74abbf229172835b983c','bfu@ser.sd'),(16,'dsad','06f1b516cb5c18b2bca0729bc524adf218a08cee',1338819379,'e2f6c71f41466ea4ce101ed38107e62c','sdfadf@fasdf.ca'),(17,'hovado','57dce859aaa57b101874d1a3774e07641617475e',1342352450,'ecd9c78aeefe39a3f5447bb172a9aecb','kokot@aragorn.cz'),(18,'hovado','8c829ee6a1ac6ffdbcf8bc0ad72b73795fff34e8',1342352491,'fa7365ff6f586166dc68c31e9ba8607f','kokot@aragorn.cz');
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
  `Table Name` tinyint NOT NULL,
  `Quant of Rows` tinyint NOT NULL,
  `Total Size Kb` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL DEFAULT '0',
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_czech_ci DEFAULT NULL,
  `groupid` smallint(5) unsigned NOT NULL DEFAULT '2',
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_czech_ci DEFAULT NULL,
  `mail` varchar(255) CHARACTER SET utf8 COLLATE utf8_czech_ci DEFAULT NULL,
  `created` int(11) unsigned DEFAULT NULL,
  `login` int(11) unsigned DEFAULT NULL,
  `icon` varchar(70) CHARACTER SET utf8 COLLATE utf8_czech_ci DEFAULT 'default.png',
  `status` text CHARACTER SET utf8 COLLATE utf8_czech_ci,
  `bank` int(11) DEFAULT '15',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_czech_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Buri',0,'4ff88aaddbd209d8026924c2cc2836b408698823','buri.buster@gmail.com',1304864844,1363360175,'1_50009f2e3e20d.png','A4 under construction.',15,'buri'),(2,'test',2,'86f7e437faa5a7fce15d1ddcb9eaeaea377667b8','test@aragorn.cz',1305046888,1342530181,'2_4fc38b008d9ec.png','Jsem tester!',15,'tester'),(3,'imhotep',2,'3c33150764403d4be7e7b49dcb9c348b37174f85','test3@aragorn.cz',1309265408,0,'default.png',NULL,15,'imhotep'),(4,'Darwin',1,'8cb2237d0679ca88db6464eac60da96345513964','darw@centrum.cz',1310236356,0,'default.png',NULL,15,'darwin'),(5,'Chiisai',1,'d9c2f352e9968706b67559e010cb17156c7ff335','psrutova@noveranet.cz',1310326091,0,'default.png','Miau',15,'chiisai'),(0,'System',0,NULL,'system@aragorn.cz',1,1,'system.png','Já vás vidím.',0,'system'),(6,'antitalent',2,'3c33150764403d4be7e7b49dcb9c348b37174f85','test@somewhere.domain',1326140875,1342523908,'default.png','Jak se mÃ¡m',15,'antitalent'),(7,'dalÅ¡Ã­',2,'a2cb5f7bb85df88bafe16acc20224e2a181e7fa4','dalsi@idiot.com',1338130378,0,'default.png',NULL,15,'dalsi'),(8,'trdlo',2,'1b6004ce49ab73225720b82d36eaaa4d6e511034','dalsi@idiots.com',1338818635,0,'default.png',NULL,15,'trdlo');
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
INSERT INTO `users_preferences` VALUES (1,'','[\"news\",\"help\",\"sample\"]','#ffc000'),(2,'{\"chat\":{\"color\":\"purple\"}}','[]','#8073fa'),(6,'{\'chat\':{\'color\':\'#0000ff\'}}',NULL,NULL);
/*!40000 ALTER TABLE `users_preferences` ENABLE KEYS */;
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
-- Current Database: `openfire`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `openfire` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `openfire`;

--
-- Table structure for table `ofExtComponentConf`
--

DROP TABLE IF EXISTS `ofExtComponentConf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofExtComponentConf` (
  `subdomain` varchar(255) NOT NULL,
  `wildcard` tinyint(4) NOT NULL,
  `secret` varchar(255) DEFAULT NULL,
  `permission` varchar(10) NOT NULL,
  PRIMARY KEY (`subdomain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofExtComponentConf`
--

LOCK TABLES `ofExtComponentConf` WRITE;
/*!40000 ALTER TABLE `ofExtComponentConf` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofExtComponentConf` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofGroup`
--

DROP TABLE IF EXISTS `ofGroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofGroup` (
  `groupName` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`groupName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofGroup`
--

LOCK TABLES `ofGroup` WRITE;
/*!40000 ALTER TABLE `ofGroup` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofGroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofGroupProp`
--

DROP TABLE IF EXISTS `ofGroupProp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofGroupProp` (
  `groupName` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `propValue` text NOT NULL,
  PRIMARY KEY (`groupName`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofGroupProp`
--

LOCK TABLES `ofGroupProp` WRITE;
/*!40000 ALTER TABLE `ofGroupProp` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofGroupProp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofGroupUser`
--

DROP TABLE IF EXISTS `ofGroupUser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofGroupUser` (
  `groupName` varchar(50) NOT NULL,
  `username` varchar(100) NOT NULL,
  `administrator` tinyint(4) NOT NULL,
  PRIMARY KEY (`groupName`,`username`,`administrator`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofGroupUser`
--

LOCK TABLES `ofGroupUser` WRITE;
/*!40000 ALTER TABLE `ofGroupUser` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofGroupUser` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofID`
--

DROP TABLE IF EXISTS `ofID`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofID` (
  `idType` int(11) NOT NULL,
  `id` bigint(20) NOT NULL,
  PRIMARY KEY (`idType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofID`
--

LOCK TABLES `ofID` WRITE;
/*!40000 ALTER TABLE `ofID` DISABLE KEYS */;
INSERT INTO `ofID` VALUES (18,1),(19,1),(23,1),(25,13),(26,2);
/*!40000 ALTER TABLE `ofID` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofMucAffiliation`
--

DROP TABLE IF EXISTS `ofMucAffiliation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofMucAffiliation` (
  `roomID` bigint(20) NOT NULL,
  `jid` text NOT NULL,
  `affiliation` tinyint(4) NOT NULL,
  PRIMARY KEY (`roomID`,`jid`(70))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofMucAffiliation`
--

LOCK TABLES `ofMucAffiliation` WRITE;
/*!40000 ALTER TABLE `ofMucAffiliation` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofMucAffiliation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofMucConversationLog`
--

DROP TABLE IF EXISTS `ofMucConversationLog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofMucConversationLog` (
  `roomID` bigint(20) NOT NULL,
  `sender` text NOT NULL,
  `nickname` varchar(255) DEFAULT NULL,
  `logTime` char(15) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text,
  KEY `ofMucConversationLog_time_idx` (`logTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofMucConversationLog`
--

LOCK TABLES `ofMucConversationLog` WRITE;
/*!40000 ALTER TABLE `ofMucConversationLog` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofMucConversationLog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofMucMember`
--

DROP TABLE IF EXISTS `ofMucMember`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofMucMember` (
  `roomID` bigint(20) NOT NULL,
  `jid` text NOT NULL,
  `nickname` varchar(255) DEFAULT NULL,
  `firstName` varchar(100) DEFAULT NULL,
  `lastName` varchar(100) DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `faqentry` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`roomID`,`jid`(70))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofMucMember`
--

LOCK TABLES `ofMucMember` WRITE;
/*!40000 ALTER TABLE `ofMucMember` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofMucMember` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofMucRoom`
--

DROP TABLE IF EXISTS `ofMucRoom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofMucRoom` (
  `serviceID` bigint(20) NOT NULL,
  `roomID` bigint(20) NOT NULL,
  `creationDate` char(15) NOT NULL,
  `modificationDate` char(15) NOT NULL,
  `name` varchar(50) NOT NULL,
  `naturalName` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `lockedDate` char(15) NOT NULL,
  `emptyDate` char(15) DEFAULT NULL,
  `canChangeSubject` tinyint(4) NOT NULL,
  `maxUsers` int(11) NOT NULL,
  `publicRoom` tinyint(4) NOT NULL,
  `moderated` tinyint(4) NOT NULL,
  `membersOnly` tinyint(4) NOT NULL,
  `canInvite` tinyint(4) NOT NULL,
  `roomPassword` varchar(50) DEFAULT NULL,
  `canDiscoverJID` tinyint(4) NOT NULL,
  `logEnabled` tinyint(4) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `rolesToBroadcast` tinyint(4) NOT NULL,
  `useReservedNick` tinyint(4) NOT NULL,
  `canChangeNick` tinyint(4) NOT NULL,
  `canRegister` tinyint(4) NOT NULL,
  PRIMARY KEY (`serviceID`,`name`),
  KEY `ofMucRoom_roomid_idx` (`roomID`),
  KEY `ofMucRoom_serviceid_idx` (`serviceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofMucRoom`
--

LOCK TABLES `ofMucRoom` WRITE;
/*!40000 ALTER TABLE `ofMucRoom` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofMucRoom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofMucRoomProp`
--

DROP TABLE IF EXISTS `ofMucRoomProp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofMucRoomProp` (
  `roomID` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `propValue` text NOT NULL,
  PRIMARY KEY (`roomID`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofMucRoomProp`
--

LOCK TABLES `ofMucRoomProp` WRITE;
/*!40000 ALTER TABLE `ofMucRoomProp` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofMucRoomProp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofMucService`
--

DROP TABLE IF EXISTS `ofMucService`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofMucService` (
  `serviceID` bigint(20) NOT NULL,
  `subdomain` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `isHidden` tinyint(4) NOT NULL,
  PRIMARY KEY (`subdomain`),
  KEY `ofMucService_serviceid_idx` (`serviceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofMucService`
--

LOCK TABLES `ofMucService` WRITE;
/*!40000 ALTER TABLE `ofMucService` DISABLE KEYS */;
INSERT INTO `ofMucService` VALUES (1,'conference',NULL,0);
/*!40000 ALTER TABLE `ofMucService` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofMucServiceProp`
--

DROP TABLE IF EXISTS `ofMucServiceProp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofMucServiceProp` (
  `serviceID` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `propValue` text NOT NULL,
  PRIMARY KEY (`serviceID`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofMucServiceProp`
--

LOCK TABLES `ofMucServiceProp` WRITE;
/*!40000 ALTER TABLE `ofMucServiceProp` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofMucServiceProp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofOffline`
--

DROP TABLE IF EXISTS `ofOffline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofOffline` (
  `username` varchar(64) NOT NULL,
  `messageID` bigint(20) NOT NULL,
  `creationDate` char(15) NOT NULL,
  `messageSize` int(11) NOT NULL,
  `stanza` text NOT NULL,
  PRIMARY KEY (`username`,`messageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofOffline`
--

LOCK TABLES `ofOffline` WRITE;
/*!40000 ALTER TABLE `ofOffline` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofOffline` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofPresence`
--

DROP TABLE IF EXISTS `ofPresence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofPresence` (
  `username` varchar(64) NOT NULL,
  `offlinePresence` text,
  `offlineDate` char(15) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofPresence`
--

LOCK TABLES `ofPresence` WRITE;
/*!40000 ALTER TABLE `ofPresence` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofPresence` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofPrivacyList`
--

DROP TABLE IF EXISTS `ofPrivacyList`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofPrivacyList` (
  `username` varchar(64) NOT NULL,
  `name` varchar(100) NOT NULL,
  `isDefault` tinyint(4) NOT NULL,
  `list` text NOT NULL,
  PRIMARY KEY (`username`,`name`),
  KEY `ofPrivacyList_default_idx` (`username`,`isDefault`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofPrivacyList`
--

LOCK TABLES `ofPrivacyList` WRITE;
/*!40000 ALTER TABLE `ofPrivacyList` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofPrivacyList` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofPrivate`
--

DROP TABLE IF EXISTS `ofPrivate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofPrivate` (
  `username` varchar(64) NOT NULL,
  `name` varchar(100) NOT NULL,
  `namespace` varchar(200) NOT NULL,
  `privateData` text NOT NULL,
  PRIMARY KEY (`username`,`name`,`namespace`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofPrivate`
--

LOCK TABLES `ofPrivate` WRITE;
/*!40000 ALTER TABLE `ofPrivate` DISABLE KEYS */;
INSERT INTO `ofPrivate` VALUES ('buri','roster','roster:delimiter','<roster xmlns=\"roster:delimiter\">\\</roster>');
/*!40000 ALTER TABLE `ofPrivate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofProperty`
--

DROP TABLE IF EXISTS `ofProperty`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofProperty` (
  `name` varchar(100) NOT NULL,
  `propValue` text NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofProperty`
--

LOCK TABLES `ofProperty` WRITE;
/*!40000 ALTER TABLE `ofProperty` DISABLE KEYS */;
INSERT INTO `ofProperty` VALUES ('admin.authorizedJIDs','buri@test.aragorn.cz'),('httpbind.CORS.domains','*'),('httpbind.CORS.enabled','true'),('httpbind.enabled','true'),('jdbcAuthProvider.allowUpdate','true'),('jdbcAuthProvider.passwordSQL','SELECT password FROM users WHERE url = ?'),('jdbcAuthProvider.passwordType','sha1'),('jdbcAuthProvider.setPasswordSQL','UPDATE users SET password = ? WHERE url = ?'),('jdbcGroupProvider.allGroupsSQL','SELECT name FROM groups'),('jdbcGroupProvider.descriptionSQL','SELECT desc FROM groups WHERE name = ?'),('jdbcGroupProvider.groupCountSQL','SELECT COUNT(id) FROM groups'),('jdbcGroupProvider.loadAdminsSQL','SELECT url FROM groups WHERE groupid IN (SELECT id FROM groups WHERE name IN (\'admin\', \'root\'))'),('jdbcGroupProvider.loadMembersSQL','SELECT url FROM users WHERE groupid = (SELECT id FROM groups WHERE name = ?)'),('jdbcGroupProvider.userGroupsSQL','SELECT name FROM groups WHERE id = (SELECT groupid FROM users WHERE url = ?)'),('jdbcProvider.connectionString','jdbc:mysql://127.0.0.1:3306/aragorn_cz?user=root&password=a'),('jdbcProvider.driver','com.mysql.jdbc.Driver'),('jdbcUserProvider.allUsersSQL','SELECT url FROM users'),('jdbcUserProvider.emailField','mail'),('jdbcUserProvider.loadUserSQL','SELECT username,mail FROM users WHERE url = ?'),('jdbcUserProvider.nameField','username'),('jdbcUserProvider.searchSQL','SELECT url FROM users WHERE'),('jdbcUserProvider.userCountSQL','SELECT COUNT(*) FROM users'),('jdbcUserProvider.usernameField','url'),('passwordKey','cjH64loLU5DAvNf'),('provider.admin.className','org.jivesoftware.openfire.admin.DefaultAdminProvider'),('provider.auth.className','org.jivesoftware.openfire.auth.JDBCAuthProvider'),('provider.group.className','org.jivesoftware.openfire.group.JDBCGroupProvider'),('provider.lockout.className','org.jivesoftware.openfire.lockout.DefaultLockOutProvider'),('provider.securityAudit.className','org.jivesoftware.openfire.security.DefaultSecurityAuditProvider'),('provider.user.className','org.jivesoftware.openfire.user.JDBCUserProvider'),('provider.vcard.className','org.jivesoftware.openfire.vcard.DefaultVCardProvider'),('register.inband','false'),('register.password','true'),('update.lastCheck','1364751903046'),('xmpp.auth.anonymous','false'),('xmpp.client.idle','3600000'),('xmpp.client.idle.ping','true'),('xmpp.domain','test.aragorn.cz'),('xmpp.httpbind.scriptSyntax.enabled','true'),('xmpp.session.conflict-limit','0'),('xmpp.socket.ssl.active','true');
/*!40000 ALTER TABLE `ofProperty` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofPubsubAffiliation`
--

DROP TABLE IF EXISTS `ofPubsubAffiliation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofPubsubAffiliation` (
  `serviceID` varchar(100) NOT NULL,
  `nodeID` varchar(100) NOT NULL,
  `jid` varchar(255) NOT NULL,
  `affiliation` varchar(10) NOT NULL,
  PRIMARY KEY (`serviceID`,`nodeID`,`jid`(70))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofPubsubAffiliation`
--

LOCK TABLES `ofPubsubAffiliation` WRITE;
/*!40000 ALTER TABLE `ofPubsubAffiliation` DISABLE KEYS */;
INSERT INTO `ofPubsubAffiliation` VALUES ('pubsub','','test.aragorn.cz','owner');
/*!40000 ALTER TABLE `ofPubsubAffiliation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofPubsubDefaultConf`
--

DROP TABLE IF EXISTS `ofPubsubDefaultConf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofPubsubDefaultConf` (
  `serviceID` varchar(100) NOT NULL,
  `leaf` tinyint(4) NOT NULL,
  `deliverPayloads` tinyint(4) NOT NULL,
  `maxPayloadSize` int(11) NOT NULL,
  `persistItems` tinyint(4) NOT NULL,
  `maxItems` int(11) NOT NULL,
  `notifyConfigChanges` tinyint(4) NOT NULL,
  `notifyDelete` tinyint(4) NOT NULL,
  `notifyRetract` tinyint(4) NOT NULL,
  `presenceBased` tinyint(4) NOT NULL,
  `sendItemSubscribe` tinyint(4) NOT NULL,
  `publisherModel` varchar(15) NOT NULL,
  `subscriptionEnabled` tinyint(4) NOT NULL,
  `accessModel` varchar(10) NOT NULL,
  `language` varchar(255) DEFAULT NULL,
  `replyPolicy` varchar(15) DEFAULT NULL,
  `associationPolicy` varchar(15) NOT NULL,
  `maxLeafNodes` int(11) NOT NULL,
  PRIMARY KEY (`serviceID`,`leaf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofPubsubDefaultConf`
--

LOCK TABLES `ofPubsubDefaultConf` WRITE;
/*!40000 ALTER TABLE `ofPubsubDefaultConf` DISABLE KEYS */;
INSERT INTO `ofPubsubDefaultConf` VALUES ('pubsub',0,0,0,0,0,1,1,1,0,0,'publishers',1,'open','English',NULL,'all',-1),('pubsub',1,1,5120,0,-1,1,1,1,0,1,'publishers',1,'open','English',NULL,'all',-1);
/*!40000 ALTER TABLE `ofPubsubDefaultConf` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofPubsubItem`
--

DROP TABLE IF EXISTS `ofPubsubItem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofPubsubItem` (
  `serviceID` varchar(100) NOT NULL,
  `nodeID` varchar(100) NOT NULL,
  `id` varchar(100) NOT NULL,
  `jid` varchar(255) NOT NULL,
  `creationDate` char(15) NOT NULL,
  `payload` mediumtext,
  PRIMARY KEY (`serviceID`,`nodeID`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofPubsubItem`
--

LOCK TABLES `ofPubsubItem` WRITE;
/*!40000 ALTER TABLE `ofPubsubItem` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofPubsubItem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofPubsubNode`
--

DROP TABLE IF EXISTS `ofPubsubNode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofPubsubNode` (
  `serviceID` varchar(100) NOT NULL,
  `nodeID` varchar(100) NOT NULL,
  `leaf` tinyint(4) NOT NULL,
  `creationDate` char(15) NOT NULL,
  `modificationDate` char(15) NOT NULL,
  `parent` varchar(100) DEFAULT NULL,
  `deliverPayloads` tinyint(4) NOT NULL,
  `maxPayloadSize` int(11) DEFAULT NULL,
  `persistItems` tinyint(4) DEFAULT NULL,
  `maxItems` int(11) DEFAULT NULL,
  `notifyConfigChanges` tinyint(4) NOT NULL,
  `notifyDelete` tinyint(4) NOT NULL,
  `notifyRetract` tinyint(4) NOT NULL,
  `presenceBased` tinyint(4) NOT NULL,
  `sendItemSubscribe` tinyint(4) NOT NULL,
  `publisherModel` varchar(15) NOT NULL,
  `subscriptionEnabled` tinyint(4) NOT NULL,
  `configSubscription` tinyint(4) NOT NULL,
  `accessModel` varchar(10) NOT NULL,
  `payloadType` varchar(100) DEFAULT NULL,
  `bodyXSLT` varchar(100) DEFAULT NULL,
  `dataformXSLT` varchar(100) DEFAULT NULL,
  `creator` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `language` varchar(255) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `replyPolicy` varchar(15) DEFAULT NULL,
  `associationPolicy` varchar(15) DEFAULT NULL,
  `maxLeafNodes` int(11) DEFAULT NULL,
  PRIMARY KEY (`serviceID`,`nodeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofPubsubNode`
--

LOCK TABLES `ofPubsubNode` WRITE;
/*!40000 ALTER TABLE `ofPubsubNode` DISABLE KEYS */;
INSERT INTO `ofPubsubNode` VALUES ('pubsub','',0,'001364751865589','001364751865589',NULL,0,0,0,0,1,1,1,0,0,'publishers',1,0,'open','','','','test.aragorn.cz','','English','',NULL,'all',-1);
/*!40000 ALTER TABLE `ofPubsubNode` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofPubsubNodeGroups`
--

DROP TABLE IF EXISTS `ofPubsubNodeGroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofPubsubNodeGroups` (
  `serviceID` varchar(100) NOT NULL,
  `nodeID` varchar(100) NOT NULL,
  `rosterGroup` varchar(100) NOT NULL,
  KEY `ofPubsubNodeGroups_idx` (`serviceID`,`nodeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofPubsubNodeGroups`
--

LOCK TABLES `ofPubsubNodeGroups` WRITE;
/*!40000 ALTER TABLE `ofPubsubNodeGroups` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofPubsubNodeGroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofPubsubNodeJIDs`
--

DROP TABLE IF EXISTS `ofPubsubNodeJIDs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofPubsubNodeJIDs` (
  `serviceID` varchar(100) NOT NULL,
  `nodeID` varchar(100) NOT NULL,
  `jid` varchar(255) NOT NULL,
  `associationType` varchar(20) NOT NULL,
  PRIMARY KEY (`serviceID`,`nodeID`,`jid`(70))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofPubsubNodeJIDs`
--

LOCK TABLES `ofPubsubNodeJIDs` WRITE;
/*!40000 ALTER TABLE `ofPubsubNodeJIDs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofPubsubNodeJIDs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofPubsubSubscription`
--

DROP TABLE IF EXISTS `ofPubsubSubscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofPubsubSubscription` (
  `serviceID` varchar(100) NOT NULL,
  `nodeID` varchar(100) NOT NULL,
  `id` varchar(100) NOT NULL,
  `jid` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `state` varchar(15) NOT NULL,
  `deliver` tinyint(4) NOT NULL,
  `digest` tinyint(4) NOT NULL,
  `digest_frequency` int(11) NOT NULL,
  `expire` char(15) DEFAULT NULL,
  `includeBody` tinyint(4) NOT NULL,
  `showValues` varchar(30) DEFAULT NULL,
  `subscriptionType` varchar(10) NOT NULL,
  `subscriptionDepth` tinyint(4) NOT NULL,
  `keyword` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`serviceID`,`nodeID`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofPubsubSubscription`
--

LOCK TABLES `ofPubsubSubscription` WRITE;
/*!40000 ALTER TABLE `ofPubsubSubscription` DISABLE KEYS */;
INSERT INTO `ofPubsubSubscription` VALUES ('pubsub','','dPx56UiQL5tTcD0AdxB7N0RAsIe8kW8PHt2me4D2','test.aragorn.cz','test.aragorn.cz','subscribed',1,0,86400000,NULL,0,' ','nodes',1,NULL);
/*!40000 ALTER TABLE `ofPubsubSubscription` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofRemoteServerConf`
--

DROP TABLE IF EXISTS `ofRemoteServerConf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofRemoteServerConf` (
  `xmppDomain` varchar(255) NOT NULL,
  `remotePort` int(11) DEFAULT NULL,
  `permission` varchar(10) NOT NULL,
  PRIMARY KEY (`xmppDomain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofRemoteServerConf`
--

LOCK TABLES `ofRemoteServerConf` WRITE;
/*!40000 ALTER TABLE `ofRemoteServerConf` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofRemoteServerConf` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofRoster`
--

DROP TABLE IF EXISTS `ofRoster`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofRoster` (
  `rosterID` bigint(20) NOT NULL,
  `username` varchar(64) NOT NULL,
  `jid` varchar(1024) NOT NULL,
  `sub` tinyint(4) NOT NULL,
  `ask` tinyint(4) NOT NULL,
  `recv` tinyint(4) NOT NULL,
  `nick` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rosterID`),
  KEY `ofRoster_unameid_idx` (`username`),
  KEY `ofRoster_jid_idx` (`jid`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofRoster`
--

LOCK TABLES `ofRoster` WRITE;
/*!40000 ALTER TABLE `ofRoster` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofRoster` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofRosterGroups`
--

DROP TABLE IF EXISTS `ofRosterGroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofRosterGroups` (
  `rosterID` bigint(20) NOT NULL,
  `rank` tinyint(4) NOT NULL,
  `groupName` varchar(255) NOT NULL,
  PRIMARY KEY (`rosterID`,`rank`),
  KEY `ofRosterGroup_rosterid_idx` (`rosterID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofRosterGroups`
--

LOCK TABLES `ofRosterGroups` WRITE;
/*!40000 ALTER TABLE `ofRosterGroups` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofRosterGroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofSASLAuthorized`
--

DROP TABLE IF EXISTS `ofSASLAuthorized`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofSASLAuthorized` (
  `username` varchar(64) NOT NULL,
  `principal` text NOT NULL,
  PRIMARY KEY (`username`,`principal`(200))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofSASLAuthorized`
--

LOCK TABLES `ofSASLAuthorized` WRITE;
/*!40000 ALTER TABLE `ofSASLAuthorized` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofSASLAuthorized` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofSecurityAuditLog`
--

DROP TABLE IF EXISTS `ofSecurityAuditLog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofSecurityAuditLog` (
  `msgID` bigint(20) NOT NULL,
  `username` varchar(64) NOT NULL,
  `entryStamp` bigint(20) NOT NULL,
  `summary` varchar(255) NOT NULL,
  `node` varchar(255) NOT NULL,
  `details` text,
  PRIMARY KEY (`msgID`),
  KEY `ofSecurityAuditLog_tstamp_idx` (`entryStamp`),
  KEY `ofSecurityAuditLog_uname_idx` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofSecurityAuditLog`
--

LOCK TABLES `ofSecurityAuditLog` WRITE;
/*!40000 ALTER TABLE `ofSecurityAuditLog` DISABLE KEYS */;
INSERT INTO `ofSecurityAuditLog` VALUES (1,'buri',1364755726170,'updated HTTP bind settings','test',NULL),(2,'buri',1364757123012,'updated HTTP bind settings','test',NULL),(3,'buri',1364758763438,'edit client connections settings','test','port = 5222\nsslPort = 5223'),(4,'buri',1364758763502,'set server property xmpp.client.idle','test','xmpp.client.idle = 360000'),(5,'buri',1364758763552,'set server property xmpp.client.idle.ping','test','xmpp.client.idle.ping = true'),(6,'buri',1364758778005,'edit client connections settings','test','port = 5222\nsslPort = 5223'),(7,'buri',1364758778012,'set server property xmpp.client.idle','test','xmpp.client.idle = 3600000'),(8,'buri',1364758778075,'set server property xmpp.client.idle.ping','test','xmpp.client.idle.ping = true'),(9,'buri',1364758798095,'updated HTTP bind settings','test',NULL),(10,'buri',1364758822956,'edited registration settings','test','inband enabled = false\ncan change password = true\nanon login = false\nallowed ips = '),(11,'buri',1364759398521,'set server property provider.group.className','test','provider.group.className = org.jivesoftware.openfire.group.JDBCGroupProvider'),(12,'buri',1364759444509,'enabled db profiling','test',NULL);
/*!40000 ALTER TABLE `ofSecurityAuditLog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofUser`
--

DROP TABLE IF EXISTS `ofUser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofUser` (
  `username` varchar(64) NOT NULL,
  `plainPassword` varchar(32) DEFAULT NULL,
  `encryptedPassword` varchar(255) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `creationDate` char(15) NOT NULL,
  `modificationDate` char(15) NOT NULL,
  PRIMARY KEY (`username`),
  KEY `ofUser_cDate_idx` (`creationDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofUser`
--

LOCK TABLES `ofUser` WRITE;
/*!40000 ALTER TABLE `ofUser` DISABLE KEYS */;
INSERT INTO `ofUser` VALUES ('admin',NULL,'f22edcb242fcb35b37d57c09c832e626','Administrator','admin@example.com','001364751846639','0'),('buri',NULL,'faf8877c57bf04c40aa10fb303fc2c57b8e9f443178170f48c7d3294cf59b7ba',NULL,NULL,'001364752515714','001364752515714');
/*!40000 ALTER TABLE `ofUser` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofUserFlag`
--

DROP TABLE IF EXISTS `ofUserFlag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofUserFlag` (
  `username` varchar(64) NOT NULL,
  `name` varchar(100) NOT NULL,
  `startTime` char(15) DEFAULT NULL,
  `endTime` char(15) DEFAULT NULL,
  PRIMARY KEY (`username`,`name`),
  KEY `ofUserFlag_sTime_idx` (`startTime`),
  KEY `ofUserFlag_eTime_idx` (`endTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofUserFlag`
--

LOCK TABLES `ofUserFlag` WRITE;
/*!40000 ALTER TABLE `ofUserFlag` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofUserFlag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofUserProp`
--

DROP TABLE IF EXISTS `ofUserProp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofUserProp` (
  `username` varchar(64) NOT NULL,
  `name` varchar(100) NOT NULL,
  `propValue` text NOT NULL,
  PRIMARY KEY (`username`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofUserProp`
--

LOCK TABLES `ofUserProp` WRITE;
/*!40000 ALTER TABLE `ofUserProp` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofUserProp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofVCard`
--

DROP TABLE IF EXISTS `ofVCard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofVCard` (
  `username` varchar(64) NOT NULL,
  `vcard` mediumtext NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofVCard`
--

LOCK TABLES `ofVCard` WRITE;
/*!40000 ALTER TABLE `ofVCard` DISABLE KEYS */;
/*!40000 ALTER TABLE `ofVCard` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ofVersion`
--

DROP TABLE IF EXISTS `ofVersion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ofVersion` (
  `name` varchar(50) NOT NULL,
  `version` int(11) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ofVersion`
--

LOCK TABLES `ofVersion` WRITE;
/*!40000 ALTER TABLE `ofVersion` DISABLE KEYS */;
INSERT INTO `ofVersion` VALUES ('openfire',21);
/*!40000 ALTER TABLE `ofVersion` ENABLE KEYS */;
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

--
-- Current Database: `openfire`
--

USE `openfire`;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-03-31 23:45:33
