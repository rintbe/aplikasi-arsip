<?php
// actions/pengajuan/proses_pengajuan.php

require_once '../../config/db_connect.php';
require_once 'upload_logic.php';

// Cek autentikasi asli
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warga') {
    set_flash_message('warning', 'Anda harus login terlebih dahulu.');
    header("Location: ../../views/auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validasi CSRF Token
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
    if (!verify_csrf_token($csrf_token)) {
        set_flash_message('danger', 'Token keamanan tidak valid. Silakan coba lagi.');
        header("Location: ../../views/warga/form_pengajuan.php");
        exit;
    }

    // 2. Ambil dan sanitasi input
    $jenis_surat = filter_input(INPUT_POST, 'jenis_surat', FILTER_SANITIZE_STRING);
    $keperluan_tambahan = filter_input(INPUT_POST, 'keperluan', FILTER_SANITIZE_STRING);
    $edit_id = filter_input(INPUT_POST, 'edit_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id = $_SESSION['user_id'];

    $meta = isset($_POST['meta']) && is_array($_POST['meta']) ? $_POST['meta'] : [];
    
    // Sanitize meta
    $clean_meta = [];
    foreach($meta as $k => $v) {
        $clean_meta[htmlspecialchars($k)] = htmlspecialchars($v);
    }
    $data_meta = json_encode($clean_meta);

    // Build summary for keperluan
    $keperluan_summary = "";
    if (!empty($clean_meta)) {
        foreach($clean_meta as $key => $val) {
            $label = ucwords(str_replace('_', ' ', $key));
            $keperluan_summary .= "{$label}: {$val}\n";
        }
    }
    
    // Add the user's extra note if any
    if (!empty(trim($keperluan_tambahan))) {
        $keperluan_summary .= "\nCatatan Tambahan:\n" . trim($keperluan_tambahan);
    }
    
    // Fallback if empty
    if (empty(trim($keperluan_summary))) {
        $keperluan_summary = "Pengajuan " . $jenis_surat;
    }

    // 3. Validasi input dasar
    if (empty($jenis_surat)) {
        set_flash_message('danger', 'Jenis surat wajib diisi.');
        header("Location: ../../views/warga/form_pengajuan.php");
        exit;
    }

    try {
        // Mulai transaksi database
        $pdo->beginTransaction();

        if ($edit_id > 0) {
            // --- UPDATE MODE (Revisi) ---
            $keperluan_revisi = strpos($keperluan_summary, '[REVISI]') === 0 ? $keperluan_summary : '[REVISI] ' . $keperluan_summary;
            $stmt = $pdo->prepare("UPDATE pengajuan_surat SET jenis_surat = ?, keperluan = ?, data_meta = ?, status = 'Pending', keterangan = NULL, tgl_pengajuan = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
            $stmt->execute([$jenis_surat, $keperluan_revisi, $data_meta, $edit_id, $user_id]);
            $pengajuan_id = $edit_id;

            // Jika warga mengupload file baru saat revisi
            if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload_result = secure_file_upload($_FILES['lampiran'], '../../uploads/lampiran/');
                
                if ($upload_result['success']) {
                    $nama_file = $upload_result['filename'];
                    $tipe_file = $upload_result['type'];
                    $path_folder = 'uploads/lampiran/'; 
                    
                    // Hapus data lampiran lama (opsional: hapus file fisik jika perlu)
                    $stmt_lampiran_del = $pdo->prepare("DELETE FROM lampiran WHERE pengajuan_id = ?");
                    $stmt_lampiran_del->execute([$pengajuan_id]);
                    
                    // Insert data lampiran baru
                    $stmt_lampiran = $pdo->prepare("INSERT INTO lampiran (pengajuan_id, nama_file, tipe_file, path_folder) VALUES (?, ?, ?, ?)");
                    $stmt_lampiran->execute([$pengajuan_id, $nama_file, $tipe_file, $path_folder]);
                } else {
                    $pdo->rollBack();
                    set_flash_message('danger', 'Gagal mengunggah lampiran revisi: ' . $upload_result['message']);
                    header("Location: ../../views/warga/form_pengajuan.php?edit_id=" . $edit_id);
                    exit;
                }
            }
        } else {
            // --- INSERT MODE (Baru) ---
            // 4. Insert data pengajuan surat ke tabel pengajuan_surat
            $stmt = $pdo->prepare("INSERT INTO pengajuan_surat (user_id, jenis_surat, keperluan, data_meta, status) VALUES (?, ?, ?, ?, 'Pending')");
            $stmt->execute([$user_id, $jenis_surat, $keperluan_summary, $data_meta]);
            
            // Ambil ID pengajuan yang baru saja diinsert
            $pengajuan_id = $pdo->lastInsertId();

            // 5. Cek wajib file lampiran yang diupload
            if (!isset($_FILES['lampiran']) || $_FILES['lampiran']['error'] === UPLOAD_ERR_NO_FILE) {
                $pdo->rollBack();
                set_flash_message('danger', 'Dokumen lampiran wajib diunggah.');
                header("Location: ../../views/warga/form_pengajuan.php");
                exit;
            }
                
            // Panggil fungsi secure_file_upload dari upload_logic.php
            $upload_result = secure_file_upload($_FILES['lampiran'], '../../uploads/lampiran/');
            
            if ($upload_result['success']) {
                $nama_file = $upload_result['filename'];
                $tipe_file = $upload_result['type'];
                $path_folder = 'uploads/lampiran/'; // path relatif untuk referensi di database
                
                // Insert data lampiran ke tabel lampiran
                $stmt_lampiran = $pdo->prepare("INSERT INTO lampiran (pengajuan_id, nama_file, tipe_file, path_folder) VALUES (?, ?, ?, ?)");
                $stmt_lampiran->execute([$pengajuan_id, $nama_file, $tipe_file, $path_folder]);
            } else {
                // Jika upload gagal, rollback transaksi (batalkan insert pengajuan_surat)
                $pdo->rollBack();
                set_flash_message('danger', 'Gagal mengunggah lampiran: ' . $upload_result['message']);
                header("Location: ../../views/warga/form_pengajuan.php");
                exit;
            }
        }

        // Commit transaksi jika semuanya berhasil
        $pdo->commit();

        // Berikan pesan sukses dan kembalikan ke dashboard
        $msg_sukses = ($edit_id > 0) ? 'Perbaikan pengajuan berhasil dikirim untuk diproses ulang.' : 'Pengajuan surat berhasil dikirim dan sedang dalam proses.';
        set_flash_message('success', $msg_sukses);
        header("Location: ../../views/warga/dashboard_warga.php");
        exit;

    } catch (PDOException $e) {
        // Rollback transaksi jika terjadi error database
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Database Error (Proses Pengajuan): " . $e->getMessage());
        set_flash_message('danger', 'Terjadi kesalahan sistem saat menyimpan pengajuan.');
        header("Location: ../../views/warga/form_pengajuan.php");
        exit;
    }

} else {
    // Jika diakses tidak melalui POST, arahkan ke form
    header("Location: ../../views/warga/form_pengajuan.php");
    exit;
}
?>
