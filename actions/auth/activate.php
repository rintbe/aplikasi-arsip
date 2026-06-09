<?php
// actions/auth/activate.php

require_once '../../config/db_connect.php';

$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);

if (empty($token)) {
    set_flash_message('danger', 'Token aktivasi tidak valid atau hilang.');
    // header("Location: ../../views/auth/login.php");
    echo "Gagal: Token tidak ada.";
    exit;
}

try {
    // Cek apakah token ada dan akun belum aktif
    $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_token = ? AND is_active = 0");
    $stmt->execute([$token]);
    
    if ($stmt->rowCount() > 0) {
        // Update is_active menjadi 1 dan kosongkan verification_token
        $update_stmt = $pdo->prepare("UPDATE users SET is_active = 1, verification_token = NULL WHERE verification_token = ?");
        $update_stmt->execute([$token]);
        
        set_flash_message('success', 'Akun berhasil diaktifkan! Silakan login.');
        // header("Location: ../../views/auth/login.php");
        echo "Sukses: Akun telah aktif. Silakan kembali ke halaman login.";
    } else {
        set_flash_message('danger', 'Link aktivasi tidak valid atau akun sudah aktif.');
        // header("Location: ../../views/auth/login.php");
        echo "Gagal: Token tidak valid atau akun sudah aktif.";
    }
} catch (PDOException $e) {
    error_log("Activation error: " . $e->getMessage());
    set_flash_message('danger', 'Terjadi kesalahan sistem saat aktivasi.');
    echo "Gagal: Terjadi kesalahan sistem.";
}
?>
