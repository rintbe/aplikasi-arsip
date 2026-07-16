<?php
// actions/auth/register_process.php

require_once '../../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validasi CSRF Token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('danger', 'Token keamanan tidak valid. Silakan coba lagi.');
        header("Location: ../../views/auth/register.php");
        exit;
    }

    // 2. Ambil dan sanitasi input
    $nik = $_POST['nik'] ?? '';
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
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
        // Kolom di database bernama 'alamat_ktp', bukan 'alamat'. Serta kolom 'username' wajib diisi (kita gunakan NIK sebagai username default).
        $sql = "INSERT INTO users (nik, username, nama_lengkap, email, no_hp, alamat_ktp, password, verification_token, role, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'warga', 0)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nik, $nik, $nama_lengkap, $email, $no_hp, $alamat, $hashed_password, $verification_token]);

        // 8. Kirim Email Verifikasi
        $activation_link = "http://localhost/aplikasi_arsip_teluknaga/actions/auth/activate.php?token=" . $verification_token;
        
        require '../../vendor/autoload.php';
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Konfigurasi Server SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            
            // ==========================================
            // KREDENSIAL DIAMBIL DARI CONFIG RAHASIA
            // ==========================================
            $email_config = require '../../config/email_config.php';
            $mail->Username   = $email_config['username'];
            $mail->Password   = $email_config['password'];
            // ==========================================
            
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Pengirim dan Penerima
            $mail->setFrom($email_config['username'], 'Aplikasi Arsip Teluknaga');
            $mail->addAddress($email, $nama_lengkap);

            // Konten Email
            $mail->isHTML(true);
            $mail->Subject = 'Aktivasi Akun Aplikasi Arsip Teluknaga';
            $mail->Body    = "Halo $nama_lengkap,<br><br>
                              Terima kasih telah mendaftar di Aplikasi Arsip Teluknaga.<br>
                              Silakan klik tombol di bawah ini untuk mengaktifkan akun Anda:<br><br>
                              <a href='$activation_link' style='padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Aktifkan Akun</a><br><br>
                              Atau copy link berikut ke browser Anda:<br>
                              $activation_link<br><br>
                              Jika Anda tidak merasa mendaftar di aplikasi ini, abaikan saja email ini.";

            $mail->send();
            
            set_flash_message('success', 'Registrasi berhasil! Silakan periksa kotak masuk (inbox) atau folder spam di email Anda untuk link aktivasi.');
        } catch (Exception $e) {
            error_log("Email tidak dapat dikirim. Mailer Error: {$mail->ErrorInfo}");
            set_flash_message('warning', 'Registrasi berhasil, tetapi sistem gagal mengirim email aktivasi. Silakan hubungi admin.');
        }

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
