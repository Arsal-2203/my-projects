-- SQL script to create the daily_blog database and blogs table

CREATE DATABASE IF NOT EXISTS daily_blog;

USE daily_blog;

CREATE TABLE IF NOT EXISTS blogs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
