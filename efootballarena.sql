CREATE DATABASE IF NOT EXISTS eFootballArena;
USE eFootballArena;

CREATE TABLE Players (
    Player_ID INT PRIMARY KEY AUTO_INCREMENT,
    Gamer_Tag VARCHAR(50) NOT NULL UNIQUE,
    Real_Name VARCHAR(100) NOT NULL,
    Country VARCHAR(50),
    Email VARCHAR(100) UNIQUE,
    Platform ENUM('PC','PS5','Mobile'),
    Favorite_Club VARCHAR(50),
    Preferred_Formation VARCHAR(20),
    Join_Date DATE
);

INSERT INTO Players
(Gamer_Tag, Real_Name, Country, Email, Platform, Favorite_Club, Preferred_Formation, Join_Date)
VALUES

('Ronaldo7', 'Cristiano Ronaldo', 'Portugal', 'cristiano@gmail.com', 'PC', 'Real Madrid', '4-3-3', '2026-07-01'),

('Leo10', 'Lionel Messi', 'Argentina', 'messi@gmail.com', 'PS5', 'Barcelona', '4-2-3-1', '2026-07-03'),

('Neymar11', 'Neymar Jr', 'Brazil', 'neymar@gmail.com', 'Mobile', 'PSG', '4-4-2', '2026-07-05'),

('Beckham17', 'David Beckham', 'England', 'david@gmail.com', 'Mobile', 'Manchester United', '4-4-2', '2026-07-05');

CREATE TABLE Tournaments (
    Tournament_ID INT PRIMARY KEY AUTO_INCREMENT,
    Tournament_Name VARCHAR(100) NOT NULL,
    Tournament_Type ENUM('Knockout','League','Group Stage'),
    Prize_Pool DECIMAL(10,2),
    Registration_Deadline DATE,
    Maximum_Players INT,
    Tournament_Status ENUM('Upcoming','Ongoing','Completed'),
    Start_Date DATE,
    End_Date DATE
);

INSERT INTO Tournaments
(Tournament_Name, Tournament_Type, Prize_Pool, Registration_Deadline,
Maximum_Players, Tournament_Status, Start_Date, End_Date)
VALUES
('FIFA Club World Cup', 'Knockout', 10000.00, '2026-08-01', 32, 'Upcoming', '2026-08-05', '2026-08-10'),
('Champions League', 'League', 25000.00, '2026-09-01', 20, 'Upcoming', '2026-09-05', '2026-09-25'),
('World eFootball Cup', 'Group Stage', 50000.00, '2026-10-01', 64, 'Upcoming', '2026-10-10', '2026-10-30');


CREATE TABLE Matches (
    Match_ID INT PRIMARY KEY AUTO_INCREMENT,
    Tournament_ID INT,
    Player1_ID INT,
    Player2_ID INT,
    Club_Player1 VARCHAR(50),
    Club_Player2 VARCHAR(50),
    Match_Date DATE,
    Match_Time TIME,
    Match_Round VARCHAR(30),
    Final_Score VARCHAR(10),
    Winner_ID INT,

    FOREIGN KEY (Tournament_ID) REFERENCES Tournaments(Tournament_ID),
    FOREIGN KEY (Player1_ID) REFERENCES Players(Player_ID),
    FOREIGN KEY (Player2_ID) REFERENCES Players(Player_ID),
    FOREIGN KEY (Winner_ID) REFERENCES Players(Player_ID)
);

INSERT INTO Matches
(Tournament_ID, Player1_ID, Player2_ID, Club_Player1, Club_Player2,
Match_Date, Match_Time, Match_Round, Final_Score, Winner_ID)
VALUES
(1, 1, 2, 'Real Madrid', 'Barcelona',
'2026-08-05', '18:00:00', 'Quarter Final', '3-1', 1),

(1, 3, 4, 'PSG', 'Manchester United',
'2026-08-05', '20:00:00', 'Quarter Final', '2-0', 3),

(2, 1, 3, 'Real Madrid', 'PSG',
'2026-09-08', '19:30:00', 'League Match', '2-2', NULL),

(3, 2, 4, 'Barcelona', 'Manchester United',
'2026-10-12', '21:00:00', 'Group Stage', '1-0', 2);

CREATE TABLE Match_Statistics (
    Statistics_ID INT PRIMARY KEY AUTO_INCREMENT,
    Match_ID INT UNIQUE,
    Goals INT,
    Possession_Percentage DECIMAL(5,2),
    Shots INT,
    Shots_On_Target INT,
    Pass_Accuracy DECIMAL(5,2),
    Fouls INT,
    Yellow_Cards INT,
    Red_Cards INT,
    Corners INT,
    Saves INT,
    Offsides INT,

    FOREIGN KEY (Match_ID) REFERENCES Matches(Match_ID)
);

INSERT INTO Match_Statistics
(Match_ID, Goals, Possession_Percentage, Shots, Shots_On_Target,
Pass_Accuracy, Fouls, Yellow_Cards, Red_Cards, Corners, Saves, Offsides)
VALUES
(1, 4, 57.50, 15, 9, 89.30, 8, 2, 0, 6, 3, 1),

(2, 2, 61.20, 12, 7, 91.50, 5, 1, 0, 5, 2, 0),

(3, 4, 50.00, 14, 8, 86.40, 9, 3, 1, 7, 5, 2),

(4, 1, 48.30, 10, 5, 84.20, 6, 2, 0, 4, 4, 1);

CREATE TABLE Player_Profile (
    Player_ID INT PRIMARY KEY,
    Rank INT,
    Matches_Played INT DEFAULT 0,
    Wins INT DEFAULT 0,
    Draws INT DEFAULT 0,
    Losses INT DEFAULT 0,
    Win_Rate DECIMAL(5,2),
    Goals_Scored INT DEFAULT 0,
    Goals_Conceded INT DEFAULT 0,
    Tournament_Titles INT DEFAULT 0,
    Favorite_Club VARCHAR(50),
    Current_Winning_Streak INT DEFAULT 0,
    Best_Club VARCHAR(50),
    Player_Style VARCHAR(50),

    FOREIGN KEY (Player_ID) REFERENCES Players(Player_ID)
);

INSERT INTO Player_Profile
VALUES
(1, 1, 25, 18, 3, 4, 72.00, 55, 20, 4, 'Real Madrid', 5, 'Real Madrid', 'Attacking'),
(2, 2, 24, 17, 4, 3, 70.83, 48, 18, 3, 'Barcelona', 4, 'Barcelona', 'Playmaker'),
(3, 3, 22, 14, 2, 6, 63.64, 39, 25, 2, 'PSG', 2, 'PSG', 'Dribbler'),
(4, 4, 20, 11, 3, 6, 55.00, 30, 27, 1, 'Manchester United', 1, 'Manchester United', 'Crossing');


