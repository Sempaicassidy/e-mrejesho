-- ====================================================================
-- KANZIDATA YA MFUMO WA E-MREJESHO (IDARA YA MIONZI)
-- Toleo: 1.0
-- Matumizi: Ingiza faili hili kwenye phpMyAdmin au MySQL Server
-- ====================================================================

-- 1. Tengeneza Kanzidata (Database)
CREATE DATABASE IF NOT EXISTS `e-mrejesho` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `e-mrejesho`;

-- ====================================================================
-- 2. Jedwali la Maoni ya Wagonjwa (maoni_mionzi)
-- Hili jedwali linapokea maoni yanayotumwa baada ya mgonjwa ku-scan QR
-- ====================================================================
DROP TABLE IF EXISTS `maoni_mionzi`;
CREATE TABLE `maoni_mionzi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `jina_mgonjwa` VARCHAR(150) NOT NULL DEFAULT 'Bila Jina (Anonymous)' COMMENT 'Jina la mgonjwa au Anonymous',
    `simu` VARCHAR(30) DEFAULT NULL COMMENT 'Namba ya simu ya hiari kwa mrejesho',
    `aina_ya_kipimo` VARCHAR(100) NOT NULL COMMENT 'X-Ray, Ultrasound, CT-Scan, MRI, Mammography',
    `rating_ujumla` INT NOT NULL DEFAULT 5 COMMENT 'Alama ya ubora kwa ujumla (1-5)',
    `rating_muda` INT NOT NULL DEFAULT 5 COMMENT 'Alama ya muda wa kusubiri (1-5)',
    `rating_wataalamu` INT NOT NULL DEFAULT 5 COMMENT 'Alama ya ukarimu na weledi wa wataalamu (1-5)',
    `rating_usafi` INT NOT NULL DEFAULT 5 COMMENT 'Alama ya usafi na faragha (1-5)',
    `rating_maelekezo` INT NOT NULL DEFAULT 5 COMMENT 'Alama ya uelewa wa maelekezo (1-5)',
    `maoni` TEXT DEFAULT NULL COMMENT 'Maelezo ya ziada, pongezi au changamoto',
    `hali` VARCHAR(30) NOT NULL DEFAULT 'Mapya' COMMENT 'Hali ya mrejesho: Mapya / Yameshughulikiwa',
    `tarehe_iliyoundwa` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 3. Jedwali la Wasimamizi / Watumiaji wa Mfumo (watumiaji)
-- Hili jedwali linatumiwa kuingia kwenye RadioAdmin dashboard
-- ====================================================================
DROP TABLE IF EXISTS `watumiaji`;
CREATE TABLE `watumiaji` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `jina` VARCHAR(100) NOT NULL COMMENT 'Jina la afisa/msimamizi',
    `barua_pepe` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Barua pepe ya kuingilia mfumo',
    `nenosiri` VARCHAR(255) NOT NULL COMMENT 'Nenosiri lililolindwa (password_hash)',
    `cheo` VARCHAR(50) DEFAULT 'Mkuu wa Idara ya Mionzi',
    `tarehe` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 4. Mtumiaji wa Awali wa Utawala (Admin Default Account)
-- Barua pepe: admin@mionzi.go.tz
-- Nenosiri:   admin123
-- ====================================================================
INSERT INTO `watumiaji` (`jina`, `barua_pepe`, `nenosiri`, `cheo`) 
VALUES (
    'Mkuu wa Idara ya Mionzi', 
    'admin@mionzi.go.tz', 
    '$2y$10$DXVa4IZwP5CRI5YHh1qhV.FdY7CBBGBXyccRgovcJfWORgDgd5yPO', 
    'Msimamizi Mkuu'
)
ON DUPLICATE KEY UPDATE `id` = `id`;

-- ====================================================================
-- 5. Data za Sampuli (Sample Data za Majaribio)
-- ====================================================================
INSERT INTO `maoni_mionzi` 
(`jina_mgonjwa`, `simu`, `aina_ya_kipimo`, `rating_ujumla`, `rating_muda`, `rating_wataalamu`, `rating_usafi`, `rating_maelekezo`, `maoni`, `hali`) 
VALUES 
('Amina Bakari', '0712345678', 'X-Ray (Mionzi ya Kawaida)', 5, 4, 5, 5, 5, 'Huduma ilitolewa kwa wakati stahiki na wataalamu walikuwa na ukarimu mkubwa.', 'Mapya'),
('Bila Jina (Anonymous)', NULL, 'Ultrasound (Mawimbi ya Sauti)', 4, 3, 5, 4, 4, 'Wataalamu walieleza vizuri utaratibu wa kipimo. Changamoto ilikuwa muda wa kusubiri.', 'Mapya'),
('Baraka Joseph', '0754001122', 'CT-Scan (Mionzi ya Tabaka)', 5, 5, 5, 5, 5, 'Chumba cha CT-Scan kilikuwa safi sana na mazingira yenye faragha ya kipekee.', 'Yameshughulikiwa');
