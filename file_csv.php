<?php
require 'koneksi.php';

// Atur header HTTP agar browser mendownload sebagai file CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=daftar_santri_' . date('Y-m-d') . '.csv');

// Buka output stream PHP
$output = fopen('php://output', 'w');

// Tambahkan BOM (Byte Order Mark) agar huruf aksen/spesial dan format UTF-8 terbaca rapi di Excel
fputs($output, "\xEF\xBB\xBF");

// 1. Tulis Header Kolom CSV
fputcsv($output, [
    'No', 
    'Nama Santri', 
    'Jenis Kelamin', 
    'Tanggal Lahir', 
    'Umur (Tahun)', 
    'Asal Daerah', 
    'Kamar', 
    'Angkatan', 
    'No HP Wali'
]);

// 2. Ambil Filter jika ada (opsional, sesuai pencarian dari index.php)
$cari = trim($_GET['cari'] ?? '');
$kamar_filter = $_GET['kamar'] ?? '';

if ($cari !== '') {
    $stmt = $pdo->prepare('SELECT * FROM santri WHERE nama LIKE :cari ORDER BY id DESC');
    $stmt->execute([':cari' => '%' . $cari . '%']);
} elseif ($kamar_filter !== '') {
    $stmt = $pdo->prepare('SELECT * FROM santri WHERE kamar = :kamar ORDER BY id DESC');
    $stmt->execute([':kamar' => $kamar_filter]);
} else {
    $stmt = $pdo->query('SELECT * FROM santri ORDER BY id DESC');
}

$daftar = $stmt->fetchAll();

// 3. Tulis Baris Data ke CSV
$no = 1;
$today = new DateTime('today');

foreach ($daftar as $row) {
    // Format jenis kelamin
    $jk = ($row['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan';

    // Hitung umur
    $birthDate = new DateTime($row['tanggal_lahir']);
    $umur = $birthDate->diff($today)->y;

    fputcsv($output, [
        $no++,
        $row['nama'],
        $jk,
        $row['tanggal_lahir'],
        $umur,
        $row['asal_daerah'],
        $row['kamar'],
        $row['angkatan'],
        "'" . $row['no_hp_wali'] // Tanda kutip tunggal agar nomor HP tidak terpotong nol depannya di Excel
    ]);
}

fclose($output);
exit();