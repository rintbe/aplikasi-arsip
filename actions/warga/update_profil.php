<?php
// actions/warga/update_profil.php
require_once '../../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validasi Autentikasi dan CSRF
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warga') {
        header("Location: ../../views/auth/login.php");
        exit;
    }

    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('danger', 'Token keamanan tidak valid.');
        header("Location: ../../views/warga/profil.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // 2. Ambil Input
    $nik = filter_input(INPUT_POST, 'nik', FILTER_SANITIZE_STRING);
    $nama_lengkap = filter_input(INPUT_POST, 'nama_lengkap', FILTER_SANITIZE_STRING);
    $tempat_lahir = filter_input(INPUT_POST, 'tempat_lahir', FILTER_SANITIZE_STRING);
    $tanggal_lahir = filter_input(INPUT_POST, 'tanggal_lahir', FILTER_SANITIZE_STRING);
    $alamat_ktp = filter_input(INPUT_POST, 'alamat_ktp', FILTER_SANITIZE_STRING);
    $alamat_domisili = filter_input(INPUT_POST, 'alamat_domisili', FILTER_SANITIZE_STRING);

    // 3. Validasi Dasar
    if (!preg_match('/^[0-9]{16}$/', $nik)) {
        set_flash_message('danger', 'NIK harus 16 digit angka.');
        header("Location: ../../views/warga/profil.php");
        exit;
    }

    // 4. Cek NIK Kembar (Kecuali Milik Sendiri)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE nik = ? AND id != ?");
    $stmt->execute([$nik, $user_id]);
    if ($stmt->rowCount() > 0) {
        set_flash_message('danger', 'NIK tersebut sudah digunakan oleh pengguna lain.');
        header("Location: ../../views/warga/profil.php");
        exit;
    }

    // 5. Proses Upload Foto (Opsional)
    $foto_profil = null; // Default null berarti tidak update foto
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['foto_profil']['tmp_name'];
        $file_name = $_FILES['foto_profil']['name'];
        $file_size = $_FILES['foto_profil']['size'];
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validasi Ekstensi dan Ukuran
        $allowed_types = ['jpg', 'jpeg', 'png'];
        if (!in_array($file_type, $allowed_types)) {
            set_flash_message('danger', 'Hanya file JPG, JPEG, dan PNG yang diperbolehkan untuk foto profil.');
            header("Location: ../../views/warga/profil.php");
            exit;
        }

        if ($file_size > 12 * 1024 * 1024) { // 12MB
            set_flash_message('danger', 'Ukuran foto profil maksimal 12MB.');
            header("Location: ../../views/warga/profil.php");
            exit;
        }

        // Simpan File
        $new_file_name = 'profil_' . $user_id . '_' . time() . '.' . $file_type;
        $upload_dir = '../../uploads/profil/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
            $foto_profil = $new_file_name;
            
            // Hapus foto lama jika ada
            $stmt_old = $pdo->prepare("SELECT foto_profil FROM users WHERE id = ?");
            $stmt_old->execute([$user_id]);
            $old_data = $stmt_old->fetch();
            if ($old_data && !empty($old_data->foto_profil) && file_exists($upload_dir . $old_data->foto_profil)) {
                unlink($upload_dir . $old_data->foto_profil);
            }
        } else {
            set_flash_message('danger', 'Gagal mengunggah foto profil.');
            header("Location: ../../views/warga/profil.php");
            exit;
        }
    }

    // 6. Update Database
    try {
        if ($foto_profil) {
            $sql = "UPDATE users SET nik = ?, nama_lengkap = ?, tempat_lahir = ?, tanggal_lahir = ?, alamat_ktp = ?, alamat_domisili = ?, foto_profil = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nik, $nama_lengkap, $tempat_lahir, $tanggal_lahir, $alamat_ktp, $alamat_domisili, $foto_profil, $user_id]);
        } else {
            // Update tanpa mengganti foto
            $sql = "UPDATE users SET nik = ?, nama_lengkap = ?, tempat_lahir = ?, tanggal_lahir = ?, alamat_ktp = ?, alamat_domisili = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nik, $nama_lengkap, $tempat_lahir, $tanggal_lahir, $alamat_ktp, $alamat_domisili, $user_id]);
        }

        // Perbarui session nama jika berubah
        $_SESSION['nama_lengkap'] = $nama_lengkap;

        set_flash_message('success', 'Profil berhasil diperbarui!');
        header("Location: ../../views/warga/profil.php");
        exit;

    } catch (PDOException $e) {
        error_log("Update Profil Error: " . $e->getMessage());
        set_flash_message('danger', 'Terjadi kesalahan saat menyimpan data.');
        header("Location: ../../views/warga/profil.php");
        exit;
    }
} else {
    header("Location: ../../views/warga/profil.php");
    exit;
}
?>
