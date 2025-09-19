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
    $nama_sopir = trim($_POST['nama_sopir']);
    $no_telepon = trim($_POST['no_telepon']);
    $status = $_POST['status'];
    $catatan = trim($_POST['catatan']);

    try {
        if (empty($nama_sopir)) {
            throw new Exception("Nama sopir wajib diisi!");
        }

        $stmt = $pdo->prepare("
            INSERT INTO sopir 
            (nama_sopir, no_telepon, status, catatan)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $nama_sopir,
            $no_telepon,
            $status,
            $catatan
        ]);

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Sopir berhasil ditambahkan!'];
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        $error = "❌ Gagal menambahkan sopir: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>➕ Tambah Sopir Baru - Agen Minyak Tanah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-user-plus me-2"></i> Tambah Sopir
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
                        <h3><i class="fas fa-user-tie me-2"></i> Tambah Sopir Baru</h3>
                        <p class="text-muted mb-0">Isi data sopir dengan lengkap</p>
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
                                <label class="form-label"><i class="fas fa-user me-2"></i> Nama Sopir <span class="text-danger">*</span></label>
                                <input type="text" name="nama_sopir" class="form-control form-control-lg" 
                                       placeholder="Contoh: Budi Santoso" required>
                                <div class="invalid-feedback">Nama sopir wajib diisi</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-phone me-2"></i> Nomor Telepon</label>
                                <input type="text" name="no_telepon" class="form-control" 
                                       placeholder="Contoh: 081234567890">
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-toggle-on me-2"></i> Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select form-control-lg" required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="aktif">Aktif</option>
                                    <option value="tidak_aktif">Tidak Aktif</option>
                                </select>
                                <div class="invalid-feedback">Pilih status sopir</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-sticky-note me-2"></i> Catatan</label>
                                <textarea name="catatan" class="form-control" rows="3" 
                                          placeholder="Contoh: Sering telat, tapi jujur dan bertanggung jawab"></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i> Simpan Sopir
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
