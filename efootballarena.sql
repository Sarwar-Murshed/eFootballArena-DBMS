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