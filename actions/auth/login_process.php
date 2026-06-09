<?php
// actions/auth/login_process.php
require_once '../../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF Token
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('danger', 'Token keamanan tidak valid. Silakan coba lagi.');
        header("Location: ../../views/auth/login.php");
        exit;
    }

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        set_flash_message('danger', 'Email dan password wajib diisi.');
        header("Location: ../../views/auth/login.php");
        exit;
    }

    try {
        // Cari user berdasarkan email
        $stmt = $pdo->prepare("SELECT id, nama_lengkap, password, role, is_active FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Verifikasi password
            if (password_verify($password, $user->password)) {
                // Cek apakah akun aktif
                if ($user->is_active == 0) {
                    set_flash_message('warning', 'Akun Anda belum diaktifkan. Silakan periksa email Anda untuk tautan aktivasi.');
                    header("Location: ../../views/auth/login.php");
                    exit;
                }

                // Password benar dan akun aktif, set session
                // Regenerate ID untuk mencegah session fixation
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user->id;
                $_SESSION['nama_lengkap'] = $user->nama_lengkap;
                $_SESSION['role'] = $user->role;

                // Redirect berdasarkan role
                if ($user->role === 'admin') {
                    header("Location: ../../views/admin/dashboard_admin.php");
                } else {
                    header("Location: ../../views/warga/dashboard_warga.php");
                }
                exit;
            } else {
                set_flash_message('danger', 'Email atau password salah.');
                header("Location: ../../views/auth/login.php");
                exit;
            }
        } else {
            // Kita berikan pesan yang sama untuk alasan keamanan agar tidak mengekspos email yang terdaftar
            set_flash_message('danger', 'Email atau password salah.');
            header("Location: ../../views/auth/login.php");
            exit;
        }

    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        set_flash_message('danger', 'Terjadi kesalahan sistem saat login.');
        header("Location: ../../views/auth/login.php");
        exit;
    }
} else {
    header("Location: ../../views/auth/login.php");
    exit;
}
?>
