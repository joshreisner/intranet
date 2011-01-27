/*
 Navicat MySQL Data Transfer

 Source Server         : z local
 Source Server Version : 50147
 Source Host           : localhost
 Source Database       : seedco_intranet

 Target Server Version : 50147
 File Encoding         : utf-8

 Date: 01/27/2011 13:51:33 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `offices`
-- ----------------------------
DROP TABLE IF EXISTS `offices`;
CREATE TABLE `offices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `fax` varchar(50) DEFAULT NULL,
  `isMain` tinyint(4) DEFAULT NULL,
  `shortName` varchar(50) DEFAULT NULL,
  `precedence` int(11) DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `offices`
-- ----------------------------
BEGIN;
INSERT INTO `offices` VALUES ('1', '915 Broadway', '(212) 473-0357', '1', null, '1', '1'), ('3', 'Alabama', '(205) 715-2711', '0', null, '6', '1'), ('6', 'UMOS', '(212) 473-0357', '0', null, '3', '1'), ('8', 'Memphis', '(901) 528-1401', '0', null, '11', '1'), ('9', 'LM BSC', '(212) 618-8866', '0', null, '5', '1'), ('11', 'UM BSC', null, '0', null, '4', '1'), ('12', 'Atlanta', null, '0', null, '7', '1'), ('13', 'Louisiana', '(504) 520-5726', '0', null, '12', '1'), ('14', 'Baltimore', '(410) 234-8829', '0', null, '8', '1'), ('15', 'Buffalo', '(716) 858-3996', '0', null, '9', '1'), ('16', 'NYC Field Offices', null, '0', null, '2', '0'), ('17', 'Denver', null, '0', null, '10', '1'), ('18', 'Bronx WF1', null, '0', null, '13', '1');
COMMIT;