CREATE TABLE Rankings (
    Rank_ID INT PRIMARY KEY AUTO_INCREMENT,
    Player_ID INT,
    Player_Rank INT,
    Matches INT,
    Wins INT,
    Draws INT,
    Losses INT,
    Points INT,
    Goal_Difference INT,
    Win_Rate DECIMAL(5,2),

    FOREIGN KEY (Player_ID) REFERENCES Players(Player_ID)
);

INSERT INTO Rankings
(Player_ID, Player_Rank, Matches, Wins, Draws, Losses,
Points, Goal_Difference, Win_Rate)
VALUES
(1, 1, 25, 18, 3, 4, 57, 35, 72.00),
(2, 2, 24, 17, 4, 3, 55, 30, 70.83),
(3, 3, 22, 14, 2, 6, 44, 14, 63.64),
(4, 4, 20, 11, 3, 6, 36, 3, 55.00);


CREATE TABLE Head_To_Head (
    H2H_ID INT PRIMARY KEY AUTO_INCREMENT,
    Player1_ID INT,
    Player2_ID INT,
    Total_Matches INT DEFAULT 0,
    Player1_Wins INT DEFAULT 0,
    Player2_Wins INT DEFAULT 0,
    Draws INT DEFAULT 0,
    Player1_Goals INT DEFAULT 0,
    Player2_Goals INT DEFAULT 0,
    Last_Match_Result VARCHAR(20),
    Winning_Percentage DECIMAL(5,2),

    FOREIGN KEY (Player1_ID) REFERENCES Players(Player_ID),
    FOREIGN KEY (Player2_ID) REFERENCES Players(Player_ID)
);

INSERT INTO Head_To_Head
(Player1_ID, Player2_ID, Total_Matches, Player1_Wins, Player2_Wins,
Draws, Player1_Goals, Player2_Goals, Last_Match_Result, Winning_Percentage)
VALUES

(1, 2, 5, 3, 2, 0, 10, 7, 'Ronaldo Won', 60.00),

(1, 3, 4, 2, 1, 1, 8, 6, 'Draw', 50.00),

(2, 4, 3, 2, 1, 0, 6, 4, 'Messi Won', 66.67),

(3, 4, 6, 4, 2, 0, 12, 9, 'Neymar Won', 66.67);

CREATE TABLE Tournament_History (
    History_ID INT PRIMARY KEY AUTO_INCREMENT,
    Tournament_ID INT,
    Champion_ID INT,
    Runner_Up_ID INT,
    Semi_Finalist1_ID INT,
    Semi_Finalist2_ID INT,
    Quarter_Finalist1_ID INT,
    Quarter_Finalist2_ID INT,
    Quarter_Finalist3_ID INT,
    Quarter_Finalist4_ID INT,
    Best_Scorer_ID INT,
    MVP_ID INT,

    FOREIGN KEY (Tournament_ID) REFERENCES Tournaments(Tournament_ID),
    FOREIGN KEY (Champion_ID) REFERENCES Players(Player_ID),
    FOREIGN KEY (Runner_Up_ID) REFERENCES Players(Player_ID),
    FOREIGN KEY (Semi_Finalist1_ID) REFERENCES Players(Player_ID),
    FOREIGN KEY (Semi_Finalist2_ID) REFERENCES Players(Player_ID),
    FOREIGN KEY (Quarter_Finalist1_ID) REFERENCES Players(Player_ID),
    FOREIGN KEY (Quarter_Finalist2_ID) REFERENCES Players(Player_ID),
    FOREIGN KEY (Quarter_Finalist3_ID) REFERENCES Players(Player_ID),
    FOREIGN KEY (Quarter_Finalist4_ID) REFERENCES Players(Player_ID),
    FOREIGN KEY (Best_Scorer_ID) REFERENCES Players(Player_ID),
    FOREIGN KEY (MVP_ID) REFERENCES Players(Player_ID)
);

INSERT INTO Tournament_History
(Tournament_ID, Champion_ID, Runner_Up_ID,
Semi_Finalist1_ID, Semi_Finalist2_ID,
Quarter_Finalist1_ID, Quarter_Finalist2_ID,
Quarter_Finalist3_ID, Quarter_Finalist4_ID,
Best_Scorer_ID, MVP_ID)
VALUES
(1, 1, 2, 3, 4, 1, 2, 3, 4, 1, 1),

(2, 2, 1, 3, 4, 1, 2, 3, 4, 2, 2),

(3, 3, 1, 2, 4, 1, 2, 3, 4, 3, 3);

CREATE TABLE Achievements (
    Achievement_ID INT PRIMARY KEY AUTO_INCREMENT,
    Achievement_Name VARCHAR(50) NOT NULL,
    Description VARCHAR(200)
);

INSERT INTO Achievements
(Achievement_Name, Description)
VALUES
('Champion', 'Won a tournament championship'),
('Golden Boot', 'Top goal scorer of a tournament'),
('100 Goals', 'Scored 100 career goals'),
('10 Match Win Streak', 'Won 10 consecutive matches'),
('Giant Killer', 'Defeated the Rank #1 player');

CREATE TABLE Player_Achievements (
    Player_Achievement_ID INT PRIMARY KEY AUTO_INCREMENT,
    Player_ID INT,
    Achievement_ID INT,
    Date_Earned DATE,

    FOREIGN KEY (Player_ID) REFERENCES Players(Player_ID),
    FOREIGN KEY (Achievement_ID) REFERENCES Achievements(Achievement_ID)
);

INSERT INTO Player_Achievements
(Player_ID, Achievement_ID, Date_Earned)
VALUES
(1, 1, '2026-08-10'),
(1, 2, '2026-08-10'),
(2, 5, '2026-10-12'),
(3, 3, '2026-09-15'),
(4, 4, '2026-10-20');

CREATE TABLE Favorite_Club_Analytics (
    Analytics_ID INT PRIMARY KEY AUTO_INCREMENT,
    Player_ID INT,
    Favorite_Club VARCHAR(50),
    Matches INT DEFAULT 0,
    Wins INT DEFAULT 0,
    Win_Rate DECIMAL(5,2),
    Best_Club VARCHAR(50),

    FOREIGN KEY (Player_ID) REFERENCES Players(Player_ID)
);

INSERT INTO Favorite_Club_Analytics
(Player_ID, Favorite_Club, Matches, Wins, Win_Rate, Best_Club)
VALUES
(1, 'Real Madrid', 75, 60, 80.00, 'Real Madrid'),
(2, 'Barcelona', 68, 52, 76.47, 'Barcelona'),
(3, 'PSG', 50, 33, 66.00, 'PSG'),
(4, 'Manchester United', 55, 35, 63.64, 'Manchester United');

