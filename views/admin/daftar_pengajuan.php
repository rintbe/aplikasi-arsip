<?php
// views/admin/daftar_pengajuan.php
$page_title = "Daftar Pengajuan Warga";
require_once __DIR__ . '/layout/header.php';

// Filter parameters
$search = $_GET['search'] ?? '';
$date_start = $_GET['date_start'] ?? '';
$date_end = $_GET['date_end'] ?? '';
$jenis_surat = $_GET['jenis_surat'] ?? '';
$status = $_GET['status'] ?? '';

// Build Query
$where_clauses = [];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(u.nama_lengkap LIKE :search OR u.nik LIKE :search OR p.keperluan LIKE :search OR p.keterangan LIKE :search)";
    $params[':search'] = "%$search%";
}
if (!empty($date_start)) {
    $where_clauses[] = "DATE(p.tgl_pengajuan) >= :date_start";
    $params[':date_start'] = $date_start;
}
if (!empty($date_end)) {
    $where_clauses[] = "DATE(p.tgl_pengajuan) <= :date_end";
    $params[':date_end'] = $date_end;
}
if (!empty($jenis_surat)) {
    $where_clauses[] = "p.jenis_surat = :jenis_surat";
    $params[':jenis_surat'] = $jenis_surat;
}
if (!empty($status)) {
    $where_clauses[] = "p.status = :status";
    $params[':status'] = $status;
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

try {
    $sql = "SELECT p.*, u.nama_lengkap, u.nik, u.no_hp, l.nama_file as lampiran_file, l.path_folder as lampiran_path 
            FROM pengajuan_surat p 
            JOIN users u ON p.user_id = u.id 
            LEFT JOIN lampiran l ON p.id = l.pengajuan_id
            $where_sql
            ORDER BY p.tgl_pengajuan DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pengajuan_list = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Gagal mengambil data pengajuan admin: " . $e->getMessage());
    $pengajuan_list = [];
}
?>

<div class="max-w-7xl mx-auto pb-10">
    <div class="bg-white rounded-2xl border border-purple-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-purple-50 flex flex-col md:flex-row justify-between items-center bg-gray-50/50 gap-4">
            <h2 class="text-xl font-bold text-slate-800"><i class="fa-solid fa-list-check mr-2 text-purple-600"></i>Daftar Pengajuan Surat Warga</h2>
        </div>
        
        <!-- Filter Form -->
        <div class="p-6 border-b border-purple-50 bg-white shadow-sm">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Pencarian -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Pencarian</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Nama, NIK, Keperluan..." class="w-full text-sm px-3 py-2 border border-purple-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-shadow">
                </div>
                
                <!-- Filter Tanggal Mulai -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Tanggal Mulai</label>
                    <input type="date" name="date_start" value="<?= htmlspecialchars($date_start) ?>" class="w-full text-sm px-3 py-2 border border-purple-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-shadow text-slate-700">
                </div>

                <!-- Filter Tanggal Akhir -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Tanggal Akhir</label>
                    <input type="date" name="date_end" value="<?= htmlspecialchars($date_end) ?>" class="w-full text-sm px-3 py-2 border border-purple-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-shadow text-slate-700">
                </div>

                <!-- Filter Jenis Surat -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Jenis Surat</label>
                    <select name="jenis_surat" class="w-full text-sm px-3 py-2 border border-purple-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-shadow text-slate-700">
                        <option value="">Semua Jenis</option>
                        <option value="Surat Keterangan Domisili" <?= $jenis_surat == 'Surat Keterangan Domisili' ? 'selected' : '' ?>>Domisili</option>
                        <option value="Surat Keterangan Usaha" <?= $jenis_surat == 'Surat Keterangan Usaha' ? 'selected' : '' ?>>Usaha</option>
                        <option value="Surat Keterangan Kematian" <?= $jenis_surat == 'Surat Keterangan Kematian' ? 'selected' : '' ?>>Kematian</option>
                        <option value="Surat Pindah Penduduk" <?= $jenis_surat == 'Surat Pindah Penduduk' ? 'selected' : '' ?>>Pindah</option>
                        <option value="Surat Pengantar Nikah" <?= $jenis_surat == 'Surat Pengantar Nikah' ? 'selected' : '' ?>>Pernikahan</option>
                    </select>
                </div>

                <!-- Filter Status & Action Buttons -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Status & Aksi</label>
                    <div class="flex gap-2">
                        <select name="status" class="w-full text-sm px-3 py-2 border border-purple-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-shadow text-slate-700">
                            <option value="">Semua Status</option>
                            <option value="Pending" <?= $status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Proses" <?= $status == 'Proses' ? 'selected' : '' ?>>Proses</option>
                            <option value="Selesai" <?= $status == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                            <option value="Ditolak" <?= $status == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                        </select>
                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center justify-center" title="Terapkan Filter">
                            <i class="fa-solid fa-search"></i>
                        </button>
                        <a href="daftar_pengajuan.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center justify-center" title="Reset Filter">
                            <i class="fa-solid fa-rotate-right"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <?php display_flash_message(); ?>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-purple-50 text-purple-700 font-medium text-sm">
                        <th class="p-4 border-b border-purple-100">Warga (NIK)</th>
                        <th class="p-4 border-b border-purple-100">Jenis Surat</th>
                        <th class="p-4 border-b border-purple-100">Keperluan</th>
                        <th class="p-4 border-b border-purple-100">Tanggal</th>
                        <th class="p-4 border-b border-purple-100">Status</th>
                        <th class="p-4 border-b border-purple-100">Keterangan</th>
                        <th class="p-4 border-b border-purple-100 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-600 divide-y divide-purple-50">
                    <?php if (empty($pengajuan_list)): ?>
                    <tr><td colspan="6" class="p-8 text-center text-slate-400">Belum ada pengajuan surat masuk.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pengajuan_list as $row): ?>
                        <tr class="hover:bg-purple-50/50 transition-colors">
                            <td class="p-4">
                                <strong class="text-slate-800"><?= htmlspecialchars($row->nama_lengkap) ?></strong><br>
                                <span class="text-xs text-slate-500"><?= htmlspecialchars($row->nik) ?></span>
                            </td>
                            <td class="p-4 font-medium text-purple-600">
                                <?= htmlspecialchars($row->jenis_surat) ?>
                                <?php if (strpos($row->keperluan, '[REVISI]') !== false): ?>
                                    <span class="bg-orange-100 text-orange-700 text-[10px] font-bold px-2 py-0.5 rounded ml-2 shadow-sm border border-orange-200 align-middle">PENGAJUAN ULANG</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 whitespace-normal break-words min-w-[250px]">
                                <?= nl2br(htmlspecialchars(str_replace('[REVISI] ', '', $row->keperluan))) ?>
                            </td>
                            <td class="p-4"><?= date('d/m/Y H:i', strtotime($row->tgl_pengajuan)) ?></td>
                            <td class="p-4">
                                <form action="../../actions/admin/update_status.php" method="POST" class="m-0" id="form-status-<?= $row->id ?>" onsubmit="if(!navigator.onLine){ alert('Koneksi internet terputus! Tidak dapat menyimpan data.'); return false; }">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="pengajuan_id" value="<?= $row->id ?>">
                                    <?php 
                                    $statusClass = 'bg-gray-100 text-gray-700 border-gray-200';
                                    if($row->status == 'Pending') $statusClass = 'bg-yellow-100 text-yellow-700 border-yellow-200';
                                    if($row->status == 'Proses') $statusClass = 'bg-blue-100 text-blue-700 border-blue-200';
                                    if($row->status == 'Selesai') $statusClass = 'bg-green-100 text-green-700 border-green-200';
                                    if($row->status == 'Ditolak') $statusClass = 'bg-red-100 text-red-700 border-red-200';
                                    ?>
                                    <select name="status" class="text-xs font-semibold rounded-full px-2 py-1 border cursor-pointer focus:outline-none focus:ring-2 focus:ring-purple-500 <?= $statusClass ?>" data-original="<?= $row->status ?>" onchange="if(navigator.onLine){ document.getElementById('form-status-<?= $row->id ?>').submit(); } else { alert('Koneksi internet terputus! Perubahan status dibatalkan.'); this.value = this.getAttribute('data-original'); }">
                                        <option value="Pending" <?= $row->status == 'Pending' ? 'selected' : '' ?> class="bg-white text-slate-800 font-medium">Pending</option>
                                        <option value="Proses" <?= $row->status == 'Proses' ? 'selected' : '' ?> class="bg-white text-slate-800 font-medium">Proses</option>
                                        <option value="Selesai" <?= $row->status == 'Selesai' ? 'selected' : '' ?> class="bg-white text-slate-800 font-medium">Selesai</option>
                                        <option value="Ditolak" <?= $row->status == 'Ditolak' ? 'selected' : '' ?> class="bg-white text-slate-800 font-medium">Ditolak</option>
                                    </select>
                            </td>
                            <td class="p-4">
                                    <div class="flex items-start gap-2">
                                        <textarea name="keterangan" rows="2" placeholder="Catatan untuk warga..." class="text-xs border border-purple-200 rounded p-1 w-40 focus:ring-purple-500 focus:border-purple-500"><?= htmlspecialchars($row->keterangan ?? '') ?></textarea>
                                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white p-1.5 rounded text-xs transition-colors shadow-sm" title="Simpan Keterangan">
                                            <i class="fa-solid fa-save"></i>
                                        </button>
                                    </div>
                                </form>
                            </td>
                            <td class="p-4">
                                <div class="flex flex-col items-center gap-2">
                                    <?php if(!empty($row->lampiran_file)): ?>
                                        <a href="../../<?= htmlspecialchars($row->lampiran_path . $row->lampiran_file) ?>" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-medium transition-colors shadow-sm inline-flex items-center justify-center gap-1 w-full" title="Lihat Berkas Persyaratan">
                                            <i class="fa-solid fa-paperclip"></i> Cek Berkas
                                        </a>
                                    <?php endif; ?>
                                    
                                    <!-- Tombol Arsip -->
                                    <?php 
                                        $target_form = '';
                                        $jenis = strtolower($row->jenis_surat);
                                        if (strpos($jenis, 'kematian') !== false) $target_form = 'kematian.php';
                                        elseif (strpos($jenis, 'domisili') !== false) $target_form = 'domisili.php';
                                        elseif (strpos($jenis, 'usaha') !== false) $target_form = 'usaha.php';
                                        elseif (strpos($jenis, 'pindah') !== false) $target_form = 'pindah.php';
                                        elseif (strpos($jenis, 'pernikahan') !== false) $target_form = 'pernikahan.php';
                                    ?>
                                    <?php if($target_form): ?>
                                    <a href="<?= $target_form ?>?action=add&pengajuan_id=<?= $row->id ?>" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded text-xs font-medium transition-colors shadow-sm inline-flex items-center justify-center gap-1 w-full">
                                        <i class="fa-solid fa-file-signature"></i> Arsipkan
                                    </a>
                                    <?php endif; ?>

                                    <?php if($row->file_hasil): ?>
                                        <a href="../../uploads/surat_hasil/<?= htmlspecialchars($row->file_hasil) ?>" target="_blank" class="text-green-600 hover:text-green-800 font-medium text-xs inline-flex items-center justify-center gap-1 border border-green-200 px-3 py-1.5 rounded bg-green-50 w-full transition-colors hover:bg-green-100" title="Lihat File Surat yang Sudah Jadi">
                                            <i class="fa-solid fa-download"></i> Surat Jadi
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
