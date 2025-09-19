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

// Ambil data master untuk dropdown
$stmt_pangkalan = $pdo->query("SELECT id, no_kontrak, nama_pangkalan, nama_pemilik FROM pangkalan ORDER BY nama_pangkalan ASC");
$pangkalan_list = $stmt_pangkalan->fetchAll();

$stmt_kendaraan = $pdo->query("SELECT id, no_polisi FROM kendaraan WHERE status = 'aktif' ORDER BY no_polisi ASC");
$kendaraan_list = $stmt_kendaraan->fetchAll();

$stmt_sopir = $pdo->query("SELECT id, nama_sopir FROM sopir WHERE status = 'aktif' ORDER BY nama_sopir ASC");
$sopir_list = $stmt_sopir->fetchAll();

$stmt_kondektur = $pdo->query("SELECT id, nama_kondektur FROM kondektur WHERE status = 'aktif' ORDER BY nama_kondektur ASC");
$kondektur_list = $stmt_kondektur->fetchAll();

if ($_POST) {
    try {
        // Ambil data dari form
        $tanggal_bongkar = $_POST['tanggal_bongkar'];
        $id_kendaraan = $_POST['id_kendaraan'];
        $id_pangkalan = $_POST['id_pangkalan'];
        $id_sopir = $_POST['id_sopir'];
        $id_kondektur = $_POST['id_kondektur'];
        $jumlah_drom = floatval($_POST['jumlah_drom']);
        $jumlah_liter = $jumlah_drom * 200;
        $harga_per_liter = 15000; // Bisa disesuaikan atau ambil dari setting
        $total_harga = $jumlah_liter * $harga_per_liter;
        $catatan = $_POST['catatan'] ?? '';

        // Validasi dasar
        if (!$tanggal_bongkar || !$id_kendaraan || !$id_pangkalan || !$id_sopir || $jumlah_drom <= 0) {
            throw new Exception("Semua field wajib diisi dengan benar!");
        }

        // Handle upload foto
        $foto_bukti = '';
        if ($_FILES['foto_bukti']['error'] == 0) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $file_ext = strtolower(pathinfo($_FILES['foto_bukti']['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_ext, $allowed_types)) {
                $foto_bukti = "bongkar_" . date('Ymd_His') . "_" . rand(100, 999) . "." . $file_ext;
                $upload_path = "../uploads/" . $foto_bukti;
                
                if (!move_uploaded_file($_FILES['foto_bukti']['tmp_name'], $upload_path)) {
                    throw new Exception("Gagal mengupload foto bukti.");
                }
            } else {
                throw new Exception("Format file tidak didukung. Gunakan JPG, PNG, atau GIF.");
            }
        }

        // Simpan ke database
        $stmt = $pdo->prepare("
            INSERT INTO bongkar_muatan 
            (id_pangkalan, id_kendaraan, id_sopir, id_kondektur, tanggal, jumlah_liter, harga_per_liter, total_harga, foto_bukti, catatan, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $id_pangkalan,
            $id_kendaraan,
            $id_sopir,
            $id_kondektur,
            $tanggal_bongkar,
            $jumlah_liter,
            $harga_per_liter,
            $total_harga,
            $foto_bukti,
            $catatan
        ]);

        // Ambil nama pangkalan untuk notifikasi
        $stmt_p = $pdo->prepare("SELECT nama_pangkalan FROM pangkalan WHERE id = ?");
        $stmt_p->execute([$id_pangkalan]);
        $nama_pangkalan = $stmt_p->fetch()['nama_pangkalan'];

        // Kirim notifikasi Telegram
        $drom = number_format($jumlah_drom, 1);
        $pesan = "✅ [INPUT ADMIN]\n";
        $pesan .= "Transaksi baru dicatat oleh admin\n";
        $pesan .= "Pangkalan: $nama_pangkalan\n";
        $pesan .= "Jumlah: $drom drom (" . number_format($jumlah_liter, 0) . " L)\n";
        $pesan .= "Jam: " . date('H:i', strtotime($tanggal_bongkar)) . "\n";
        if (!empty($foto_bukti)) {
            $pesan .= "Foto: tersimpan ✅";
        }

        include '../notifikasi/kirim_telegram.php';
        kirim_telegram("MASUKKAN_CHAT_ID_ADMIN_ANDA", $pesan); // Ganti dengan chat_id admin/grup

        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Transaksi berhasil disimpan! Notifikasi telah dikirim.'
        ];
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        $error = "❌ Gagal menyimpan transaksi: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>➕ Tambah Transaksi Bongkar - Agen Minyak Tanah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-gas-pump me-2"></i> Tambah Transaksi
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
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-gas-pump me-2"></i> Tambah Transaksi Bongkar</h3>
                        <p class="text-muted mb-0">Isi data transaksi bongkar dengan lengkap</p>
                    </div>
                    <div class="card-body">
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-calendar-alt me-2"></i> Tanggal & Jam Bongkar <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="tanggal_bongkar" class="form-control form-control-lg" required>
                                <div class="invalid-feedback">Tanggal dan jam wajib diisi</div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label"><i class="fas fa-truck me-2"></i> Kendaraan <span class="text-danger">*</span></label>
                                    <select name="id_kendaraan" class="form-select form-control-lg" required>
                                        <option value="">-- Pilih Kendaraan --</option>
                                        <?php foreach ($kendaraan_list as $k): ?>
                                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['no_polisi']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Pilih kendaraan yang digunakan</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><i class="fas fa-store me-2"></i> Pangkalan Tujuan <span class="text-danger">*</span></label>
                                    <select name="id_pangkalan" class="form-select form-control-lg" required>
                                        <option value="">-- Pilih Pangkalan --</option>
                                        <?php foreach ($pangkalan_list as $p): ?>
                                            <option value="<?= $p['id'] ?>">
                                                [<?= htmlspecialchars($p['no_kontrak']) ?>] <?= htmlspecialchars($p['nama_pangkalan']) ?> - <?= htmlspecialchars($p['nama_pemilik']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Pilih pangkalan tujuan</div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label"><i class="fas fa-user-tie me-2"></i> Sopir (AMT1) <span class="text-danger">*</span></label>
                                    <select name="id_sopir" class="form-select form-control-lg" required>
                                        <option value="">-- Pilih Sopir --</option>
                                        <?php foreach ($sopir_list as $s): ?>
                                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama_sopir']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Pilih sopir yang bertugas</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><i class="fas fa-user me-2"></i> Kondektur (AMT2)</label>
                                    <select name="id_kondektur" class="form-select form-control-lg">
                                        <option value="">-- Pilih Kondektur --</option>
                                        <?php foreach ($kondektur_list as $k): ?>
                                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kondektur']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-weight me-2"></i> Jumlah Drom <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.5" name="jumlah_drom" class="form-control form-control-lg" 
                                           placeholder="Contoh: 2.5" min="0.5" required>
                                    <span class="input-group-text">drom</span>
                                </div>
                                <div class="form-text">1 drom = 200 liter</div>
                                <div class="invalid-feedback">Jumlah drom wajib diisi (min 0.5)</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-image me-2"></i> Foto Bukti Bongkar (Opsional)</label>
                                <input type="file" name="foto_bukti" class="form-control" accept="image/*">
                                <div class="form-text">Ambil foto struk/tanda terima/penerima</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="fas fa-sticky-note me-2"></i> Catatan</label>
                                <textarea name="catatan" class="form-control" rows="3" 
                                          placeholder="Contoh: Bayar tunai, jeriken rusak, penerima Ibu Siti"></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i> SIMPAN TRANSAKSI
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

        // Set default datetime to now
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            
            const datetimeLocal = `${year}-${month}-${day}T${hours}:${minutes}`;
            document.querySelector('[name="tanggal_bongkar"]').value = datetimeLocal;
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