CREATE TABLE Live_Streams (
    Stream_ID INT PRIMARY KEY AUTO_INCREMENT,
    Match_ID INT,
    Stream_URL VARCHAR(255),
    Status ENUM('Upcoming', 'Live', 'Ended'),
    Start_Time DATETIME,
    End_Time DATETIME,
    Viewer_Count INT DEFAULT 0,

    FOREIGN KEY (Match_ID) REFERENCES Matches(Match_ID)
);

INSERT INTO Live_Streams
(Match_ID, Stream_URL, Status, Start_Time, End_Time, Viewer_Count)
VALUES
(1, 'https://www.youtube.com/@playeFootball', 'Ended',
'2026-08-05 18:00:00', '2026-08-05 19:45:00', 8500),

(2, 'https://www.youtube.com/@FIFA', 'Live',
'2026-08-05 20:00:00', NULL, 12350),

(3, 'https://www.youtube.com/@playeFootball', 'Upcoming',
'2026-09-08 19:30:00', NULL, 0),

(4, 'https://www.youtube.com/@FIFA', 'Upcoming',
'2026-10-12 21:00:00', NULL, 0);

CREATE TABLE Admins (
    Admin_ID INT PRIMARY KEY AUTO_INCREMENT,
    Admin_Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE,
    Password VARCHAR(100) NOT NULL,
    Role ENUM('Super Admin','Tournament Admin','Moderator'),
    Join_Date DATE,
    Last_Login DATETIME
);

INSERT INTO Admins
(Admin_Name, Email, Password, Role, Join_Date)
VALUES
('Sarwar', 'sarwar@gmail.com', 'admin123', 'Super Admin', '2026-07-01'),

('Shafin', 'shafin@gmail.com', 'shafin123', 'Tournament Admin', '2026-07-05'),

('Sanchez', 'sanchez@gmail.com', 'sanchez123', 'Tournament Admin', '2026-07-05');

INSERT INTO Tournaments
(Tournament_Name, Tournament_Type, Prize_Pool,Registration_Deadline, Maximum_Players,
Tournament_Status, Start_Date, End_Date)

VALUES
('Summer eFootball Cup','Knockout',15000,'2026-11-01',32,'Upcoming','2026-11-05','2026-11-10');

UPDATE Matches
SET Final_Score = '3-2',
Winner_ID = 1 WHERE Match_ID = 3;

ALTER TABLE Players
ADD Status ENUM('Active','Banned')
DEFAULT 'Active';

UPDATE Players
SET Status = 'Banned' WHERE Player_ID = 4;

UPDATE Rankings
SET Player_Rank = 1, Points = 60 WHERE Player_ID = 1;

UPDATE Players
SET Favorite_Club = 'Al Nassr' WHERE Player_ID = 1;

UPDATE Live_Streams
SET Status = 'Live',
    Start_Time = NOW() WHERE Match_ID = 3;

UPDATE Live_Streams
SET Status = 'Ended',
    End_Time = NOW() WHERE Match_ID = 3;
    
UPDATE Rankings
SET Wins = Wins + 1,
    Points = Points + 3 WHERE Player_ID = 1; 

CREATE TABLE Live_Chat (
    Chat_ID INT PRIMARY KEY AUTO_INCREMENT,
    Stream_ID INT,
    Player_ID INT,
    Message VARCHAR(255) NOT NULL,
    Message_Time DATETIME,

    FOREIGN KEY (Stream_ID) REFERENCES Live_Streams(Stream_ID),
    FOREIGN KEY (Player_ID) REFERENCES Players(Player_ID)
);

INSERT INTO Live_Chat
(Stream_ID, Player_ID, Message, Message_Time)
VALUES

(2, 1, 'Good luck everyone!', '2026-08-05 20:05:10'),

(2, 2, 'Amazing goal!', '2026-08-05 20:18:30'),

(2, 3, 'What a save!', '2026-08-05 20:25:15'),

(2, 4, 'This match is incredible!', '2026-08-05 20:40:50');

CREATE TABLE Predictions (
    Prediction_ID INT PRIMARY KEY AUTO_INCREMENT,
    Match_ID INT,
    Player1_Win_Probability DECIMAL(5,2),
    Player2_Win_Probability DECIMAL(5,2),
    Prediction_Date DATE,

    FOREIGN KEY (Match_ID) REFERENCES Matches(Match_ID)
);

INSERT INTO Predictions
(Match_ID, Player1_Win_Probability, Player2_Win_Probability, Prediction_Date)
VALUES

(1, 74.00, 26.00, '2026-08-05'),

(2, 61.00, 39.00, '2026-08-05'),

(3, 50.00, 50.00, '2026-09-08'),

(4, 45.00, 55.00, '2026-10-12');

CREATE TABLE Recommendations (
    Recommendation_ID INT PRIMARY KEY AUTO_INCREMENT,
    Player_ID INT,
    Area_of_Improvement VARCHAR(100),
    Recommendation VARCHAR(200),
    Generated_Date DATE,

    FOREIGN KEY (Player_ID) REFERENCES Players(Player_ID)
);

INSERT INTO Recommendations
(Player_ID, Area_of_Improvement, Recommendation, Generated_Date)
VALUES

(1, 'Passing', 'Practice Short Passing', '2026-08-15'),

(2, 'Defending', 'Improve Defending', '2026-08-15'),

(3, 'Club Selection', 'Try Barcelona', '2026-08-15'),

(4, 'Finishing', 'Practice Finishing', '2026-08-15');

ALTER TABLE Player_Profile
ADD Performance_Rating DECIMAL(2,1);

UPDATE Player_Profile
SET Performance_Rating = 9.8
WHERE Player_ID = 1;

UPDATE Player_Profile
SET Performance_Rating = 9.7
WHERE Player_ID = 2;

UPDATE Player_Profile
SET Performance_Rating = 9.3
WHERE Player_ID = 3;

UPDATE Player_Profile
SET Performance_Rating = 8.8
WHERE Player_ID = 4;

UPDATE Players
SET Gamer_Tag = 'Sarwar7',
    Real_Name = 'Sarwar Murshed'
WHERE Player_ID = 1;

UPDATE Players
SET Gamer_Tag = 'Shafin10',
    Real_Name = 'Shahariar Shafin'
WHERE Player_ID = 2;

UPDATE Players
SET Gamer_Tag = 'Farha8',
    Real_Name = 'Farha Ahmed'
WHERE Player_ID = 3;

UPDATE Players
SET Gamer_Tag = 'Nolan2',
    Real_Name = 'Christopher Nolan'
WHERE Player_ID = 4;

CREATE TABLE Football_Players (
    Football_Player_ID INT PRIMARY KEY AUTO_INCREMENT,
    Player_Name VARCHAR(100) NOT NULL,
    Position ENUM('GK','DF','MF','FW') NOT NULL,
    Player_Type ENUM('Good','Bot') NOT NULL,
    Overall_Rating DECIMAL(4,1),
    Goals INT DEFAULT 0,
    Assists INT DEFAULT 0,
    Tackles INT DEFAULT 0,
    Saves INT DEFAULT 0,
    Matches_Played INT DEFAULT 0
);

