/*
 Navicat MySQL Data Transfer

 Source Server         : z local
 Source Server Version : 50147
 Source Host           : localhost
 Source Database       : seedco_intranet

 Target Server Version : 50147
 File Encoding         : utf-8

 Date: 01/27/2011 12:22:53 PM
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `modulettes`
-- ----------------------------
BEGIN;
INSERT INTO `modulettes` VALUES ('17', 'Admin', null, null, null, 'admin', '0', '2011-01-26 13:52:53', '1', null, null, null, null, '1', null, null, '0', null), ('18', 'Funders', null, null, null, 'funders', '1', '2011-01-26 13:53:51', '1', null, null, null, null, '1', null, null, '0', null), ('20', 'Long Distance Codes', null, null, null, 'ldcodes', null, '2011-01-26 13:55:08', '1', null, null, null, null, '1', null, null, '0', null);
COMMIT;

