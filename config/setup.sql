/* =======================================
   Missile Control CTF Database Setup
   ======================================= */

DROP DATABASE IF EXISTS missile_ctf;

CREATE DATABASE missile_ctf;

USE missile_ctf;


/* =======================================
   USERS TABLE
   ======================================= */

CREATE TABLE Users(
    User_ID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(50) NOT NULL,
    Password VARCHAR(50) NOT NULL,
    Role VARCHAR(20),
    Security_Clearance VARCHAR(20)
);

INSERT INTO Users (Username,Password,Role,Security_Clearance) VALUES
('officer1','1234','Officer','Delta'),
('officer2','pass','Officer','Charlie'),
('admin','admin123','Admin','Alpha');


/* =======================================
   TARGETS TABLE
   ======================================= */

CREATE TABLE Targets(
    Target_ID INT AUTO_INCREMENT PRIMARY KEY,
    Country VARCHAR(50),
    Coordinates VARCHAR(100),
    Locked_Status VARCHAR(20)
);

INSERT INTO Targets (Country,Coordinates,Locked_Status) VALUES
('CountryA','23.45,45.12','Unlocked'),
('CountryB','50.21,30.11','Locked'),
('CountryC','12.44,60.55','Unlocked');


/* =======================================
   MISSILES TABLE
   ======================================= */

CREATE TABLE Missiles(
    Missile_ID INT AUTO_INCREMENT PRIMARY KEY,
    Type VARCHAR(50),
    Range_km INT,
    Fuel VARCHAR(50),
    Status VARCHAR(20),
    Locked_Target_ID INT,
    Classification_Level VARCHAR(20)
);

INSERT INTO Missiles(Type,Range_km,Fuel,Status,Locked_Target_ID,Classification_Level) VALUES
('Cruise',1200,'Solid','Ready',1,'Delta'),
('Ballistic',3000,'Liquid','Standby',2,'Charlie'),
('Hypersonic',5000,'Solid','Ready',2,'Alpha'),
('Nuclear',8000,'Liquid','Ready',3,'Alpha');


/* =======================================
   SYSTEM HEALTH TABLE
   ======================================= */

CREATE TABLE System_Health(
    Health_Percentage INT,
    Last_Updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO System_Health VALUES (100,NOW());


/* =======================================
   OPTIONAL FLAG TABLE (if needed)
   ======================================= */

CREATE TABLE Flags(
    Flag_ID INT AUTO_INCREMENT PRIMARY KEY,
    Flag_Value VARCHAR(100),
    Description VARCHAR(200)
);

INSERT INTO Flags (Flag_Value,Description) VALUES
('FLAG{ALPHA_CLASSIFIED_ACCESS}','Access classified missile inventory'),
('FLAG{ADMIN_PRIV_ESC}','Admin privilege escalation'),
('FLAG{SYSTEM_HEALTH_COMPROMISED}','Health manipulation endpoint'),
('FLAG{DATABASE_DESTROYED}','Missile database deletion');


INSERT INTO Missiles (Type,Range_km,Fuel,Status,Locked_Target_ID,Classification_Level) VALUES ('Tactical', 500, 'Solid', 'Ready', 1, 'Delta'), 
('Surface-to-Air', 300, 'Solid', 'Standby', 2, 'Delta'), 
('Cruise Advanced', 1500, 'Liquid', 'Ready', 3, 'Charlie'), 
('Long Range Ballistic', 4000, 'Liquid', 'Standby', 1, 'Beta'), 
('Stealth Strike', 2500, 'Solid', 'Ready', 2, 'Charlie'), 
('Hypersonic Strike', 6000, 'Liquid', 'Ready', 3, 'Alpha'), 
('Orbital Missile', 9000, 'Liquid', 'Standby', 2, 'Alpha'), ('Precision Tactical', 800, 'Solid', 'Ready', 1, 'Delta'), ('Interceptor', 700, 'Solid', 'Standby', 3, 'Charlie');