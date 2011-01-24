/*
 Navicat MySQL Data Transfer

 Source Server         : z local
 Source Server Version : 50147
 Source Host           : localhost
 Source Database       : livingcities_intranet

 Target Server Version : 50147
 File Encoding         : utf-8

 Date: 01/24/2011 13:09:01 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `pages_views`
-- ----------------------------
DROP TABLE IF EXISTS `pages_views`;
CREATE TABLE `pages_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2149 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
