# Host: Chigix.com  (Version: 5.5.16-log)
# Date: 2013-06-30 16:27:06
# Generator: MySQL-Front 5.3  (Build 4.4)

/*!40101 SET NAMES utf8 */;

#
# Source for table "chigi_access_ctrl"
#

DROP TABLE IF EXISTS `chigi_access_ctrl`;
CREATE TABLE `chigi_access_ctrl` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `role_service` char(32) NOT NULL DEFAULT '' COMMENT '角色32位标识码，默认为usergroup',
  `role_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '角色ID',
  `node_service` char(32) NOT NULL DEFAULT '' COMMENT '资源32位标识码',
  `node_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '资源ID',
  `node_name` char(32) NOT NULL DEFAULT '' COMMENT '资源名称md5',
  `node_level` enum('PAGE','VIEW','FILTER') NOT NULL DEFAULT 'PAGE' COMMENT '资源级别',
  `node_remark` char(10) NOT NULL DEFAULT 'none' COMMENT '资源注释',
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='千木权限分配表';

#
# Source for table "chigi_couple"
#

DROP TABLE IF EXISTS `chigi_couple`;
CREATE TABLE `chigi_couple` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `main_service` char(32) NOT NULL DEFAULT '' COMMENT '主服务32位标识码',
  `main_remark` char(10) NOT NULL DEFAULT 'NONE' COMMENT 'main服务名注释',
  `main_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '主服务数据资源ID',
  `resource_service` char(32) NOT NULL DEFAULT '' COMMENT '资源服务32位标识码',
  `resource_remark` char(10) NOT NULL DEFAULT 'NONE' COMMENT 'Resource服务注释',
  `resource_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '资源服务数据ID',
  `couple_service` char(32) NOT NULL DEFAULT '' COMMENT '拼装服务自身的32位标识码',
  PRIMARY KEY (`Id`),
  KEY `resource_service` (`resource_service`,`resource_id`),
  KEY `main_service` (`main_service`,`main_id`),
  KEY `couple_service` (`couple_service`,`main_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='千木服务拼装表';

#
# Source for table "chigi_page"
#

DROP TABLE IF EXISTS `chigi_page`;
CREATE TABLE `chigi_page` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `pagename` varchar(32) DEFAULT NULL,
  `domain` char(20) NOT NULL DEFAULT 'www.chigix.com' COMMENT '所属域名',
  `protocol` char(6) NOT NULL DEFAULT 'http' COMMENT '协议',
  `status` bit(1) NOT NULL DEFAULT b'0' COMMENT '是否可用',
  `apphost` char(32) NOT NULL DEFAULT '' COMMENT '所属应用',
  PRIMARY KEY (`Id`),
  UNIQUE KEY `pagename` (`pagename`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='全局页面注册表';
