-- Buat database (opsional)
-- CREATE DATABASE agen_minyak CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tabel: users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100),
    role ENUM('admin', 'operator', 'amt1', 'amt2') DEFAULT 'operator',
    telegram_chat_id VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel: settings
CREATE TABLE settings (
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
CREATE TABLE pengumuman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200),
    isi TEXT,
    status ENUM('aktif', 'tidak_aktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel: pangkalan
CREATE TABLE pangkalan (
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
CREATE TABLE kendaraan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_polisi VARCHAR(20) UNIQUE NOT NULL,
    status_stnk ENUM('hidup', 'mati') DEFAULT 'hidup',
    tgl_kadaluarsa_stnk DATE,
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel: sopir
CREATE TABLE sopir (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_sopir VARCHAR(100) NOT NULL,
    no_telepon VARCHAR(20),
    status ENUM('aktif', 'tidak_aktif') DEFAULT 'aktif',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel: kondektur
CREATE TABLE kondektur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kondektur VARCHAR(100) NOT NULL,
    no_telepon VARCHAR(20),
    status ENUM('aktif', 'tidak_aktif') DEFAULT 'aktif',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel: bongkar_muatan
CREATE TABLE bongkar_muatan (
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
