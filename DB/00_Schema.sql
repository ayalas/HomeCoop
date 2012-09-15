CREATE DATABASE  IF NOT EXISTS `HomeCoop` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `HomeCoop`;
-- MySQL dump 10.13  Distrib 5.5.24, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: HomeCoop
-- ------------------------------------------------------
-- Server version	5.5.24-0ubuntu0.12.04.1

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
-- Table structure for table `Tlng_String`
--

DROP TABLE IF EXISTS `Tlng_String`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Tlng_String` (
  `KeyID` bigint(20) unsigned NOT NULL,
  `LangID` tinyint(3) unsigned NOT NULL,
  `sString` varchar(3000) NOT NULL,
  PRIMARY KEY (`KeyID`,`LangID`),
  KEY `indLangID` (`LangID`) USING BTREE,
  CONSTRAINT `fk_KeyID` FOREIGN KEY (`KeyID`) REFERENCES `T_Key` (`KeyID`) ON DELETE CASCADE,
  CONSTRAINT `fk_LangID` FOREIGN KEY (`LangID`) REFERENCES `Tlng_Language` (`LangID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_PermissionArea`
--

DROP TABLE IF EXISTS `T_PermissionArea`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_PermissionArea` (
  `PermissionAreaKeyID` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`PermissionAreaKeyID`),
  CONSTRAINT `fkPermissionAreaKeyID` FOREIGN KEY (`PermissionAreaKeyID`) REFERENCES `T_Key` (`KeyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_Role`
--

DROP TABLE IF EXISTS `T_Role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_Role` (
  `RoleKeyID` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`RoleKeyID`),
  CONSTRAINT `fkRoleKeyID` FOREIGN KEY (`RoleKeyID`) REFERENCES `T_Key` (`KeyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_CoopOrderProduct`
--

DROP TABLE IF EXISTS `T_CoopOrderProduct`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_CoopOrderProduct` (
  `CoopOrderKeyID` bigint(20) unsigned NOT NULL,
  `ProductKeyID` bigint(20) unsigned NOT NULL,
  `mProducerPrice` decimal(10,2) unsigned NOT NULL,
  `mCoopPrice` decimal(10,2) unsigned NOT NULL,
  `fMaxUserOrder` float unsigned DEFAULT NULL,
  `fMaxCoopOrder` float unsigned DEFAULT NULL,
  `fBurden` float unsigned DEFAULT NULL,
  `fTotalCoopOrder` float unsigned DEFAULT NULL,
  `nJoinedStatus` tinyint(4) NOT NULL,
  `mProducerTotal` decimal(10,2) unsigned DEFAULT NULL,
  `mCoopTotal` decimal(10,2) unsigned DEFAULT NULL,
  PRIMARY KEY (`CoopOrderKeyID`,`ProductKeyID`),
  KEY `indCOPProductKeyID` (`ProductKeyID`) USING BTREE,
  CONSTRAINT `fkCOPCoopOrderKeyID` FOREIGN KEY (`CoopOrderKeyID`) REFERENCES `T_CoopOrder` (`CoopOrderKeyID`) ON DELETE CASCADE,
  CONSTRAINT `fkCOPProductKeyID` FOREIGN KEY (`ProductKeyID`) REFERENCES `T_Product` (`ProductKeyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_MemberRole`
--

DROP TABLE IF EXISTS `T_MemberRole`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_MemberRole` (
  `MemberID` bigint(20) unsigned NOT NULL,
  `RoleKeyID` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`MemberID`,`RoleKeyID`),
  KEY `fk_RoleKeyID` (`RoleKeyID`),
  CONSTRAINT `fkMemberID` FOREIGN KEY (`MemberID`) REFERENCES `T_Member` (`MemberID`) ON DELETE CASCADE,
  CONSTRAINT `fk_RoleKeyID` FOREIGN KEY (`RoleKeyID`) REFERENCES `T_Role` (`RoleKeyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_PaymentMethod`
--

DROP TABLE IF EXISTS `T_PaymentMethod`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_PaymentMethod` (
  `PaymentMethodKeyID` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`PaymentMethodKeyID`),
  CONSTRAINT `fk_PaymentMethodKeyID` FOREIGN KEY (`PaymentMethodKeyID`) REFERENCES `T_Key` (`KeyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_Measure`
--

DROP TABLE IF EXISTS `T_Measure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_Measure` (
  `MeasureKeyID` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`MeasureKeyID`),
  CONSTRAINT `fk_MeasureKeyID` FOREIGN KEY (`MeasureKeyID`) REFERENCES `T_Key` (`KeyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_Product`
--

DROP TABLE IF EXISTS `T_Product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_Product` (
  `ProductKeyID` bigint(20) unsigned NOT NULL,
  `ProducerKeyID` bigint(20) unsigned NOT NULL,
  `SpecStringKeyID` bigint(20) unsigned NOT NULL,
  `UnitKeyID` bigint(20) unsigned NOT NULL,
  `fQuantity` float unsigned NOT NULL DEFAULT '1',
  `fPackageSize` float unsigned DEFAULT NULL,
  `fUnitInterval` float unsigned DEFAULT NULL,
  `fMaxUserOrder` float unsigned DEFAULT NULL,
  `bDisabled` tinyint(1) NOT NULL DEFAULT '0',
  `mProducerPrice` decimal(10,2) unsigned NOT NULL,
  `mCoopPrice` decimal(10,2) unsigned NOT NULL,
  `fBurden` float unsigned NOT NULL DEFAULT '1',
  `sImage1FileName` varchar(128) DEFAULT NULL,
  `nItems` int(10) unsigned NOT NULL DEFAULT '1',
  `ItemUnitKeyID` bigint(20) unsigned DEFAULT NULL,
  `fItemQuantity` float unsigned DEFAULT NULL,
  `nSortOrder` int(11) DEFAULT NULL,
  `JoinToProductKeyID` bigint(20) unsigned DEFAULT NULL,
  `sImage2FileName` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`ProductKeyID`),
  UNIQUE KEY `indSpecStringKeyID` (`SpecStringKeyID`),
  KEY `indProducerKeyID` (`ProducerKeyID`),
  KEY `indUnit` (`UnitKeyID`) USING BTREE,
  KEY `indItemUnit` (`ItemUnitKeyID`) USING BTREE,
  KEY `indSort` (`nSortOrder`),
  KEY `indJoinToProductKeyID` (`JoinToProductKeyID`),
  CONSTRAINT `fj_JoinToProductKeyID` FOREIGN KEY (`JoinToProductKeyID`) REFERENCES `T_Product` (`ProductKeyID`) ON DELETE SET NULL,
  CONSTRAINT `fkItemUnit` FOREIGN KEY (`ItemUnitKeyID`) REFERENCES `T_Unit` (`UnitKeyID`),
  CONSTRAINT `fkProducerKeyID2` FOREIGN KEY (`ProducerKeyID`) REFERENCES `T_Producer` (`ProducerKeyID`),
  CONSTRAINT `fkProductKeyID` FOREIGN KEY (`ProductKeyID`) REFERENCES `T_Key` (`KeyID`),
  CONSTRAINT `fkUnit` FOREIGN KEY (`UnitKeyID`) REFERENCES `T_Unit` (`UnitKeyID`),
  CONSTRAINT `fk_SpecStringKeyID` FOREIGN KEY (`SpecStringKeyID`) REFERENCES `T_Key` (`KeyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_CoopOrderPickupLocationProducer`
--

DROP TABLE IF EXISTS `T_CoopOrderPickupLocationProducer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_CoopOrderPickupLocationProducer` (
  `CoopOrderKeyID` bigint(20) unsigned NOT NULL,
  `ProducerKeyID` bigint(20) unsigned NOT NULL,
  `PickupLocationKeyID` bigint(20) unsigned NOT NULL,
  `mProducerTotal` decimal(10,2) unsigned DEFAULT '0.00',
  `mCoopTotal` decimal(10,2) unsigned DEFAULT '0.00',
  PRIMARY KEY (`CoopOrderKeyID`,`ProducerKeyID`,`PickupLocationKeyID`),
  KEY `indCOPLPR_PickupLocation` (`PickupLocationKeyID`,`CoopOrderKeyID`),
  CONSTRAINT `fkCOPLPR_PickupLocation` FOREIGN KEY (`PickupLocationKeyID`) REFERENCES `T_PickupLocation` (`PickupLocationKeyID`),
  CONSTRAINT `fkCOPLPR_Producer` FOREIGN KEY (`CoopOrderKeyID`, `ProducerKeyID`) REFERENCES `T_CoopOrderProducer` (`CoopOrderKeyID`, `ProducerKeyID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_Order`
--

DROP TABLE IF EXISTS `T_Order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_Order` (
  `OrderID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `CoopOrderKeyID` bigint(20) unsigned NOT NULL,
  `MemberID` bigint(20) unsigned NOT NULL,
  `PickupLocationKeyID` bigint(20) unsigned DEFAULT NULL,
  `dCreated` datetime NOT NULL,
  `dModified` datetime NOT NULL,
  `mCoopTotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `CreatedByMemberID` bigint(20) unsigned NOT NULL,
  `ModifiedByMemberID` bigint(20) unsigned NOT NULL,
  `mProducerTotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `sMemberComments` varchar(250) DEFAULT NULL,
  `mCoopFee` decimal(10,2) DEFAULT NULL,
  `bHasItemComments` tinyint(4) NOT NULL DEFAULT '0',
  `fBurden` float unsigned DEFAULT NULL,
  `mCoopTotalIncFee` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`OrderID`),
  UNIQUE KEY `indCoopOrderMember` (`CoopOrderKeyID`,`MemberID`),
  KEY `indMemberID` (`MemberID`),
  KEY `indCreatedByMemberID` (`CreatedByMemberID`),
  KEY `indModifiedByMemberID` (`ModifiedByMemberID`),
  KEY `indCreated` (`dCreated`),
  KEY `indPickupLocationKeyID` (`PickupLocationKeyID`,`CoopOrderKeyID`),
  KEY `indCOPL` (`CoopOrderKeyID`,`PickupLocationKeyID`),
  KEY `fkOrderPL` (`PickupLocationKeyID`),
  CONSTRAINT `fkCreatedByOrder` FOREIGN KEY (`CreatedByMemberID`) REFERENCES `T_Member` (`MemberID`),
  CONSTRAINT `fkMemberOrder` FOREIGN KEY (`MemberID`) REFERENCES `T_Member` (`MemberID`),
  CONSTRAINT `fkModifiedByOrder` FOREIGN KEY (`ModifiedByMemberID`) REFERENCES `T_Member` (`MemberID`),
  CONSTRAINT `fkOrderCoopOrder` FOREIGN KEY (`CoopOrderKeyID`) REFERENCES `T_CoopOrder` (`CoopOrderKeyID`) ON DELETE CASCADE,
  CONSTRAINT `fkOrderPL` FOREIGN KEY (`PickupLocationKeyID`) REFERENCES `T_PickupLocation` (`PickupLocationKeyID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_CoopOrder`
--

DROP TABLE IF EXISTS `T_CoopOrder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_CoopOrder` (
  `CoopOrderKeyID` bigint(20) unsigned NOT NULL,
  `dStart` datetime NOT NULL,
  `dEnd` datetime NOT NULL,
  `dDelivery` datetime NOT NULL,
  `mCoopFee` decimal(10,2) NOT NULL,
  `mSmallOrder` decimal(10,2) NOT NULL,
  `mSmallOrderCoopFee` decimal(10,2) NOT NULL,
  `fCoopFee` float NOT NULL,
  `ModifiedByMemberID` bigint(20) unsigned NOT NULL,
  `nStatus` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `CoordinatingGroupID` bigint(20) unsigned DEFAULT NULL,
  `mMaxCoopTotal` decimal(10,2) unsigned DEFAULT NULL,
  `fMaxBurden` float unsigned DEFAULT NULL,
  `fBurden` float unsigned DEFAULT NULL,
  `mCoopTotal` decimal(10,2) DEFAULT '0.00',
  `mProducerTotal` decimal(10,2) DEFAULT '0.00',
  `mTotalDelivery` decimal(10,2) DEFAULT '0.00',
  `bHasJoinedProducts` tinyint(4) NOT NULL,
  PRIMARY KEY (`CoopOrderKeyID`),
  KEY `indModifiedByMemberID` (`ModifiedByMemberID`),
  KEY `indCoordinatingGroupID` (`CoordinatingGroupID`) USING BTREE,
  KEY `indStatus` (`nStatus`,`dDelivery`),
  CONSTRAINT `fkCOCoordinatingGroupID` FOREIGN KEY (`CoordinatingGroupID`) REFERENCES `T_CoordinatingGroup` (`CoordinatingGroupID`) ON DELETE SET NULL,
  CONSTRAINT `fkCoopOrderKeyID` FOREIGN KEY (`CoopOrderKeyID`) REFERENCES `T_Key` (`KeyID`),
  CONSTRAINT `fkModifiedByMemberID` FOREIGN KEY (`ModifiedByMemberID`) REFERENCES `T_Member` (`MemberID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_CoordinatingGroupMember`
--

DROP TABLE IF EXISTS `T_CoordinatingGroupMember`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_CoordinatingGroupMember` (
  `CoordinatingGroupID` bigint(20) unsigned NOT NULL,
  `MemberID` bigint(20) unsigned NOT NULL,
  `bContactPerson` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`CoordinatingGroupID`,`MemberID`),
  KEY `indCGMMemberID` (`MemberID`) USING BTREE,
  CONSTRAINT `fkCGMMemberID` FOREIGN KEY (`MemberID`) REFERENCES `T_Member` (`MemberID`) ON DELETE CASCADE,
  CONSTRAINT `fkCoordinatingGroupID` FOREIGN KEY (`CoordinatingGroupID`) REFERENCES `T_CoordinatingGroup` (`CoordinatingGroupID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_CoopOrderPickupLocationProduct`
--

DROP TABLE IF EXISTS `T_CoopOrderPickupLocationProduct`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_CoopOrderPickupLocationProduct` (
  `CoopOrderKeyID` bigint(20) unsigned NOT NULL,
  `ProductKeyID` bigint(20) unsigned NOT NULL,
  `PickupLocationKeyID` bigint(20) unsigned NOT NULL,
  `mProducerTotal` decimal(10,2) unsigned DEFAULT '0.00',
  `mCoopTotal` decimal(10,2) unsigned DEFAULT '0.00',
  `fTotalCoopOrder` float unsigned DEFAULT '0',
  PRIMARY KEY (`CoopOrderKeyID`,`ProductKeyID`,`PickupLocationKeyID`),
  KEY `indCOPLPRD_PickupLocation` (`PickupLocationKeyID`,`CoopOrderKeyID`),
  CONSTRAINT `fkCOPLPRD_PickupLocation` FOREIGN KEY (`PickupLocationKeyID`) REFERENCES `T_PickupLocation` (`PickupLocationKeyID`),
  CONSTRAINT `fkCOPLPRD_Product` FOREIGN KEY (`CoopOrderKeyID`, `ProductKeyID`) REFERENCES `T_CoopOrderProduct` (`CoopOrderKeyID`, `ProductKeyID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_PermissionType`
--

DROP TABLE IF EXISTS `T_PermissionType`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_PermissionType` (
  `PermissionTypeKeyID` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`PermissionTypeKeyID`),
  CONSTRAINT `fkPermissionTypeKeyID` FOREIGN KEY (`PermissionTypeKeyID`) REFERENCES `T_Key` (`KeyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_Producer`
--

DROP TABLE IF EXISTS `T_Producer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_Producer` (
  `ProducerKeyID` bigint(20) unsigned NOT NULL,
  `bDisabled` tinyint(1) NOT NULL DEFAULT '0',
  `CoordinatingGroupID` bigint(20) unsigned DEFAULT NULL,
  `sExportFileName` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`ProducerKeyID`),
  KEY `indCoordinatingGroupID` (`CoordinatingGroupID`) USING BTREE,
  CONSTRAINT `fkPCoordinatingGroupID` FOREIGN KEY (`CoordinatingGroupID`) REFERENCES `T_CoordinatingGroup` (`CoordinatingGroupID`) ON DELETE SET NULL,
  CONSTRAINT `fkProducerKeyID` FOREIGN KEY (`ProducerKeyID`) REFERENCES `T_Key` (`KeyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_Unit`
--

DROP TABLE IF EXISTS `T_Unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_Unit` (
  `UnitKeyID` bigint(20) unsigned NOT NULL,
  `MeasureKeyID` bigint(20) unsigned NOT NULL,
  `nFloatingPoint` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `UnitAbbreviationStringKeyID` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`UnitKeyID`),
  KEY `indMeasure` (`MeasureKeyID`) USING BTREE,
  CONSTRAINT `fkUnitMeasure` FOREIGN KEY (`MeasureKeyID`) REFERENCES `T_Measure` (`MeasureKeyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_Permission`
--

DROP TABLE IF EXISTS `T_Permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_Permission` (
  `PermissionAreaKeyID` bigint(20) unsigned NOT NULL,
  `PermissionTypeKeyID` bigint(20) unsigned NOT NULL,
  `nAllowedScopeCodes` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`PermissionAreaKeyID`,`PermissionTypeKeyID`) USING BTREE,
  KEY `indPermissionTypeKeyID` (`PermissionTypeKeyID`) USING BTREE,
  CONSTRAINT `fkPPermissionAreaKeyID` FOREIGN KEY (`PermissionAreaKeyID`) REFERENCES `T_PermissionArea` (`PermissionAreaKeyID`),
  CONSTRAINT `fkPPermissionTypeKeyID` FOREIGN KEY (`PermissionTypeKeyID`) REFERENCES `T_PermissionType` (`PermissionTypeKeyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_PickupLocation`
--

DROP TABLE IF EXISTS `T_PickupLocation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_PickupLocation` (
  `PickupLocationKeyID` bigint(20) unsigned NOT NULL,
  `AddressStringKeyID` bigint(20) unsigned NOT NULL,
  `PublishedCommentsStringKeyID` bigint(20) unsigned DEFAULT NULL,
  `AdminCommentsStringKeyID` bigint(20) unsigned DEFAULT NULL,
  `bDisabled` tinyint(1) NOT NULL DEFAULT '0',
  `CoordinatingGroupID` bigint(20) unsigned DEFAULT NULL,
  `fMaxBurden` float unsigned DEFAULT NULL,
  `nRotationOrder` int(11) DEFAULT NULL,
  `sExportFileName` varchar(40) DEFAULT NULL,
  `mCachier` decimal(10,2) DEFAULT NULL,
  `mPrevCachier` decimal(10,2) DEFAULT NULL,
  `dCachierUpdate` datetime DEFAULT NULL,
  PRIMARY KEY (`PickupLocationKeyID`),
  UNIQUE KEY `indAddressStringKeyID` (`AddressStringKeyID`),
  KEY `indPublishedCommentsStringKeyID` (`PublishedCommentsStringKeyID`),
  KEY `indAdminCommentsStringKeyID` (`AdminCommentsStringKeyID`),
  KEY `indCoordinatingGroupID` (`CoordinatingGroupID`) USING BTREE,
  KEY `indRotationOrder` (`nRotationOrder`),
  CONSTRAINT `fkAddressStringKeyID` FOREIGN KEY (`AddressStringKeyID`) REFERENCES `T_Key` (`KeyID`),
  CONSTRAINT `fkAdminCommentsStringKeyID` FOREIGN KEY (`AdminCommentsStringKeyID`) REFERENCES `T_Key` (`KeyID`),
  CONSTRAINT `fkPickupLocationKeyID` FOREIGN KEY (`PickupLocationKeyID`) REFERENCES `T_Key` (`KeyID`),
  CONSTRAINT `fkPLCoordinatingGroupID` FOREIGN KEY (`CoordinatingGroupID`) REFERENCES `T_CoordinatingGroup` (`CoordinatingGroupID`) ON DELETE SET NULL,
  CONSTRAINT `fkPublishedCommentsStringKeyID` FOREIGN KEY (`PublishedCommentsStringKeyID`) REFERENCES `T_Key` (`KeyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_OrderItem`
--

DROP TABLE IF EXISTS `T_OrderItem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_OrderItem` (
  `OrderItemID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `OrderID` bigint(20) unsigned NOT NULL,
  `ProductKeyID` bigint(20) unsigned NOT NULL,
  `fQuantity` float NOT NULL,
  `mCoopPrice` decimal(10,2) unsigned NOT NULL,
  `mProducerPrice` decimal(10,2) unsigned NOT NULL,
  `fOriginalQuantity` float unsigned DEFAULT NULL,
  `fMaxFixQuantityAddition` float unsigned DEFAULT NULL,
  `sMemberComments` varchar(100) DEFAULT NULL,
  `fUnjoinedQuantity` float DEFAULT NULL,
  `nJoinedItems` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`OrderItemID`),
  UNIQUE KEY `indOrderID` (`OrderID`,`ProductKeyID`),
  KEY `indProductKeyID` (`ProductKeyID`),
  CONSTRAINT `fkOrderOrderItem` FOREIGN KEY (`OrderID`) REFERENCES `T_Order` (`OrderID`) ON DELETE CASCADE,
  CONSTRAINT `fkProductOrderItem` FOREIGN KEY (`ProductKeyID`) REFERENCES `T_Product` (`ProductKeyID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_Key`
--

DROP TABLE IF EXISTS `T_Key`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_Key` (
  `KeyID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sStringKey` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`KeyID`),
  UNIQUE KEY `ind_sStringKey` (`sStringKey`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_RolePermission`
--

DROP TABLE IF EXISTS `T_RolePermission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_RolePermission` (
  `RoleKeyID` bigint(20) unsigned NOT NULL,
  `PermissionAreaKeyID` bigint(20) unsigned NOT NULL,
  `PermissionTypeKeyID` bigint(20) unsigned NOT NULL,
  `PermissionScopeKeyID` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`RoleKeyID`,`PermissionAreaKeyID`,`PermissionTypeKeyID`) USING BTREE,
  KEY `indPermission` (`PermissionAreaKeyID`,`PermissionTypeKeyID`) USING BTREE,
  KEY `indScope` (`PermissionScopeKeyID`) USING BTREE,
  CONSTRAINT `fkRPPermission` FOREIGN KEY (`PermissionAreaKeyID`, `PermissionTypeKeyID`) REFERENCES `T_Permission` (`PermissionAreaKeyID`, `PermissionTypeKeyID`),
  CONSTRAINT `fkScope` FOREIGN KEY (`PermissionScopeKeyID`) REFERENCES `T_PermissionScope` (`PermissionScopeKeyID`),
  CONSTRAINT `fk_Role` FOREIGN KEY (`RoleKeyID`) REFERENCES `T_Role` (`RoleKeyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_PermissionScope`
--

DROP TABLE IF EXISTS `T_PermissionScope`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_PermissionScope` (
  `PermissionScopeKeyID` bigint(20) unsigned NOT NULL,
  `nScopeCode` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`PermissionScopeKeyID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_CoopOrderProducer`
--

DROP TABLE IF EXISTS `T_CoopOrderProducer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_CoopOrderProducer` (
  `CoopOrderKeyID` bigint(20) unsigned NOT NULL,
  `ProducerKeyID` bigint(20) unsigned NOT NULL,
  `mMaxProducerOrder` decimal(10,2) unsigned DEFAULT NULL,
  `mProducerTotal` decimal(10,2) DEFAULT '0.00',
  `fDelivery` float DEFAULT NULL,
  `mDelivery` decimal(10,2) DEFAULT NULL,
  `mMinDelivery` decimal(10,2) DEFAULT NULL,
  `mMaxDelivery` decimal(10,2) DEFAULT NULL,
  `mTotalDelivery` decimal(10,2) DEFAULT '0.00',
  `fBurden` float unsigned DEFAULT NULL,
  `fMaxBurden` float unsigned DEFAULT NULL,
  `mCoopTotal` decimal(10,2) unsigned DEFAULT NULL,
  PRIMARY KEY (`CoopOrderKeyID`,`ProducerKeyID`),
  KEY `fkProducer` (`ProducerKeyID`),
  CONSTRAINT `fkCoopOrder` FOREIGN KEY (`CoopOrderKeyID`) REFERENCES `T_CoopOrder` (`CoopOrderKeyID`) ON DELETE CASCADE,
  CONSTRAINT `fkProducer` FOREIGN KEY (`ProducerKeyID`) REFERENCES `T_Producer` (`ProducerKeyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_CoordinatingGroup`
--

DROP TABLE IF EXISTS `T_CoordinatingGroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_CoordinatingGroup` (
  `CoordinatingGroupID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sCoordinatingGroup` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`CoordinatingGroupID`),
  UNIQUE KEY `indCoordinatingGroup` (`sCoordinatingGroup`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Tlng_Language`
--

DROP TABLE IF EXISTS `Tlng_Language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Tlng_Language` (
  `LangID` tinyint(3) unsigned NOT NULL,
  `sLanguage` varchar(100) NOT NULL,
  `bActive` tinyint(1) NOT NULL DEFAULT '1',
  `bRequired` tinyint(1) NOT NULL DEFAULT '1',
  `FallingLangID` tinyint(3) unsigned DEFAULT NULL,
  `sPhpFolder` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`LangID`),
  UNIQUE KEY `ind_sLanguage` (`sLanguage`),
  UNIQUE KEY `indPhpFolder` (`sPhpFolder`),
  KEY `ind_FallingLangID` (`FallingLangID`),
  CONSTRAINT `fk_FallingLangID` FOREIGN KEY (`FallingLangID`) REFERENCES `Tlng_Language` (`LangID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_CoopOrderPickupLocation`
--

DROP TABLE IF EXISTS `T_CoopOrderPickupLocation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_CoopOrderPickupLocation` (
  `CoopOrderKeyID` bigint(20) unsigned NOT NULL,
  `PickupLocationKeyID` bigint(20) unsigned NOT NULL,
  `fMaxBurden` float unsigned DEFAULT NULL,
  `fBurden` float unsigned DEFAULT NULL,
  `mMaxCoopTotal` decimal(10,2) unsigned DEFAULT NULL,
  `mCoopTotal` decimal(10,2) unsigned DEFAULT NULL,
  PRIMARY KEY (`CoopOrderKeyID`,`PickupLocationKeyID`),
  KEY `fkPickupLocationKeyID2` (`PickupLocationKeyID`),
  CONSTRAINT `fkCoopOrder2` FOREIGN KEY (`CoopOrderKeyID`) REFERENCES `T_CoopOrder` (`CoopOrderKeyID`) ON DELETE CASCADE,
  CONSTRAINT `fkPickupLocationKeyID2` FOREIGN KEY (`PickupLocationKeyID`) REFERENCES `T_PickupLocation` (`PickupLocationKeyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `T_Member`
--

DROP TABLE IF EXISTS `T_Member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `T_Member` (
  `MemberID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sLoginName` varchar(50) NOT NULL,
  `sPassword` varchar(200) NOT NULL,
  `sEMail` varchar(100) NOT NULL,
  `PaymentMethodKeyID` bigint(20) unsigned NOT NULL,
  `dJoined` datetime NOT NULL,
  `mBalance` decimal(10,2) DEFAULT NULL,
  `sName` varchar(100) NOT NULL,
  `fPercentOverBalance` float unsigned DEFAULT NULL,
  `sEMail2` varchar(100) DEFAULT NULL,
  `sEMail3` varchar(100) DEFAULT NULL,
  `sEMail4` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`MemberID`),
  UNIQUE KEY `ind_sLoginName` (`sLoginName`),
  UNIQUE KEY `indName` (`sName`),
  KEY `ind_PaymentMethodKeyID` (`PaymentMethodKeyID`),
  KEY `indEMail` (`sEMail`),
  KEY `indEMail2` (`sEMail2`),
  KEY `indEMail3` (`sEMail3`),
  KEY `indEMail4` (`sEMail4`),
  CONSTRAINT `fkPaymentMethodKeyID` FOREIGN KEY (`PaymentMethodKeyID`) REFERENCES `T_PaymentMethod` (`PaymentMethodKeyID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-09-14 17:57:29
