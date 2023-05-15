CREATE DATABASE todo;

USE todo;

CREATE TABLE user_table
(
    id       int         NOT NULL AUTO_INCREMENT,
    name     varchar(20) NOT NULL,
    password varchar(20) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE todo_table
(
    id     int     NOT NULL AUTO_INCREMENT,
    UserId int(20) NOT NULL,
    Datum  DATE,
    todo   varchar(100),
    PRIMARY KEY (id),
    FOREIGN KEY (UserId) REFERENCES user_table (id)
);

INSERT INTO user_table (name, password)
VALUES ("Thea", "0000"),
       ("Lara", "1111"),
       ("Luisa", "2222")
;