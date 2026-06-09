<?php
require_once __DIR__ . '/../config/db_connect.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS surat_keluar (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        nomor_surat VARCHAR(100) NOT NULL,
        instansi_tujuan VARCHAR(255) NOT NULL,
        perihal TEXT NOT NULL,
        tanggal_pembuatan_surat DATE NOT NULL,
        tanggal_pengiriman_surat DATE NOT NULL,
        file_pdf VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Tabel surat_keluar berhasil dibuat atau sudah ada.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
