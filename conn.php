<?php

function getDatabaseConfig()
{
    return require __DIR__ . '/db.php';
}

function getDbConnection()
{
    $config = getDatabaseConfig();
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['dbname'],
        $config['charset']
    );

    try {
        return new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
}

function createDatabaseIfNeeded()
{
    $config = getDatabaseConfig();
    $dsn = sprintf('mysql:host=%s;charset=%s', $config['host'], $config['charset']);

    try {
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . $config['dbname'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    } catch (PDOException $e) {
        die('Database setup failed: ' . $e->getMessage());
    }
}

function ensureSchemaExists()
{
    $pdo = getDbConnection();

    $tables = [
        'admin_users' => "
            CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ",
        'clients' => "
            CREATE TABLE IF NOT EXISTS clients (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                phone VARCHAR(40) NOT NULL,
                email VARCHAR(120) NOT NULL,
                preferred_style VARCHAR(80) DEFAULT NULL,
                fitting_date DATE DEFAULT NULL,
                fitting_time TIME DEFAULT NULL,
                fitting_status VARCHAR(30) DEFAULT 'Pending',
                fitting_notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ",
        'gowns' => "
            CREATE TABLE IF NOT EXISTS gowns (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                style VARCHAR(80) NOT NULL,
                size VARCHAR(20) NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'Available',
                image VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ",
        'fittings' => "
            CREATE TABLE IF NOT EXISTS fittings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                client_id INT NOT NULL,
                gown_id INT DEFAULT NULL,
                fitting_date DATE NOT NULL,
                fitting_time TIME NOT NULL,
                notes TEXT,
                status VARCHAR(30) NOT NULL DEFAULT 'Pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
                FOREIGN KEY (gown_id) REFERENCES gowns(id) ON DELETE SET NULL
            ) ENGINE=InnoDB
        ",
        'inspections' => "
            CREATE TABLE IF NOT EXISTS inspections (
                id INT AUTO_INCREMENT PRIMARY KEY,
                gown_id INT NOT NULL,
                inspector VARCHAR(120) NOT NULL,
                inspection_date DATE NOT NULL,
                result VARCHAR(50) NOT NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (gown_id) REFERENCES gowns(id) ON DELETE CASCADE
            ) ENGINE=InnoDB
        ",
        'reservations' => "
            CREATE TABLE IF NOT EXISTS reservations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                client_id INT NOT NULL,
                gown_id INT NOT NULL,
                event_date DATE NOT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'Pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
                FOREIGN KEY (gown_id) REFERENCES gowns(id) ON DELETE CASCADE
            ) ENGINE=InnoDB
        ",
    ];

    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }

    $columns = [
        'fitting_date' => 'ALTER TABLE clients ADD COLUMN fitting_date DATE DEFAULT NULL',
        'fitting_time' => 'ALTER TABLE clients ADD COLUMN fitting_time TIME DEFAULT NULL',
        'fitting_status' => "ALTER TABLE clients ADD COLUMN fitting_status VARCHAR(30) DEFAULT 'Pending'",
        'fitting_notes' => 'ALTER TABLE clients ADD COLUMN fitting_notes TEXT',
    ];

    $existingColumns = $pdo->query('SHOW COLUMNS FROM clients')->fetchAll(PDO::FETCH_COLUMN);

    foreach ($columns as $column => $sql) {
        if (!in_array($column, $existingColumns, true)) {
            $pdo->exec($sql);
        }
    }

    $gownColumns = [
        'image' => 'ALTER TABLE gowns ADD COLUMN image VARCHAR(255) DEFAULT NULL',
    ];

    $existingGownColumns = $pdo->query('SHOW COLUMNS FROM gowns')->fetchAll(PDO::FETCH_COLUMN);

    foreach ($gownColumns as $column => $sql) {
        if (!in_array($column, $existingGownColumns, true)) {
            $pdo->exec($sql);
        }
    }
}

createDatabaseIfNeeded();
ensureSchemaExists();
