<?php
// Usanidi wa Muunganisho wa Database ya e-Mrejesho
$isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) || (php_sapi_name() === 'cli' && empty($_SERVER['HTTP_HOST']));

if ($isLocal) {
    // Usanidi wa WAMP / Localhost
    $host = "localhost";
    $username = "root";
    $password = "";
    $dbname = "e-mrejesho";
} else {
    // Usanidi wa Live cPanel (Badilisha hapa kulingana na MySQL ya cPanel yako)
    $host = "localhost";
    $username = "platinum_emrejesho"; // Jina la mtumiaji wa DB kwenye cPanel
    $password = "NenosiriLakoHapa";     // Nenosiri la DB kwenye cPanel
    $dbname   = "platinum_emrejesho"; // Jina la database kwenye cPanel
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Kushindwa kuunganisha kwenye kanzidata: " . $e->getMessage());
}