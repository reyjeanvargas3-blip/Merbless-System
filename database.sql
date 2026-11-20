CREATE DATABASE IF NOT EXISTS merbless_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE merbless_db;

CREATE TABLE IF NOT EXISTS clients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  contact VARCHAR(20),
  bust INT,
  waist INT,
  hips INT,
  photo VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS gowns (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  size VARCHAR(20),
  color VARCHAR(30),
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status ENUM('Available', 'Reserved', 'Rented', 'For Cleaning', 'Damaged')
    NOT NULL DEFAULT 'Available',
  image VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  gown_id INT NOT NULL,
  reserve_date DATE NOT NULL,
  event_date DATE NOT NULL,
  return_date DATE,
  status VARCHAR(20) NOT NULL DEFAULT 'Reserved',
  FOREIGN KEY (client_id) REFERENCES clients(id),
  FOREIGN KEY (gown_id) REFERENCES gowns(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS virtual_fitting (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  gown_id INT NOT NULL,
  fitting_result VARCHAR(100),
  date_tried DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES clients(id),
  FOREIGN KEY (gown_id) REFERENCES gowns(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO gowns (code, name, size, color, price, status, image) VALUES
('GWN001', 'White Ball Gown', 'Medium', 'White', 2500.00, 'Available', 'gown1.png'),
('GWN002', 'Red Debut Gown', 'Small', 'Red', 2000.00, 'Available', 'gown2.png');