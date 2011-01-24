/*
 Navicat MySQL Data Transfer

 Source Server         : z local
 Source Server Version : 50147
 Source Host           : localhost
 Source Database       : livingcities_intranet

 Target Server Version : 50147
 File Encoding         : utf-8

 Date: 01/24/2011 10:59:53 AM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `languages`
-- ----------------------------
DROP TABLE IF EXISTS `languages`;
CREATE TABLE `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `code` char(2) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `created_user` int(11) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `updated_user` int(11) DEFAULT NULL,
  `publish_date` datetime DEFAULT NULL,
  `publish_user` int(11) DEFAULT NULL,
  `is_published` tinyint(4) NOT NULL,
  `deleted_date` datetime DEFAULT NULL,
  `deleted_user` int(11) DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL,
  `precedence` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
--  Records of `languages`
-- ----------------------------
BEGIN;
INSERT INTO `languages` VALUES ('1', 'English', 'en', null, null, null, null, null, null, '0', null, null, '0', null), ('2', 'Español', 'es', null, null, null, null, null, null, '0', null, null, '0', null), ('3', 'Français', 'fr', null, null, null, null, null, null, '0', null, null, '0', null), ('4', 'Pусский', 'ru', null, null, null, null, null, null, '0', null, null, '0', null);
COMMIT;

