-- POOSD PlayerFinder (TF2-themed matchmaking) - Database Schema
-- Creates the PlayerFinderDB database, all app tables, and the app's DB user.

CREATE DATABASE IF NOT EXISTS `PlayerFinderDB`;
USE `PlayerFinderDB`;

-- Users table: a player's account + matchmaking preferences
CREATE TABLE IF NOT EXISTS `Users` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `FirstName` VARCHAR(50) NOT NULL,
  `LastName` VARCHAR(50) NOT NULL,
  `Username` VARCHAR(50) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,       -- 255 wide in case API hashes it (bcrypt etc.)
  `DisplayName` VARCHAR(50),
  `SteamUsername` VARCHAR(100),
  `Platform` ENUM('PC', 'Steam Deck', 'Other') DEFAULT 'PC',
  `TeamPreference` ENUM('RED', 'BLU', 'Either') DEFAULT 'Either',
  `PreferredRole` ENUM('Offense', 'Defense', 'Support', 'Flexible') DEFAULT 'Flexible',
  `PreferredGameMode` ENUM('Casual', 'Competitive', 'Mann vs Machine', 'Community Servers', 'Any') DEFAULT 'Any',
  `SkillLevel` ENUM('Beginner', 'Intermediate', 'Experienced', 'Competitive') DEFAULT 'Beginner',
  `Bio` VARCHAR(255),
  `LookingForTeam` BOOLEAN DEFAULT TRUE,
  `DateCreated` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `DateUpdated` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE INDEX `idx_users_username` (`Username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contacts table: other players a user has added as a contact (1:M Users -> Contacts)
CREATE TABLE IF NOT EXISTS `Contacts` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `FirstName` VARCHAR(50) NOT NULL,
  `LastName` VARCHAR(50) NOT NULL,
  `EmailAddress` VARCHAR(100),
  `PhoneNumber` VARCHAR(30),
  `PlayerUsername` VARCHAR(100),
  `MainClass` ENUM('Scout', 'Soldier', 'Pyro', 'Demoman', 'Heavy', 'Engineer', 'Medic', 'Sniper', 'Spy'),
  `SecondaryClass` ENUM('Scout', 'Soldier', 'Pyro', 'Demoman', 'Heavy', 'Engineer', 'Medic', 'Sniper', 'Spy'),
  `PreferredRole` ENUM('Offense', 'Defense', 'Support', 'Flexible'),
  `SkillLevel` ENUM('Beginner', 'Intermediate', 'Experienced', 'Competitive'),
  `PreferredGameMode` ENUM('Casual', 'Competitive', 'Mann vs Machine', 'Community Servers', 'Any'),
  `TeamPreference` ENUM('RED', 'BLU', 'Either'),
  `Platform` ENUM('PC', 'Steam Deck', 'Other') DEFAULT 'PC',
  `Notes` VARCHAR(255),
  `UserID` INT NOT NULL,
  `DateCreated` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `DateUpdated` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  INDEX `idx_contacts_userid` (`UserID`),
  CONSTRAINT `fk_contacts_userid` FOREIGN KEY (`UserID`) REFERENCES `Users`(`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- UserClasses table: which TF2 classes a user plays, and how they rank them
CREATE TABLE IF NOT EXISTS `UserClasses` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `UserID` INT NOT NULL,
  `ClassName` ENUM('Scout', 'Soldier', 'Pyro', 'Demoman', 'Heavy', 'Engineer', 'Medic', 'Sniper', 'Spy') NOT NULL,
  `ClassSkillLevel` ENUM('Beginner', 'Intermediate', 'Experienced', 'Main'),
  `IsMain` BOOLEAN DEFAULT FALSE,
  `PreferenceLevel` TINYINT DEFAULT 3,
  PRIMARY KEY (`ID`),
  INDEX `idx_userclasses_userid` (`UserID`),
  CONSTRAINT `fk_userclasses_userid` FOREIGN KEY (`UserID`) REFERENCES `Users`(`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Availability table: recurring windows during the week when a user likes to play
CREATE TABLE IF NOT EXISTS `Availability` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `UserID` INT NOT NULL,
  `DayOfWeek` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
  `StartTime` TIME,
  `EndTime` TIME,
  `TimeZone` VARCHAR(20),
  PRIMARY KEY (`ID`),
  INDEX `idx_availability_userid` (`UserID`),
  CONSTRAINT `fk_availability_userid` FOREIGN KEY (`UserID`) REFERENCES `Users`(`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Matches table: suggested/accepted pairings between two users
CREATE TABLE IF NOT EXISTS `Matches` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `User1ID` INT NOT NULL,
  `User2ID` INT NOT NULL,
  `CompatibilityScore` DECIMAL(5,2),
  `MatchReason` VARCHAR(255),
  `MatchStatus` ENUM('Suggested', 'Accepted', 'Declined', 'Connected') DEFAULT 'Suggested',
  `DateCreated` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `DateUpdated` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  INDEX `idx_matches_user1id` (`User1ID`),
  INDEX `idx_matches_user2id` (`User2ID`),
  CONSTRAINT `fk_matches_user1id` FOREIGN KEY (`User1ID`) REFERENCES `Users`(`ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_matches_user2id` FOREIGN KEY (`User2ID`) REFERENCES `Users`(`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- UserItems table: loadout items a user likes to run, per class
CREATE TABLE IF NOT EXISTS `UserItems` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `UserID` INT NOT NULL,
  `ClassName` ENUM('Scout', 'Soldier', 'Pyro', 'Demoman', 'Heavy', 'Engineer', 'Medic', 'Sniper', 'Spy'),
  `ItemName` VARCHAR(100) NOT NULL,
  `ItemSlot` ENUM('Primary', 'Secondary', 'Melee', 'PDA', 'Other'),
  `IsPreferred` BOOLEAN DEFAULT FALSE,
  PRIMARY KEY (`ID`),
  INDEX `idx_useritems_userid` (`UserID`),
  CONSTRAINT `fk_useritems_userid` FOREIGN KEY (`UserID`) REFERENCES `Users`(`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PlayerStats table: aggregate stats used for skill/compatibility matching
CREATE TABLE IF NOT EXISTS `PlayerStats` (
  `ID` INT NOT NULL AUTO_INCREMENT,
  `UserID` INT NOT NULL,
  `TotalHoursPlayed` DECIMAL(8,2) DEFAULT 0,
  `MatchesPlayed` INT DEFAULT 0,
  `Wins` INT DEFAULT 0,
  `Losses` INT DEFAULT 0,
  `KDRatio` DECIMAL(6,2),
  `TeamworkRating` DECIMAL(5,2),
  `CommunicationPreference` ENUM('Voice', 'Text', 'Either', 'No Mic'),
  PRIMARY KEY (`ID`),
  UNIQUE INDEX `idx_playerstats_userid` (`UserID`),
  CONSTRAINT `fk_playerstats_userid` FOREIGN KEY (`UserID`) REFERENCES `Users`(`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dedicated application user (never let the API connect as root)
-- CHANGE ME: pick your own password, don't use this example one in production
CREATE USER IF NOT EXISTS 'PlayerFinderUser'@'localhost' IDENTIFIED BY 'app_user';
GRANT ALL PRIVILEGES ON `PlayerFinderDB`.* TO 'PlayerFinderUser'@'localhost';
FLUSH PRIVILEGES;
