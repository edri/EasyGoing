/*
    SQL script to create database EasyGoing
    Author : Raphael Racine
    Creation Date : 25.10.2015
	Last Modified : 26.10.2015
*/

/* Delete the schema if already exists and creation of a new schema */
DROP SCHEMA IF EXISTS easygoing;
CREATE SCHEMA easygoing;

/* Creation of tables */
USE easygoing;

/* To avoid error 1418 while creating functions and procedures */
SET GLOBAL log_bin_trust_function_creators = 1; 

CREATE TABLE users
(
    id INT NOT NULL AUTO_INCREMENT,
    email VARCHAR(50) NOT NULL,
	username VARCHAR(30) NOT NULL,
    hashedPassword VARCHAR(64) NOT NULL, /* Algorithm SHA-256 */
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

CREATE TABLE projectsUsersSpecializations
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

/* Stored procedures and functions */

/* This function check if a user can be affected to a task */
USE easygoing;

DELIMITER $$
DROP FUNCTION IF EXISTS checkUserCanBeAffectedToTask $$

CREATE FUNCTION checkUserCanBeAffectedToTask
(
	task INT,
	user INT
)
RETURNS BOOLEAN
BEGIN
	RETURN NOT EXISTS
	(
		SELECT t.project 
		FROM tasks AS t
		WHERE t.id = task AND t.project IN
		(
			SELECT pum.project
			FROM projectsUsersMembers AS pum
			WHERE pum.user = user
		)
	);
END $$
DELIMITER;

USE easygoing;

DELIMITER $$
DROP FUNCTION IF EXISTS checkUserCanProduceInTask $$

CREATE FUNCTION checkUserCanProduceInTask
(
	task INT,
	user INT
)
RETURNS BOOLEAN
BEGIN
	RETURN EXISTS
	(
		SELECT * 
		FROM usersTasksAffectations AS uta 
		WHERE uta.task = task 
			AND uta.user = user
	);
END $$
DELIMITER;

/* This function check if a user can login or not */
USE easygoing;

DELIMITER $$
DROP FUNCTION IF EXISTS checkLogin $$

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


/* TRIGGERS */
USE easygoing;
DROP TRIGGER IF EXISTS usersTasksAffectationsBeforeInsert;

DELIMITER $$
USE easygoing $$

CREATE TRIGGER usersTasksAffectationsBeforeInsert
BEFORE INSERT ON usersTasksAffectations
FOR EACH ROW
BEGIN
		
	IF checkUserCanBeAffectedToTask(NEW.task, NEW.user) THEN
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = "Impossible to affect this task at this user. He is not a member of the project where the task is";
	END IF;
	
END $$

DELIMITER ;

USE easygoing;
DROP TRIGGER IF EXISTS usersTasksAffectationsBeforeUpdate

DELIMITER $$
USE easygoing $$

CREATE TRIGGER usersTasksAffectationsBeforeUpdate
BEFORE UPDATE ON usersTasksAffectations
FOR EACH ROW
BEGIN
		
	IF checkUserCanBeAffectedToTask(NEW.task, NEW.user) THEN
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = "Impossible to affect this task at this user. He is not a member of the project where the task is";
	END IF;
	
END $$

DELIMITER ;

USE easygoing;
DROP TRIGGER IF EXISTS usersTasksProductionsBeforeInsert

DELIMITER $$
USE easygoing $$

CREATE TRIGGER usersTasksProductionsBeforeInsert
BEFORE INSERT ON usersTasksProductions
FOR EACH ROW
BEGIN

	IF NOT checkUserCanProduceInTask(NEW.task, NEW.user) THEN
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = "User is not affected at this task";
	END IF;
	
END $$

DELIMITER ;

USE easygoing;
DROP TRIGGER IF EXISTS usersTasksProductionsBeforeUpdate

DELIMITER $$
USE easygoing $$

CREATE TRIGGER usersTasksProductionsBeforeUpdate
BEFORE UPDATE ON usersTasksProductions
FOR EACH ROW
BEGIN

	IF NOT checkUserCanProduceInTask(NEW.task, NEW.user) THEN
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = "User is not affected at this task";
	END IF;
	
END $$

DELIMITER ;

/*
•	Réalisation de tâche : Un utilisateur ne peut pas réaliser une tâche s’il n’y est pas affecté.
•	Sous tâche : Une tâche qui a déjà une tâche parente ne peut pas avoir de sous-tâche. Autrement dit, on s’arrête à un seul niveau de sous-tâche.*/


/* Insert some data */
INSERT INTO users
VALUES(
	null, 
	"raphael.racine@heig-vd.ch",
	"raphaelracine",
	"d74ff0ee8da3b9806b18c877dbf29bbde50b5bd8e4dad7a3a725000feb82e8f1",
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
	"d74ff0ee8da3b9806b18c877dbf29bbde50b5bd8e4dad7a3a725000feb82e8f1",
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
	"d74ff0ee8da3b9806b18c877dbf29bbde50b5bd8e4dad7a3a725000feb82e8f1",
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
	"d74ff0ee8da3b9806b18c877dbf29bbde50b5bd8e4dad7a3a725000feb82e8f1",
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
	"d74ff0ee8da3b9806b18c877dbf29bbde50b5bd8e4dad7a3a725000feb82e8f1",
	"Vanessa",
	"Meguep",
	"vanessa.jpg",
	true, true
);

SET GLOBAL log_bin_trust_function_creators = 0; 







