<?php

$host = 'localhost';
$dbname = 'merbless_db';
$username = 'root';
$password = ''; // Karaniwang blank sa default na XAMPP setup.
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    exit('Hindi makakonekta sa database. Tingnan kung tumatakbo ang MySQL sa XAMPP.');
}