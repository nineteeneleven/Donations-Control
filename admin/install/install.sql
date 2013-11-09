-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 09, 2013 at 03:25 PM
-- Server version: 5.5.24-log
-- PHP Version: 5.3.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `donations`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE IF NOT EXISTS `cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `steamid` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  `avatar` varchar(256) CHARACTER SET latin1 DEFAULT NULL,
  `avatarmedium` varchar(256) CHARACTER SET latin1 DEFAULT NULL,
  `avatarfull` varchar(256) CHARACTER SET latin1 DEFAULT NULL,
  `personaname` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `timestamp` varchar(32) CHARACTER SET latin1 DEFAULT NULL,
  `steamid64` varchar(64) CHARACTER SET latin1 DEFAULT NULL,
  `steam_link` varchar(256) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `custom_chatcolors`
--

CREATE TABLE IF NOT EXISTS `custom_chatcolors` (
  `index` int(11) NOT NULL AUTO_INCREMENT,
  `identity` varchar(32) CHARACTER SET latin1 NOT NULL,
  `flag` char(1) CHARACTER SET latin1 DEFAULT NULL,
  `tag` varchar(32) CHARACTER SET latin1 DEFAULT NULL,
  `tagcolor` varchar(8) CHARACTER SET latin1 DEFAULT NULL,
  `namecolor` varchar(8) CHARACTER SET latin1 DEFAULT NULL,
  `textcolor` varchar(8) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`index`),
  UNIQUE KEY `identity` (`identity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `donors`
--

CREATE TABLE IF NOT EXISTS `donors` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET latin1 NOT NULL,
  `steam_id` varchar(30) CHARACTER SET latin1 NOT NULL,
  `sign_up_date` varchar(10) CHARACTER SET latin1 NOT NULL,
  `email` varchar(60) CHARACTER SET latin1 DEFAULT NULL,
  `renewal_date` varchar(10) CHARACTER SET latin1 DEFAULT NULL,
  `current_amount` varchar(10) CHARACTER SET latin1 NOT NULL,
  `total_amount` varchar(10) CHARACTER SET latin1 DEFAULT NULL,
  `expiration_date` varchar(10) CHARACTER SET latin1 NOT NULL,
  `steam_link` varchar(200) CHARACTER SET latin1 DEFAULT NULL,
  `notes` varchar(200) CHARACTER SET latin1 DEFAULT NULL,
  `activated` varchar(1) CHARACTER SET latin1 DEFAULT '0',
  `txn_id` varchar(128) CHARACTER SET latin1 DEFAULT NULL,
  `tier` varchar(10) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `player_tracker`
--

CREATE TABLE IF NOT EXISTS `player_tracker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `steamid` varchar(255) CHARACTER SET latin1 NOT NULL,
  `playername` varchar(255) CHARACTER SET latin1 NOT NULL,
  `playerip` varchar(255) CHARACTER SET latin1 NOT NULL,
  `servertype` varchar(255) CHARACTER SET latin1 NOT NULL,
  `serverip` varchar(255) CHARACTER SET latin1 NOT NULL,
  `serverport` varchar(255) CHARACTER SET latin1 NOT NULL,
  `geoipcountry` varchar(255) CHARACTER SET latin1 NOT NULL,
  `status` varchar(255) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `steamid` (`steamid`),
  KEY `playername` (`playername`),
  KEY `playerip` (`playerip`),
  KEY `servertype` (`servertype`),
  KEY `serverip` (`serverip`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
