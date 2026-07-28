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
    $no_hp = trim($_POST['no_hp'] ?? '');
    $tanggal_lahir = trim($_POST['tanggal_lahir'] ?? '');
    $nama_ibu_kandung = trim($_POST['nama_ibu_kandung'] ?? '');

    // 3. Validasi Input
    if (empty($nik) || empty($email) || empty($no_hp) || empty($tanggal_lahir) || empty($nama_ibu_kandung)) {
        set_flash_message('danger', 'Semua form verifikasi wajib diisi.');
        header("Location: ../../views/auth/forgot_password.php");
        exit;
    }

    try {
        // 4. Cek semua data di database
        $stmt = $pdo->prepare("SELECT id, nama_lengkap FROM users WHERE nik = ? AND email = ? AND no_hp = ? AND tanggal_lahir = ? AND nama_ibu_kandung = ?");
        $stmt->execute([$nik, $email, $no_hp, $tanggal_lahir, $nama_ibu_kandung]);
        $user = $stmt->fetch();

        if ($user) {
            // Data cocok, buat reset_token
            $reset_token = bin2hex(random_bytes(32));
            
            // Update token ke database
            $updateStmt = $pdo->prepare("UPDATE users SET reset_token = ? WHERE id = ?");
            $updateStmt->execute([$reset_token, $user->id]);
            
            // 5. Kirim Email Verifikasi
            $reset_link = "http://localhost/aplikasi_arsip_teluknaga/views/auth/reset_password.php?token=" . $reset_token;
            
            require '../../vendor/autoload.php';
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                
                $email_config = require '../../config/email_config.php';
                $mail->Username   = $email_config['username'];
                $mail->Password   = $email_config['password'];
                
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                $mail->setFrom($email_config['username'], 'Aplikasi Arsip Teluknaga');
                $mail->addAddress($email, $user->nama_lengkap);

                $mail->isHTML(true);
                $mail->Subject = 'Reset Password Akun Arsip Teluknaga';
                $mail->Body    = "Halo {$user->nama_lengkap},<br><br>
                                  Kami menerima permintaan untuk mereset password akun Anda.<br>
                                  Silakan klik link di bawah ini untuk membuat password baru:<br><br>
                                  <a href='$reset_link' style='padding: 10px 20px; background-color: #e91e63; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Reset Password</a><br><br>
                                  Atau copy link berikut ke browser Anda:<br>
                                  $reset_link<br><br>
                                  Jika Anda tidak merasa meminta reset password, abaikan email ini.";

                $mail->send();
                
                set_flash_message('success', 'Verifikasi berhasil. Link reset password telah dikirim ke email Anda. Silakan cek Inbox atau folder Spam.');
            } catch (Exception $e) {
                error_log("Email reset password gagal dikirim. Mailer Error: {$mail->ErrorInfo}");
                set_flash_message('danger', 'Gagal mengirim email reset password. Silakan hubungi admin.');
            }

            header("Location: ../../views/auth/forgot_password.php");
            exit;
        } else {
            set_flash_message('danger', 'Data yang Anda masukkan tidak cocok dengan sistem kami.');
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
