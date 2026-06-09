<?php
// views/admin/dashboard_admin.php
$page_title = "Dashboard Utama";
require_once __DIR__ . '/layout/header.php';

// Fetch summary data counts
$tables = [
    'pengajuan_surat' => ['icon' => 'fa-file-signature', 'color' => 'bg-indigo-500', 'label' => 'Pengajuan Warga', 'link' => 'daftar_pengajuan.php'],
    'surat_kematian' => ['icon' => 'fa-book-dead', 'color' => 'bg-red-500', 'label' => 'Kematian', 'link' => 'kematian.php'],
    'surat_pernikahan' => ['icon' => 'fa-ring', 'color' => 'bg-pink-500', 'label' => 'Pernikahan', 'link' => 'pernikahan.php'],
    'surat_usaha' => ['icon' => 'fa-store', 'color' => 'bg-purple-500', 'label' => 'Usaha', 'link' => 'usaha.php'],
    'surat_pindah' => ['icon' => 'fa-truck-fast', 'color' => 'bg-blue-500', 'label' => 'Pindah', 'link' => 'pindah.php'],
    'surat_domisili' => ['icon' => 'fa-map-location-dot', 'color' => 'bg-green-500', 'label' => 'Domisili', 'link' => 'domisili.php'],
    'surat_masuk' => ['icon' => 'fa-inbox', 'color' => 'bg-emerald-500', 'label' => 'Masuk', 'link' => 'masuk.php']
];

$counts = [];
foreach($tables as $table => $data) {
    try {
        $stmt_count = $pdo->query("SELECT COUNT(*) as total FROM $table");
        $row = $stmt_count->fetch();
        $counts[$table] = $row->total ?? 0;
    } catch (PDOException $e) {
        $counts[$table] = 0;
    }
}
?>

<div class="max-w-7xl mx-auto pb-10">
    <div class="bg-white rounded-2xl p-8 mb-8 border border-purple-100 shadow-sm relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-64 h-64 bg-gradient-to-br from-pink-200 to-purple-200 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 mb-2">Selamat Datang, <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin') ?>! 👋</h1>
                <p class="text-slate-500">Berikut adalah ringkasan data arsip surat di Desa Teluknaga saat ini.</p>
            </div>
            <div>
                <!-- You can put export button here if needed -->
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php foreach($tables as $table => $data): ?>
        <a href="<?= $data['link'] ?>" class="bg-white rounded-2xl p-6 border border-purple-50 shadow-sm hover:shadow-md transition-shadow group flex items-start text-left cursor-pointer">
            <div class="<?= $data['color'] ?> text-white w-12 h-12 rounded-xl flex items-center justify-center text-xl shadow-lg group-hover:scale-110 transition-transform flex-shrink-0">
                <i class="fa-solid <?= $data['icon'] ?>"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-slate-500 text-sm font-medium mb-1"><?= $table == 'pengajuan_surat' ? '' : 'Surat ' ?><?= $data['label'] ?></h3>
                <div class="text-2xl font-bold text-slate-800"><?= number_format($counts[$table]) ?> <span class="text-sm font-normal text-slate-400"><?= $table == 'pengajuan_surat' ? 'Pengajuan' : 'Arsip' ?></span></div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
