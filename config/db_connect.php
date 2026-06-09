<?php
// config/db_connect.php

// Konfigurasi Database
$host = 'localhost';
$dbname = 'db_arsip_teluknaga';
$username = 'root'; // Sesuaikan jika menggunakan user lain
$password = ''; // Sesuaikan jika ada password

try {
    // Membuat koneksi PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set error mode ke Exception agar aman dari kebocoran struktur query dan mudah di debug
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Mengatur default fetch mode menjadi objek untuk kemudahan OOP
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    // Menonaktifkan emulated prepared statements untuk keamanan ekstra dari SQL Injection
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    // Jangan tampilkan error asli di production, catat di log
    error_log("Connection failed: " . $e->getMessage());
    die("Koneksi database gagal. Silakan coba beberapa saat lagi.");
}

// Memulai session dengan pengaturan aman
if (session_status() === PHP_SESSION_NONE) {
    // Pengaturan cookie session agar lebih aman
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Ubah ke 1 jika menggunakan HTTPS
    ini_set('session.gc_maxlifetime', 1800); // 30 menit
    session_set_cookie_params(1800); // 30 menit
    session_start();
}

// Keamanan Sesi: Auto-logout setelah 30 menit (1800 detik) tidak ada aktivitas
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_action']) && (time() - $_SESSION['last_action'] > 1800)) {
        session_unset();
        session_destroy();
        // Sesi dihapus, script view akan otomatis me-redirect ke halaman login
    } else {
        $_SESSION['last_action'] = time(); // Update waktu aktivitas terakhir
    }
}

// Fungsi untuk membuat CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fungsi validasi CSRF Token
function verify_csrf_token($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

// Fungsi pembantu untuk set flash message
function set_flash_message($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

// Fungsi pembantu untuk menampilkan flash message
function display_flash_message() {
    if (isset($_SESSION['flash'])) {
        $type = htmlspecialchars($_SESSION['flash']['type'], ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($_SESSION['flash']['message'], ENT_QUOTES, 'UTF-8');
        
        echo "<div class='flash-message alert alert-{$type}'>
                {$message}
                <button type='button' class='close-btn' onclick='this.parentElement.style.display=\"none\";'>&times;</button>
              </div>";
              
        unset($_SESSION['flash']);
    }
}
?>
