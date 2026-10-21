<?php
require 'koneksi.php';


$limit = 10;


$halaman_aktif = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_aktif = ($halaman_aktif > 0) ? $halaman_aktif : 1;


$offset = ($halaman_aktif - 1) * $limit;


$cari = trim($_GET['cari'] ?? '');
$kamar_filter = $_GET['kamar'] ?? '';


if ($cari !== '') {
    $stmt_total = $pdo->prepare('SELECT COUNT(*) FROM santri WHERE nama LIKE :cari');
    $stmt_total->execute([':cari' => '%' . $cari . '%']);
} elseif ($kamar_filter !== '') {
    $stmt_total = $pdo->prepare('SELECT COUNT(*) FROM santri WHERE kamar = :kamar');
    $stmt_total->execute([':kamar' => $kamar_filter]);
} else {
    $stmt_total = $pdo->query('SELECT COUNT(*) FROM santri');
}
$total_data = $stmt_total->fetchColumn();
$total_halaman = ceil($total_data / $limit);


if ($cari !== '') {
    $stmt = $pdo->prepare('SELECT * FROM santri WHERE nama LIKE :cari ORDER BY id DESC LIMIT :limit OFFSET :offset');
    $stmt->bindValue(':cari', '%' . $cari . '%', PDO::PARAM_STR);
} elseif ($kamar_filter !== '') {
    $stmt = $pdo->prepare('SELECT * FROM santri WHERE kamar = :kamar ORDER BY id DESC LIMIT :limit OFFSET :offset');
    $stmt->bindValue(':kamar', $kamar_filter, PDO::PARAM_STR);
} else {
    $stmt = $pdo->prepare('SELECT * FROM santri ORDER BY id DESC LIMIT :limit OFFSET :offset');
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$daftar = $stmt->fetchAll();

include 'header.php';
?>
<!-- 
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Daftar Santri</h4>
    <a href="tambah.php" class="btn btn-success">+ Tambah Santri</a>
</div> -->

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Daftar Santri</h4>
    <div>
        <a href="file_csv.php?cari=<?= urlencode($cari); ?>&kamar=<?= urlencode($kamar_filter); ?>" class="btn btn-outline-success me-2">
            <i class="bi bi-file-earmark-excel"></i> Unduh CSV
        </a>
        <a href="tambah.php" class="btn btn-success">+ Tambah Santri</a>
    </div>
</div>

<form method="get" class="row g-2 mb-3">
    <div class="col-sm-8 col-md-6">
        <input type="text" name="cari" class="form-control"
            placeholder="Cari nama santri..." value="<?= e($cari) ?>">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-success">Cari</button>
        <a href="index.php" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>

<p class="text-muted">Menampilkan <?= count($daftar) ?> santri.</p>

<form method="get" class="row g-2 mb-3">
 
    <div class="col-sm-5">
        <input type="text" name="cari" class="form-control" 
               placeholder="Cari nama santri..." value="<?= htmlspecialchars($cari); ?>">
    </div>

  
    <div class="col-sm-3">
        <select name="kamar" class="form-control">
            <option value="">-- Semua Kamar --</option>
            <option value="A1" <?= ($kamar_filter == 'A1') ? 'selected' : ''; ?>>A1</option>
            <option value="A2" <?= ($kamar_filter == 'A2') ? 'selected' : ''; ?>>A2</option>
            <option value="A3" <?= ($kamar_filter == 'A3') ? 'selected' : ''; ?>>A3</option>
            <option value="B1" <?= ($kamar_filter == 'B1') ? 'selected' : ''; ?>>B1</option>
            <option value="B2" <?= ($kamar_filter == 'B2') ? 'selected' : ''; ?>>B2</option>
            <option value="B3" <?= ($kamar_filter == 'B3') ? 'selected' : ''; ?>>B3</option>
            <option value="3C" <?= ($kamar_filter == '3C') ? 'selected' : ''; ?>>3C</option>
       
        </select>
    </div>

  
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-success">Filter / Cari</button>
        <a href="index.php" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>


<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-success">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>L/P</th>
                    <th>Umur</th>
                    <th>Asal Daerah</th>
                    <th>Kamar</th>
                    <th>Angkatan</th>
                    <th>No. HP Wali</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($daftar) === 0): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada data santri.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($daftar as $no => $s): ?>
                    <tr>
                        <td><?= $no + 1 ?></td>
                        <td><?= e($s['nama']) ?></td>
                        <td>
                            <?php
                            if ($s['jenis_kelamin'] == 'L') {
                                echo 'Laki-laki';
                            } else {
                                echo 'Perempuan';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $tanggalLahir = new DateTime($s['tanggal_lahir']);
                            $sekarang = new DateTime('today');
                            $umur = $tanggalLahir->diff($sekarang)->y;

                            echo " ". $umur . " thn";
                            ?>
                        </td>
                        <td><?= e($s['asal_daerah']) ?></td>
                        <td><?= e($s['kamar']) ?></td>
                        <td><?= e($s['angkatan']) ?></td>
                        <td><?= e($s['no_hp_wali']) ?></td>
                        <td>
                            <a href="edit.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            
                            <button type="button" class="btn btn-sm btn-danger"
                                data-bs-toggle="modal" data-bs-target="#modalHapus"
                                data-id="<?= $s['id'] ?>" data-nama="<?= e($s['nama']) ?>">Hapus</button>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php if ($total_halaman > 1) : ?>
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= ($halaman_aktif <= 1) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?halaman=<?= $halaman_aktif - 1; ?>&cari=<?= urlencode($cari); ?>&kamar=<?= urlencode($kamar_filter); ?>">Sebelumnya</a>
        </li>
        <?php for ($i = 1; $i <= $total_halaman; $i++) : ?>
            <li class="page-item <?= ($i == $halaman_aktif) ? 'active' : ''; ?>">
                <a class="page-link" href="?halaman=<?= $i; ?>&cari=<?= urlencode($cari); ?>&kamar=<?= urlencode($kamar_filter); ?>"><?= $i; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= ($halaman_aktif >= $total_halaman) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?halaman=<?= $halaman_aktif + 1; ?>&cari=<?= urlencode($cari); ?>&kamar=<?= urlencode($kamar_filter); ?>">Selanjutnya</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
<div class="modal fade" id="modalHapus" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="hapus.php" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Yakin ingin menghapus data <strong id="namaHapus"></strong>?
                <input type="hidden" name="id" id="idHapus">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('modalHapus').addEventListener('show.bs.modal', function(event) {
        var tombol = event.relatedTarget;
        document.getElementById('idHapus').value = tombol.getAttribute('data-id');
        document.getElementById('namaHapus').textContent = tombol.getAttribute('data-nama');
    });
</script>
<?php include 'footer.php'; ?>