<?php
// actions/admin/update_status.php
require_once '../../config/db_connect.php';

// Cek autentikasi admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    set_flash_message('danger', 'Akses ditolak.');
    header("Location: ../../views/auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('danger', 'Token keamanan tidak valid.');
        header("Location: ../../views/admin/dashboard_admin.php");
        exit;
    }

    $pengajuan_id = filter_input(INPUT_POST, 'pengajuan_id', FILTER_VALIDATE_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    $keterangan = filter_input(INPUT_POST, 'keterangan', FILTER_SANITIZE_STRING);

    $valid_statuses = ['Pending', 'Proses', 'Selesai', 'Ditolak'];
    
    if (!$pengajuan_id || !in_array($status, $valid_statuses)) {
        set_flash_message('danger', 'Data tidak valid.');
        header("Location: ../../views/admin/daftar_pengajuan.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE pengajuan_surat SET status = ?, keterangan = ? WHERE id = ?");
        $stmt->execute([$status, $keterangan, $pengajuan_id]);
        
        $flash_type = 'info';
        if ($status === 'Pending') {
            $flash_type = 'warning';
        } elseif ($status === 'Proses') {
            $flash_type = 'info';
        } elseif ($status === 'Selesai') {
            $flash_type = 'success';
        } elseif ($status === 'Ditolak') {
            $flash_type = 'danger';
        }

        set_flash_message($flash_type, 'Status pengajuan berhasil diperbarui menjadi ' . htmlspecialchars($status));
    } catch (PDOException $e) {
        error_log("Gagal update status: " . $e->getMessage());
        set_flash_message('danger', 'Terjadi kesalahan saat memperbarui status.');
    }

    header("Location: ../../views/admin/daftar_pengajuan.php");
    exit;
} else {
    header("Location: ../../views/admin/daftar_pengajuan.php");
    exit;
}
?>
