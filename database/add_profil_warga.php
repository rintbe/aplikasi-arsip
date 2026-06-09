<?php
require_once __DIR__ . '/../config/db_connect.php';

try {
    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'foto_profil'");
    if ($stmt->rowCount() == 0) {
        $sql = "ALTER TABLE users 
                CHANGE alamat alamat_ktp TEXT,
                ADD foto_profil VARCHAR(255) NULL AFTER email,
                ADD tempat_lahir VARCHAR(100) NULL AFTER foto_profil,
                ADD tanggal_lahir DATE NULL AFTER tempat_lahir,
                ADD alamat_domisili TEXT NULL AFTER alamat_ktp";
        $pdo->exec($sql);
        echo "Tabel users berhasil diupdate.";
    } else {
        echo "Kolom sudah ada.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
