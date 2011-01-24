/*
 Navicat MySQL Data Transfer

 Source Server         : z local
 Source Server Version : 50147
 Source Host           : localhost
 Source Database       : livingcities_intranet

 Target Server Version : 50147
 File Encoding         : utf-8

 Date: 01/24/2011 11:19:26 AM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `modulettes`
-- ----------------------------
DROP TABLE IF EXISTS `modulettes`;
CREATE TABLE `modulettes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT NULL,
  `title_es` varchar(255) DEFAULT NULL,
  `title_fr` varchar(255) DEFAULT NULL,
  `title_ru` varchar(255) DEFAULT NULL,
  `folder` varchar(255) DEFAULT NULL,
  `is_public` tinyint(4) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `created_user` int(11) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `updated_user` int(11) DEFAULT NULL,
  `deleted_date` datetime DEFAULT NULL,
  `deleted_user` int(11) DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL,
  `publish_date` datetime DEFAULT NULL,
  `publish_user` int(11) DEFAULT NULL,
  `is_published` tinyint(4) NOT NULL,
  `precedence` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
