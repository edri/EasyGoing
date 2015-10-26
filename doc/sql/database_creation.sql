/*
    SQL script to create database EasyGoing
    Author : Raphael Racine
    Creation Date : 25.10.2015
*/

/* Delete the schema if already exists and creation of a new schema */
DROP SCHEMA IF EXISTS easygoing;
CREATE SCHEMA easygoing;

/* Creation of tables */
USE easygoing;

CREATE TABLE users
(
    id INT NOT NULL AUTO_INCREMENT,
    email VARCHAR(50) NOT NULL,
	username VARCHAR(30) NOT NULL,
    hashedPassword VARCHAR(64) NOT NULL,
    firstName VARCHAR(30) NOT NULL,
    lastName VARCHAR(30) NOT NULL,
    filePhoto VARCHAR(30),
    wantTutorial BOOLEAN NOT NULL DEFAULT TRUE,
    wantNotifications BOOLEAN NOT NULL DEFAULT TRUE,
    UNIQUE(email),
    PRIMARY KEY(id)
);

CREATE TABLE projects
(
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT, /* Maximum of 65'535 characters */
    startDate DATE NOT NULL,
    deadLineDate DATE,
    fileLogo VARCHAR(50),
    PRIMARY KEY(id)
);

CREATE TABLE tasks
(
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TINYTEXT, /* Maximum of 255 characters */ 
    deadLineDate DATE,
    durationsInHours FLOAT NOT NULL,
    priorityLevel TINYINT UNSIGNED NOT NULL DEFAULT 0,
    
    /* default color for task : yellow */
    estheticColorRGBRed TINYINT UNSIGNED DEFAULT 255, /* 0 - 255 */
    estheticColorRGBGreen TINYINT UNSIGNED DEFAULT 255, /* 0 - 255 */
    estheticColorRGBBlue TINYINT UNSIGNED DEFAULT 0, /* 0 - 255 */
    
    parentTask INT,
    
    project INT NOT NULL,
    
    PRIMARY KEY(id),
    FOREIGN KEY(parentTask) REFERENCES tasks(id),
    FOREIGN KEY(project) REFERENCES projects(id)
);

CREATE TABLE eventTypes
(
    id INT NOT NULL AUTO_INCREMENT,
    type ENUM('A définir...') NOT NULL UNIQUE,
    fileLogo VARCHAR(50),
    PRIMARY KEY(id)
);

CREATE TABLE events
(
    id INT NOT NULL AUTO_INCREMENT,
    date DATE NOT NULL,
    message TINYTEXT,
    eventType INT,
    PRIMARY KEY(id),
    FOREIGN KEY(eventType) REFERENCES eventTypes(id)
);

CREATE TABLE eventsOnTasks
(
    event INT NOT NULL,
    task INT NOT NULL,
    PRIMARY KEY(event),
    FOREIGN KEY(event) REFERENCES events(id),
    FOREIGN KEY(task) REFERENCES tasks(id)
);

CREATE TABLE eventsOnProjects
(
    event INT NOT NULL,
    project INT NOT NULL,
    PRIMARY KEY(event),
    FOREIGN KEY(event) REFERENCES events(id),
    FOREIGN KEY(project) REFERENCES projects(id)
);

CREATE TABLE eventsUsers
(
    user INT NOT NULL,
    event INT NOT NULL,
    FOREIGN KEY(user) REFERENCES users(id),
    FOREIGN KEY(event) REFERENCES events(id)
);

CREATE TABLE projectsUsersMembers
(
    user INT NOT NULL,
    project INT NOT NULL,
    isAdmin BOOLEAN NOT NULL,
    UNIQUE(user, project),
    FOREIGN KEY(user) REFERENCES users(id),
    FOREIGN KEY(project) REFERENCES projects(id)
);

CREATE TABLE projectsUsersSpecifications
(
    user INT NOT NULL,
    project INT NOT NULL,
    specialization VARCHAR(50) NOT NULL,
    UNIQUE(user, project, specialization),
    FOREIGN KEY(user) REFERENCES users(id),
    FOREIGN KEY(project) REFERENCES projects(id)
);

CREATE TABLE usersTasksAffectations
(
    user INT NOT NULL,
    task INT NOT NULL,
    UNIQUE(user, task),
    FOREIGN KEY(user) REFERENCES users(id),
    FOREIGN KEY(task) REFERENCES tasks(id)
);

CREATE TABLE usersTasksProductions 
(
    user INT NOT NULL,
    task INT NOT NULL,
    effectiveDurationInHours FLOAT NOT NULL,
    FOREIGN KEY(user) REFERENCES users(id),
    FOREIGN KEY(task) REFERENCES tasks(id)
);

/* Insert some data */
INSERT INTO users
VALUES(
	null, 
	"raphael.racine@heig-vd.ch",
	"raphaelracine",
	"1bfbdf35b1359fc6b6f93893874cf23a50293de5",
	"Raphaël",
	"Racine",
	"raphael.jpg",
	true, true
);

INSERT INTO users
VALUES(
	null, 
	"karim.ghozlani@heig-vd.ch",
	"karimghozlani",
	"1bfbdf35b1359fc6b6f93893874cf23a50293de5",
	"Karim",
	"Ghozlani",
	"karim.jpg",
	false, true
);

INSERT INTO users
VALUES(
	null, 
	"thibault.duchoud@heig-vd.ch",
	"thibaudduchoud",
	"1bfbdf35b1359fc6b6f93893874cf23a50293de5",
	"Thibault",
	"Duchoud",
	"thibault.jpg",
	true, false
);

INSERT INTO users
VALUES(
	null, 
	"miguel.santamaria@heig-vd.ch",
	"miguelsantamaria",
	"1bfbdf35b1359fc6b6f93893874cf23a50293de5",
	"Miguel",
	"Santamaria",
	"miguel.jpg",
	false, false
);

INSERT INTO users
VALUES(
	null, 
	"vanessa.meguep@heig-vd.ch",
	"vanessameguep",
	"1bfbdf35b1359fc6b6f93893874cf23a50293de5",
	"Vanessa",
	"Meguep",
	"vanessa.jpg",
	true, true
);

/* Stored procedures and functions */
USE easygoing;

DELIMITER $$
DROP FUNCTION IF EXISTS checkLogin $$

/* This function check if a user can login or not */
CREATE FUNCTION checkLogin
(
	username VARCHAR(30),
	hashedPassword VARCHAR(64)
)
RETURNS BOOLEAN
BEGIN
	RETURN EXISTS(
		SELECT * 
		FROM users AS u 
		WHERE u.username = username AND u.hashedPassword = hashedPassword
	);
END $$
DELIMITER ;


