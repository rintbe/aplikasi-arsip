<?php
// actions/auth/register_process.php

require_once '../../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validasi CSRF Token
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('danger', 'Token keamanan tidak valid. Silakan coba lagi.');
        header("Location: ../../views/auth/register.php");
        exit;
    }

    // 2. Ambil dan sanitasi input
    $nik = filter_input(INPUT_POST, 'nik', FILTER_SANITIZE_STRING);
    $nama_lengkap = filter_input(INPUT_POST, 'nama_lengkap', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $no_hp = filter_input(INPUT_POST, 'no_hp', FILTER_SANITIZE_STRING);
    $alamat = filter_input(INPUT_POST, 'alamat', FILTER_SANITIZE_STRING);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 3. Validasi Input
    $errors = [];

    // Validasi NIK: Harus 16 digit angka
    if (!preg_match('/^[0-9]{16}$/', $nik)) {
        $errors[] = 'NIK harus terdiri dari 16 digit angka.';
    }

    if (empty($nama_lengkap)) {
        $errors[] = 'Nama lengkap wajib diisi.';
    }

    if (empty($alamat)) {
        $errors[] = 'Alamat lengkap wajib diisi.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    if (!empty($errors)) {
        // Gabungkan error menjadi satu pesan HTML
        $error_msg = implode('<br>', $errors);
        set_flash_message('danger', $error_msg);
        header("Location: ../../views/auth/register.php");
        exit;
    }

    // 4. Cek NIK atau Email yang sudah terdaftar
    $stmt = $pdo->prepare("SELECT id FROM users WHERE nik = ? OR email = ?");
    $stmt->execute([$nik, $email]);
    if ($stmt->rowCount() > 0) {
        set_flash_message('danger', 'NIK atau Email sudah terdaftar.');
        header("Location: ../../views/auth/register.php");
        exit;
    }

    // 5. Hash Password (Bcrypt)
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // 6. Generate Verification Token
    $verification_token = bin2hex(random_bytes(32));

    try {
        // 7. Simpan data ke database
        $sql = "INSERT INTO users (nik, nama_lengkap, email, no_hp, alamat, password, verification_token, role, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'warga', 0)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nik, $nama_lengkap, $email, $no_hp, $alamat, $hashed_password, $verification_token]);

        // 8. Simulasi Pengiriman Email
        $activation_link = "http://localhost/aplikasi_arsip_teluknaga/actions/auth/activate.php?token=" . $verification_token;
        
        // Dalam implementasi nyata, gunakan PHPMailer untuk mengirim email ini.
        // Di sini kita mencatat log sebagai simulasi.
        error_log("SIMULASI EMAIL TERKIRIM KE: " . $email);
        error_log("Subjek: Aktivasi Akun Arsip Teluknaga");
        error_log("Pesan: Klik tautan berikut untuk mengaktifkan akun Anda: " . $activation_link);

        set_flash_message('success', 'Registrasi berhasil! Silakan periksa email Anda (simulasi) untuk tautan aktivasi.');
        header("Location: ../../views/auth/login.php");
        exit;

    } catch (PDOException $e) {
        // Tangkap error jika ada kegagalan insert
        error_log("Registration error: " . $e->getMessage());
        set_flash_message('danger', 'Terjadi kesalahan sistem saat registrasi.');
        header("Location: ../../views/auth/register.php");
        exit;
    }

} else {
    // Jika bukan POST request, arahkan kembali ke form
    header("Location: ../../views/auth/register.php");
    exit;
}
?>