INSERT INTO Football_Players
(Player_Name, Position, Player_Type, Overall_Rating, Goals, Assists, Tackles, Saves, Matches_Played)
VALUES

-- Sarwar7's 15 players

('Cristiano Ronaldo', 'FW', 'Good', 9.8, 35, 8, 5, 0, 20),
('Kylian Mbappe', 'FW', 'Good', 9.6, 30, 10, 3, 0, 20),
('Kevin De Bruyne', 'MF', 'Good', 9.4, 12, 18, 15, 0, 20),
('Virgil van Dijk', 'DF', 'Good', 9.3, 4, 2, 35, 0, 20),
('Thibaut Courtois', 'GK', 'Good', 9.2, 0, 0, 0, 42, 20),
('khvicha kvaratskhelia', 'FW', 'Good', 9.2, 25, 12, 4, 0, 20),
('Jude Bellingham', 'MF', 'Good', 9.1, 15, 10, 20, 0, 20),
('Marcelo', 'DF', 'Good', 9.0, 2, 1, 32, 0, 20),
('Alisson Becker', 'GK', 'Good', 9.0, 0, 0, 0, 38, 20),
('Vinicius Jr', 'FW', 'Good', 9.1, 27, 14, 3, 0, 20),
('Rodri', 'MF', 'Good', 9.2, 8, 12, 28, 0, 20),
('Sergio Ramos', 'DF', 'Good', 8.8, 2, 1, 30, 0, 20),
('Harry Kane', 'FW', 'Good', 9.3, 29, 9, 2, 0, 20),
('Luka Modric', 'MF', 'Good', 8.9, 6, 15, 12, 0, 20),
('Achraf Hakimi', 'DF', 'Good', 8.7, 5, 8, 24, 0, 20),

-- Shafin10's 15 players

('Lionel Messi', 'FW', 'Good', 9.7, 32, 20, 2, 0, 20),
('Erling Haaland', 'FW', 'Good', 9.6, 38, 6, 2, 0, 20),
('Pedri', 'MF', 'Good', 8.9, 7, 14, 13, 0, 20),
('William Saliba', 'DF', 'Good', 9.0, 2, 1, 31, 0, 20),
('Ederson', 'GK', 'Good', 8.9, 0, 0, 0, 36, 20),
('Bukayo Saka', 'FW', 'Good', 9.0, 22, 13, 5, 0, 20),
('Bernardo Silva', 'MF', 'Good', 9.0, 9, 16, 11, 0, 20),
('Marquinhos', 'DF', 'Good', 8.8, 2, 2, 28, 0, 20),
('Mike Maignan', 'GK', 'Good', 8.8, 0, 0, 0, 35, 20),
('Son Heung-min', 'FW', 'Good', 9.0, 24, 11, 3, 0, 20),
('Toni Kroos', 'MF', 'Good', 8.8, 5, 17, 10, 0, 20),
('John Stones', 'DF', 'Good', 8.6, 3, 2, 25, 0, 20),
('Robert Lewandowski', 'FW', 'Good', 9.2, 31, 7, 2, 0, 20),
('Declan Rice', 'MF', 'Good', 8.9, 6, 10, 27, 0, 20),
('Trent Alexander-Arnold', 'DF', 'Good', 8.7, 4, 13, 22, 0, 20),

-- Farha8's 15 players

('Neymar Jr', 'FW', 'Good', 9.4, 25, 18, 3, 0, 20),
('Lautaro Martinez', 'FW', 'Good', 9.1, 28, 8, 2, 0, 20),
('Martin Odegaard', 'MF', 'Good', 9.0, 10, 17, 12, 0, 20),
('Antonio Rudiger', 'DF', 'Good', 8.8, 2, 1, 29, 0, 20),
('Emiliano Martinez', 'GK', 'Good', 8.8, 0, 0, 0, 40, 20),
('Raphinha', 'FW', 'Good', 8.8, 20, 14, 4, 0, 20),
('Federico Valverde', 'MF', 'Good', 9.0, 8, 11, 25, 0, 20),
('Ronald Araujo', 'DF', 'Good', 8.7, 2, 1, 33, 0, 20),
('Gianluigi Donnarumma', 'GK', 'Good', 9.0, 0, 0, 0, 44, 20),
('Ousmane Dembele', 'FW', 'Good', 8.9, 21, 15, 3, 0, 20),
('Frenkie de Jong', 'MF', 'Good', 8.8, 5, 14, 18, 0, 20),
('Matthijs de Ligt', 'DF', 'Good', 8.6, 3, 1, 27, 0, 20),
('Victor Osimhen', 'FW', 'Good', 9.1, 30, 5, 2, 0, 20),
('Joshua Kimmich', 'MF', 'Good', 8.9, 6, 15, 21, 0, 20),
('Theo Hernandez', 'DF', 'Good', 8.7, 6, 9, 26, 0, 20),

-- Nolan2's 15 players

('Karim Benzema', 'FW', 'Good', 9.1, 27, 12, 2, 0, 20),
('Phil Foden', 'MF', 'Good', 9.0, 18, 15, 5, 0, 20),
('Gavi', 'MF', 'Good', 8.6, 5, 10, 22, 0, 20),
('Eder Militao', 'DF', 'Good', 8.7, 2, 1, 30, 0, 20),
('Jan Oblak', 'GK', 'Good', 8.9, 0, 0, 0, 41, 20),
('Marcus Rashford', 'FW', 'Good', 8.7, 21, 8, 3, 0, 20),
('Bruno Fernandes', 'MF', 'Good', 9.0, 11, 18, 10, 0, 20),
('Raphael Varane', 'DF', 'Good', 8.5, 2, 1, 28, 0, 20),
('Marc-Andre ter Stegen', 'GK', 'Good', 8.8, 0, 0, 0, 39, 20),
('Riyad Mahrez', 'FW', 'Good', 8.7, 19, 13, 3, 0, 20),
('Casemiro', 'MF', 'Good', 8.5, 5, 7, 31, 0, 20),
('Kyle Walker', 'DF', 'Good', 8.5, 1, 4, 26, 0, 20),
('Romelu Lukaku', 'FW', 'Good', 8.8, 26, 6, 2, 0, 20),
('Thomas Muller', 'MF', 'Good', 8.6, 12, 16, 4, 0, 20),
('Alphonso Davies', 'DF', 'Good', 8.6, 4, 10, 24, 0, 20);

CREATE TABLE Team_Players (
    Team_Player_ID INT PRIMARY KEY AUTO_INCREMENT,
    Manager_ID INT,
    Football_Player_ID INT,
    Acquired_Date DATE,
    Acquisition_Type ENUM('Initial','Auction','Trade') NOT NULL,

    FOREIGN KEY (Manager_ID) REFERENCES Players(Player_ID),
    FOREIGN KEY (Football_Player_ID) REFERENCES Football_Players(Football_Player_ID)
);

