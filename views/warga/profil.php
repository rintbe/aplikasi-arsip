<?php
// views/warga/profil.php
require_once '../../config/db_connect.php';

// Cek autentikasi dan otorisasi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warga') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$page_title = 'Profil Saya';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Arsip Teluknaga</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a148c;
            --secondary-color: #e91e63;
            --bg-color: #fdf2f8;
            --card-bg: #ffffff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --warning-color: #f59e0b;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn-back {
            display: inline-block;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        .btn-back i { margin-right: 5px; }
        .btn-back:hover { text-decoration: underline; }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(74, 20, 140, 0.08);
            padding: 30px;
            border-top: 5px solid var(--primary-color);
        }

        .card-header {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .card-header h2 {
            margin: 0;
            color: var(--primary-color);
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            gap: 20px;
        }
        .form-row > .form-group {
            flex: 1;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-main);
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            box-sizing: border-box;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.1);
        }

        .form-control[readonly] {
            background-color: #f3f4f6;
            cursor: not-allowed;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(233, 30, 99, 0.2);
        }

        .alert-info {
            background-color: #e0f2fe;
            color: #0369a1;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #0284c7;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }

        .alert-info strong { color: #075985; }

        .foto-preview {
            width: 150px;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px dashed var(--border-color);
            display: block;
            margin-bottom: 10px;
            background-color: #f9fafb;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            font-size: 14px;
            color: var(--primary-color);
            font-weight: 600;
        }

        .btn-logout:hover {
            background-color: #b91c1c !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(220, 38, 38, 0.2);
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .form-row { flex-direction: column; gap: 0; }
            .container { padding: 15px; margin: 10px auto; }
            .card { padding: 20px; }
            .btn-logout { width: 100% !important; margin-top: 10px; }
        }

        .flash-message {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .alert-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .close-btn { background: none; border: none; font-size: 18px; cursor: pointer; color: inherit; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-actions">
        <a href="dashboard_warga.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><i class="fa-solid fa-user-pen"></i> Profil Saya</h2>
        </div>

        <?php display_flash_message(); ?>

        <div class="alert-info">
            <strong><i class="fa-solid fa-circle-info"></i> Panduan Foto Profil:</strong><br>
            Unggah pas foto dengan wajah terlihat jelas.
            <ul style="margin-top: 5px; margin-bottom: 0; padding-left: 20px;">
                <li>Latar belakang <strong>MERAH</strong> untuk Tahun Lahir <strong>Ganjil</strong>.</li>
                <li>Latar belakang <strong>BIRU</strong> untuk Tahun Lahir <strong>Genap</strong>.</li>
            </ul>
        </div>

        <form action="../../actions/warga/update_profil.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form-row">
                <div class="form-group" style="flex: 0 0 150px;">
                    <label>Foto Profil Saat Ini</label>
                    <?php if (!empty($user->foto_profil) && file_exists("../../uploads/profil/" . $user->foto_profil)): ?>
                        <img src="../../uploads/profil/<?= htmlspecialchars($user->foto_profil) ?>" class="foto-preview" alt="Foto Profil">
                    <?php else: ?>
                        <div class="foto-preview" style="display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                            <i class="fa-solid fa-user text-4xl"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="foto_profil">Unggah Foto Baru (Maks 12MB, JPG/PNG)</label>
                    <input type="file" id="foto_profil" name="foto_profil" class="form-control" accept="image/jpeg, image/png">
                    <small style="color: var(--text-muted); display: block; margin-top: 5px;">Biarkan kosong jika tidak ingin mengubah foto profil.</small>
                </div>
            </div>

            <div class="form-group">
                <label for="nik">NIK (16 Digit)</label>
                <input type="text" id="nik" name="nik" class="form-control" value="<?= htmlspecialchars($user->nik) ?>" required minlength="16" maxlength="16" pattern="[0-9]{16}">
            </div>

            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user->nama_lengkap) ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tempat_lahir">Tempat Lahir</label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" class="form-control" value="<?= htmlspecialchars($user->tempat_lahir ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="tanggal_lahir">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control" value="<?= htmlspecialchars($user->tanggal_lahir ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="alamat_ktp">Alamat Sesuai KTP</label>
                <textarea id="alamat_ktp" name="alamat_ktp" class="form-control" rows="3" required><?= htmlspecialchars($user->alamat_ktp ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Alamat Domisili</label>
                <div class="checkbox-container">
                    <input type="checkbox" id="sama_dengan_ktp">
                    <label for="sama_dengan_ktp" style="margin: 0; cursor: pointer;">Sama dengan Alamat KTP</label>
                </div>
                <textarea id="alamat_domisili" name="alamat_domisili" class="form-control" rows="3" required><?= htmlspecialchars($user->alamat_domisili ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn-submit" style="margin-bottom: 15px;"><i class="fa-solid fa-save"></i> Simpan Perubahan</button>
        </form>
        <a href="../../actions/auth/logout.php" class="btn-logout" style="display: inline-block; width: 33.33%; text-align: center; background-color: #dc2626; color: white; padding: 12px 15px; border-radius: 8px; text-decoration: none; font-weight: bold; transition: all 0.2s; box-sizing: border-box; font-size: 15px;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</div>

<script>
    // Logika auto-fill alamat domisili
    const alamatKtp = document.getElementById('alamat_ktp');
    const alamatDomisili = document.getElementById('alamat_domisili');
    const cbSamaKtp = document.getElementById('sama_dengan_ktp');

    cbSamaKtp.addEventListener('change', function() {
        if (this.checked) {
            alamatDomisili.value = alamatKtp.value;
            alamatDomisili.setAttribute('readonly', true);
        } else {
            alamatDomisili.removeAttribute('readonly');
        }
    });

    // Jika alamat ktp diubah saat checkbox dicentang, update domisili juga
    alamatKtp.addEventListener('input', function() {
        if (cbSamaKtp.checked) {
            alamatDomisili.value = this.value;
        }
    });

    // Validasi Tanggal Lahir vs Foto (Peringatan Sederhana)
    const tanggalLahir = document.getElementById('tanggal_lahir');
    const fotoProfil = document.getElementById('foto_profil');

    fotoProfil.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const tgl = tanggalLahir.value;
            if (!tgl) {
                alert("Mohon isi Tanggal Lahir terlebih dahulu untuk memastikan syarat warna latar foto.");
                return;
            }

            const year = new Date(tgl).getFullYear();
            const isGanjil = year % 2 !== 0;
            const requiredColor = isGanjil ? "MERAH" : "BIRU";

            alert(`PERINGATAN TAHUN LAHIR:
Tahun lahir Anda adalah ${year} (${isGanjil ? 'Ganjil' : 'Genap'}).
Pastikan pas foto Anda memiliki Latar Belakang berwarna ${requiredColor}. 
Jika salah, pengajuan dokumen Anda dapat ditolak oleh admin desa.`);
        }
    });

    // Inactivity Auto-Logout Logic
    let inactivityTime = function() {
        let time;
        const logoutUrl = '../../actions/auth/logout.php';
        function resetTimer() {
            clearTimeout(time);
            time = setTimeout(function() {
                alert('Sesi Anda telah berakhir karena tidak ada aktivitas selama 30 menit. Anda akan diarahkan ke halaman login.');
                window.location.href = logoutUrl;
            }, 1800000);
        }
        window.onload = resetTimer;
        document.onmousemove = resetTimer;
        document.onkeypress = resetTimer;
        document.ontouchstart = resetTimer;
        document.onclick = resetTimer;
        document.onscroll = resetTimer;
    };
    inactivityTime();
</script>

</body>
</html>
