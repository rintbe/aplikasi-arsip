<?php
$dir = 'c:/xampp/htdocs/aplikasi_arsip_teluknaga/views/admin/';
$files = glob($dir . '*.php');

foreach($files as $file) {
    $content = file_get_contents($file);
    $new_content = str_replace('href="uploads/<?= $row[\'file_pdf\'] ?>"','href="../../uploads/surat_hasil/<?= $row[\'file_pdf\'] ?>"',$content);
    if ($content !== $new_content) {
        file_put_contents($file, $new_content);
        echo "Updated $file\n";
    }
}
?>