INSERT INTO Football_Players
(Player_Name, Position, Player_Type, Overall_Rating, Goals, Assists, Tackles, Saves, Matches_Played)
VALUES

-- Sarwar7's 12 Default Players

('Oliver Hayes', 'GK', 'Bot', 7.0, 0, 0, 0, 18, 15),
('Lucas Martin', 'GK', 'Bot', 6.8, 0, 0, 0, 16, 15),
('Ethan Walker', 'DF', 'Bot', 7.1, 1, 2, 22, 0, 15),
('Daniel Foster', 'DF', 'Bot', 6.9, 1, 1, 20, 0, 15),
('Marco Rossi', 'DF', 'Bot', 6.7, 0, 2, 18, 0, 15),
('Adrian Silva', 'DF', 'Bot', 6.6, 1, 1, 17, 0, 15),
('Leon Weber', 'MF', 'Bot', 7.0, 4, 5, 12, 0, 15),
('Oscar Bennett', 'MF', 'Bot', 6.8, 3, 4, 14, 0, 15),
('Felix Muller', 'MF', 'Bot', 6.7, 2, 5, 11, 0, 15),
('Jack Wilson', 'FW', 'Bot', 7.2, 9, 3, 2, 0, 15),
('Liam Carter', 'FW', 'Bot', 7.0, 7, 4, 2, 0, 15),
('Noah Anderson', 'FW', 'Bot', 6.8, 6, 2, 3, 0, 15),

-- Shafin10's 12 Default Players

('James Mitchell', 'GK', 'Bot', 7.1, 0, 0, 0, 20, 15),
('Henry Collins', 'GK', 'Bot', 6.9, 0, 0, 0, 17, 15),
('Arthur Johnson', 'DF', 'Bot', 7.0, 1, 2, 23, 0, 15),
('William Turner', 'DF', 'Bot', 6.8, 0, 2, 19, 0, 15),
('Matteo Bianchi', 'DF', 'Bot', 6.7, 1, 1, 21, 0, 15),
('Diego Navarro', 'DF', 'Bot', 6.6, 0, 1, 18, 0, 15),
('Thomas Evans', 'MF', 'Bot', 7.0, 3, 6, 13, 0, 15),
('Alexander Reed', 'MF', 'Bot', 6.9, 4, 4, 15, 0, 15),
('Louis Bernard', 'MF', 'Bot', 6.7, 2, 5, 12, 0, 15),
('Charlie Morgan', 'FW', 'Bot', 7.2, 10, 3, 2, 0, 15),
('Benjamin Scott', 'FW', 'Bot', 7.0, 8, 3, 3, 0, 15),
('Jack Harrison', 'FW', 'Bot', 6.8, 6, 4, 2, 0, 15),

-- Farha8's 12 Default Players

('William Parker', 'GK', 'Bot', 7.0, 0, 0, 0, 19, 15),
('George Cooper', 'GK', 'Bot', 6.8, 0, 0, 0, 15, 15),
('Robert Hughes', 'DF', 'Bot', 7.1, 1, 2, 24, 0, 15),
('Nathan Brooks', 'DF', 'Bot', 6.9, 1, 1, 21, 0, 15),
('Alessandro Romano', 'DF', 'Bot', 6.7, 0, 2, 19, 0, 15),
('Sergio Costa', 'DF', 'Bot', 6.6, 1, 1, 17, 0, 15),
('Sebastian Klein', 'MF', 'Bot', 7.1, 4, 6, 13, 0, 15),
('Maximilian Wolf', 'MF', 'Bot', 6.9, 3, 5, 14, 0, 15),
('Julien Moreau', 'MF', 'Bot', 6.8, 2, 4, 12, 0, 15),
('Ryan Murphy', 'FW', 'Bot', 7.3, 10, 4, 2, 0, 15),
('Jacob Thompson', 'FW', 'Bot', 7.0, 8, 3, 2, 0, 15),
('Mason Lewis', 'FW', 'Bot', 6.9, 7, 4, 3, 0, 15),

-- Nolan2's 12 Default Players

('Edward Watson', 'GK', 'Bot', 7.1, 0, 0, 0, 21, 15),
('Samuel Wright', 'GK', 'Bot', 6.9, 0, 0, 0, 18, 15),
('Joseph King', 'DF', 'Bot', 7.0, 1, 2, 22, 0, 15),
('Andrew Scott', 'DF', 'Bot', 6.8, 0, 1, 20, 0, 15),
('Nicolas Dupont', 'DF', 'Bot', 6.7, 1, 2, 18, 0, 15),
('Lorenzo Ferrari', 'DF', 'Bot', 6.6, 0, 1, 17, 0, 15),
('Victor Schmidt', 'MF', 'Bot', 7.0, 4, 5, 13, 0, 15),
('Felipe Santos', 'MF', 'Bot', 6.9, 3, 5, 15, 0, 15),
('Thomas Laurent', 'MF', 'Bot', 6.8, 2, 4, 11, 0, 15),
('Michael Adams', 'FW', 'Bot', 7.2, 9, 4, 2, 0, 15),
('Christopher Moore', 'FW', 'Bot', 7.0, 8, 3, 3, 0, 15),
('Ryan Mitchell', 'FW', 'Bot', 6.8, 6, 3, 2, 0, 15);

CREATE TABLE Auctions (
    Auction_ID INT PRIMARY KEY AUTO_INCREMENT,
    Football_Player_ID INT,
    Starting_Price DECIMAL(10,2),
    Current_Bid DECIMAL(10,2),
    Auction_Status ENUM('Upcoming','Live','Sold','Unsold'),
    Winner_Manager_ID INT,
    Auction_Date DATETIME,

    FOREIGN KEY (Football_Player_ID)
        REFERENCES Football_Players(Football_Player_ID),

    FOREIGN KEY (Winner_Manager_ID)
        REFERENCES Players(Player_ID)
);

CREATE TABLE Bids (
    Bid_ID INT PRIMARY KEY AUTO_INCREMENT,
    Auction_ID INT,
    Manager_ID INT,
    Bid_Amount DECIMAL(10,2),
    Bid_Time DATETIME,

    FOREIGN KEY (Auction_ID)
        REFERENCES Auctions(Auction_ID),

    FOREIGN KEY (Manager_ID)
        REFERENCES Players(Player_ID)
);

