/*
 Navicat MySQL Data Transfer

 Source Server         : z local
 Source Server Version : 50147
 Source Host           : localhost
 Source Database       : seedco_intranet

 Target Server Version : 50147
 File Encoding         : utf-8

 Date: 01/26/2011 14:22:08 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `cal_events_types`
-- ----------------------------
DROP TABLE IF EXISTS `cal_events_types`;
CREATE TABLE `cal_events_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `cal_events_types`
-- ----------------------------
BEGIN;
INSERT INTO `cal_events_types` VALUES ('1', 'Seedco Event', '#d8282d'), ('2', 'Brown Bag Lunch', '#663333'), ('3', 'Training', '#666666'), ('4', 'Seedco FUSE', '#336633'), ('5', 'Open Meetings', '#336699');
COMMIT;

