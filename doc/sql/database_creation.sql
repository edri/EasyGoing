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
	UNIQUE(username),
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
	state ENUM('TODO', 'DOING', 'DONE') NOT NULL DEFAULT 'TODO',
    
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

/* Views */

/* This view show all the projects with all members id which are in the project */
DROP VIEW IF EXISTS view_projects_min;

CREATE VIEW view_projects_min AS
(
	SELECT p.id, p.name, p.fileLogo, pu.user AS userId, pu.isAdmin
	FROM projectsUsersMembers as pu
		INNER JOIN projects AS p ON p.id = pu.project
	ORDER BY p.name	 
);

/* This view show all the members of a project and theirs specializations in the same project */
DROP VIEW IF EXISTS view_projects_members_specializations;

CREATE VIEW view_projects_members_specializations AS
(
	SELECT pum.project, u.username, pus.specialization, pum.isAdmin
	FROM projectsUsersMembers AS pum 
		INNER JOIN users AS u 
			ON u.id = pum.user
		LEFT JOIN projectsUsersSpecializations AS pus 
			ON u.id = pus.user AND pus.project = pum.project
);

DROP VIEW IF EXISTS view_users_projects;

CREATE VIEW view_users_projects AS
(
	SELECT * 
	FROM users
	INNER JOIN projectsUsersMembers ON users.id = projectsUsersMembers.user
);

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

/* This function check if a user can produce in a task */
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

/* This function check if a task have a parent task */
USE easygoing;

DELIMITER $$
DROP FUNCTION IF EXISTS taskHasParent $$

CREATE FUNCTION taskHasParent
(
	task INT
)
RETURNS BOOLEAN
BEGIN
	RETURN EXISTS(
		SELECT parentTask 
		FROM tasks 
		WHERE id = task AND parentTask <> NULL
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

USE easygoing;
DROP TRIGGER IF EXISTS tasksBeforeInsert

DELIMITER $$
USE easygoing $$

CREATE TRIGGER tasksBeforeInsert
BEFORE INSERT ON tasks
FOR EACH ROW
BEGIN

	IF taskHasParent(NEW.parentTask) THEN
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = "The parent_task has a parent.";
	END IF;
	
END $$

DELIMITER ;
USE easygoing;
DROP TRIGGER IF EXISTS tasksBeforeUpdate

DELIMITER $$
USE easygoing $$

CREATE TRIGGER tasksBeforeUpdate
BEFORE UPDATE ON tasks
FOR EACH ROW
BEGIN

	IF taskHasParent(NEW.parentTask) THEN
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = "The parent_task has a parent.";
	END IF;
	
END $$

DELIMITER ;

/* Insert some data */
INSERT INTO users
VALUES(
	null, 
	"raphael.racine@heig-vd.ch",
	"raphaelracine",
	"e35e61fb41f672d781d24d3f5c793b754ee88b41dc43c712477a9f06e1fdb616",
	"Raphaël",
	"Racine",
	"default.png",
	true, true
);

INSERT INTO users
VALUES(
	null, 
	"karim.ghozlani@heig-vd.ch",
	"karimghozlani",
	"e35e61fb41f672d781d24d3f5c793b754ee88b41dc43c712477a9f06e1fdb616",
	"Karim",
	"Ghozlani",
	"default.png",
	false, true
);

INSERT INTO users
VALUES(
	null, 
	"thibault.duchoud@heig-vd.ch",
	"thibaudduchoud",
	"e35e61fb41f672d781d24d3f5c793b754ee88b41dc43c712477a9f06e1fdb616",
	"Thibault",
	"Duchoud",
	"default.png",
	true, false
);

INSERT INTO users
VALUES(
	null, 
	"miguel.santamaria@heig-vd.ch",
	"miguelsantamaria",
	"e35e61fb41f672d781d24d3f5c793b754ee88b41dc43c712477a9f06e1fdb616",
	"Miguel",
	"Santamaria",
	"default.png",
	false, false
);

INSERT INTO users
VALUES(
	null, 
	"vanessa.meguep@heig-vd.ch",
	"vanessameguep",
	"e35e61fb41f672d781d24d3f5c793b754ee88b41dc43c712477a9f06e1fdb616",
	"Vanessa",
	"Meguep",
	"default.png",
	true, true
);

SELECT id INTO @user1
FROM users
WHERE username = 'raphaelracine';

SELECT id INTO @user2
FROM users
WHERE username = 'karimghozlani';

SELECT id INTO @user3
FROM users
WHERE username = 'miguelsantamaria';

SELECT id INTO @user4
FROM users
WHERE username = 'thibaudduchoud';

SELECT id INTO @user5
FROM users
WHERE username = 'vanessameguep';

/* Create some projects */
INSERT INTO projects(name, description, startDate, deadLineDate) VALUES
(
	"Travail de Bachelor",	
	"Un projet difficile... Mais intéressant !", 
	"2015-01-26", 
	"2016-10-04"
);

INSERT INTO projects(name, description, startDate) VALUES
(
	"TWEB Liechti Moustache Project",
	"Description is too long and unuseful...",
	"2015-03-06"
);

SELECT id INTO @project1
FROM projects
WHERE name = 'Travail de Bachelor';

SELECT id INTO @project2
FROM projects
WHERE name = 'TWEB Liechti Moustache Project';

INSERT INTO projectsUsersMembers VALUES(@user1, @project1, true);
INSERT INTO projectsUsersMembers VALUES(@user2, @project1, false);
INSERT INTO projectsUsersMembers VALUES(@user3, @project1, true);
INSERT INTO projectsUsersMembers VALUES(@user4, @project1, false);
INSERT INTO projectsUsersMembers VALUES(@user3, @project2, false);
INSERT INTO projectsUsersMembers VALUES(@user4, @project2, true);
INSERT INTO projectsUsersMembers VALUES(@user5, @project2, false);

INSERT INTO projectsUsersSpecializations VALUES(@user1, @project1, "Base de données");
INSERT INTO projectsUsersSpecializations VALUES(@user1, @project1, "Programmation répartie");
INSERT INTO projectsUsersSpecializations VALUES(@user2, @project1, "Java 8");
INSERT INTO projectsUsersSpecializations VALUES(@user3, @project1, "Programmation C++");
INSERT INTO projectsUsersSpecializations VALUES(@user3, @project2, "Base de données");
INSERT INTO projectsUsersSpecializations VALUES(@user3, @project2, "Styles CSS");
INSERT INTO projectsUsersSpecializations VALUES(@user4, @project2, "Node JS");
INSERT INTO projectsUsersSpecializations VALUES(@user5, @project2, "Internet Explorer");

SET GLOBAL log_bin_trust_function_creators = 0; 
