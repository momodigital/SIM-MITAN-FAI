<?php
session_start();
include '../config.php';

// Proteksi halaman
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'operator'])) {
    header("Location: ../login.php");
    exit;
}

$transaksi = [];
$periode = '';

if ($_POST) {
    $tgl_awal = $_POST['tanggal_awal'];
    $tgl_akhir = $_POST['tanggal_akhir'];
    $periode = "Periode: " . date('d-m-Y', strtotime($tgl_awal)) . " s/d " . date('d-m-Y', strtotime($tgl_akhir));

    $stmt = $pdo->prepare("
        SELECT 
            b.tanggal,
            k.no_polisi,
            s.nama_sopir,
            b.jumlah_liter,
            p.nama_pangkalan,
            p.nama_pemilik
        FROM bongkar_muatan b
        JOIN kendaraan k ON b.id_kendaraan = k.id
        JOIN sopir s ON b.id_sopir = s.id
        JOIN pangkalan p ON b.id_pangkalan = p.id
        WHERE DATE(b.tanggal) BETWEEN ? AND ?
        ORDER BY b.tanggal ASC
    ");
    $stmt->execute([$tgl_awal, $tgl_akhir]);
    $transaksi = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 Laporan Bongkar Muatan - Agen Minyak Tanah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../admin/dashboard/index.php">
                <i class="fas fa-file-alt me-2"></i> Laporan Bongkar
            </a>
            <div class="d-flex align-items-center">
                <a href="../admin/dashboard/index.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-arrow-left me-1"></i> Dashboard
                </a>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-file-invoice me-2"></i> Laporan Bongkar Muatan</h3>
                <p class="text-muted mb-0">Filter laporan berdasarkan periode</p>
            </div>
            <div class="card-body">
                <form method="POST" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label"><i class="fas fa-calendar-alt me-2"></i> Tanggal Awal</label>
                            <input type="date" name="tanggal_awal" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="fas fa-calendar-alt me-2"></i> Tanggal Akhir</label>
                            <input type="date" name="tanggal_akhir" class="form-control" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i> Tampilkan Laporan
                            </button>
                        </div>
                    </div>
                </form>

                <?php if (!empty($transaksi)): ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5><?= $periode ?></h5>
                        <a href="export_bongkar_pdf.php?awal=<?= $_POST['tanggal_awal'] ?>&akhir=<?= $_POST['tanggal_akhir'] ?>" 
                           class="btn btn-danger" target="_blank">
                            <i class="fas fa-file-pdf me-2"></i> Export PDF
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Plat Kendaraan</th>
                                    <th>Sopir (AMT1)</th>
                                    <th>Jumlah (Drom)</th>
                                    <th>Pangkalan (Pemilik)</th>
                                    <th style="width: 150px;">Tanda Tangan Pemilik</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transaksi as $t): 
                                    $drom = $t['jumlah_liter'] / 200;
                                ?>
                                    <tr>
                                        <td><?= date('d-m-Y H:i', strtotime($t['tanggal'])) ?></td>
                                        <td><?= htmlspecialchars($t['no_polisi']) ?></td>
                                        <td><?= htmlspecialchars($t['nama_sopir']) ?></td>
                                        <td><?= number_format($drom, 1) ?> drom (<?= number_format($t['jumlah_liter'], 0) ?> L)</td>
                                        <td><?= htmlspecialchars($t['nama_pangkalan']) ?> (<?= htmlspecialchars($t['nama_pemilik']) ?>)</td>
                                        <td style="height: 50px;">&nbsp;</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif ($_POST): ?>
                    <div class="alert alert-warning alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Tidak ada data transaksi di periode ini.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
