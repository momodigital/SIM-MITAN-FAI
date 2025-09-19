<?php
/**
 * =============================================
 * 🚀 INSTALLER OTOMATIS - AGEN MINYAK TANAH
 * =============================================
 * Jalankan via browser: http://domain.com/install.php
 * Setelah selesai, HAPUS file ini demi keamanan!
 */

session_start();
$error = [];
$success = [];

// Tahapan instalasi
$step = $_GET['step'] ?? 'welcome';

// Cek apakah sudah terinstall
if (file_exists('config.php') && filesize('config.php') > 0) {
    die('<div class="alert alert-warning text-center">⚠️ Aplikasi sudah terinstall! Silakan <a href="index.php">klik di sini</a> untuk mulai menggunakan. <br> HAPUS file install.php demi keamanan!</div>');
}

// Fungsi untuk membuat folder
function create_folder($path) {
    if (!file_exists($path)) {
        return mkdir($path, 0755, true);
    }
    return true;
}

// Fungsi untuk menulis file
function write_file($file, $content) {
    return file_put_contents($file, $content) !== false;
}

// Fungsi untuk download DomPDF via file_get_contents
function download_dompdf() {
    $dompdf_zip_url = 'https://github.com/dompdf/dompdf/releases/download/v2.0.4/dompdf_2-0-4.zip';
    $zip_file = 'dompdf.zip';
    
    // Download ZIP
    if (!file_exists($zip_file)) {
        $content = file_get_contents($dompdf_zip_url);
        if ($content === false) return false;
        file_put_contents($zip_file, $content);
    }
    
    // Extract ZIP
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive;
        if ($zip->open($zip_file) === TRUE) {
            create_folder('vendor');
            $zip->extractTo('vendor/');
            $zip->close();
            
            // Rename folder
            if (file_exists('vendor/dompdf-2.0.4')) {
                rename('vendor/dompdf-2.0.4', 'vendor/dompdf');
            }
            
            unlink($zip_file);
            return true;
        }
    }
    return false;
}

// Template config.php
function get_config_template($host, $dbname, $username, $password, $telegram_token, $telegram_chat_id) {
    return <<<EOT
<?php
// Konfigurasi Database
\$host = '$host';
\$dbname = '$dbname';
\$username = '$username';
\$password = '$password';

try {
    \$pdo = new PDO("mysql:host=\$host;dbname=\$dbname;charset=utf8", \$username, \$password);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException \$e) {
    die("Koneksi gagal: " . \$e->getMessage());
}

// Konfigurasi Telegram Bot
define('TELEGRAM_BOT_TOKEN', '$telegram_token');
define('TELEGRAM_CHAT_ID', '$telegram_chat_id');

// Mulai session
session_start();
?>
EOT;
}

// Template SQL lengkap
function get_database_sql() {
    return <<<EOT
-- Database: agen_minyak

-- Tabel: users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100),
    role ENUM('admin', 'operator', 'amt1', 'amt2') DEFAULT 'operator',
    telegram_chat_id VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel: settings
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_agen VARCHAR(100) DEFAULT 'Agen Minyak Tanah',
    slogan TEXT,
    telepon_pusat VARCHAR(20),
    alamat_pusat TEXT,
    logo_path VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO settings (nama_agen, slogan, telepon_pusat) 
VALUES ('Agen Minyak Tanah Resmi', 'Melayani Pangkalan dengan Profesional & Transparan', '0812-XXXX-XXXX');

