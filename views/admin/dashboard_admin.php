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

$filter_month = isset($_GET['filter_month']) ? $_GET['filter_month'] : date('m');
$filter_year = isset($_GET['filter_year']) ? $_GET['filter_year'] : date('Y');

$counts = [];
foreach($tables as $table => $data) {
    try {
        $date_col = ($table == 'pengajuan_surat') ? 'tgl_pengajuan' : 'tanggal_pembuatan_surat';
        $sql = "SELECT COUNT(*) as total FROM $table";
        $params = [];
        $conditions = [];

        if ($filter_month != 'all') {
            $conditions[] = "MONTH($date_col) = ?";
            $params[] = $filter_month;
        }
        if ($filter_year != 'all') {
            $conditions[] = "YEAR($date_col) = ?";
            $params[] = $filter_year;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt_count = $pdo->prepare($sql);
        $stmt_count->execute($params);
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
                <form method="GET" action="dashboard_admin.php" class="flex flex-col sm:flex-row items-center gap-3 bg-slate-50/80 p-3 rounded-xl border border-purple-100 shadow-inner">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-calendar-days text-purple-400"></i>
                        <select name="filter_month" onchange="this.form.submit()" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block p-2 outline-none cursor-pointer">
                            <option value="all" <?= $filter_month == 'all' ? 'selected' : '' ?>>Semua Bulan</option>
                            <?php
                            $months = [
                                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                            ];
                            foreach($months as $num => $name) {
                                $selected = ($filter_month == $num) ? 'selected' : '';
                                echo "<option value=\"$num\" $selected>$name</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <select name="filter_year" onchange="this.form.submit()" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block p-2 outline-none cursor-pointer">
                        <option value="all" <?= $filter_year == 'all' ? 'selected' : '' ?>>Semua Tahun</option>
                        <?php
                        $current_year = date('Y');
                        for($y = $current_year; $y >= $current_year - 5; $y--) {
                            $selected = ($filter_year == $y) ? 'selected' : '';
                            echo "<option value=\"$y\" $selected>$y</option>";
                        }
                        ?>
                    </select>
                </form>
            </div>
        </div>
    </div>

    <?php
    // Menyiapkan teks judul besar sesuai filter yang dipilih
    $title_month = ($filter_month != 'all' && isset($months[$filter_month])) ? $months[$filter_month] : 'Semua Bulan';
    $title_year = ($filter_year != 'all') ? $filter_year : 'Semua Tahun';
    
    if ($filter_month == 'all' && $filter_year == 'all') {
        $display_title = "Semua Waktu";
    } elseif ($filter_month == 'all') {
        $display_title = "Tahun " . $title_year;
    } elseif ($filter_year == 'all') {
        $display_title = "Bulan " . $title_month;
    } else {
        $display_title = $title_month . " " . $title_year;
    }
    ?>
    <style>
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .text-animated-gradient {
            background-size: 200% auto;
            animation: gradientShift 4s ease infinite;
        }
        @keyframes floatEffect {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        .animate-float {
            animation: floatEffect 4s ease-in-out infinite;
        }
        .hover-glow {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hover-glow:hover {
            text-shadow: 0 0 35px rgba(219, 39, 119, 0.5);
            transform: scale(1.05) translateY(-5px);
        }
    </style>

    <div class="mb-12 mt-6 text-center animate-float">
        <div class="inline-flex items-center justify-center text-purple-400 font-bold mb-3 tracking-wider text-xs uppercase bg-purple-50/50 px-3 py-1 rounded-full border border-purple-100">
            <i class="fa-solid fa-calendar-check mr-2"></i> Periode Terpilih
        </div>
        
        <h2 class="text-5xl md:text-7xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-600 via-pink-500 to-rose-500 text-animated-gradient hover-glow cursor-default py-3">
            <?= htmlspecialchars($display_title) ?>
        </h2>
        
        <p class="text-slate-400 mt-2 font-medium text-lg max-w-lg mx-auto">
            Menampilkan ringkasan data pengajuan dan arsip surat
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php foreach($tables as $table => $data): ?>
        <a href="<?= $data['link'] ?>" class="bg-white rounded-2xl p-6 border border-purple-50 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group flex items-start text-left cursor-pointer">
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
