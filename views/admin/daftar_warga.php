<?php
// views/admin/daftar_warga.php
require_once '../../config/db_connect.php';

// Proteksi halaman admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$page_title = 'Data Warga';

$filter_search = $_GET['search'] ?? '';

// Ambil data warga
$where_sql = "WHERE role = 'warga'";
$params = [];

if (!empty($filter_search)) {
    $where_sql .= " AND (nik LIKE ? OR nama_lengkap LIKE ? OR email LIKE ? OR no_hp LIKE ?)";
    $search_term = "%$filter_search%";
    $params = [$search_term, $search_term, $search_term, $search_term];
}

$stmt = $pdo->prepare("SELECT id, nik, nama_lengkap, email, no_hp FROM users $where_sql ORDER BY nama_lengkap ASC");
$stmt->execute($params);
$warga_list = $stmt->fetchAll();

include __DIR__ . '/layout/header.php';
?>

<div class="flex-1 overflow-y-auto p-4 md:p-6 bg-slate-50/50">
    <div class="max-w-6xl mx-auto space-y-6">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-2">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Daftar Warga</h1>
                <p class="text-slate-500 text-sm mt-1">Data seluruh warga yang terdaftar pada sistem.</p>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <!-- Pencarian -->
                <div class="lg:col-span-3">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Pencarian</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($filter_search) ?>" placeholder="NIK, Nama, Email, No HP..." class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-shadow">
                </div>
                
                <!-- Action Buttons -->
                <div class="flex gap-2 h-[38px]">
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center justify-center gap-2">
                        <i class="fa-solid fa-search"></i> Cari
                    </button>
                    <a href="daftar_warga.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center justify-center px-4" title="Reset Filter">
                        <i class="fa-solid fa-rotate-right"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-600 font-medium border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">NIK</th>
                            <th class="px-6 py-4">Nama Lengkap</th>
                            <th class="px-6 py-4">Email / No HP</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php if (empty($warga_list)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                    <i class="fa-solid fa-users-slash text-3xl mb-3 opacity-50 block"></i>
                                    Belum ada data warga terdaftar.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($warga_list as $index => $row): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4"><?= $index + 1 ?></td>
                                    <td class="px-6 py-4 font-mono text-slate-600"><?= htmlspecialchars($row->nik) ?></td>
                                    <td class="px-6 py-4 font-medium text-slate-800"><?= htmlspecialchars($row->nama_lengkap) ?></td>
                                    <td class="px-6 py-4">
                                        <div class="text-slate-800"><?= htmlspecialchars($row->email) ?></div>
                                        <div class="text-slate-500 text-xs"><?= htmlspecialchars($row->no_hp) ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="profil_warga.php?id=<?= $row->id ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-100 text-purple-700 hover:bg-purple-200 rounded-lg text-xs font-semibold transition-colors">
                                            <i class="fa-solid fa-eye"></i> Detail Profil
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
