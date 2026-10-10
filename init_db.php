<?php
$host = "localhost";
$username = "root";
$password = "";

try {
    // Unganisha kwenye MySQL Server bila kutaja database kwanza
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Tengeneza database kama haipo
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `e-mrejesho` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database `e-mrejesho` ipo tayari.\n";

    // Unganisha kwenye database `e-mrejesho`
    $pdo->exec("USE `e-mrejesho`");

    // Tengeneza table ya maoni ya idara ya mionzi
    $tableSql = "CREATE TABLE IF NOT EXISTS `maoni_mionzi` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `jina_mgonjwa` VARCHAR(150) DEFAULT 'Bila Jina (Anonymous)',
        `simu` VARCHAR(30) DEFAULT NULL,
        `aina_ya_kipimo` VARCHAR(100) NOT NULL,
        `rating_ujumla` INT NOT NULL DEFAULT 5,
        `rating_muda` INT NOT NULL DEFAULT 5,
        `rating_wataalamu` INT NOT NULL DEFAULT 5,
        `rating_usafi` INT NOT NULL DEFAULT 5,
        `rating_maelekezo` INT NOT NULL DEFAULT 5,
        `maoni` TEXT DEFAULT NULL,
        `hali` VARCHAR(30) DEFAULT 'Mapya',
        `tarehe_iliyoundwa` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($tableSql);
    echo "Jedwali `maoni_mionzi` limetengenezwa kikamilifu.\n";

    // Tengeneza table ya watumiaji wa admin kama inahitajika
    $adminTableSql = "CREATE TABLE IF NOT EXISTS `watumiaji` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `jina` VARCHAR(100) NOT NULL,
        `barua_pepe` VARCHAR(100) UNIQUE NOT NULL,
        `nenosiri` VARCHAR(255) NOT NULL,
        `cheo` VARCHAR(50) DEFAULT 'Afisa Mionzi',
        `tarehe` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($adminTableSql);

    // Weka mtumiaji wa majaribio wa admin kama hayupo
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `watumiaji` WHERE `barua_pepe` = 'admin@mionzi.go.tz'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash("admin123", PASSWORD_DEFAULT);
        $insertAdmin = $pdo->prepare("INSERT INTO `watumiaji` (`jina`, `barua_pepe`, `nenosiri`, `cheo`) VALUES ('Mkuu wa Idara ya Mionzi', 'admin@mionzi.go.tz', ?, 'Msimamizi')");
        $insertAdmin->execute([$hash]);
        echo "Mtumiaji wa admin ameongezwa: admin@mionzi.go.tz / admin123\n";
    }

} catch (PDOException $e) {
    echo "Hitilafu: " . $e->getMessage() . "\n";
}
