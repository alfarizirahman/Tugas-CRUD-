<?php
require 'koneksi.php';

$errors = [];
$santri = [
  'nama'          => '',
  'jenis_kelamin' => '',
  'tanggal_lahir' => '',
  'asal_daerah'   => '',
  'kamar'         => '',
  'no_hp_wali'    => '',
  'angkatan'    => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  foreach ($santri as $kolom => $nilai) {
    $santri[$kolom] = trim($_POST[$kolom] ?? '');
  }

  if ($santri['nama'] === '') {
    $errors[] = 'Nama wajib diisi.';
  }
  if (!in_array($santri['jenis_kelamin'], ['L', 'P'])) {
    $errors[] = 'Jenis kelamin wajib dipilih.';
  }
  if (mb_strlen($santri['nama']) > 100) {
    $errors[] = 'Nama maksimal 100 karakter.';
  }

  if ($santri['no_hp_wali'] !== '' && !preg_match('/^[0-9+]{8,15}$/', $santri['no_hp_wali'])) {
    $errors[] = 'No. HP wali hanya boleh angka (8-15 digit).';
  }

  if ($santri['tanggal_lahir'] !== '') {
    $tgl = DateTime::createFromFormat('Y-m-d', $santri['tanggal_lahir']);
    if (!$tgl || $tgl->format('Y-m-d') !== $santri['tanggal_lahir']) {
      $errors[] = 'Format tanggal lahir tidak valid.';
    }
  }


  if (count($errors) === 0) {
    $sql = 'INSERT INTO santri
                  (nama, jenis_kelamin, tanggal_lahir, asal_daerah, kamar, no_hp_wali, angkatan)
                VALUES
                  (:nama, :jk, :tgl, :asal, :kamar, :hp, :angkatan)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':nama'  => $santri['nama'],
      ':jk'    => $santri['jenis_kelamin'],
      ':tgl'   => $santri['tanggal_lahir'] ?: null,
      ':asal'  => $santri['asal_daerah'],
      ':kamar' => $santri['kamar'],
      ':hp'    => $santri['no_hp_wali'],
      ':angkatan'    => $santri['angkatan'],
    ]);

    $_SESSION['pesan'] = 'santri santri berhasil ditambahkan.';
    header('Location: index.php');
    exit;
  }
}

include 'header.php';
?>

<h4 class="mb-3">Tambah Santri</h4>

<?php if ($errors): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $pesan): ?>
        <li><?= e($pesan) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

      <?php include 'form.php'; ?>

<?php include 'footer.php'; ?>