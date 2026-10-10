<?php
// Usanidi wa Muunganisho wa Database ya e-Mrejesho
$username = "root";
$password = "";
$host = "localhost";
$dbname = "e-mrejesho";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Kushindwa kuunganisha kwenye kanzidata: " . $e->getMessage());
}