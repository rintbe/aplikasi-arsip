<?php
require_once __DIR__ . '/../config/db_connect.php';

try {
    $sql = "ALTER TABLE pengajuan_surat ADD COLUMN data_meta TEXT NULL AFTER keperluan";
    $pdo->exec($sql);
    echo "Kolom data_meta berhasil ditambahkan ke tabel pengajuan_surat.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Kolom data_meta sudah ada.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
