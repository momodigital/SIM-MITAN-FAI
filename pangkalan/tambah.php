<?php
session_start();
include '../config.php';

// Proteksi halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$sukses = '';
$error = '';

if ($_POST) {
    $no_kontrak = trim($_POST['no_kontrak']);
    $nama_pangkalan = trim($_POST['nama_pangkalan']);
    $nama_pemilik = trim($_POST['nama_pemilik']);
    $kelurahan = trim($_POST['kelurahan']);
    $kecamatan = trim($_POST['kecamatan']);
    $telepon = trim($_POST['telepon']);
    $latitude = !empty($_POST['latitude']) ? trim($_POST['latitude']) : null;
    $longitude = !empty($_POST['longitude']) ? trim($_POST['longitude']) : null;
    $catatan = trim($_POST['catatan']);

    try {
        // Validasi no_kontrak unik
        $stmt = $pdo->prepare("SELECT id FROM pangkalan WHERE no_kontrak = ?");
        $stmt->execute([$no_kontrak]);
        if ($stmt->fetch()) {
            throw new Exception("Nomor kontrak sudah digunakan!");
        }

        $stmt = $pdo->prepare("
            INSERT INTO pangkalan 
            (no_kontrak, nama_pangkalan, nama_pemilik, kelurahan, kecamatan, telepon, latitude, longitude, catatan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $no_kontrak,
            $nama_pangkalan,
            $nama_pemilik,
            $kelurahan,
            $kecamatan,
            $telepon,
            $latitude,
            $longitude,
            $catatan
        ]);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Pangkalan berhasil ditambahkan!'];
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        $error = "❌ Gagal menambahkan pangkalan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>➕ Tambah Pangkalan Baru - Agen Minyak Tanah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-plus-circle me-2"></i> Tambah Pangkalan
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
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-store me-2"></i> Tambah Pangkalan Baru</h3>
                        <p class="text-muted mb-0">Isi data pangkalan dengan lengkap</p>
                    </div>
                    <div class="card-body">
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-file-contract me-2"></i> No. Kontrak Pangkalan <span class="text-danger">*</span></label>
                                <input type="text" name="no_kontrak" class="form-control form-control-lg" 
                                       placeholder="Contoh: PK-2024-001" required>
                                <div class="invalid-feedback">Nomor kontrak wajib diisi dan harus unik</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-store me-2"></i> Nama Pangkalan <span class="text-danger">*</span></label>
                                <input type="text" name="nama_pangkalan" class="form-control form-control-lg" 
                                       placeholder="Contoh: Pangkalan Sejahtera" required>
                                <div class="invalid-feedback">Nama pangkalan wajib diisi</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-user me-2"></i> Nama Pemilik <span class="text-danger">*</span></label>
                                <input type="text" name="nama_pemilik" class="form-control" 
                                       placeholder="Contoh: Budi Santoso" required>
                                <div class="invalid-feedback">Nama pemilik wajib diisi</div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label"><i class="fas fa-map-marker-alt me-2"></i> Kelurahan</label>
                                    <input type="text" name="kelurahan" class="form-control" 
                                           placeholder="Contoh: Kebon Jeruk">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><i class="fas fa-map-marker-alt me-2"></i> Kecamatan</label>
                                    <input type="text" name="kecamatan" class="form-control" 
                                           placeholder="Contoh: Kebon Jeruk">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-phone me-2"></i> Nomor Handphone</label>
                                <input type="text" name="telepon" class="form-control" 
                                       placeholder="Contoh: 081234567890">
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label"><i class="fas fa-globe me-2"></i> Latitude (Koordinat GPS)</label>
                                    <input type="text" name="latitude" class="form-control" 
                                           placeholder="Contoh: -6.2088">
                                    <div class="form-text">
                                        <a href="https://www.google.com/maps" target="_blank" class="text-decoration-none">
                                            <i class="fas fa-info-circle me-1"></i> Cara dapatkan koordinat
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><i class="fas fa-globe me-2"></i> Longitude (Koordinat GPS)</label>
                                    <input type="text" name="longitude" class="form-control" 
                                           placeholder="Contoh: 106.8456">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-sticky-note me-2"></i> Catatan</label>
                                <textarea name="catatan" class="form-control" rows="3" 
                                          placeholder="Contoh: Langganan tetap, bayar tepat waktu"></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i> Simpan Pangkalan
                                </button>
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Validasi form Bootstrap
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