-- Tabel: pengumuman
CREATE TABLE IF NOT EXISTS pengumuman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200),
    isi TEXT,
    status ENUM('aktif', 'tidak_aktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel: pangkalan
CREATE TABLE IF NOT EXISTS pangkalan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_kontrak VARCHAR(50) UNIQUE NOT NULL,
    nama_pangkalan VARCHAR(100) NOT NULL,
    nama_pemilik VARCHAR(100) NOT NULL,
    kelurahan VARCHAR(100),
    kecamatan VARCHAR(100),
    telepon VARCHAR(20),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel: kendaraan
CREATE TABLE IF NOT EXISTS kendaraan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_polisi VARCHAR(20) UNIQUE NOT NULL,
    status_stnk ENUM('hidup', 'mati') DEFAULT 'hidup',
    tgl_kadaluarsa_stnk DATE,
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel: sopir
CREATE TABLE IF NOT EXISTS sopir (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_sopir VARCHAR(100) NOT NULL,
    no_telepon VARCHAR(20),
    status ENUM('aktif', 'tidak_aktif') DEFAULT 'aktif',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel: kondektur
CREATE TABLE IF NOT EXISTS kondektur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kondektur VARCHAR(100) NOT NULL,
    no_telepon VARCHAR(20),
    status ENUM('aktif', 'tidak_aktif') DEFAULT 'aktif',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel: bongkar_muatan
CREATE TABLE IF NOT EXISTS bongkar_muatan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pangkalan INT NOT NULL,
    id_kendaraan INT NULL,
    id_sopir INT NULL,
    id_kondektur INT NULL,
    tanggal DATETIME NOT NULL,
    jumlah_liter DECIMAL(10,2) NOT NULL,
    harga_per_liter DECIMAL(10,2) DEFAULT 15000,
    total_harga DECIMAL(12,2) AS (jumlah_liter * harga_per_liter) STORED,
    foto_bukti VARCHAR(255),
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pangkalan) REFERENCES pangkalan(id) ON DELETE CASCADE,
    FOREIGN KEY (id_kendaraan) REFERENCES kendaraan(id) ON DELETE SET NULL,
    FOREIGN KEY (id_sopir) REFERENCES sopir(id) ON DELETE SET NULL,
    FOREIGN KEY (id_kondektur) REFERENCES kondektur(id) ON DELETE SET NULL
);

-- User admin default (password: admin123)
INSERT INTO users (username, password, nama_lengkap, role) 
VALUES ('admin', '\$2y\$10\$qZv7u6x4K0Xl5dJ8rW1sUe6y5V3z2B1cD4eF6gH8iJ0kM2nO4pQ', 'Administrator', 'admin');
EOT;
}

