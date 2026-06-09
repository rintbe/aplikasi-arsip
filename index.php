<?php
// index.php
require_once 'config/db_connect.php';

// Route sederhana berdasarkan session
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: views/admin/dashboard_admin.php");
    } else {
        header("Location: views/warga/dashboard_warga.php");
    }
} else {
    // Jika belum login, redirect ke halaman login
    header("Location: views/auth/login.php");
}
exit;
?>