CREATE TABLE Player_Transfers (
    Transfer_ID INT PRIMARY KEY AUTO_INCREMENT,
    Football_Player_ID INT,
    From_Manager_ID INT,
    To_Manager_ID INT,
    Transfer_Type ENUM('Buy','Sell','Trade'),
    Transfer_Fee DECIMAL(10,2),
    Transfer_Date DATETIME,

    FOREIGN KEY (Football_Player_ID)
        REFERENCES Football_Players(Football_Player_ID),

    FOREIGN KEY (From_Manager_ID)
        REFERENCES Players(Player_ID),

    FOREIGN KEY (To_Manager_ID)
        REFERENCES Players(Player_ID)
);

CREATE TABLE Player_Awards (
    Award_ID INT PRIMARY KEY AUTO_INCREMENT,
    Football_Player_ID INT,
    Award_Type ENUM('Player of the Week','Player of the Month'),
    Award_Period DATE,
    Goals INT,
    Assists INT,
    Tackles INT,
    Saves INT,
    Reason VARCHAR(255),

    FOREIGN KEY (Football_Player_ID)
        REFERENCES Football_Players(Football_Player_ID)
);


INSERT INTO Team_Players
(Manager_ID, Football_Player_ID, Acquired_Date, Acquisition_Type)
VALUES

-- Sarwar7: Good players 1-15
(1, 1, '2026-08-20', 'Initial'),
(1, 2, '2026-08-20', 'Initial'),
(1, 3, '2026-08-20', 'Initial'),
(1, 4, '2026-08-20', 'Initial'),
(1, 5, '2026-08-20', 'Initial'),
(1, 6, '2026-08-20', 'Initial'),
(1, 7, '2026-08-20', 'Initial'),
(1, 8, '2026-08-20', 'Initial'),
(1, 9, '2026-08-20', 'Initial'),
(1, 10, '2026-08-20', 'Initial'),
(1, 11, '2026-08-20', 'Initial'),
(1, 12, '2026-08-20', 'Initial'),
(1, 13, '2026-08-20', 'Initial'),
(1, 14, '2026-08-20', 'Initial'),
(1, 15, '2026-08-20', 'Initial'),

-- Sarwar7: Bot players 61-72
(1, 61, '2026-08-20', 'Initial'),
(1, 62, '2026-08-20', 'Initial'),
(1, 63, '2026-08-20', 'Initial'),
(1, 64, '2026-08-20', 'Initial'),
(1, 65, '2026-08-20', 'Initial'),
(1, 66, '2026-08-20', 'Initial'),
(1, 67, '2026-08-20', 'Initial'),
(1, 68, '2026-08-20', 'Initial'),
(1, 69, '2026-08-20', 'Initial'),
(1, 70, '2026-08-20', 'Initial'),
(1, 71, '2026-08-20', 'Initial'),
(1, 72, '2026-08-20', 'Initial'),

-- Shafin10: Good players 16-30
(2, 16, '2026-08-20', 'Initial'),
(2, 17, '2026-08-20', 'Initial'),
(2, 18, '2026-08-20', 'Initial'),
(2, 19, '2026-08-20', 'Initial'),
(2, 20, '2026-08-20', 'Initial'),
(2, 21, '2026-08-20', 'Initial'),
(2, 22, '2026-08-20', 'Initial'),
(2, 23, '2026-08-20', 'Initial'),
(2, 24, '2026-08-20', 'Initial'),
(2, 25, '2026-08-20', 'Initial'),
(2, 26, '2026-08-20', 'Initial'),
(2, 27, '2026-08-20', 'Initial'),
(2, 28, '2026-08-20', 'Initial'),
(2, 29, '2026-08-20', 'Initial'),
(2, 30, '2026-08-20', 'Initial'),

-- Shafin10: Bot players 73-84
(2, 73, '2026-08-20', 'Initial'),
(2, 74, '2026-08-20', 'Initial'),
(2, 75, '2026-08-20', 'Initial'),
(2, 76, '2026-08-20', 'Initial'),
(2, 77, '2026-08-20', 'Initial'),
(2, 78, '2026-08-20', 'Initial'),
(2, 79, '2026-08-20', 'Initial'),
(2, 80, '2026-08-20', 'Initial'),
(2, 81, '2026-08-20', 'Initial'),
(2, 82, '2026-08-20', 'Initial'),
(2, 83, '2026-08-20', 'Initial'),
(2, 84, '2026-08-20', 'Initial'),

-- Farha8: Good players 31-45
    
(3, 31, '2026-08-20', 'Initial'),
(3, 32, '2026-08-20', 'Initial'),
(3, 33, '2026-08-20', 'Initial'),
(3, 34, '2026-08-20', 'Initial'),
(3, 35, '2026-08-20', 'Initial'),
(3, 36, '2026-08-20', 'Initial'),
(3, 37, '2026-08-20', 'Initial'),
(3, 38, '2026-08-20', 'Initial'),
(3, 39, '2026-08-20', 'Initial'),
(3, 40, '2026-08-20', 'Initial'),
(3, 41, '2026-08-20', 'Initial'),
(3, 42, '2026-08-20', 'Initial'),
(3, 43, '2026-08-20', 'Initial'),
(3, 44, '2026-08-20', 'Initial'),
(3, 45, '2026-08-20', 'Initial'),

-- Farha8: Bot players 85-96
(3, 85, '2026-08-20', 'Initial'),
(3, 86, '2026-08-20', 'Initial'),
(3, 87, '2026-08-20', 'Initial'),
(3, 88, '2026-08-20', 'Initial'),
(3, 89, '2026-08-20', 'Initial'),
(3, 90, '2026-08-20', 'Initial'),
(3, 91, '2026-08-20', 'Initial'),
(3, 92, '2026-08-20', 'Initial'),
(3, 93, '2026-08-20', 'Initial'),
(3, 94, '2026-08-20', 'Initial'),
(3, 95, '2026-08-20', 'Initial'),
(3, 96, '2026-08-20', 'Initial'),

-- Nolan2: Good players 46-60
(4, 46, '2026-08-20', 'Initial'),
(4, 47, '2026-08-20', 'Initial'),
(4, 48, '2026-08-20', 'Initial'),
(4, 49, '2026-08-20', 'Initial'),
(4, 50, '2026-08-20', 'Initial'),
(4, 51, '2026-08-20', 'Initial'),
(4, 52, '2026-08-20', 'Initial'),
(4, 53, '2026-08-20', 'Initial'),
(4, 54, '2026-08-20', 'Initial'),
(4, 55, '2026-08-20', 'Initial'),
(4, 56, '2026-08-20', 'Initial'),
(4, 57, '2026-08-20', 'Initial'),
(4, 58, '2026-08-20', 'Initial'),
(4, 59, '2026-08-20', 'Initial'),
(4, 60, '2026-08-20', 'Initial'),

