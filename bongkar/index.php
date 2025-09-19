<?php
session_start();
include '../config.php';

// Proteksi halaman
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'operator'])) {
    header("Location: ../login.php");
    exit;
}

// Handle delete (hanya admin)
if (isset($_GET['delete']) && $_SESSION['role'] == 'admin') {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM bongkar_muatan WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Transaksi berhasil dihapus!'];
    header("Location: index.php");
    exit;
}

// Ambil semua transaksi bongkar
$stmt = $pdo->prepare("
    SELECT 
        b.id,
        b.tanggal,
        b.jumlah_liter,
        b.total_harga,
        b.foto_bukti,
        b.catatan,
        p.nama_pangkalan,
        p.nama_pemilik,
        k.no_polisi,
        s.nama_sopir,
        k2.nama_kondektur
    FROM bongkar_muatan b
    LEFT JOIN pangkalan p ON b.id_pangkalan = p.id
    LEFT JOIN kendaraan k ON b.id_kendaraan = k.id
    LEFT JOIN sopir s ON b.id_sopir = s.id
    LEFT JOIN kondektur k2 ON b.id_kondektur = k2.id
    ORDER BY b.tanggal DESC
");
$stmt->execute();
$transaksi_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📋 Transaksi Bongkar Muatan - Agen Minyak Tanah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../admin/dashboard/index.php">
                <i class="fas fa-gas-pump me-2"></i> Transaksi Bongkar
            </a>
            <div class="d-flex align-items-center">
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <a href="tambah.php" class="btn btn-outline-light btn-sm me-2">
                        <i class="fas fa-plus me-1"></i> Tambah Transaksi
                    </a>
                <?php endif; ?>
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
                <h3><i class="fas fa-list me-2"></i> Daftar Transaksi Bongkar</h3>
                <p class="text-muted mb-0">Total: <?= number_format(count($transaksi_list)) ?> transaksi tercatat</p>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['toast'])): ?>
                    <div class="alert alert-<?= $_SESSION['toast']['type'] ?> alert-dismissible fade show">
                        <?= $_SESSION['toast']['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['toast']); ?>
                <?php endif; ?>

                <?php if (empty($transaksi_list)): ?>
                    <div class="text-center py-5">
                        <div class="display-4 text-muted mb-3">
                            <i class="fas fa-gas-pump"></i>
                        </div>
                        <h4>Belum ada transaksi tercatat</h4>
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                            <p class="text-muted">Silakan tambah transaksi baru.</p>
                            <a href="tambah.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i> Tambah Transaksi
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Pangkalan</th>
                                    <th>Kendaraan</th>
                                    <th>Sopir</th>
                                    <th>Kondektur</th>
                                    <th>Jumlah</th>
                                    <th>Total</th>
                                    <th>Bukti</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transaksi_list as $t): 
                                    $drom = $t['jumlah_liter'] / 200;
                                ?>
                                    <tr>
                                        <td><?= date('d-m-Y H:i', strtotime($t['tanggal'])) ?></td>
                                        <td>
                                            <?= htmlspecialchars($t['nama_pangkalan'] ?? '-') ?><br>
                                            <small class="text-muted"><?= htmlspecialchars($t['nama_pemilik'] ?? '') ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($t['no_polisi'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($t['nama_sopir'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($t['nama_kondektur'] ?? '-') ?></td>
                                        <td><?= number_format($drom, 1) ?> drom</td>
                                        <td><?= number_format($t['total_harga'], 0, ',', '.') ?></td>
                                        <td>
                                            <?php if (!empty($t['foto_bukti']) && file_exists('../uploads/' . $t['foto_bukti'])): ?>
                                                <a href="../uploads/<?= $t['foto_bukti'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-image"></i>
                                                </a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($_SESSION['role'] == 'admin'): ?>
                                                <a href="?delete=<?= $t['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Yakin ingin menghapus transaksi ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
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
