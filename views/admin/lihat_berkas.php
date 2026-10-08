<?php
// views/admin/lihat_berkas.php
$page_title = "Lihat Berkas Warga";
require_once __DIR__ . '/layout/header.php';

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$id) {
    echo "<div class='p-6'><div class='bg-red-100 text-red-700 p-4 rounded-lg'>ID Pengajuan tidak valid.</div></div>";
    require_once __DIR__ . '/layout/footer.php';
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT p.jenis_surat, u.nama_lengkap, l.nama_file, l.path_folder, l.tipe_file FROM pengajuan_surat p JOIN users u ON p.user_id = u.id LEFT JOIN lampiran l ON p.id = l.pengajuan_id WHERE p.id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();
} catch (PDOException $e) {
    echo "<div class='p-6'><div class='bg-red-100 text-red-700 p-4 rounded-lg'>Terjadi kesalahan database.</div></div>";
    require_once __DIR__ . '/layout/footer.php';
    exit;
}

if (!$data || empty($data->nama_file)) {
    echo "<div class='p-6'><div class='bg-red-100 text-red-700 p-4 rounded-lg'>Berkas tidak ditemukan atau belum diunggah.</div></div>";
    require_once __DIR__ . '/layout/footer.php';
    exit;
}

$file_url = '../../' . $data->path_folder . $data->nama_file;
$file_path = __DIR__ . '/../../' . $data->path_folder . $data->nama_file;

if (!file_exists($file_path)) {
    echo "<div class='p-6'><div class='bg-red-100 text-red-700 p-4 rounded-lg'>File fisik tidak ditemukan di server. Kemungkinan file telah dihapus.</div></div>";
    require_once __DIR__ . '/layout/footer.php';
    exit;
}
?>

<div class="max-w-5xl mx-auto pb-10">
    <div class="bg-white rounded-2xl border border-purple-100 shadow-sm overflow-hidden flex flex-col" style="height: 80vh;">
        <!-- Header area -->
        <div class="p-4 border-b border-purple-50 flex flex-col sm:flex-row justify-between items-center bg-gray-50 gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800 flex items-center">
                    <i class="fa-solid fa-file-lines mr-2 text-purple-600"></i> Pratinjau Berkas Lampiran
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    Pengaju: <span class="font-semibold text-slate-700"><?= htmlspecialchars($data->nama_lengkap) ?></span> &bull; Jenis: <?= htmlspecialchars($data->jenis_surat) ?>
                </p>
            </div>
            <div class="flex gap-2">
                <a href="<?= htmlspecialchars($file_url) ?>" download="<?= htmlspecialchars($data->nama_file) ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm inline-flex items-center">
                    <i class="fa-solid fa-download mr-2"></i> Download Berkas
                </a>
                <button onclick="window.close()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm inline-flex items-center">
                    <i class="fa-solid fa-times mr-2"></i> Tutup Tab
                </button>
            </div>
        </div>

        <!-- Viewer area -->
        <div class="flex-grow bg-slate-100 p-4 flex justify-center items-center overflow-auto">
            <?php if (strpos($data->tipe_file, 'image/') === 0): ?>
                <img src="<?= htmlspecialchars($file_url) ?>" alt="Lampiran" class="max-w-full max-h-full object-contain shadow-sm border border-slate-200 rounded">
            <?php elseif ($data->tipe_file === 'application/pdf'): ?>
                <iframe src="<?= htmlspecialchars($file_url) ?>" class="w-full h-full border-0 shadow-sm rounded bg-white"></iframe>
            <?php else: ?>
                <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200 text-center">
                    <i class="fa-solid fa-file text-5xl text-slate-300 mb-4"></i>
                    <p class="text-slate-600 font-medium">Pratinjau tidak tersedia untuk format file ini.</p>
                    <p class="text-slate-400 text-sm mt-1 mb-4">Silakan download berkas untuk melihat isinya.</p>
                    <a href="<?= htmlspecialchars($file_url) ?>" download="<?= htmlspecialchars($data->nama_file) ?>" class="text-blue-600 font-medium hover:underline inline-flex items-center">
                        <i class="fa-solid fa-download mr-1"></i> Download Sekarang
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