-- Nolan2: Bot players 97-108
(4, 97, '2026-08-20', 'Initial'),
(4, 98, '2026-08-20', 'Initial'),
(4, 99, '2026-08-20', 'Initial'),
(4, 100, '2026-08-20', 'Initial'),
(4, 101, '2026-08-20', 'Initial'),
(4, 102, '2026-08-20', 'Initial'),
(4, 103, '2026-08-20', 'Initial'),
(4, 104, '2026-08-20', 'Initial'),
(4, 105, '2026-08-20', 'Initial'),
(4, 106, '2026-08-20', 'Initial'),
(4, 107, '2026-08-20', 'Initial'),
(4, 108, '2026-08-20', 'Initial');

CREATE TABLE Manager_Wallet ( 
    Wallet_ID INT PRIMARY KEY AUTO_INCREMENT, 
    Manager_ID INT UNIQUE, 
    Balance DECIMAL(12,2) DEFAULT 1000000.00, 
 
    FOREIGN KEY (Manager_ID) 
        REFERENCES Players(Player_ID) 
); 
 
INSERT INTO Manager_Wallet (Manager_ID, Balance) 
VALUES 
(1, 1000000.00), 
(2, 1000000.00), 
(3, 1000000.00), 
(4, 1000000.00); 
 
ALTER TABLE Football_Players 
ADD Market_Value DECIMAL(12,2); 
 
INSERT INTO Auctions 
(Football_Player_ID, Starting_Price, Current_Bid, Auction_Status, Auction_Date) 
VALUES 
(16, 300000.00, 350000.00, 'Live', '2026-08-20 20:00:00'), 
(31, 250000.00, 300000.00, 'Upcoming', '2026-08-21 20:00:00'), 
(46, 200000.00, 200000.00, 'Upcoming', '2026-08-22 20:00:00'); 
 
INSERT INTO Player_Awards 
(Football_Player_ID, Award_Type, Award_Period, 
 Goals, Assists, Tackles, Saves, Reason) 
VALUES 
(1, 'Player of the Week', '2026-08-17', 
 5, 2, 1, 0, 'Outstanding attacking performance'), 
 
(4, 'Player of the Month', '2026-08-01', 
 2, 1, 18, 0, 'Excellent defensive performance'), 
 
(5, 'Player of the Week', '2026-08-17', 
 0, 0, 0, 12, 'Exceptional goalkeeping performance'); 
  
INSERT INTO Player_Transfers 
(Football_Player_ID, From_Manager_ID, To_Manager_ID, 
 Transfer_Type, Transfer_Fee, Transfer_Date) 
VALUES 
(16, 1, 2, 'Sell', 750000.00, '2026-08-25');

UPDATE Auctions
SET Current_Bid = 400000.00
WHERE Auction_ID = 1;

UPDATE Auctions
SET Current_Bid = 350000.00
WHERE Auction_ID = 2;

UPDATE Auctions
SET Current_Bid = 250000.00
WHERE Auction_ID = 3;

-- Rodri auction
UPDATE Auctions
SET Football_Player_ID = 11,
    Starting_Price = 300000.00,
    Current_Bid = 400000.00,
    Auction_Status = 'Live'
WHERE Auction_ID = 1;

-- Bids for Rodri
INSERT INTO Bids
(Auction_ID, Manager_ID, Bid_Amount, Bid_Time)
VALUES
(1, 2, 350000.00, '2026-08-20 20:05:00'),
(1, 3, 375000.00, '2026-08-20 20:08:00'),
(1, 2, 400000.00, '2026-08-20 20:10:00');

-- Shafin wins Rodri
UPDATE Auctions
SET Current_Bid = 400000.00,
    Auction_Status = 'Sold',
    Winner_Manager_ID = 2
WHERE Auction_ID = 1;

INSERT INTO Player_Transfers
(Football_Player_ID, From_Manager_ID, To_Manager_ID,
 Transfer_Type, Transfer_Fee, Transfer_Date)
VALUES
(11, 1, 2, 'Sell', 400000.00, '2026-08-20 20:10:00');

DELETE FROM Team_Players
WHERE Manager_ID = 1
AND Football_Player_ID = 11;

INSERT INTO Team_Players
(Manager_ID, Football_Player_ID, Acquired_Date, Acquisition_Type)
VALUES
(2, 11, '2026-08-20', 'Auction');

-- Rodri Auction

UPDATE Auctions
SET Football_Player_ID = 11,
    Starting_Price = 300000.00,
    Current_Bid = 400000.00,
    Auction_Status = 'Live'
WHERE Auction_ID = 1;


-- Bids for Rodri

INSERT INTO Bids
(Auction_ID, Manager_ID, Bid_Amount, Bid_Time)
VALUES
(1, 2, 350000.00, '2026-08-20 20:05:00'),
(1, 3, 375000.00, '2026-08-20 20:08:00'),
(1, 2, 400000.00, '2026-08-20 20:10:00');


-- Shafin wins Rodri

UPDATE Auctions
SET Current_Bid = 400000.00,
    Auction_Status = 'Sold',
    Winner_Manager_ID = 2
WHERE Auction_ID = 1;


-- Rodri transferred from Sarwar to Shafin

INSERT INTO Player_Transfers
(Football_Player_ID, From_Manager_ID, To_Manager_ID,
Transfer_Type, Transfer_Fee, Transfer_Date)
VALUES
(11, 1, 2, 'Sell', 400000.00, '2026-08-20 20:10:00');


-- Remove Rodri from Sarwar's team

DELETE FROM Team_Players
WHERE Manager_ID = 1
AND Football_Player_ID = 11;


-- Add Rodri to Shafin's team

INSERT INTO Team_Players
(Manager_ID, Football_Player_ID, Acquired_Date, Acquisition_Type)
VALUES
(2, 11, '2026-08-20', 'Auction');


-- Update wallets

UPDATE Manager_Wallet
SET Balance = Balance - 400000.00
WHERE Manager_ID = 2;

UPDATE Manager_Wallet
SET Balance = Balance + 400000.00
WHERE Manager_ID = 1;

INSERT INTO Player_Transfers
(Football_Player_ID, From_Manager_ID, To_Manager_ID,
Transfer_Type, Transfer_Fee, Transfer_Date)
VALUES
(16, 1, 2, 'Sell', 750000.00, '2026-08-25');

DELETE FROM Team_Players
WHERE Football_Player_ID = 11;

INSERT INTO Team_Players
(Manager_ID, Football_Player_ID, Acquired_Date, Acquisition_Type)
VALUES
(2, 11, '2026-08-20', 'Auction');

ALTER TABLE Players
MODIFY Preferred_Formation ENUM(
    '4-3-3','4-2-3-1','4-4-2','4-3-1-2','4-1-2-3','4-2-2-2','5-3-2'
);

