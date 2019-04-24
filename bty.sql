-- MySQL dump 10.13  Distrib 5.5.62, for Linux (x86_64)
--
-- Host: localhost    Database: bty
-- ------------------------------------------------------
-- Server version	5.5.62-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `bty_admin`
--

DROP TABLE IF EXISTS `bty_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bty_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(128) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(60) NOT NULL DEFAULT '' COMMENT '密码',
  `Ltime` int(11) DEFAULT NULL COMMENT '最后登录时间',
  `Lip` text COMMENT '最后登录IP',
  `Oip` varchar(128) NOT NULL DEFAULT '' COMMENT '登录IP',
  `token` varchar(255) DEFAULT NULL COMMENT '密钥',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bty_admin`
--

LOCK TABLES `bty_admin` WRITE;
/*!40000 ALTER TABLE `bty_admin` DISABLE KEYS */;
INSERT INTO `bty_admin` VALUES (1,'admin','$2y$10$TF/vIde3FbSR6rwx6ccwGeNGlpaWo7md3at68GJApsTPxfbMawf6m',1552806601,'127.0.0.1','0.0.0.0','');
/*!40000 ALTER TABLE `bty_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bty_bill`
--

DROP TABLE IF EXISTS `bty_bill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bty_bill` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL COMMENT '用户ID',
  `money` int(11) DEFAULT '0' COMMENT '金额',
  `ctime` text COMMENT '创建时间',
  `type` tinyint(3) DEFAULT '0' COMMENT '类型',
  `desc` varchar(255) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bty_bill`
--

LOCK TABLES `bty_bill` WRITE;
/*!40000 ALTER TABLE `bty_bill` DISABLE KEYS */;
/*!40000 ALTER TABLE `bty_bill` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bty_host`
--

DROP TABLE IF EXISTS `bty_host`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bty_host` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '机名',
  `ip` varchar(255) DEFAULT NULL COMMENT '服务器IP',
  `money` int(11) DEFAULT '0' COMMENT '费用',
  `bturl` varchar(255) DEFAULT NULL COMMENT '宝塔地址',
  `btoken` varchar(255) DEFAULT NULL COMMENT '宝塔API密钥',
  `expiretime` int(11) DEFAULT NULL COMMENT '到期时间',
  `group` int(11) DEFAULT '1' COMMENT '用户组',
  `status` int(11) DEFAULT '0' COMMENT '状态',
  `tag` varchar(255) DEFAULT NULL COMMENT '性能标签',
  `local` varchar(255) DEFAULT NULL COMMENT '地区标签',
  `hot` int(11) DEFAULT '0' COMMENT '是否推荐',
  `domain` varchar(255) DEFAULT NULL COMMENT '默认域名',
  `sqlurl` varchar(255) DEFAULT NULL COMMENT '数据库地址',
  `service` varchar(255) DEFAULT NULL COMMENT '系统环境标签',
  `quota` int(11) DEFAULT '0' COMMENT '配额',
  `being` int(11) DEFAULT '0' COMMENT '当前服务器开通主机数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bty_host`
--

LOCK TABLES `bty_host` WRITE;
/*!40000 ALTER TABLE `bty_host` DISABLE KEYS */;
/*!40000 ALTER TABLE `bty_host` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bty_hostlist`
--

DROP TABLE IF EXISTS `bty_hostlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bty_hostlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL COMMENT '用户ID',
  `hostid` int(11) DEFAULT NULL COMMENT '服务器ID',
  `domain` varchar(255) DEFAULT NULL COMMENT '绑定域名',
  `status` int(2) DEFAULT '1' COMMENT '状态',
  `stime` text COMMENT '开始时间',
  `etime` text COMMENT '到期时间',
  `ftpname` varchar(255) DEFAULT NULL COMMENT 'FTP用户名',
  `ftpkey` varchar(255) DEFAULT NULL COMMENT 'FTP密码',
  `sqlname` varchar(255) DEFAULT NULL COMMENT '数据库账号',
  `sqlkey` varchar(255) DEFAULT NULL COMMENT '数据库密码',
  `btid` int(11) DEFAULT NULL COMMENT '宝塔网站ID',
  `username` varchar(255) DEFAULT NULL COMMENT '账号',
  `password` varchar(255) DEFAULT NULL COMMENT '密码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bty_hostlist`
--

LOCK TABLES `bty_hostlist` WRITE;
/*!40000 ALTER TABLE `bty_hostlist` DISABLE KEYS */;
/*!40000 ALTER TABLE `bty_hostlist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bty_order`
--

DROP TABLE IF EXISTS `bty_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bty_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单号',
  `userid` int(11) DEFAULT NULL COMMENT '用户ID',
  `hostip` varchar(255) DEFAULT NULL COMMENT '服务器IP',
  `quantity` int(11) DEFAULT '1' COMMENT '数量',
  `discount` int(11) DEFAULT '0' COMMENT '优惠',
  `status` int(2) DEFAULT '0' COMMENT '订单状态',
  `ctime` text COMMENT '订单创建时间',
  `paytime` text COMMENT '支付时间',
  `hostid` int(11) DEFAULT NULL COMMENT '主机ID',
  `payment` int(11) DEFAULT '0' COMMENT '付费金额',
  `shouldment` int(11) DEFAULT '0' COMMENT '应付款',
  `hostlistid` int(11) DEFAULT NULL COMMENT '已开通的主机ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bty_order`
--

LOCK TABLES `bty_order` WRITE;
/*!40000 ALTER TABLE `bty_order` DISABLE KEYS */;
/*!40000 ALTER TABLE `bty_order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bty_pay`
--

DROP TABLE IF EXISTS `bty_pay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bty_pay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL COMMENT '用户ID',
  `money` float(8,2) DEFAULT NULL COMMENT '金额',
  `ctime` text COMMENT '下单时间',
  `ptime` text COMMENT '支付时间',
  `status` int(11) DEFAULT '0' COMMENT '订单状态',
  `type` varchar(32) DEFAULT '0' COMMENT '支付方式',
  `out_trade_no` varchar(255) DEFAULT NULL COMMENT '商户订单号',
  `name` varchar(255) DEFAULT NULL COMMENT '商品名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='账户充值表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bty_pay`
--

LOCK TABLES `bty_pay` WRITE;
/*!40000 ALTER TABLE `bty_pay` DISABLE KEYS */;
/*!40000 ALTER TABLE `bty_pay` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bty_payconfig`
--

DROP TABLE IF EXISTS `bty_payconfig`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bty_payconfig` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `partner` int(11) DEFAULT NULL COMMENT '商户ID',
  `key` varchar(255) DEFAULT NULL COMMENT '商户key',
  `ssl` int(11) DEFAULT '0' COMMENT '是否支持https',
  `apiurl` varchar(255) DEFAULT NULL COMMENT '支付API地址',
  `notify_url` varchar(255) DEFAULT NULL COMMENT '异步通知页面地址',
  `return_url` varchar(255) DEFAULT NULL COMMENT '页面跳转同步通知页面路径',
  `status` varchar(128) DEFAULT NULL COMMENT '接口开启状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='支付配置表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bty_payconfig`
--

LOCK TABLES `bty_payconfig` WRITE;
/*!40000 ALTER TABLE `bty_payconfig` DISABLE KEYS */;
INSERT INTO `bty_payconfig` VALUES (1,NULL,'',1,'https://pay.v8jisu.cn/','/user/notify_pay.html','/user/return_pay.html','{\"alipay\":1,\"qqpay\":0,\"wxpay\":0,\"tenpay\":0}');
/*!40000 ALTER TABLE `bty_payconfig` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bty_redeem`
--

DROP TABLE IF EXISTS `bty_redeem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bty_redeem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `redeem` varchar(255) DEFAULT NULL COMMENT '兑换码',
  `money` int(11) DEFAULT '0' COMMENT '金额',
  `ctime` varchar(255) DEFAULT NULL COMMENT '生成时间',
  `etime` varchar(255) DEFAULT NULL COMMENT '兑换时间',
  `userid` int(11) DEFAULT NULL COMMENT '兑换者ID',
  `status` int(11) DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='兑换码表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bty_redeem`
--

LOCK TABLES `bty_redeem` WRITE;
/*!40000 ALTER TABLE `bty_redeem` DISABLE KEYS */;
/*!40000 ALTER TABLE `bty_redeem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bty_smtp`
--

DROP TABLE IF EXISTS `bty_smtp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bty_smtp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` tinytext NOT NULL COMMENT '邮箱host',
  `port` int(11) NOT NULL DEFAULT '0' COMMENT '发信端口',
  `fromname` tinytext NOT NULL COMMENT '发件人',
  `username` tinytext NOT NULL COMMENT '邮件用户名',
  `password` tinytext NOT NULL COMMENT '邮件密码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bty_smtp`
--

LOCK TABLES `bty_smtp` WRITE;
/*!40000 ALTER TABLE `bty_smtp` DISABLE KEYS */;
INSERT INTO `bty_smtp` VALUES (1,'smtp.exmail.qq.com',465,'','','');
/*!40000 ALTER TABLE `bty_smtp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bty_user`
--

DROP TABLE IF EXISTS `bty_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bty_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL DEFAULT '' COMMENT '账号',
  `password` varchar(60) NOT NULL DEFAULT '' COMMENT '密码',
  `rip` varchar(128) DEFAULT '0' COMMENT '注册IP',
  `rtime` int(11) DEFAULT '0' COMMENT '注册时间',
  `ltime` int(11) DEFAULT '0' COMMENT '最后登录时间',
  `lip` varchar(128) DEFAULT '0' COMMENT '最后登录IP',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '用户状态',
  `usergroup` int(11) NOT NULL DEFAULT '0' COMMENT '用户组',
  `email` varchar(128) NOT NULL DEFAULT '' COMMENT '邮箱',
  `qq` int(11) DEFAULT NULL COMMENT 'QQ号',
  `phone` varchar(20) DEFAULT NULL COMMENT '手机号',
  `money` float(11,2) DEFAULT '0.00' COMMENT '金币',
  `token` varchar(255) DEFAULT NULL COMMENT '登录token',
  `emailauth` tinyint(3) DEFAULT '0' COMMENT '邮箱认证',
  `invitation` varchar(255) DEFAULT NULL COMMENT '邀请码',
  `invitationuser` varchar(255) DEFAULT NULL COMMENT '邀请列表',
  `Retrieve` varchar(255) DEFAULT NULL COMMENT '邮件找回密码token',
  `token_exptime` text COMMENT '邮箱找回token有效时长',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bty_user`
--

LOCK TABLES `bty_user` WRITE;
/*!40000 ALTER TABLE `bty_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `bty_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bty_usergroup`
--

DROP TABLE IF EXISTS `bty_usergroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bty_usergroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '组名',
  `hostlist` varchar(255) DEFAULT NULL COMMENT '可用服务器',
  `discount` varchar(255) DEFAULT NULL COMMENT '优惠',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bty_usergroup`
--

LOCK TABLES `bty_usergroup` WRITE;
/*!40000 ALTER TABLE `bty_usergroup` DISABLE KEYS */;
INSERT INTO `bty_usergroup` VALUES (1,'v0','*','0'),(2,'v1','*','2'),(3,'v2','*','5'),(4,'v3','*','10'),(5,'v4',NULL,'10');
/*!40000 ALTER TABLE `bty_usergroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bty_webinfo`
--

DROP TABLE IF EXISTS `bty_webinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bty_webinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `webdomain` varchar(256) NOT NULL COMMENT '站点域名',
  `webname` varchar(255) DEFAULT NULL COMMENT '网站名',
  `webkey` varchar(256) NOT NULL COMMENT '站点关键词',
  `webdes` varchar(256) NOT NULL COMMENT '站点描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bty_webinfo`
--

LOCK TABLES `bty_webinfo` WRITE;
/*!40000 ALTER TABLE `bty_webinfo` DISABLE KEYS */;
INSERT INTO `bty_webinfo` VALUES (1,'bty.yum6.cn','Bty云','宝塔,宝塔分销,分销中心,宝塔IDC,BTY','全国首个PHP宝塔IDC分销系统');
/*!40000 ALTER TABLE `bty_webinfo` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-03-17 15:46:49
