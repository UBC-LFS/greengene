-- phpMyAdmin SQL Dump
-- version 2.6.1
-- http://www.phpmyadmin.net
--
-- Host: mysql.sourceforge.net
-- Generation Time: Apr 08, 2005 at 09:55 PM
-- Server version: 3.23.58
-- PHP Version: 4.3.10
--
-- Database: `yellowleaf`
--

-- --------------------------------------------------------

--
-- Table structure for table `Course`
--

CREATE TABLE `Course` (
  `CourseId` int(10) unsigned NOT NULL auto_increment,
  `Name` varchar(30) NOT NULL default '',
  `Description` varchar(250) default NULL,
  PRIMARY KEY  (`CourseId`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Table structure for table `Cross`
--

CREATE TABLE `Cross` (
  `CrossNum` int(10) unsigned NOT NULL default '0',
  `UserId` varchar(10) NOT NULL default '',
  `PollenCrossNum` int(10) unsigned NOT NULL default '0',
  `PollenPlantNum` int(10) NOT NULL default '0',
  `PollenGene` varchar(4) NOT NULL default '',
  `SeedCrossNum` int(10) unsigned NOT NULL default '0',
  `SeedPlantNum` int(10) NOT NULL default '0',
  `SeedGene` varchar(4) NOT NULL default '',
  `GeneSequences` varchar(200) default NULL,
  `CreationDate` timestamp NOT NULL,
  PRIMARY KEY  (`CrossNum`,`UserId`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Table structure for table `LongerGeneSequences`
--

CREATE TABLE `LongerGeneSequences` (
  `SequencesId` int(10) unsigned NOT NULL auto_increment,
  `UserId` varchar(10) NOT NULL default '',
  `CrossNum` int(10) unsigned NOT NULL default '0',
  `GeneSequences` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`SequencesId`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Table structure for table `MasterProblem`
--

CREATE TABLE `MasterProblem` (
  `ProblemId` int(10) NOT NULL auto_increment,
  `Description` varchar(250) default NULL,
  `Name` varchar(30) NOT NULL default '',
  `GMU1_2` float unsigned NOT NULL default '50',
  `GMU2_3` float unsigned NOT NULL default '50',
  `TraitOrder` char(3) NOT NULL default '123',
  `CourseId` int(10) NOT NULL default '0',
  `ModificationDate` timestamp NOT NULL,
  `EpistasisCode` tinyint(3) unsigned default NULL,
  `Trait1Name` varchar(20) NOT NULL default '',
  `Trait1AAPhenoName` varchar(20) NOT NULL default '',
  `Trait1AbPhenoName` varchar(20) NOT NULL default '',
  `Trait1bAPhenoName` varchar(20) NOT NULL default '',
  `Trait1bbPhenoName` varchar(20) NOT NULL default '',
  `Trait2Name` varchar(20) NOT NULL default '',
  `Trait2AAPhenoName` varchar(20) NOT NULL default '',
  `Trait2AbPhenoName` varchar(20) NOT NULL default '',
  `Trait2bAPhenoName` varchar(20) NOT NULL default '',
  `Trait2bbPhenoName` varchar(20) NOT NULL default '',
  `Trait3Name` varchar(20) NOT NULL default '',
  `Trait3AAPhenoName` varchar(20) NOT NULL default '',
  `Trait3AbPhenoName` varchar(20) NOT NULL default '',
  `Trait3bAPhenoName` varchar(20) NOT NULL default '',
  `Trait3bbPhenoName` varchar(20) NOT NULL default '',
  `ProgenyPerMating` int(10) unsigned NOT NULL default '50',
  `MaxProgeny` int(10) unsigned NOT NULL default '1000',
  PRIMARY KEY  (`ProblemId`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Table structure for table `Phenotype`
--

CREATE TABLE `Phenotype` (
  `PhenotypeId` int(10) unsigned NOT NULL auto_increment,
  `TraitId` int(10) unsigned NOT NULL default '0',
  `Name` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`PhenotypeId`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Table structure for table `StudentProblem`
--

CREATE TABLE `StudentProblem` (
  `UserId` varchar(10) NOT NULL default '',
  `MasterProblemId` int(10) NOT NULL default '0',
  `Modified` tinyint(1) NOT NULL default '0',
  `Description` varchar(250) default NULL,
  `Name` varchar(30) NOT NULL default '',
  `GMU1_2` float unsigned NOT NULL default '50',
  `GMU2_3` float unsigned NOT NULL default '50',
  `TraitOrder` char(3) NOT NULL default '123',
  `ModificationDate` timestamp NOT NULL,
  `EpistasisCode` tinyint(3) unsigned default NULL,
  `Trait1Name` varchar(20) NOT NULL default '',
  `Trait1AAPhenoName` varchar(20) NOT NULL default '',
  `Trait1AbPhenoName` varchar(20) NOT NULL default '',
  `Trait1bAPhenoName` varchar(20) NOT NULL default '',
  `Trait1bbPhenoName` varchar(20) NOT NULL default '',
  `Trait2Name` varchar(20) NOT NULL default '',
  `Trait2AAPhenoName` varchar(20) NOT NULL default '',
  `Trait2AbPhenoName` varchar(20) NOT NULL default '',
  `Trait2bAPhenoName` varchar(20) NOT NULL default '',
  `Trait2bbPhenoName` varchar(20) NOT NULL default '',
  `Trait3Name` varchar(20) NOT NULL default '',
  `Trait3AAPhenoName` varchar(20) NOT NULL default '',
  `Trait3AbPhenoName` varchar(20) NOT NULL default '',
  `Trait3bAPhenoName` varchar(20) NOT NULL default '',
  `Trait3bbPhenoName` varchar(20) NOT NULL default '',
  `ProgenyPerMating` int(10) unsigned NOT NULL default '50',
  `MaxProgeny` int(10) unsigned NOT NULL default '1000',
  `ProgenyGenerated` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`UserId`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Table structure for table `Trait`
--

CREATE TABLE `Trait` (
  `TraitId` int(10) unsigned NOT NULL auto_increment,
  `CourseId` int(10) unsigned NOT NULL default '0',
  `Name` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`TraitId`),
  FULLTEXT KEY `Name` (`Name`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `UserId` varchar(10) NOT NULL default '',
  `FirstName` varchar(20) default '',
  `LastName` varchar(20) default '',
  PRIMARY KEY  (`UserId`),
  KEY `LastName` (`LastName`)
) ENGINE=MyISAM;

CREATE TABLE User_Course (
    `id` int(10) PRIMARY KEY,
    `uid` varchar(10) NOT NULL,
    `cid` int(10) NOT NULL,
    `PrivilegeLvl` tinyint(3) unsigned default '3',
    FOREIGN KEY (`uid`) REFERENCES User(`UserId`),
    FOREIGN KEY (`cid`) REFERENCES Course(`CourseId`)
) ENGINE = MyISAM;
