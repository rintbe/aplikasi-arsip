<?php
require_once '../../config/db_connect.php';
require_once '../../config.php';

$page_title = "Data Surat Masuk (Instansi)";
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg = '';

$filter_search = $_GET['search'] ?? '';
$filter_date_start = $_GET['date_start'] ?? '';
$filter_date_end = $_GET['date_end'] ?? '';

if($action == 'delete' && $id > 0) {
    $qFile = mysqli_query($conn, "SELECT file_pdf FROM surat_masuk WHERE id=$id");
    if($rFile = mysqli_fetch_assoc($qFile)) {
        if(file_exists('../../uploads/surat_hasil/'.$rFile['file_pdf'])) unlink('../../uploads/surat_hasil/'.$rFile['file_pdf']);
    }
    mysqli_query($conn, "DELETE FROM surat_masuk WHERE id=$id");
    header("Location: masuk.php?msg=deleted");
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomor_surat = mysqli_real_escape_string($conn, $_POST['nomor_surat']);
    $instansi_pengirim = mysqli_real_escape_string($conn, $_POST['instansi_pengirim']);
    $perihal = mysqli_real_escape_string($conn, $_POST['perihal']);
    $tanggal_pembuatan = $_POST['tanggal_pembuatan_surat']; // Tanggal di dalam surat
    $tanggal_penerimaan = $_POST['tanggal_penerimaan_surat']; // Tanggal diterima desa

    // Validasi Nomor Surat
    if (mysqli_num_rows(mysqli_query($conn, "SELECT id FROM surat_masuk WHERE nomor_surat='$nomor_surat' AND id != $id")) > 0) {
        $msg = 'Gagal menyimpan: Nomor Surat tersebut sudah pernah digunakan.';
    }

    $file_pdf = '';
    if(isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] == 0 && empty($msg)) {
        $ext = strtolower(pathinfo($_FILES['file_pdf']['name'], PATHINFO_EXTENSION));
        if($ext == 'pdf') {
            $file_pdf = time().'_msk_'.rand(100,999).'.pdf';
            move_uploaded_file($_FILES['file_pdf']['tmp_name'], '../../uploads/surat_hasil/'.$file_pdf);
        } else {
            $msg = 'File harus PDF!';
        }
    }

    if($action == 'add' && empty($msg)) {
        $sql = "INSERT INTO surat_masuk (nomor_surat, instansi_pengirim, perihal, tanggal_pembuatan_surat, tanggal_penerimaan_surat, file_pdf) 
                VALUES ('$nomor_surat', '$instansi_pengirim', '$perihal', '$tanggal_pembuatan', '$tanggal_penerimaan', '$file_pdf')";
        mysqli_query($conn, $sql);
                if ($pengajuan_id > 0) {
            // Update pengajuan_surat status and file_hasil
            $stmt_u = $pdo->prepare("UPDATE pengajuan_surat SET status = 'Selesai', file_hasil = ? WHERE id = ?");
            $stmt_u->execute([$file_pdf, $pengajuan_id]);
        }
        header("Location: masuk.php?msg=added");
        exit;
    } elseif($action == 'edit' && empty($msg)) {
        $setFile = $file_pdf ? ", file_pdf='$file_pdf'" : "";
        $sql = "UPDATE surat_masuk SET nomor_surat='$nomor_surat', instansi_pengirim='$instansi_pengirim', 
                perihal='$perihal', tanggal_pembuatan_surat='$tanggal_pembuatan', 
                tanggal_penerimaan_surat='$tanggal_penerimaan' $setFile WHERE id=$id";
        mysqli_query($conn, $sql);
        header("Location: masuk.php?msg=updated");
        exit;
    }
}

if(isset($_GET['msg'])) {
    if($_GET['msg'] == 'added') $msg = 'Data berhasil ditambahkan.';
    if($_GET['msg'] == 'updated') $msg = 'Data berhasil diubah.';
    if($_GET['msg'] == 'deleted') $msg = 'Data berhasil dihapus.';
}
require_once __DIR__ . '/layout/header.php';
?>

<div class="max-w-7xl mx-auto pb-10">
    
    <?php if($msg): 
        $isError = strpos(strtolower($msg), 'gagal') !== false || strpos(strtolower($msg), 'harus') !== false;
        $bgClass = $isError ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700';
    ?>
    <div class="<?= $bgClass ?> border px-4 py-3 rounded relative mb-4">
        <?= $msg ?>
    </div>
    <?php endif; ?>

    <?php if($action == 'add' || $action == 'edit'): 
        $row = [];
