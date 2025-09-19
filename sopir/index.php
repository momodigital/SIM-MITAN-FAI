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
    $stmt = $pdo->prepare("DELETE FROM sopir WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Sopir berhasil dihapus!'];
    header("Location: index.php");
    exit;
}

// Ambil semua sopir
$stmt = $pdo->query("
    SELECT 
        id,
        nama_sopir,
        no_telepon,
        status,
        catatan,
        created_at
    FROM sopir 
    ORDER BY nama_sopir ASC
");
$sopir_list = $stmt->fetchAll();

// Hitung total trip per sopir
$total_trip = [];
foreach ($sopir_list as $s) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bongkar_muatan WHERE id_sopir = ?");
    $stmt->execute([$s['id']]);
    $total_trip[$s['id']] = $stmt->fetch()['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>👨‍✈️ Manajemen Sopir - Agen Minyak Tanah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../admin/dashboard/index.php">
                <i class="fas fa-user-tie me-2"></i> Manajemen Sopir
            </a>
            <div class="d-flex align-items-center">
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <a href="tambah.php" class="btn btn-outline-light btn-sm me-2">
                        <i class="fas fa-plus me-1"></i> Tambah Sopir
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
                <h3><i class="fas fa-list me-2"></i> Daftar Sopir</h3>
                <p class="text-muted mb-0">Total: <?= number_format(count($sopir_list)) ?> sopir terdaftar</p>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['toast'])): ?>
                    <div class="alert alert-<?= $_SESSION['toast']['type'] ?> alert-dismissible fade show">
                        <?= $_SESSION['toast']['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['toast']); ?>
                <?php endif; ?>

                <?php if (empty($sopir_list)): ?>
                    <div class="text-center py-5">
                        <div class="display-4 text-muted mb-3">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h4>Belum ada sopir terdaftar</h4>
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                            <p class="text-muted">Silakan tambah sopir baru.</p>
                            <a href="tambah.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i> Tambah Sopir
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nama Sopir</th>
                                    <th>Telepon</th>
                                    <th>Status</th>
                                    <th>Total Trip</th>
                                    <th>Catatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sopir_list as $index => $s): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><strong><?= htmlspecialchars($s['nama_sopir']) ?></strong></td>
                                        <td>
                                            <?php if (!empty($s['no_telepon'])): ?>
                                                <a href="tel:<?= $s['no_telepon'] ?>" class="text-decoration-none">
                                                    <i class="fas fa-phone me-1"></i> <?= htmlspecialchars($s['no_telepon']) ?>
                                                </a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($s['status'] == 'aktif'): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-white">
                                                <?= number_format($total_trip[$s['id']]) ?> trip
                                            </span>
                                        </td>
                                        <td>
                                            <?= !empty($s['catatan']) ? htmlspecialchars($s['catatan']) : '-' ?>
                                        </td>
                                        <td>
                                            <a href="edit.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($_SESSION['role'] == 'admin'): ?>
                                                <a href="?delete=<?= $s['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Yakin ingin menghapus sopir ini?')">
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
