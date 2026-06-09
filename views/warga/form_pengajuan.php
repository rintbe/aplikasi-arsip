<?php
// views/warga/form_pengajuan.php
require_once '../../config/db_connect.php';

// Cek autentikasi asli
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warga') {
    set_flash_message('warning', 'Anda harus login terlebih dahulu.');
    header("Location: ../auth/login.php");
    exit;
}

$edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
$row_edit = null;

if ($edit_id > 0) {
    try {
        $stmt_edit = $pdo->prepare("SELECT * FROM pengajuan_surat WHERE id = ? AND user_id = ?");
        $stmt_edit->execute([$edit_id, $_SESSION['user_id']]);
        $row_edit = $stmt_edit->fetch(PDO::FETCH_OBJ);
        
        if (!$row_edit || strtolower($row_edit->status) !== 'ditolak') {
            set_flash_message('danger', 'Pengajuan ini tidak dapat diedit atau tidak ditemukan.');
            header("Location: dashboard_warga.php");
            exit;
        }
    } catch (PDOException $e) {
        set_flash_message('danger', 'Gagal memuat data revisi.');
        header("Location: dashboard_warga.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Surat Baru - Arsip Teluknaga</title>
    <style>
        :root {
            --primary-color: #4a148c; /* Ungu Tua */
            --secondary-color: #e91e63; /* Merah Muda */
            --success-color: #16a34a;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            --bg-color: #fdf2f8; /* Pink sangat pudar */
            --card-bg: #ffffff; /* Putih */
            --text-main: #1f2937;
            --text-muted: #6b7280;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
        }

        .card {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(74, 20, 140, 0.1);
            padding: 30px;
            border-top: 5px solid var(--primary-color);
        }

        .card-header {
            margin-bottom: 25px;
            text-align: center;
        }

        .card-header h2 {
            margin: 0;
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--primary-color);
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            box-sizing: border-box;
            font-family: inherit;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.2);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: opacity 0.2s;
        }

        .btn-submit:hover {
            opacity: 0.9;
        }

        .btn-back {
            display: inline-block;
            margin-top: 15px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
        }

        .btn-back:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .help-text {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 5px;
            display: block;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
                margin: 20px auto;
            }
            .card {
                padding: 20px;
            }
            .card-header h2 {
                font-size: 20px;
            }
            .btn-submit {
                padding: 14px 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2><?= $row_edit ? 'Form Revisi Surat' : 'Form Pengajuan Surat' ?></h2>
            <p style="color: var(--text-muted); font-size: 14px;">Silakan lengkapi data di bawah ini untuk mengajukan surat.</p>
        </div>

        <?php display_flash_message(); ?>

        <!-- Form mengarah ke actions/pengajuan/proses_pengajuan.php yang akan kita buat selanjutnya -->
        <form action="../../actions/pengajuan/proses_pengajuan.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <?php if ($row_edit): ?>
            <input type="hidden" name="edit_id" value="<?= $row_edit->id ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="jenis_surat">Jenis Surat Keterangan</label>
                <select name="jenis_surat" id="jenis_surat" class="form-control" required>
                    <option value="">-- Pilih Jenis Surat --</option>
                    <option value="Surat Keterangan Domisili" <?= ($row_edit && $row_edit->jenis_surat == 'Surat Keterangan Domisili') ? 'selected' : '' ?>>Surat Keterangan Domisili</option>
                    <option value="Surat Keterangan Usaha" <?= ($row_edit && $row_edit->jenis_surat == 'Surat Keterangan Usaha') ? 'selected' : '' ?>>Surat Keterangan Usaha</option>
                    <option value="Surat Keterangan Tidak Mampu (SKTM)" <?= ($row_edit && $row_edit->jenis_surat == 'Surat Keterangan Tidak Mampu (SKTM)') ? 'selected' : '' ?>>Surat Keterangan Tidak Mampu (SKTM)</option>
                    <option value="Surat Keterangan Kematian" <?= ($row_edit && $row_edit->jenis_surat == 'Surat Keterangan Kematian') ? 'selected' : '' ?>>Surat Keterangan Kematian</option>
                    <option value="Surat Keterangan Pindah" <?= ($row_edit && $row_edit->jenis_surat == 'Surat Keterangan Pindah') ? 'selected' : '' ?>>Surat Keterangan Pindah</option>
                    <option value="Surat Pengantar Pernikahan" <?= ($row_edit && $row_edit->jenis_surat == 'Surat Pengantar Pernikahan') ? 'selected' : '' ?>>Surat Pengantar Pernikahan</option>
                </select>
            </div>

            <div id="dynamic-fields-container"></div>

            <div class="form-group">
                <label for="keperluan">Catatan Tambahan (Opsional)</label>
                <textarea name="keperluan" id="keperluan" class="form-control" placeholder="Tuliskan jika ada pesan tambahan..."><?= $row_edit ? htmlspecialchars($row_edit->keperluan) : '' ?></textarea>
            </div>

            <div class="form-group">
                <label for="lampiran">Dokumen Lampiran <?= $row_edit ? '<span style="color:var(--text-muted); font-weight:normal; font-size:13px;">(Abaikan jika tidak ingin mengganti file)</span>' : '(Wajib)' ?></label>
                <input type="file" name="lampiran" id="lampiran" class="form-control" accept=".pdf,.jpg,.jpeg,.png" <?= $row_edit ? '' : 'required' ?>>
                <span class="help-text">Format yang didukung: PDF, JPG, PNG. Maksimal 2MB.</span>
                <div id="instruksi-lampiran" style="margin-top: 12px; padding: 12px; background-color: #f8fafc; border-left: 4px solid var(--secondary-color); font-size: 13px; color: var(--text-main); border-radius: 4px; display: none;">
                    <!-- Pesan instruksi akan muncul di sini -->
                </div>
            </div>

            <button type="submit" class="btn-submit"><?= $row_edit ? 'Simpan Revisi' : 'Kirim Pengajuan' ?></button>
        </form>
        
        <div style="text-align: center;">
            <a href="dashboard_warga.php" class="btn-back">← Kembali ke Dashboard</a>
        </div>
    </div>
</div>

<?php 
// Parse existing data_meta for edit mode
$existing_meta = $row_edit && $row_edit->data_meta ? $row_edit->data_meta : '{}';
?>

<script>
    const jenisSuratSelect = document.getElementById('jenis_surat');
    const instruksiBox = document.getElementById('instruksi-lampiran');
    const dynamicContainer = document.getElementById('dynamic-fields-container');
    const existingMeta = <?= $existing_meta ?>;

    const instruksiData = {
        'Surat Keterangan Domisili': '<strong>📌 Persyaratan Dokumen Domisili:</strong><br>Mohon jadikan satu file (PDF/JPG) scan atau foto dari:<br>1. KTP Asli<br>2. Kartu Keluarga (KK)',
        'Surat Keterangan Usaha': '<strong>📌 Persyaratan Dokumen Usaha:</strong><br>Mohon jadikan satu file (PDF/JPG) scan atau foto dari:<br>1. KTP Pemilik Usaha<br>2. Foto Tempat Usaha atau Surat Pengantar RT/RW',
        'Surat Keterangan Tidak Mampu (SKTM)': '<strong>📌 Persyaratan Dokumen SKTM:</strong><br>Mohon jadikan satu file (PDF/JPG) scan atau foto dari:<br>1. KTP dan KK<br>2. Surat Pengantar Tidak Mampu dari RT/RW',
        'Surat Keterangan Kematian': '<strong>📌 Persyaratan Dokumen Kematian:</strong><br>Mohon jadikan satu file (PDF/JPG) scan atau foto dari:<br>1. KTP/KK Almarhum<br>2. KTP Pelapor<br>3. Surat Keterangan Kematian (RS/Dokter/RT)',
        'Surat Keterangan Pindah': '<strong>📌 Persyaratan Dokumen Pindah:</strong><br>Mohon jadikan satu file (PDF/JPG) scan atau foto dari:<br>1. KTP dan KK Asli<br>2. Surat Pengantar Pindah dari RT/RW',
        'Surat Pengantar Pernikahan': '<strong>📌 Persyaratan Dokumen Pernikahan:</strong><br>Mohon jadikan satu file (PDF/JPG) scan atau foto dari:<br>1. KTP Pria & KTP Wanita<br>2. KK Pria & KK Wanita<br>3. Surat Pengantar RT/RW'
    };

    const dynamicFieldsConfig = {
        'Surat Keterangan Domisili': `
            <div class="form-group">
                <label>Tempat, Tanggal Lahir</label>
                <input type="text" name="meta[tempat_tanggal_lahir]" class="form-control" required placeholder="Contoh: Tangerang, 17 Agustus 1990" value="${existingMeta.tempat_tanggal_lahir || ''}">
            </div>
            <div class="form-group">
                <label>Alamat Lengkap Domisili</label>
                <textarea name="meta[alamat_domisili]" class="form-control" required rows="2" placeholder="Detail jalan, RT/RW, dsb.">${existingMeta.alamat_domisili || ''}</textarea>
            </div>
        `,
        'Surat Keterangan Usaha': `
            <div class="form-group">
                <label>Nama Usaha</label>
                <input type="text" name="meta[nama_usaha]" class="form-control" required placeholder="Contoh: Toko Berkah" value="${existingMeta.nama_usaha || ''}">
            </div>
            <div class="form-group">
                <label>Bidang Usaha</label>
                <input type="text" name="meta[bidang_usaha]" class="form-control" required placeholder="Contoh: Perdagangan Sembako" value="${existingMeta.bidang_usaha || ''}">
            </div>
            <div class="form-group">
                <label>Alamat Usaha</label>
                <textarea name="meta[alamat_usaha]" class="form-control" required rows="2" placeholder="Detail alamat lokasi usaha...">${existingMeta.alamat_usaha || ''}</textarea>
            </div>
        `,
        'Surat Keterangan Kematian': `
            <div class="form-group">
                <label>Nama Almarhum / Almarhumah</label>
                <input type="text" name="meta[nama_almarhum]" class="form-control" required placeholder="Nama lengkap almarhum" value="${existingMeta.nama_almarhum || ''}">
            </div>
            <div class="form-group">
                <label>NIK Almarhum</label>
                <input type="text" name="meta[nik_almarhum]" class="form-control" required pattern="[0-9]{16}" placeholder="16 digit angka" value="${existingMeta.nik_almarhum || ''}">
            </div>
            <div class="form-group">
                <label>Tempat Meninggal</label>
                <input type="text" name="meta[tempat_meninggal]" class="form-control" required placeholder="Contoh: RSUD Kabupaten Tangerang" value="${existingMeta.tempat_meninggal || ''}">
            </div>
            <div class="form-group">
                <label>Tanggal Meninggal</label>
                <input type="date" name="meta[tanggal_meninggal]" class="form-control" required value="${existingMeta.tanggal_meninggal || ''}">
            </div>
            <div class="form-group">
                <label>Sebab Kematian</label>
                <input type="text" name="meta[sebab_kematian]" class="form-control" required placeholder="Contoh: Sakit" value="${existingMeta.sebab_kematian || ''}">
            </div>
        `,
        'Surat Keterangan Pindah': `
            <div class="form-group">
                <label>Alamat Asal</label>
                <textarea name="meta[alamat_asal]" class="form-control" required rows="2" placeholder="Alamat lengkap asal...">${existingMeta.alamat_asal || ''}</textarea>
            </div>
            <div class="form-group">
                <label>Alamat Tujuan Pindah</label>
                <textarea name="meta[alamat_tujuan]" class="form-control" required rows="2" placeholder="Alamat lengkap tujuan pindah...">${existingMeta.alamat_tujuan || ''}</textarea>
            </div>
            <div class="form-group">
                <label>Alasan Pindah</label>
                <input type="text" name="meta[alasan_pindah]" class="form-control" required placeholder="Contoh: Pekerjaan / Mengikuti Suami" value="${existingMeta.alasan_pindah || ''}">
            </div>
        `,
        'Surat Pengantar Pernikahan': `
            <div class="form-group">
                <label>Nama Pria</label>
                <input type="text" name="meta[nama_pria]" class="form-control" required placeholder="Nama lengkap calon suami" value="${existingMeta.nama_pria || ''}">
            </div>
            <div class="form-group">
                <label>NIK Pria</label>
                <input type="text" name="meta[nik_pria]" class="form-control" required pattern="[0-9]{16}" placeholder="16 digit NIK" value="${existingMeta.nik_pria || ''}">
            </div>
            <div class="form-group">
                <label>Nama Wanita</label>
                <input type="text" name="meta[nama_wanita]" class="form-control" required placeholder="Nama lengkap calon istri" value="${existingMeta.nama_wanita || ''}">
            </div>
            <div class="form-group">
                <label>NIK Wanita</label>
                <input type="text" name="meta[nik_wanita]" class="form-control" required pattern="[0-9]{16}" placeholder="16 digit NIK" value="${existingMeta.nik_wanita || ''}">
            </div>
            <div class="form-group">
                <label>Rencana Tanggal Pernikahan</label>
                <input type="date" name="meta[tanggal_pernikahan]" class="form-control" required value="${existingMeta.tanggal_pernikahan || ''}">
            </div>
        `
    };

    jenisSuratSelect.addEventListener('change', function() {
        const selected = this.value;
        if (instruksiData[selected]) {
            instruksiBox.innerHTML = instruksiData[selected];
            instruksiBox.style.display = 'block';
        } else {
            instruksiBox.style.display = 'none';
            instruksiBox.innerHTML = '';
        }

        if (dynamicFieldsConfig[selected]) {
            dynamicContainer.innerHTML = dynamicFieldsConfig[selected];
            dynamicContainer.style.display = 'block';
        } else {
            dynamicContainer.innerHTML = '';
            dynamicContainer.style.display = 'none';
        }
    });

    // Jalankan sekali saat halaman dimuat jika ada nilai yang sudah terpilih
    if(jenisSuratSelect.value) {
        jenisSuratSelect.dispatchEvent(new Event('change'));
    }

    // Inactivity Auto-Logout Logic (30 menit)
    let inactivityTime = function() {
        let time;
        const logoutUrl = '../../actions/auth/logout.php';
        
        function resetTimer() {
            clearTimeout(time);
            time = setTimeout(function() {
                alert('Sesi Anda telah berakhir karena tidak ada aktivitas selama 30 menit. Anda akan diarahkan ke halaman login.');
                window.location.href = logoutUrl;
            }, 1800000); // 30 Menit
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
