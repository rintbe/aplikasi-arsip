<?php
// actions/auth/forgot_password_process.php

require_once '../../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validasi CSRF Token
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('danger', 'Token keamanan tidak valid. Silakan coba lagi.');
        header("Location: ../../views/auth/forgot_password.php");
        exit;
    }

    // 2. Ambil dan sanitasi input
    $nik = filter_input(INPUT_POST, 'nik', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    // 3. Validasi Input
    if (empty($nik) || empty($email)) {
        set_flash_message('danger', 'NIK dan Email wajib diisi.');
        header("Location: ../../views/auth/forgot_password.php");
        exit;
    }

    try {
        // 4. Cek NIK dan Email di database
        $stmt = $pdo->prepare("SELECT id FROM users WHERE nik = ? AND email = ?");
        $stmt->execute([$nik, $email]);
        $user = $stmt->fetch();

        if ($user) {
            // Data cocok, buat sesi untuk reset password
            $_SESSION['reset_user_id'] = $user->id;
            $_SESSION['reset_token'] = bin2hex(random_bytes(16));
            
            set_flash_message('success', 'Verifikasi berhasil. Silakan masukkan password baru Anda.');
            header("Location: ../../views/auth/reset_password.php?token=" . $_SESSION['reset_token']);
            exit;
        } else {
            set_flash_message('danger', 'NIK atau Email yang Anda masukkan salah atau tidak terdaftar.');
            header("Location: ../../views/auth/forgot_password.php");
            exit;
        }

    } catch (PDOException $e) {
        error_log("Forgot Password Error: " . $e->getMessage());
        set_flash_message('danger', 'Terjadi kesalahan sistem.');
        header("Location: ../../views/auth/forgot_password.php");
        exit;
    }

} else {
    header("Location: ../../views/auth/forgot_password.php");
    exit;
}
?>
