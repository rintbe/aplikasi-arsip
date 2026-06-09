<?php
require_once '../../config/db_connect.php';
require_once '../../config.php';
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

check_login();

$type = isset($_GET['type']) ? $_GET['type'] : 'all';

$tables = [
    'domisili' => [
        'title' => 'Surat Domisili',
        'table' => 'surat_domisili',
        'columns' => [
            'ID' => 'id',
            'Nomor Surat' => 'nomor_surat',
            'Nama Lengkap' => 'nama_lengkap',
            'NIK' => 'nik',
            'Tempat Tanggal Lahir' => 'tempat_tanggal_lahir',
            'Alamat Domisili' => 'alamat_domisili',
            'Tgl Pembuatan' => 'tanggal_pembuatan_surat',
            'Tgl Penerimaan' => 'tanggal_penerimaan_surat',
            'Created At' => 'created_at'
        ]
    ],
    'kematian' => [
        'title' => 'Surat Kematian',
        'table' => 'surat_kematian',
        'columns' => [
            'ID' => 'id',
            'Nomor Surat' => 'nomor_surat',
            'Nama Almarhum' => 'nama_almarhum',
            'NIK' => 'nik',
            'Tempat Meninggal' => 'tempat_meninggal',
            'Tgl Meninggal' => 'tanggal_meninggal',
            'Sebab Kematian' => 'sebab_kematian',
            'Tgl Pembuatan' => 'tanggal_pembuatan_surat',
            'Tgl Penerimaan' => 'tanggal_penerimaan_surat',
            'Created At' => 'created_at'
        ]
    ],
    'masuk' => [
        'title' => 'Surat Masuk',
        'table' => 'surat_masuk',
        'columns' => [
            'ID' => 'id',
            'Nomor Surat' => 'nomor_surat',
            'Instansi Pengirim' => 'instansi_pengirim',
            'Perihal' => 'perihal',
            'Tgl Pembuatan' => 'tanggal_pembuatan_surat',
            'Tgl Penerimaan' => 'tanggal_penerimaan_surat',
            'Created At' => 'created_at'
        ]
    ],
    'keluar' => [
        'title' => 'Surat Keluar',
        'table' => 'surat_keluar',
        'columns' => [
            'ID' => 'id',
            'Nomor Surat' => 'nomor_surat',
            'Instansi Tujuan' => 'instansi_tujuan',
            'Perihal' => 'perihal',
            'Tgl Pembuatan' => 'tanggal_pembuatan_surat',
            'Tgl Dikirim' => 'tanggal_pengiriman_surat',
            'Created At' => 'created_at'
        ]
    ],
    'pernikahan' => [
        'title' => 'Surat Pernikahan',
        'table' => 'surat_pernikahan',
        'columns' => [
            'ID' => 'id',
            'Nomor Surat' => 'nomor_surat',
            'Nama Pria' => 'nama_pria',
            'NIK Pria' => 'nik_pria',
            'Nama Wanita' => 'nama_wanita',
            'NIK Wanita' => 'nik_wanita',
            'Tgl Pernikahan' => 'tanggal_pernikahan',
            'Tgl Pembuatan' => 'tanggal_pembuatan_surat',
            'Tgl Penerimaan' => 'tanggal_penerimaan_surat',
            'Created At' => 'created_at'
        ]
    ],
    'pindah' => [
        'title' => 'Surat Pindah',
        'table' => 'surat_pindah',
        'columns' => [
            'ID' => 'id',
            'Nomor Surat' => 'nomor_surat',
            'Nama Lengkap' => 'nama_lengkap',
            'NIK' => 'nik',
            'Alamat Asal' => 'alamat_asal',
            'Alamat Tujuan' => 'alamat_tujuan',
            'Alasan Pindah' => 'alasan_pindah',
            'Tgl Pembuatan' => 'tanggal_pembuatan_surat',
            'Tgl Penerimaan' => 'tanggal_penerimaan_surat',
            'Created At' => 'created_at'
        ]
    ],
    'usaha' => [
        'title' => 'Surat Usaha',
        'table' => 'surat_usaha',
        'columns' => [
            'ID' => 'id',
            'Nomor Surat' => 'nomor_surat',
            'Nama Pemilik' => 'nama_pemilik',
            'NIK' => 'nik',
            'Nama Usaha' => 'nama_usaha',
            'Bidang Usaha' => 'bidang_usaha',
            'Alamat Usaha' => 'alamat_usaha',
            'Tgl Pembuatan' => 'tanggal_pembuatan_surat',
            'Tgl Penerimaan' => 'tanggal_penerimaan_surat',
            'Created At' => 'created_at'
        ]
    ]
];

$valid_types = array_keys($tables);
$valid_types[] = 'all';

if (!in_array($type, $valid_types)) {
    die("Tipe ekspor tidak valid.");
}

$spreadsheet = new Spreadsheet();
$first_sheet_created = false;

$types_to_export = ($type === 'all') ? array_keys($tables) : [$type];
$sheet_index = 0;

foreach ($types_to_export as $t) {
    if (!$first_sheet_created) {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($tables[$t]['title']);
        $first_sheet_created = true;
    } else {
        $sheet = $spreadsheet->createSheet($sheet_index);
        $sheet->setTitle($tables[$t]['title']);
    }
    
    // Header Style Setup
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['argb' => 'FFFFFFFF'],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'FF6B21A8', // Purple tone to match the app
            ],
        ],
    ];
    
    $headers = array_keys($tables[$t]['columns']);
    $col_names = array_values($tables[$t]['columns']);
    
    // Set Headers
    $col_idx = 1;
    foreach ($headers as $header) {
        $cell_coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_idx) . '1';
        $sheet->setCellValue($cell_coord, $header);
        $col_idx++;
    }
    
    // Apply Header Style
    $last_col_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
    $sheet->getStyle('A1:' . $last_col_letter . '1')->applyFromArray($headerStyle);
    
    // Fetch Data
    $table_name = $tables[$t]['table'];
    $sql = "SELECT * FROM $table_name ORDER BY id ASC";
    $result = mysqli_query($conn, $sql);
    
    $row_idx = 2;
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $col_idx = 1;
            foreach ($col_names as $col_name) {
                $cell_coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_idx) . $row_idx;
                $sheet->setCellValue($cell_coord, $row[$col_name]);
                $col_idx++;
            }
            $row_idx++;
        }
    }
    
    // Auto-size columns based on content length
    for ($i = 1; $i <= count($headers); $i++) {
        $column_letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
        $sheet->getColumnDimension($column_letter)->setAutoSize(true);
    }
    
    // Borders for data rows
    if ($row_idx > 2) {
        $sheet->getStyle('A2:' . $last_col_letter . ($row_idx - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }
    
    $sheet_index++;
}

// Redirect output to a client's web browser (Xlsx)
$filename = ($type === 'all') ? "Laporan_Semua_Surat" : "Laporan_" . str_replace(" ", "_", $tables[$type]['title']);
$filename .= "_" . date('Ymd_His') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
