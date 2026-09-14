-- Seed data: gives you test data for the demo (they'll ask you to SELECT * FROM Users/etc.)
USE `PlayerFinderDB`;

INSERT INTO `Users` (
    `FirstName`, `LastName`, `Username`, `Password`, `DisplayName`, `SteamUsername`,
    `Platform`, `TeamPreference`, `PreferredRole`, `PreferredGameMode`, `SkillLevel`, `Bio`
)
VALUES (
    'Jane', 'Doe', 'UberMedic42', 'password123', 'PocketMedic', 'UberMedic42',
    'PC', 'BLU', 'Support', 'Casual', 'Experienced', 'Medic main looking for regular teammates'
),
(
    'Alex', 'Rivera', 'SoldierRivera', 'password123', 'RocketAlex', 'SoldierRivera',
    'PC', 'RED', 'Offense', 'Competitive', 'Competitive', 'Soldier main, always up for 6s'
);

-- A contact belonging to user 1 (UberMedic42)
INSERT INTO `Contacts` (
    `FirstName`, `LastName`, `EmailAddress`, `PhoneNumber`, `PlayerUsername`,
    `MainClass`, `SecondaryClass`, `PreferredRole`, `SkillLevel`, `PreferredGameMode`,
    `TeamPreference`, `Platform`, `Notes`, `UserID`
)
VALUES (
    'John', 'Smith', 'john@example.com', '4075551234', 'HeavyMain99',
    'Heavy', 'Soldier', 'Offense', 'Experienced', 'Casual',
    'RED', 'PC', 'Usually plays evenings', 1
);

INSERT INTO `UserClasses` (`UserID`, `ClassName`, `ClassSkillLevel`, `IsMain`, `PreferenceLevel`)
VALUES
(1, 'Medic', 'Main', TRUE, 5),
(1, 'Engineer', 'Experienced', FALSE, 4),
(2, 'Soldier', 'Main', TRUE, 5),
(2, 'Demoman', 'Experienced', FALSE, 3);

INSERT INTO `Availability` (`UserID`, `DayOfWeek`, `StartTime`, `EndTime`, `TimeZone`)
VALUES
(1, 'Monday', '18:00:00', '23:00:00', 'EST'),
(1, 'Wednesday', '18:00:00', '23:00:00', 'EST'),
(2, 'Friday', '20:00:00', '01:00:00', 'EST'),
(2, 'Saturday', '14:00:00', '20:00:00', 'EST');

INSERT INTO `PlayerStats` (
    `UserID`, `TotalHoursPlayed`, `MatchesPlayed`, `Wins`, `Losses`,
    `KDRatio`, `TeamworkRating`, `CommunicationPreference`
)
VALUES
(1, 750.5, 400, 250, 150, 1.25, 92.0, 'Voice'),
(2, 1200.0, 600, 340, 260, 1.80, 85.5, 'Voice');

INSERT INTO `UserItems` (`UserID`, `ClassName`, `ItemName`, `ItemSlot`, `IsPreferred`)
VALUES
(1, 'Medic', 'Kritzkrieg', 'Secondary', TRUE),
(1, 'Medic', 'Ubersaw', 'Melee', TRUE),
(2, 'Soldier', 'Original', 'Primary', TRUE),
(2, 'Soldier', 'Escape Plan', 'Melee', FALSE);

INSERT INTO `Matches` (`User1ID`, `User2ID`, `CompatibilityScore`, `MatchReason`, `MatchStatus`)
VALUES
(1, 2, 88.50, 'Overlapping evening availability and complementary Medic/Soldier roles', 'Suggested');
