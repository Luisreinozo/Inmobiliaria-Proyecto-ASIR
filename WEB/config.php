<?php
// conexión que utilizo para otros scripts

$dbHost = '10.0.2.22';
$dbName = 'inmobiliaria';
$dbUser = 'webapp';
$dbPass = 'Web1234.';

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Error de conexión a la base de datos');
}

session_start();
