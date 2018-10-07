-- MySQL dump 10.13  Distrib 5.5.53, for Win32 (AMD64)
--
-- Host: localhost    Database: laravel_shop
-- ------------------------------------------------------
-- Server version	5.5.53

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
-- Dumping data for table `admin_menu`
--

LOCK TABLES `admin_menu` WRITE;
/*!40000 ALTER TABLE `admin_menu` DISABLE KEYS */;
INSERT INTO `admin_menu` VALUES (1,0,1,'首页','fa-bar-chart','/',NULL,'2018-08-02 03:09:47'),(2,0,2,'权限管理','fa-tasks',NULL,NULL,'2018-08-02 03:10:05'),(3,2,3,'管理员','fa-users','auth/users',NULL,'2018-08-02 03:10:21'),(4,2,4,'角色','fa-user','auth/roles',NULL,'2018-08-02 03:10:32'),(5,2,5,'权限','fa-ban','auth/permissions',NULL,'2018-08-02 03:10:39'),(6,2,6,'菜单','fa-bars','auth/menu',NULL,'2018-08-02 03:10:45'),(7,2,7,'操作日志','fa-history','auth/logs',NULL,'2018-08-02 03:10:53'),(8,0,16,'会员管理','fa-user','/users','2018-08-02 02:45:37','2018-09-07 21:35:45'),(9,0,11,'商品管理','fa-cubes','/products','2018-08-02 02:47:05','2018-09-07 21:34:52'),(10,0,10,'优惠券管理','fa-cc-amex','/coupon_codes','2018-08-02 02:48:37','2018-09-07 21:34:52'),(11,0,9,'订单管理','fa-list-alt','/orders','2018-08-02 03:08:14','2018-09-07 21:34:52'),(12,9,15,'商品属性值管理','fa-bars','/attributes','2018-09-07 21:31:47','2018-09-07 21:35:45'),(13,9,13,'商品管理','fa-adjust','/products','2018-09-07 21:32:07','2018-09-07 21:35:45'),(14,9,14,'库存管理','fa-apple','/skus','2018-09-07 21:32:35','2018-09-07 21:35:45'),(15,0,8,'轮播图管理','fa-cc-discover','/banners','2018-09-07 21:34:17','2018-09-07 21:34:52'),(16,0,17,'站点管理','fa-bars','/web_infos','2018-09-07 21:34:31','2018-09-07 21:35:45'),(17,9,12,'商品分类','fa-500px','/categories','2018-09-07 21:35:25','2018-09-07 21:35:45'),(19,0,0,'众筹商品','fa-bars','/crowdfunding_products','2018-10-01 08:55:11','2018-10-01 08:55:11'),(20,0,0,'秒杀商品','fa-clock-o','/seckill_products','2018-10-04 13:42:19','2018-10-04 13:42:19');
/*!40000 ALTER TABLE `admin_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_permissions`
--

LOCK TABLES `admin_permissions` WRITE;
/*!40000 ALTER TABLE `admin_permissions` DISABLE KEYS */;
INSERT INTO `admin_permissions` VALUES (1,'All permission','*','','*',NULL,NULL),(2,'仪表盘','dashboard','GET','/',NULL,'2018-08-02 02:55:13'),(3,'登录','auth.login','','/auth/login\r\n/auth/logout',NULL,'2018-08-02 02:55:24'),(4,'个人信息','auth.setting','GET,PUT','/auth/setting',NULL,'2018-08-02 02:55:33'),(5,'权限管理','auth.management','','/auth/roles\r\n/auth/permissions\r\n/auth/menu\r\n/auth/logs',NULL,'2018-08-02 02:55:43'),(6,'商品管理','products','','/products*','2018-08-02 02:54:57','2018-08-02 02:54:57'),(7,'优惠券管理','coupon','','/coupon_codes*','2018-08-02 03:05:15','2018-08-02 03:05:15'),(8,'订单管理','orders','','/orders*','2018-08-02 03:05:37','2018-08-02 03:05:37'),(9,'会员管理','users','','/users*','2018-08-02 03:06:04','2018-08-02 03:06:04');
/*!40000 ALTER TABLE `admin_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_role_menu`
--

LOCK TABLES `admin_role_menu` WRITE;
/*!40000 ALTER TABLE `admin_role_menu` DISABLE KEYS */;
INSERT INTO `admin_role_menu` VALUES (1,2,NULL,NULL),(1,2,NULL,NULL);
/*!40000 ALTER TABLE `admin_role_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_role_permissions`
--

LOCK TABLES `admin_role_permissions` WRITE;
/*!40000 ALTER TABLE `admin_role_permissions` DISABLE KEYS */;
INSERT INTO `admin_role_permissions` VALUES (1,1,NULL,NULL),(2,2,NULL,NULL),(2,3,NULL,NULL),(2,4,NULL,NULL),(2,6,NULL,NULL),(2,7,NULL,NULL),(2,8,NULL,NULL),(2,9,NULL,NULL),(1,1,NULL,NULL),(2,2,NULL,NULL),(2,3,NULL,NULL),(2,4,NULL,NULL),(2,6,NULL,NULL),(2,7,NULL,NULL),(2,8,NULL,NULL),(2,9,NULL,NULL);
/*!40000 ALTER TABLE `admin_role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_role_users`
--

LOCK TABLES `admin_role_users` WRITE;
/*!40000 ALTER TABLE `admin_role_users` DISABLE KEYS */;
INSERT INTO `admin_role_users` VALUES (1,1,NULL,NULL),(2,2,NULL,NULL),(1,1,NULL,NULL),(2,2,NULL,NULL);
/*!40000 ALTER TABLE `admin_role_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_roles`
--

LOCK TABLES `admin_roles` WRITE;
/*!40000 ALTER TABLE `admin_roles` DISABLE KEYS */;
INSERT INTO `admin_roles` VALUES (1,'超级管理员','administrator','2018-07-28 10:13:23','2018-08-02 02:50:10'),(2,'运营','yunying','2018-08-02 02:51:29','2018-08-02 02:51:29');
/*!40000 ALTER TABLE `admin_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_user_permissions`
--

LOCK TABLES `admin_user_permissions` WRITE;
/*!40000 ALTER TABLE `admin_user_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_user_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` VALUES (1,'admin','$2y$10$MGosT826VpAtx6vajLN5iuEXlCpAsq.EkAeizk0k61xYTXmmNEK9C','易水','images/e85efba637e28f85a1cc06aee0aefc5b.jpg','5R2hC3tvsZ8R6mWPjXugOL44zoDiTmYfFaGJEi9zWQ5ZXSyWdUNgNQGrDIcE','2018-07-28 10:13:23','2018-08-02 02:51:57'),(2,'yunying','$2y$10$jgtt0lQhl8lWfNTLXy1v3eR/0SeSP9af42/DcMGYe/GvXC9H8D98e','运营大哥','images/21186511253decb1bb3b4cc6c637dc6c_big.jpg','X1GYf1EjP5DAxgKVUqb9hB1d3l64HtMIBkpL0BiEf0zd5iH02z2PpwrcKhEu','2018-08-02 02:52:56','2018-08-02 02:53:15');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-10-07  7:45:11