CREATE TABLE Audience_Reviews (
    Review_ID INT PRIMARY KEY AUTO_INCREMENT,
    Player_ID INT,
    Match_ID INT,
    Audience_Name VARCHAR(100) NOT NULL,
    Rating INT NOT NULL CHECK (Rating BETWEEN 1 AND 5),
    Review_Text VARCHAR(500),
    Review_Date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Player_ID) REFERENCES Players(Player_ID),
    FOREIGN KEY (Match_ID) REFERENCES Matches(Match_ID)
);
INSERT INTO Audience_Reviews
(Player_ID, Match_ID, Audience_Name, Rating, Review_Text)
VALUES
(1, 1, 'Fahim', 5, 'Excellent performance by the player.'),
(2, 2, 'Rahim', 4, 'Very competitive match.'),
(3, 3, 'Nabil', 5, 'Amazing attacking performance.'),
(1, 3, 'Sakib', 4, 'Great match and good gameplay.');

CREATE TABLE Admin_Permissions (
    Permission_ID INT PRIMARY KEY AUTO_INCREMENT,
    Admin_ID INT NOT NULL,
    Table_Name VARCHAR(100) NOT NULL,
    Can_View BOOLEAN DEFAULT TRUE,
    Can_Insert BOOLEAN DEFAULT FALSE,
    Can_Update BOOLEAN DEFAULT FALSE,
    Can_Delete BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (Admin_ID) REFERENCES Admins(Admin_ID),
    UNIQUE (Admin_ID, Table_Name)
);

INSERT INTO Admin_Permissions
(Admin_ID, Table_Name, Can_View, Can_Insert, Can_Update, Can_Delete)
VALUES
(1, 'Players', TRUE, TRUE, TRUE, TRUE),
(1, 'Tournaments', TRUE, TRUE, TRUE, TRUE),
(1, 'Matches', TRUE, TRUE, TRUE, TRUE),
(1, 'Match_Statistics', TRUE, TRUE, TRUE, TRUE),
(1, 'Player_Profile', TRUE, TRUE, TRUE, TRUE),
(1, 'Rankings', TRUE, TRUE, TRUE, TRUE),
(1, 'Live_Streams', TRUE, TRUE, TRUE, TRUE),
(1, 'Live_Chat', TRUE, TRUE, TRUE, TRUE),
(1, 'Predictions', TRUE, TRUE, TRUE, TRUE),
(1, 'Recommendations', TRUE, TRUE, TRUE, TRUE),
(1, 'Football_Players', TRUE, TRUE, TRUE, TRUE),
(1, 'Team_Players', TRUE, TRUE, TRUE, TRUE),
(1, 'Manager_Wallet', TRUE, TRUE, TRUE, TRUE),
(1, 'Auctions', TRUE, TRUE, TRUE, TRUE),
(1, 'Bids', TRUE, TRUE, TRUE, TRUE),
(1, 'Player_Transfers', TRUE, TRUE, TRUE, TRUE),
(1, 'Player_Awards', TRUE, TRUE, TRUE, TRUE),
(1, 'Audience_Reviews', TRUE, TRUE, TRUE, TRUE),
(2, 'Tournaments', TRUE, TRUE, TRUE, TRUE),
(3, 'Tournaments', TRUE, TRUE, TRUE, TRUE)
;

ALTER TABLE Players
ADD Password VARCHAR(255) NOT NULL;

UPDATE Players
SET Password = 'Sarwar123'
WHERE Gamer_Tag = 'Sarwar7';

UPDATE Players
SET Password = 'Shafin123'
WHERE Gamer_Tag = 'Shafin10';

UPDATE Players
SET Password = 'Farha123'
WHERE Gamer_Tag = 'Farha8';

UPDATE Players
SET Password = 'Nolan123'
WHERE Gamer_Tag = 'Nolan2';

UPDATE Live_Streams
SET Status = 'Ended',
    End_Time = NOW()
WHERE Status = 'Live';

UPDATE Matches
SET Final_Score = '2-1',
    Winner_ID = 1
WHERE Match_ID = 1;

UPDATE Live_Streams
SET Status = 'Live',
    Start_Time = NOW() - INTERVAL 45 MINUTE - INTERVAL 32 SECOND,
    End_Time = NULL,
    Viewer_Count = 1250
WHERE Match_ID = 1;

ALTER TABLE Players
ADD COLUMN Profile_Picture VARCHAR(255) NULL;

CREATE TABLE Market_Listings (
    Listing_ID INT PRIMARY KEY AUTO_INCREMENT,
    Football_Player_ID INT NOT NULL,
    Seller_ID INT,                    
    Asking_Price DECIMAL(12,2) NOT NULL,
    Listing_Status ENUM('Available','Sold') DEFAULT 'Available',
    Listed_Date DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (Football_Player_ID) REFERENCES Football_Players(Football_Player_ID),
    FOREIGN KEY (Seller_ID) REFERENCES Players(Player_ID)
);

INSERT INTO Market_Listings (Football_Player_ID, Seller_ID, Asking_Price, Listing_Status)
VALUES
(16, NULL, 350000.00, 'Available'),
(31, NULL, 280000.00, 'Available'),
(46, NULL, 220000.00, 'Available'),
(11, NULL, 400000.00, 'Available'),
(5,  NULL, 320000.00, 'Available');

CREATE TABLE Notifications (
    Notification_ID INT PRIMARY KEY AUTO_INCREMENT,
    Player_ID INT NULL,
    Title VARCHAR(150) NOT NULL,
    Message TEXT NOT NULL,
    Type ENUM('System', 'Match', 'Transfer', 'Tournament', 'Achievement', 'Market') DEFAULT 'System',
    Is_Read TINYINT(1) DEFAULT 0,
    Created_At DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Player_ID) REFERENCES Players(Player_ID)
);

INSERT INTO Notifications (Player_ID, Title, Message, Type, Is_Read) VALUES
(NULL, 'Official FIFA Collaboration', 'eFootball Arena has officially partnered with FIFA for the 2026 World eFootball Cup. Exclusive rewards and licensed tournaments are now available!', 'System', 0),

(NULL, 'New Season Started', 'Welcome to eFootball Arena Season 2026! Build your squad, compete in tournaments and climb the rankings.', 'System', 0),

(NULL, 'Market Update', 'Player market prices have been updated. Ronaldo and Messi are now listed at 700,000 coins each.', 'Market', 0),

(NULL, 'Tournament Alert', 'FIFA Club World Cup registration is now open. Prize pool: $10,000. Register before the deadline!', 'Tournament', 0),

(NULL, 'Live Match Starting', 'A high-intensity match is going live in 15 minutes. Don\'t miss the action!', 'Match', 0),

(NULL, 'Achievement Unlocked', 'New achievement available: "Giant Killer" – Defeat the Rank #1 player to unlock special rewards.', 'Achievement', 0),

(NULL, 'Transfer Window Open', 'The transfer window is active. Buy and sell players freely in the Markets section.', 'Transfer', 0),

(NULL, 'Weekly Rewards', 'Log in daily this week to receive bonus coins and exclusive player packs.', 'System', 0);



