<?php
require_once '../../config/db_connect.php';
require_once '../../config.php';

$page_title = "Data Surat Kematian";
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg = '';

// Handle Delete
if($action == 'delete' && $id > 0) {
    $qFile = mysqli_query($conn, "SELECT file_pdf FROM surat_kematian WHERE id=$id");
    if($rFile = mysqli_fetch_assoc($qFile)) {
        if(file_exists('../../uploads/surat_hasil/'.$rFile['file_pdf'])) {
            unlink('../../uploads/surat_hasil/'.$rFile['file_pdf']);
        }
    }
    mysqli_query($conn, "DELETE FROM surat_kematian WHERE id=$id");
    header("Location: kematian.php?msg=deleted");
    exit;
}

// Handle POST Add/Edit
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomor_surat = mysqli_real_escape_string($conn, $_POST['nomor_surat']);
    $nama_almarhum = mysqli_real_escape_string($conn, $_POST['nama_almarhum']);
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $tempat_meninggal = mysqli_real_escape_string($conn, $_POST['tempat_meninggal']);
    $tanggal_meninggal = $_POST['tanggal_meninggal'];
    $sebab_kematian = mysqli_real_escape_string($conn, $_POST['sebab_kematian']);
    $tanggal_pembuatan = $_POST['tanggal_pembuatan_surat'];
    $tanggal_penerimaan = $_POST['tanggal_penerimaan_surat'];

    // Validasi NIK dan Nomor Surat
    if (!preg_match('/^[0-9]{16}$/', $nik)) {
        $msg = 'Gagal menyimpan: NIK harus berupa angka dan persis 16 digit.';
    } elseif (mysqli_num_rows(mysqli_query($conn, "SELECT id FROM surat_kematian WHERE nik='$nik' AND id != $id")) > 0) {
        $msg = 'Gagal menyimpan: NIK tersebut sudah terdaftar pada data kematian lain.';
    } elseif (mysqli_num_rows(mysqli_query($conn, "SELECT id FROM surat_kematian WHERE nomor_surat='$nomor_surat' AND id != $id")) > 0) {
        $msg = 'Gagal menyimpan: Nomor Surat tersebut sudah pernah digunakan.';
    }

    $file_pdf = '';
    // Upload File
    if(isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] == 0 && empty($msg)) {
        $ext = strtolower(pathinfo($_FILES['file_pdf']['name'], PATHINFO_EXTENSION));
        if($ext == 'pdf') {
            $file_pdf = time().'_kmtn_'.rand(100,999).'.pdf';
            move_uploaded_file($_FILES['file_pdf']['tmp_name'], '../../uploads/surat_hasil/'.$file_pdf);
        } else {
            $msg = 'File harus PDF!';
        }
    }

    if($action == 'add' && empty($msg)) {
        $sql = "INSERT INTO surat_kematian (nomor_surat, nama_almarhum, nik, tempat_meninggal, tanggal_meninggal, sebab_kematian, tanggal_pembuatan_surat, tanggal_penerimaan_surat, file_pdf) 
                VALUES ('$nomor_surat', '$nama_almarhum', '$nik', '$tempat_meninggal', '$tanggal_meninggal', '$sebab_kematian', '$tanggal_pembuatan', '$tanggal_penerimaan', '$file_pdf')";
        mysqli_query($conn, $sql);
                if ($pengajuan_id > 0) {
            // Update pengajuan_surat status and file_hasil
            $stmt_u = $pdo->prepare("UPDATE pengajuan_surat SET status = 'Selesai', file_hasil = ? WHERE id = ?");
            $stmt_u->execute([$file_pdf, $pengajuan_id]);
        }
        header("Location: kematian.php?msg=added");
        exit;
    } elseif($action == 'edit' && empty($msg)) {
        $setFile = $file_pdf ? ", file_pdf='$file_pdf'" : "";
        $sql = "UPDATE surat_kematian SET nomor_surat='$nomor_surat', nama_almarhum='$nama_almarhum', nik='$nik', tempat_meninggal='$tempat_meninggal', tanggal_meninggal='$tanggal_meninggal', sebab_kematian='$sebab_kematian', tanggal_pembuatan_surat='$tanggal_pembuatan', tanggal_penerimaan_surat='$tanggal_penerimaan' $setFile WHERE id=$id";
        mysqli_query($conn, $sql);
        header("Location: kematian.php?msg=updated");
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
        $row['nama_pemohon'] = $p_data->nama_lengkap;
        $row['nama_almarhum'] = $p_data->nama_lengkap;
        $row['nama_pemilik'] = $p_data->nama_lengkap;
        $row['nama'] = $p_data->nama_lengkap;
        $row['nama_lengkap'] = $p_data->nama_lengkap;
        
        if (!empty($p_data->data_meta)) {
            $meta = json_decode($p_data->data_meta, true);
            if (is_array($meta)) {
                foreach ($meta as $k => $v) {
                    $row[$k] = $v;
                }
            }
        }
    }
}
        if($action == 'edit' && $id > 0) {
            $res = mysqli_query($conn, "SELECT * FROM surat_kematian WHERE id=$id");
            $row = mysqli_fetch_assoc($res);
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($msg)) {
            foreach($_POST as $k => $v) { $row[$k] = $v; }
        }
    ?>
    <div class="bg-white rounded-2xl p-8 border border-purple-100 shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-slate-800"><?= $action == 'add' ? 'Tambah' : 'Edit' ?> Surat Kematian</h2>
            <a href="kematian.php" class="text-slate-500 hover:text-slate-700 bg-slate-100 px-4 py-2 rounded-lg text-sm font-medium transition">Batal</a>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Nomor Surat</label>
                    <input type="text" name="nomor_surat" value="<?= $row['nomor_surat'] ?? '' ?>" required class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Nama Almarhum</label>
                    <input type="text" name="nama_almarhum" value="<?= $row['nama_almarhum'] ?? '' ?>" required class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">NIK</label>
                    <input type="text" name="nik" value="<?= $row['nik'] ?? '' ?>" required minlength="16" maxlength="16" pattern="[0-9]{16}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Wajib 16 digit angka" class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Tempat Meninggal</label>
                    <input type="text" name="tempat_meninggal" value="<?= $row['tempat_meninggal'] ?? '' ?>" required class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Tanggal Meninggal</label>
                    <input type="date" name="tanggal_meninggal" value="<?= $row['tanggal_meninggal'] ?? '' ?>" required class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Sebab Kematian</label>
                    <input type="text" name="sebab_kematian" value="<?= $row['sebab_kematian'] ?? '' ?>" required class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Tanggal Pembuatan Surat</label>
                    <input type="date" name="tanggal_pembuatan_surat" value="<?= $row['tanggal_pembuatan_surat'] ?? '' ?>" required class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Tanggal Penerimaan Surat</label>
                    <input type="date" name="tanggal_penerimaan_surat" value="<?= $row['tanggal_penerimaan_surat'] ?? '' ?>" required class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Upload PDF (Bila diedit kosongkan jika tidak ganti)</label>
                    <input type="file" name="file_pdf" accept="application/pdf" <?= $action=='add' ? 'required' : '' ?> class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <?php if($action == 'edit' && !empty($row['file_pdf'])): ?>
                        <div class="mt-2 text-sm text-slate-500">File saat ini: <a href="../../uploads/surat_hasil/<?= $row['file_pdf'] ?>" target="_blank" class="text-purple-600 underline">Lihat PDF</a></div>
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
            <h2 class="text-xl font-bold text-slate-800">Daftar Surat Kematian</h2>
            <div class="flex items-center gap-4 w-full md:w-auto">
                <div class="relative w-full md:w-64">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fa-solid fa-search text-slate-400"></i>
                    </div>
                    <input type="text" id="tableSearch" class="bg-white border border-purple-200 text-slate-700 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full pl-10 p-2.5" placeholder="Cari data..." autocomplete="off">
                </div>
                <a href="export_excel.php?type=kematian" class="flex-shrink-0 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center transition-colors shadow-sm">
                    <i class="fa-solid fa-file-excel mr-2"></i> Ekspor
                </a>
                <a href="kematian.php?action=add" class="flex-shrink-0 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center transition-colors shadow-sm">
                    <i class="fa-solid fa-plus mr-2"></i> Tambah Baru
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-purple-50 text-purple-700 font-medium text-sm">
                        <th class="p-4 border-b border-purple-100">No</th>
                        <th class="p-4 border-b border-purple-100">Nomor Surat</th>
                        <th class="p-4 border-b border-purple-100">Nama Almarhum</th>
                        <th class="p-4 border-b border-purple-100">NIK</th>
                        <th class="p-4 border-b border-purple-100">Tgl Meninggal</th>
                        <th class="p-4 border-b border-purple-100">Tgl Dibuat</th>
                        <th class="p-4 border-b border-purple-100">Tgl Diterima</th>
                        <th class="p-4 border-b border-purple-100">File</th>
                        <th class="p-4 border-b border-purple-100 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-600 divide-y divide-purple-50">
                    <?php
                    $res = mysqli_query($conn, "SELECT * FROM surat_kematian ORDER BY id DESC");
                    $no = 1;
                    while($row = mysqli_fetch_assoc($res)) {
                    ?>
                    <tr class="hover:bg-purple-50/50 transition-colors">
                        <td class="p-4"><?= $no++ ?></td>
                        <td class="p-4 font-medium text-slate-800"><?= $row['nomor_surat'] ?></td>
                        <td class="p-4"><?= $row['nama_almarhum'] ?></td>
                        <td class="p-4"><?= $row['nik'] ?></td>
                        <td class="p-4"><?= date('d/m/Y', strtotime($row['tanggal_meninggal'])) ?></td>
                        <td class="p-4"><?= date('d/m/Y', strtotime($row['tanggal_pembuatan_surat'])) ?></td>
                        <td class="p-4"><?= date('d/m/Y', strtotime($row['tanggal_penerimaan_surat'])) ?></td>
                        <td class="p-4">
                            <?php if($row['file_pdf']): ?>
                            <a href="../../uploads/surat_hasil/<?= $row['file_pdf'] ?>" target="_blank" class="text-red-500 hover:text-red-700" title="Buka PDF">
                                <i class="fa-solid fa-file-pdf text-xl"></i>
                            </a>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td class="p-4 flex gap-2 justify-center">
                            <a href="kematian.php?action=edit&id=<?= $row['id'] ?>" class="text-sky-500 hover:text-sky-700 bg-sky-50 hover:bg-sky-100 px-2 py-1 rounded transition">Edit</a>
                            <a href="kematian.php?action=delete&id=<?= $row['id'] ?>" onclick="confirmAction(event, this.href, 'Hapus data ini?');" class="text-pink-500 hover:text-pink-700 bg-pink-50 hover:bg-pink-100 px-2 py-1 rounded transition">Hapus</a>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if(mysqli_num_rows($res) == 0): ?>
                    <tr><td colspan="9" class="p-8 text-center text-slate-400">Belum ada data tersedia.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('tableSearch');
    if(searchInput) {
        const datalist = document.createElement('datalist');
        datalist.id = 'searchSuggest';
        document.body.appendChild(datalist);
        searchInput.setAttribute('list', 'searchSuggest');

        const uniqueTerms = new Set();
        const tableRows = document.querySelectorAll('tbody tr');
        
        tableRows.forEach(row => {
            if (row.cells.length <= 1) return;
            if(row.cells[1]) uniqueTerms.add(row.cells[1].textContent.trim());
            if(row.cells[2]) uniqueTerms.add(row.cells[2].textContent.trim());
            if(row.cells[3]) uniqueTerms.add(row.cells[3].textContent.trim());
        });

        uniqueTerms.forEach(term => {
            if(term && term !== '-' && term.length > 2) {
                const option = document.createElement('option');
                option.value = term;
                datalist.appendChild(option);
            }
        });

        function doSearch() {
            const searchTerm = searchInput.value.toLowerCase();
            tableRows.forEach(row => {
                if (row.cells.length === 1 && row.cells[0].colSpan > 1) return;
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        }

        searchInput.addEventListener('keyup', doSearch);
        searchInput.addEventListener('input', doSearch);
    }
});
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

