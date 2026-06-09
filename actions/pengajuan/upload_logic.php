<?php
// actions/pengajuan/upload_logic.php

/**
 * Fungsi untuk menangani proses upload file yang aman.
 * Mencegah eksekusi script berbahaya dan memastikan file terisolasi.
 *
 * @param array $file Array dari $_FILES['nama_input']
 * @param string $destination_dir Path relatif ke direktori tujuan
 * @return array Array asosiatif ['success' => bool, 'message' => string, 'filename' => string|null]
 */
function secure_file_upload($file, $destination_dir = '../../uploads/lampiran/') {
    // 1. Cek apakah ada error saat upload
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Parameter file tidak valid.'];
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['success' => false, 'message' => 'Tidak ada file yang diunggah.'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'message' => 'Ukuran file melebihi batas.'];
        default:
            return ['success' => false, 'message' => 'Terjadi kesalahan saat mengunggah (Error Code: ' . $file['error'] . ').'];
    }

    // 2. Validasi Ukuran (Maksimal 2MB = 2 * 1024 * 1024 bytes)
    $max_size = 2 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 2MB.'];
    }

    // 3. Validasi Tipe File/MIME dan Ekstensi
    // Gunakan finfo untuk mendapatkan MIME type sebenarnya dari konten, bukan dari ekstensi
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);

    $allowed_mimes = [
        'application/pdf' => 'pdf',
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png'
    ];

    if (!array_key_exists($mime_type, $allowed_mimes)) {
        return ['success' => false, 'message' => 'Format file tidak diizinkan. Hanya PDF, JPG, dan PNG.'];
    }

    $ext = $allowed_mimes[$mime_type];

    // 4. Pastikan direktori tujuan ada
    if (!is_dir($destination_dir)) {
        mkdir($destination_dir, 0755, true);
        
        // Buat file index.php kosong untuk mencegah directory listing (jika diakses langsung)
        file_put_contents($destination_dir . 'index.php', '<?php // Silence is golden');
        // Buat file .htaccess untuk mencegah eksekusi PHP di folder upload
        file_put_contents($destination_dir . '.htaccess', 'php_flag engine off');
    }

    // 5. Ganti Nama File dengan UUID/Hash unik
    // Kombinasi uniqid(lebih aman) + hash untuk keamanan ganda agar sulit ditebak
    $new_filename = uniqid('file_', true) . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $target_path = $destination_dir . $new_filename;

    // 6. Pindahkan File dari temporary ke tujuan
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return [
            'success' => true,
            'message' => 'File berhasil diunggah.',
            'filename' => $new_filename,
            'path' => $target_path,
            'type' => $mime_type
        ];
    } else {
        return ['success' => false, 'message' => 'Gagal memindahkan file yang diunggah.'];
    }
}

// CONTOH PENGGUNAAN (Bisa diletakkan di file terpisah misal: proses_pengajuan.php)
/*
require_once '../../config/db_connect.php';
require_once 'upload_logic.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['lampiran'])) {
    // Validasi CSRF dulu (sebaiknya)
    
    $upload_result = secure_file_upload($_FILES['lampiran']);
    
    if ($upload_result['success']) {
        // File berhasil diupload, kita dapatkan nama barunya
        $nama_file_baru = $upload_result['filename'];
        $tipe_file = $upload_result['type'];
        $path_folder = $upload_result['path'];
        
        // Selanjutnya simpan data pengajuan beserta nama file ini ke database
        // ... (Logika INSERT ke tabel pengajuan_surat dan tabel lampiran) ...
        
        echo "Berhasil! File tersimpan sebagai: " . $nama_file_baru;
    } else {
        // Gagal upload
        echo "Gagal: " . $upload_result['message'];
    }
}
*/
?>
