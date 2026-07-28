<?php
// actions/auth/reset_password_process.php

require_once '../../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validasi CSRF Token
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('danger', 'Token keamanan tidak valid. Silakan coba lagi.');
        header("Location: ../../views/auth/login.php");
        exit;
    }

    // 2. Validasi Token Reset dari form (database based)
    $token_post = $_POST['token'] ?? '';
    if (empty($token_post)) {
        set_flash_message('danger', 'Token reset tidak ditemukan.');
        header("Location: ../../views/auth/login.php");
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ?");
    $stmt->execute([$token_post]);
    $user = $stmt->fetch();

    if (!$user) {
        set_flash_message('danger', 'Token reset password tidak valid atau telah kedaluwarsa.');
        header("Location: ../../views/auth/login.php");
        exit;
    }

    // 3. Ambil input
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 4. Validasi Input
    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    if (!empty($errors)) {
        $error_msg = implode('<br>', $errors);
        set_flash_message('danger', $error_msg);
        header("Location: ../../views/auth/reset_password.php?token=" . $token_post);
        exit;
    }

    // 5. Update Password
    try {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        $updateStmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE id = ?");
        $updateStmt->execute([$hashed_password, $user->id]);

        set_flash_message('success', 'Password berhasil direset. Silakan login dengan password baru Anda.');
        header("Location: ../../views/auth/login.php");
        exit;

    } catch (PDOException $e) {
        error_log("Reset Password Error: " . $e->getMessage());
        set_flash_message('danger', 'Terjadi kesalahan sistem saat memperbarui password.');
        header("Location: ../../views/auth/reset_password.php?token=" . $token_post);
        exit;
    }

} else {
    header("Location: ../../views/auth/login.php");
    exit;
}
?>
