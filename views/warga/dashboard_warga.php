<?php
// views/warga/dashboard_warga.php
require_once '../../config/db_connect.php';

// Cek autentikasi asli
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warga') {
    set_flash_message('warning', 'Anda harus login terlebih dahulu.');
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data pengajuan dari database
try {
    $stmt = $pdo->prepare("SELECT * FROM pengajuan_surat WHERE user_id = ? ORDER BY tgl_pengajuan DESC");
    $stmt->execute([$user_id]);
    $pengajuan_list = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Gagal mengambil data pengajuan: " . $e->getMessage());
    $pengajuan_list = [];
}

// Ambil data user untuk foto profil
try {
    $stmtUser = $pdo->prepare("SELECT foto_profil FROM users WHERE id = ?");
    $stmtUser->execute([$user_id]);
    $userData = $stmtUser->fetch();
    $foto_profil = $userData ? $userData->foto_profil : null;
} catch (PDOException $e) {
    $foto_profil = null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Warga - Arsip Teluknaga</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a148c; /* Ungu Tua */
            --secondary-color: #e91e63; /* Merah Muda */
            --success-color: #16a34a;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            --bg-color: #fdf2f8; /* Pink sangat pudar untuk latar belakang */
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, var(--primary-color), #7b1fa2);
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 4px 10px rgba(74, 20, 140, 0.3);
            border-bottom: 4px solid var(--secondary-color);
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .btn {
            background-color: var(--secondary-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(233, 30, 99, 0.3);
        }

        .btn:hover {
            background-color: #d81b60;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(233, 30, 99, 0.4);
        }

        .btn-primary {
            background-color: var(--secondary-color);
            color: white;
            display: inline-block;
            margin-bottom: 20px;
        }

        .btn-primary:hover {
            background-color: #d81b60;
        }

        /* Flash Message Styles */
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            position: relative;
            animation: slideDown 0.3s ease-out;
        }
        .alert-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-warning { background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .alert-info { background-color: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: inherit;
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Table Styles */
        .card {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background-color: #f9fafb;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.05em;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: #f9fafb;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending { background-color: #fef3c7; color: var(--warning-color); }
        .status-proses { background-color: #dbeafe; color: var(--primary-color); }
        .status-selesai { background-color: #dcfce7; color: var(--success-color); }
        .status-ditolak { background-color: #fee2e2; color: var(--danger-color); }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }

        /* Mobile Responsiveness */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            width: 100%;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
                padding: 15px;
            }
            .header > div {
                margin-top: 10px;
                display: flex;
                flex-direction: column;
                gap: 10px;
                align-items: center !important;
                text-align: center !important;
            }
            .header h2 {
                font-size: 18px !important;
            }
            .header .btn {
                margin-left: 0 !important;
                width: 100%;
                box-sizing: border-box;
            }
            .btn-primary {
                width: 100%;
                text-align: center;
                box-sizing: border-box;
            }
            th, td {
                padding: 10px;
                font-size: 13px;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div style="display: flex; align-items: center; gap: 15px; text-align: left;">
            <img src="../../assets/images/logo.png" alt="Logo" style="width: 55px; height: auto; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
            <div>
                <h2 style="font-size: 20px; font-family: 'Inter', 'Segoe UI', sans-serif; margin: 0; line-height: 1.3; font-weight: 700; text-transform: capitalize;">Sistem Pengajuan Surat Keterangan<br>Warga Desa Teluknaga</h2>
                <p style="margin-top: 5px; font-size: 14px; opacity: 0.9;">
                    Selamat datang, <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Warga') ?>
                </p>
            </div>
        </div>
        <div style="display: flex; gap: 15px; align-items: center;">
            <a href="profil.php" title="Profil Saya" style="display: inline-block; width: 45px; height: 45px; border-radius: 50%; overflow: hidden; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.2); transition: transform 0.2s;">
                <?php if (!empty($foto_profil) && file_exists("../../uploads/profil/" . $foto_profil)): ?>
                    <img src="../../uploads/profil/<?= htmlspecialchars($foto_profil) ?>" alt="Profil" style="width: 100%; height: 100%; object-fit: cover; object-position: top center;">
                <?php else: ?>
                    <div style="width: 100%; height: 100%; background-color: #6b7280; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <!-- Menampilkan Flash Message jika ada -->
    <?php display_flash_message(); ?>

    <div>
        <!-- Link ke halaman form pengajuan (yang nantinya dibuat) -->
        <a href="form_pengajuan.php" class="btn btn-primary">+ Ajukan Surat Baru</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis Surat</th>
                        <th>Keperluan</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pengajuan_list)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    📁 Belum ada riwayat pengajuan surat.
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pengajuan_list as $index => $row): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($row->jenis_surat) ?></td>
                                <td><?= htmlspecialchars(str_replace('[REVISI] ', '', $row->keperluan)) ?></td>
                                <td><?= date('d M Y H:i', strtotime($row->tgl_pengajuan)) ?></td>
                                <td>
                                    <?php 
                                        $statusClass = strtolower($row->status);
                                    ?>
                                    <span class="status-badge status-<?= $statusClass ?>">
                                        <?= htmlspecialchars($row->status) ?>
                                    </span>
                                    <?php if ($row->status == 'Selesai' && !empty($row->file_hasil)): ?>
                                        <div style="margin-top: 10px;">
                                            <a href="../../uploads/surat_hasil/<?= htmlspecialchars($row->file_hasil) ?>" target="_blank" class="btn" style="padding: 6px 12px; font-size: 12px; background-color: var(--success-color); display: inline-block; white-space: nowrap; text-align: center; border-radius: 4px;">
                                                <i class="fa-solid fa-download"></i> Unduh PDF
                                            </a>
                                        </div>
                                    <?php elseif (strtolower($row->status) == 'ditolak'): ?>
                                        <div style="margin-top: 10px;">
                                            <a href="form_pengajuan.php?edit_id=<?= $row->id ?>" class="btn" style="padding: 6px 12px; font-size: 12px; background-color: var(--warning-color); color: white; display: inline-block; white-space: nowrap; text-align: center; border-radius: 4px;">
                                                <i class="fa-solid fa-pen-to-square"></i> Perbaiki Data
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="max-width: 200px; white-space: normal; font-size: 13px; color: var(--text-muted); line-height: 1.4;">
                                    <?= empty($row->keterangan) ? '-' : nl2br(htmlspecialchars($row->keterangan)) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
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
