/*
 Navicat MySQL Data Transfer

 Source Server         : z local
 Source Server Version : 50147
 Source Host           : localhost
 Source Database       : seedco_intranet

 Target Server Version : 50147
 File Encoding         : utf-8

 Date: 01/26/2011 13:50:08 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `modules`
-- ----------------------------
DROP TABLE IF EXISTS `modules`;
CREATE TABLE `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `isPublic` tinyint(4) DEFAULT NULL,
  `precedence` int(11) DEFAULT NULL,
  `pallet` varchar(50) DEFAULT NULL,
  `homePageID` int(11) DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT NULL,
  `hasChildren` tinyint(4) DEFAULT NULL,
  `folder` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `hilite` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `modules`
-- ----------------------------
BEGIN;
INSERT INTO `modules` VALUES ('1', 'Bulletin Board', 'Bulletin Board', '1', '2', '/bb/pallet.php', '1', '1', '0', 'bb', '336633', 'DDEEDD'), ('2', 'Calendar', 'Calendar', '1', '4', '/cal/pallet.php', '60', '1', '0', 'cal', '336699', 'DDEEFF'), ('3', 'Helpdesk', 'Helpdesk', '1', '6', '/helpdesk/pallet.php', '6', '1', '0', 'helpdesk', '996633', 'FFEEDD'), ('4', 'Intranet Areas', 'Intranet Areas', '1', '3', '/a/pallet.php', '92', '1', '1', 'a', 'D8282D', 'FFDDDD'), ('12', 'Documents', 'Documents', '1', '7', '/docs/pallet.php', '3', '1', '0', 'docs', '6A476A', 'FFEEFF'), ('13', 'Contacts', 'Contacts', '1', '5', '/contacts/pallet.php', '40', '1', '0', 'contacts', '12253C', 'EEEEEE'), ('19', 'Staff', 'Staff', '1', '1', '/staff/pallet.php', '14', '1', '0', 'staff', '666666', 'EEEEEE');
COMMIT;

