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
    hashedPassword VARCHAR(32) NOT NULL,
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
    description TINYTEXT, /* Maximum of 255 characters */
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