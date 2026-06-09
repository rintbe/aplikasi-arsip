<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_arsip_teluknaga";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 1800);
    session_set_cookie_params(1800);
    session_start();
}

// Keamanan Sesi: Auto-logout setelah 30 menit (1800 detik) tidak ada aktivitas
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_action']) && (time() - $_SESSION['last_action'] > 1800)) {
        session_unset();
        session_destroy();
    } else {
        $_SESSION['last_action'] = time();
    }
}

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}
?>
