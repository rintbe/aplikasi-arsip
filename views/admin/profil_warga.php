<?php
// views/admin/profil_warga.php
require_once '../../config/db_connect.php';

// Proteksi halaman admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: daftar_warga.php");
    exit;
}

$warga_id = $_GET['id'];

// Ambil data warga
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'warga'");
$stmt->execute([$warga_id]);
$warga = $stmt->fetch();

if (!$warga) {
    header("Location: daftar_warga.php");
    exit;
}

$page_title = 'Detail Profil Warga';

// Penentuan syarat latar belakang foto
$syarat_warna = "-";
$badge_color = "bg-slate-100 text-slate-800";
if (!empty($warga->tanggal_lahir)) {
    $tahun = (int) date('Y', strtotime($warga->tanggal_lahir));
    if ($tahun % 2 !== 0) {
        $syarat_warna = "MERAH (Tahun Ganjil: $tahun)";
        $badge_color = "bg-red-100 text-red-800 border-red-200";
    } else {
        $syarat_warna = "BIRU (Tahun Genap: $tahun)";
        $badge_color = "bg-blue-100 text-blue-800 border-blue-200";
    }
}

include __DIR__ . '/layout/header.php';
?>

<div class="flex-1 overflow-y-auto p-4 md:p-6 bg-slate-50/50">
    <div class="max-w-4xl mx-auto space-y-6">
        
        <div class="flex items-center gap-3 mb-6">
            <a href="daftar_warga.php" class="p-2 bg-white text-slate-500 hover:text-purple-700 hover:bg-purple-50 rounded-xl shadow-sm border border-slate-200 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Detail Warga</h1>
                <p class="text-slate-500 text-sm mt-1">Data profil warga (Read-Only).</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 md:p-8">
                
                <div class="flex flex-col md:flex-row gap-8">
                    <!-- Bagian Foto Profil -->
                    <div class="w-full md:w-1/3 flex flex-col items-center">
                        <div class="w-48 h-64 bg-slate-100 rounded-lg border-2 border-dashed border-slate-300 flex flex-col items-center justify-center overflow-hidden mb-4 relative shadow-inner">
                            <?php if (!empty($warga->foto_profil) && file_exists("../../uploads/profil/" . $warga->foto_profil)): ?>
                                <img src="../../uploads/profil/<?= htmlspecialchars($warga->foto_profil) ?>" class="w-full h-full object-cover" alt="Foto Profil">
                            <?php else: ?>
                                <i class="fa-solid fa-user text-6xl text-slate-300 mb-2"></i>
                                <span class="text-sm text-slate-400">Belum ada foto</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="w-full text-center p-3 rounded-lg border <?= $badge_color ?>">
                            <div class="text-xs font-semibold uppercase tracking-wider mb-1 opacity-75">Syarat Warna Latar Foto</div>
                            <div class="font-bold text-sm"><?= $syarat_warna ?></div>
                        </div>
                    </div>

                    <!-- Bagian Data Diri -->
                    <div class="w-full md:w-2/3 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">NIK</label>
                                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg font-mono text-slate-800">
                                    <?= htmlspecialchars($warga->nik) ?>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama Lengkap</label>
                                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 font-medium">
                                    <?= htmlspecialchars($warga->nama_lengkap) ?>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Tempat Lahir</label>
                                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800">
                                    <?= empty($warga->tempat_lahir) ? '<span class="text-slate-400 italic">Belum diisi</span>' : htmlspecialchars($warga->tempat_lahir) ?>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal Lahir</label>
                                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800">
                                    <?= empty($warga->tanggal_lahir) ? '<span class="text-slate-400 italic">Belum diisi</span>' : date('d M Y', strtotime($warga->tanggal_lahir)) ?>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Email</label>
                                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800">
                                    <?= htmlspecialchars($warga->email) ?>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nomor HP</label>
                                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800">
                                    <?= htmlspecialchars($warga->no_hp) ?>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Alamat Sesuai KTP</label>
                            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 min-h-[80px]">
                                <?= empty($warga->alamat_ktp) ? '<span class="text-slate-400 italic">Belum diisi</span>' : nl2br(htmlspecialchars($warga->alamat_ktp)) ?>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Alamat Domisili Saat Ini</label>
                            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 min-h-[80px]">
                                <?= empty($warga->alamat_domisili) ? '<span class="text-slate-400 italic">Belum diisi</span>' : nl2br(htmlspecialchars($warga->alamat_domisili)) ?>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
