-- À exécuter dans phpMyAdmin sur la base de données "hotel"

USE hotel;

CREATE TABLE IF NOT EXISTS users (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    nom          VARCHAR(100)  NOT NULL,
    email        VARCHAR(150)  NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255)  NOT NULL,
    role         ENUM('admin', 'client') DEFAULT 'client',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Compte de test : admin@hotel.com / Admin1234
INSERT INTO users (nom, email, mot_de_passe, role)
VALUES ('Admin', 'admin@hotel.com', SHA2('Admin1234', 256), 'admin');


CREATE TABLE IF NOT EXISTS utilisateurs (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    nom          VARCHAR(100)  NOT NULL,
    email        VARCHAR(150)  NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255)  NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
