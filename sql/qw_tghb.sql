/*
Navicat MySQL Data Transfer

Source Server         : 192.168.1.102
Source Server Version : 50547
Source Host           : 192.168.1.102:3306
Source Database       : xdy_cps

Target Server Type    : MYSQL
Target Server Version : 50547
File Encoding         : 65001

Date: 2017-03-15 20:36:46
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for qw_tghb
-- ----------------------------
DROP TABLE IF EXISTS `qw_tghb`;
CREATE TABLE `qw_tghb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_domain` varchar(20) NOT NULL COMMENT '海报接口请求域名',
  `show_domain` varchar(20) NOT NULL COMMENT '海报展示域名',
  `ip` varchar(40) NOT NULL COMMENT '所在服务器IP',
  `state` tinyint(1) DEFAULT '1' COMMENT '使用状态：1-未使用，2-使用中',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