$pengajuan_id = isset($_GET['pengajuan_id']) ? (int)$_GET['pengajuan_id'] : 0;
if ($action == 'add' && $pengajuan_id > 0) {
    // Fetch data from pengajuan_surat using PDO
    $stmt_p = $pdo->prepare("SELECT p.*, u.nama_lengkap, u.nik FROM pengajuan_surat p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
    $stmt_p->execute([$pengajuan_id]);
    $p_data = $stmt_p->fetch();
    if ($p_data) {
        $row['nik'] = $p_data->nik;
        $row['nama_pemohon'] = $p_data->nama_lengkap; // Used in some forms
        $row['nama_almarhum'] = $p_data->nama_lengkap; // Used in kematian
        $row['nama'] = $p_data->nama_lengkap; // Used in some forms
    }
}
        if($action == 'edit' && $id > 0) {
            $res = mysqli_query($conn, "SELECT * FROM surat_masuk WHERE id=$id");
            $row = mysqli_fetch_assoc($res);
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($msg)) {
            foreach($_POST as $k => $v) { $row[$k] = $v; }
        }
    ?>
    <div class="bg-white rounded-2xl p-8 border border-purple-100 shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-slate-800"><?= $action == 'add' ? 'Tambah' : 'Edit' ?> Surat Masuk</h2>
            <a href="masuk.php" class="text-slate-500 hover:text-slate-700 bg-slate-100 px-4 py-2 rounded-lg text-sm font-medium transition">Batal</a>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Nomor Surat (dari Instansi)</label>
                    <input type="text" name="nomor_surat" value="<?= $row['nomor_surat'] ?? '' ?>" required class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Instansi/Organisasi Pengirim</label>
                    <input type="text" name="instansi_pengirim" value="<?= $row['instansi_pengirim'] ?? '' ?>" required class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Perihal Surat</label>
                    <textarea name="perihal" required rows="2" class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500"><?= $row['perihal'] ?? '' ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Tanggal di Surat (Pembuatan)</label>
                    <input type="date" name="tanggal_pembuatan_surat" value="<?= $row['tanggal_pembuatan_surat'] ?? '' ?>" required class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Tanggal Diterima Desa</label>
                    <input type="date" name="tanggal_penerimaan_surat" value="<?= $row['tanggal_penerimaan_surat'] ?? '' ?>" required class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Upload File (PDF)</label>
                    <input type="file" name="file_pdf" accept="application/pdf" <?= $action=='add' ? 'required' : '' ?> class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <?php if($action == 'edit' && !empty($row['file_pdf'])): ?>
                        <div class="mt-2 text-sm text-slate-500">File: <a href="../../uploads/surat_hasil/<?= $row['file_pdf'] ?>" target="_blank" class="text-purple-600 underline">Lihat PDF</a></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex justify-end pt-4 border-t border-purple-50">
                <button type="submit" class="bg-gradient-to-r from-purple-600 to-pink-500 text-white px-6 py-2 rounded-lg font-medium shadow-md hover:shadow-lg transition-all">
                    Simpan Data
                </button>
            </div>
        </form>
    </div>

    <?php else: ?>
    <!-- Table View -->
    <div class="bg-white rounded-2xl border border-purple-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-purple-50 flex flex-col md:flex-row justify-between items-center bg-gray-50/50 gap-4">
            <h2 class="text-xl font-bold text-slate-800">Daftar Surat Masuk Instansi</h2>
            <div class="flex items-center gap-4 w-full md:w-auto">
                <a href="export_excel.php?type=masuk" class="flex-shrink-0 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center transition-colors shadow-sm">
                    <i class="fa-solid fa-file-excel mr-2"></i> Ekspor
                </a>
                <a href="masuk.php?action=add" class="flex-shrink-0 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center transition-colors shadow-sm">
                    <i class="fa-solid fa-plus mr-2"></i> Tambah Baru
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="p-6 border-b border-purple-50 bg-white shadow-sm">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <!-- Pencarian -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Pencarian</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($filter_search) ?>" placeholder="Nomor Surat, Instansi..." class="w-full text-sm px-3 py-2 border border-purple-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-shadow">
                </div>
                
                <!-- Filter Tanggal Mulai -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Tanggal Mulai</label>
                    <input type="date" name="date_start" value="<?= htmlspecialchars($filter_date_start) ?>" class="w-full text-sm px-3 py-2 border border-purple-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-shadow text-slate-700">
                </div>

                <!-- Filter Tanggal Akhir -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Tanggal Akhir</label>
                    <input type="date" name="date_end" value="<?= htmlspecialchars($filter_date_end) ?>" class="w-full text-sm px-3 py-2 border border-purple-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition-shadow text-slate-700">
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2 h-[38px]">
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center justify-center gap-2">
                        <i class="fa-solid fa-search"></i> Cari
                    </button>
                    <a href="masuk.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center justify-center px-4" title="Reset Filter">
                        <i class="fa-solid fa-rotate-right"></i>
                    </a>
                </div>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-purple-50 text-purple-700 font-medium text-sm">
                        <th class="p-4 border-b border-purple-100">No</th>
                        <th class="p-4 border-b border-purple-100">Nomor Surat</th>
                        <th class="p-4 border-b border-purple-100">Instansi Pengirim</th>
                        <th class="p-4 border-b border-purple-100">Perihal</th>
                        <th class="p-4 border-b border-purple-100">Tgl Surat</th>
                        <th class="p-4 border-b border-purple-100">Tgl Diterima</th>
                        <th class="p-4 border-b border-purple-100">File</th>
                        <th class="p-4 border-b border-purple-100 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-600 divide-y divide-purple-50">
                    <?php
                    $where_clauses = [];
                    if (!empty($filter_search)) {
                        $search_clean = mysqli_real_escape_string($conn, $filter_search);
                        $where_clauses[] = "(nomor_surat LIKE '%$search_clean%' OR instansi_pengirim LIKE '%$search_clean%' OR perihal LIKE '%$search_clean%')";
                    }
                    if (!empty($filter_date_start)) {
                        $date_start_clean = mysqli_real_escape_string($conn, $filter_date_start);
                        $where_clauses[] = "tanggal_penerimaan_surat >= '$date_start_clean'";
                    }
                    if (!empty($filter_date_end)) {
                        $date_end_clean = mysqli_real_escape_string($conn, $filter_date_end);
                        $where_clauses[] = "tanggal_penerimaan_surat <= '$date_end_clean'";
                    }
                    $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";
                    
                    $res = mysqli_query($conn, "SELECT * FROM surat_masuk $where_sql ORDER BY id DESC");
                    $no = 1;
                    if(mysqli_num_rows($res) == 0) {
                        echo '<tr><td colspan="8" class="p-8 text-center text-slate-400">Tidak ada data ditemukan.</td></tr>';
                    }
                    while($row = mysqli_fetch_assoc($res)) {
                    ?>
                    <tr class="hover:bg-purple-50/50 transition-colors">
                        <td class="p-4"><?= $no++ ?></td>
                        <td class="p-4 font-medium text-slate-800"><?= $row['nomor_surat'] ?></td>
                        <td class="p-4"><?= $row['instansi_pengirim'] ?></td>
                        <td class="p-4"><?= strlen($row['perihal']) > 30 ? substr($row['perihal'],0,30).'...' : $row['perihal'] ?></td>
                        <td class="p-4"><?= date('d/m/Y', strtotime($row['tanggal_pembuatan_surat'])) ?></td>
                        <td class="p-4"><?= date('d/m/Y', strtotime($row['tanggal_penerimaan_surat'])) ?></td>
                        <td class="p-4">
                            <?php if($row['file_pdf']): ?>
                            <a href="../../uploads/surat_hasil/<?= $row['file_pdf'] ?>" target="_blank" class="text-red-500 hover:text-red-700">
                                <i class="fa-solid fa-file-pdf text-xl"></i>
                            </a>
                            <?php else: ?>-<?php endif; ?>
                        </td>
                        <td class="p-4 flex gap-2 justify-center">
                            <a href="masuk.php?action=edit&id=<?= $row['id'] ?>" class="text-sky-500 hover:text-sky-700 bg-sky-50 hover:bg-sky-100 px-2 py-1 rounded transition">Edit</a>
                            <a href="masuk.php?action=delete&id=<?= $row['id'] ?>" onclick="confirmAction(event, this.href, 'Hapus data ini?');" class="text-pink-500 hover:text-pink-700 bg-pink-50 hover:bg-pink-100 px-2 py-1 rounded transition">Hapus</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>




<?php require_once __DIR__ . '/layout/footer.php'; ?>

