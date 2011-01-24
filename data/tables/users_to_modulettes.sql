/*
 Navicat MySQL Data Transfer

 Source Server         : z local
 Source Server Version : 50147
 Source Host           : localhost
 Source Database       : livingcities_intranet

 Target Server Version : 50147
 File Encoding         : utf-8

 Date: 01/24/2011 11:40:33 AM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `users_to_modulettes`
-- ----------------------------
DROP TABLE IF EXISTS `users_to_modulettes`;
CREATE TABLE `users_to_modulettes` (
  `user_id` int(11) NOT NULL,
  `modulette_id` int(11) NOT NULL,
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
  `precedence` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED;