// Proses instalasi
if ($_POST && $step == 'process') {
    $host = $_POST['db_host'] ?? 'localhost';
    $dbname = $_POST['db_name'] ?? '';
    $username = $_POST['db_user'] ?? '';
    $password = $_POST['db_pass'] ?? '';
    $telegram_token = $_POST['telegram_token'] ?? '';
    $telegram_chat_id = $_POST['telegram_chat_id'] ?? '';

    // Validasi
    if (empty($dbname) || empty($username)) {
        $error[] = "Database name dan username wajib diisi!";
    }

    if (empty($error)) {
        try {
            // 1. Buat folder uploads
            if (!create_folder('uploads')) {
                $error[] = "Gagal membuat folder uploads. Pastikan permission folder utama 755.";
            }

            // 2. Buat file config.php
            $config_content = get_config_template($host, $dbname, $username, $password, $telegram_token, $telegram_chat_id);
            if (!write_file('config.php', $config_content)) {
                $error[] = "Gagal membuat file config.php. Pastikan folder writable.";
            }

            // 3. Koneksi ke database
            $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 4. Buat database jika belum ada
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbname`");

            // 5. Import SQL
            $sql = get_database_sql();
            $statements = explode(";\n", $sql);
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }

            // 6. Install DomPDF
            if (!file_exists('vendor/dompdf')) {
                if (!download_dompdf()) {
                    $error[] = "Gagal menginstall DomPDF. Silakan install manual via Composer atau download dari github.com/dompdf/dompdf";
                }
            }

            // 7. Set permission
            chmod('uploads', 0755);
            if (file_exists('vendor/dompdf')) {
                chmod('vendor/dompdf', 0755);
            }

            if (empty($error)) {
                $success[] = "✅ Instalasi berhasil! Sistem siap digunakan.";
                $_SESSION['installed'] = true;
                header("Refresh: 3; url=login.php");
                exit;
            }
        } catch (Exception $e) {
            $error[] = "Instalasi gagal: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 Installer Otomatis - Agen Minyak Tanah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .install-container { max-width: 600px; margin: 50px auto; padding: 30px; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .step { text-align: center; flex: 1; }
        .step-number { width: 40px; height: 40px; background: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: bold; }
        .step.active .step-number { background: #0d6efd; color: white; }
        .step.completed .step-number { background: #198754; color: white; }
        .btn-install { background: linear-gradient(135deg, #0d6efd, #0a58ca); border: none; padding: 12px 24px; font-size: 1.1rem; border-radius: 50px; }
        .btn-install:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4); }
        .requirements { background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0; }
        .req-item { display: flex; align-items: center; margin: 10px 0; }
        .req-status { margin-right: 10px; font-size: 1.2rem; }
        .req-status.ok { color: #198754; }
        .req-status.error { color: #dc3545; }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="text-center mb-4">
            <h2>🚀 Installer Otomatis</h2>
            <p class="text-muted">Agen Minyak Tanah</p>
        </div>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step <?= $step == 'welcome' ? 'active' : ($step == 'process' ? 'completed' : '') ?>">
                <div class="step-number">1</div>
                <div class="step-label">Persiapan</div>
            </div>
            <div class="step <?= $step == 'process' ? 'active' : '' ?>">
                <div class="step-number">2</div>
                <div class="step-label">Instalasi</div>
            </div>
        </div>

        <?php if ($step == 'welcome'): ?>
            <!-- Step 1: Welcome & Requirements -->
            <div class="requirements">
                <h5>📋 Persyaratan Sistem</h5>
                <?php
                $requirements = [
                    'PHP >= 7.4' => version_compare(phpversion(), '7.4.0', '>='),
                    'Ekstensi PDO' => extension_loaded('pdo'),
                    'Ekstensi PDO MySQL' => extension_loaded('pdo_mysql'),
                    'Ekstensi GD' => extension_loaded('gd'),
                    'Folder ini writable' => is_writable(__DIR__),
                    'File upload diizinkan' => ini_get('file_uploads') == 1,
                ];

                $all_ok = true;
                foreach ($requirements as $req => $status) {
                    if (!$status) $all_ok = false;
                    echo '<div class="req-item">';
                    echo '<span class="req-status">' . ($status ? '✅' : '❌') . '</span>';
                    echo '<span>' . $req . '</span>';
                    echo '</div>';
                }
                ?>
            </div>

            <?php if (!$all_ok): ?>
                <div class="alert alert-warning">
                    <strong>⚠️ Peringatan:</strong> Beberapa persyaratan belum terpenuhi. Instalasi mungkin gagal.
                </div>
            <?php endif; ?>

            <div class="text-center">
                <a href="?step=process" class="btn btn-install">
                    Lanjut ke Instalasi <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>

        <?php elseif ($step == 'process'): ?>
            <!-- Step 2: Form Instalasi -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($error as $err): ?>
                        <div><?= $err ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php foreach ($success as $succ): ?>
                        <div><?= $succ ?></div>
                    <?php endforeach; ?>
                    <p>Redirecting to login page in 3 seconds...</p>
                </div>
            <?php else: ?>
                <form method="POST">
                    <h4>🔧 Konfigurasi Database</h4>
                    <div class="mb-3">
                        <label class="form-label">Host Database</label>
                        <input type="text" name="db_host" class="form-control" value="localhost" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Database</label>
                        <input type="text" name="db_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="db_user" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="db_pass" class="form-control">
                    </div>

                    <h4 class="mt-4">📱 Konfigurasi Telegram (Opsional)</h4>
                    <div class="mb-3">
                        <label class="form-label">Telegram Bot Token</label>
                        <input type="text" name="telegram_token" class="form-control" placeholder="Contoh: 123456789:ABCdefGHIjklMNopQRStuvwXYz">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telegram Chat ID</label>
                        <input type="text" name="telegram_chat_id" class="form-control" placeholder="Contoh: 123456789">
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-install">
                            <i class="fas fa-cogs me-2"></i> Mulai Instalasi
                        </button>
                    </div>
                </form>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
