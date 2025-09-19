<?php
session_start();
include '../config.php';

// Proteksi halaman
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'operator'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil ID kendaraan dari URL
$id_kendaraan = $_GET['id'] ?? 0;
if (!$id_kendaraan) {
    header("Location: index.php");
    exit;
}

// Ambil data kendaraan
$stmt = $pdo->prepare("SELECT no_polisi FROM kendaraan WHERE id = ?");
$stmt->execute([$id_kendaraan]);
$kendaraan = $stmt->fetch();

if (!$kendaraan) {
    $_SESSION['toast'] = ['type' => 'danger', 'message' => 'Kendaraan tidak ditemukan!'];
    header("Location: index.php");
    exit;
}

// Ambil riwayat perjalanan
$stmt = $pdo->prepare("
    SELECT 
        b.tanggal,
        p.nama_pangkalan,
        p.nama_pemilik,
        s.nama_sopir,
        k2.nama_kondektur,
        b.jumlah_liter,
        b.catatan
    FROM bongkar_muatan b
    LEFT JOIN pangkalan p ON b.id_pangkalan = p.id
    LEFT JOIN sopir s ON b.id_sopir = s.id
    LEFT JOIN kondektur k2 ON b.id_kondektur = k2.id
    WHERE b.id_kendaraan = ?
    ORDER BY b.tanggal DESC
");
$stmt->execute([$id_kendaraan]);
$perjalanan_list = $stmt->fetchAll();

// Hitung statistik
$total_trip = count($perjalanan_list);
$total_liter = 0;
foreach ($perjalanan_list as $p) {
    $total_liter += $p['jumlah_liter'];
}
$total_drom = $total_liter / 200;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 Laporan Perjalanan - <?= htmlspecialchars($kendaraan['no_polisi']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-route me-2"></i> Laporan Perjalanan
            </a>
            <div class="d-flex align-items-center">
                <a href="index.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
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
                <h3><i class="fas fa-truck me-2"></i> Laporan Perjalanan: <?= htmlspecialchars($kendaraan['no_polisi']) ?></h3>
                <div class="row">
                    <div class="col-md-4">
                        <p class="text-muted mb-0"><strong>Total Trip:</strong> <?= number_format($total_trip) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-0"><strong>Total Drom:</strong> <?= number_format($total_drom, 1) ?> drom</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-0"><strong>Total Liter:</strong> <?= number_format($total_liter, 0) ?> L</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($perjalanan_list)): ?>
                    <div class="text-center py-5">
                        <div class="display-4 text-muted mb-3">
                            <i class="fas fa-history"></i>
                        </div>
                        <h4>Belum ada riwayat perjalanan</h4>
                        <p class="text-muted">Kendaraan ini belum digunakan untuk bongkar muatan.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Pangkalan</th>
                                    <th>Sopir</th>
                                    <th>Kondektur</th>
                                    <th>Jumlah</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($perjalanan_list as $p): 
                                    $drom = $p['jumlah_liter'] / 200;
                                ?>
                                    <tr>
                                        <td><?= date('d-m-Y H:i', strtotime($p['tanggal'])) ?></td>
                                        <td>
                                            <?= htmlspecialchars($p['nama_pangkalan'] ?? '-') ?><br>
                                            <small class="text-muted"><?= htmlspecialchars($p['nama_pemilik'] ?? '') ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($p['nama_sopir'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($p['nama_kondektur'] ?? '-') ?></td>
                                        <td><?= number_format($drom, 1) ?> drom</td>
                                        <td><?= !empty($p['catatan']) ? htmlspecialchars($p['catatan']) : '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
