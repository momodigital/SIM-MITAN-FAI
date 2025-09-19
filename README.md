# 🛢️ AgenMita — Sistem Manajemen Agen Minyak Tanah

> **Aplikasi web modern untuk mencatat transaksi bongkar muatan, manajemen pangkalan, kendaraan, sopir, kondektur, dan laporan — dengan antarmuka responsif, notifikasi Telegram otomatis, dan laporan PDF siap cetak.**

Dibangun untuk membantu agen minyak tanah dalam mengelola operasional harian secara digital, transparan, dan efisien.

---

## ✨ Fitur Utama

- ✅ **Input Transaksi Real-Time** — oleh AMT (sopir/kondektur) langsung dari lokasi pangkalan via HP
- 📸 **Upload Foto Bukti** — langsung dari kamera HP
- 🏪 **Manajemen Pangkalan** — data lengkap: no. kontrak, nama, pemilik, kelurahan, kecamatan, koordinat GPS
- 🚚 **Manajemen Kendaraan** — plat nomor, status STNK, tanggal kadaluarsa, riwayat perjalanan
- 👨‍✈️ **Manajemen SDM** — sopir (AMT1) & kondektur (AMT2)
- 📄 **Laporan PDF** — format siap cetak dengan kolom tanda tangan pemilik pangkalan
- 📱 **Peta Publik Interaktif** — tampilkan semua lokasi pangkalan di peta (Leaflet.js)
- 🤖 **Notifikasi Telegram Otomatis** — saat ada transaksi baru, STNK hampir kadaluarsa, laporan mingguan
- 📊 **Dashboard Admin Modern** — statistik real-time, shortcut cepat
- 🎛️ **Pengaturan Dinamis** — ubah nama agen, logo, slogan, telepon dari dashboard admin
- 📅 **Cron Job Otomatis** — notifikasi STNK & laporan mingguan terjadwal

---

## 📁 Struktur Folder

```
/agen-minyak-tanah/
│
├── 📄 index.php                     → Halaman depan publik
├── 📄 login.php                     → Login multi-role (admin, amt)
├── 📄 logout.php                    → Logout
├── 📄 config.php                    → Koneksi database + konfigurasi
├── 📄 database.sql                  → SQL lengkap semua tabel
├── 📄 peta-pangkalan.php            → Peta publik semua pangkalan
│
├── 📁 assets/
│   ├── 📁 css/
│   │   └── 📄 style.css             → Custom styling modern
│   └── 📁 js/
│       └── 📄 main.js               → JavaScript pendukung
│
├── 📁 uploads/                      → Folder upload foto & logo (chmod 755)
│   └── 📄 .gitignore                → Jangan upload file upload
│
├── 📁 admin/
│   ├── 📁 dashboard/                → Dashboard admin modern
│   │   └── 📄 index.php
│   └── 📁 settings/                 → Kelola pengaturan situs & logo
│       └── 📄 index.php
│
├── 📁 amt/                          → Input transaksi oleh AMT
│   ├── 📄 input_bongkar.php
│   └── 📄 sukses.php
│
├── 📁 pangkalan/                    → Manajemen pangkalan
│   ├── 📄 index.php
│   ├── 📄 tambah.php
│   └── 📄 edit.php
│
├── 📁 kendaraan/                    → Manajemen kendaraan
│   ├── 📄 index.php
│   ├── 📄 tambah.php
│   └── 📄 laporan_perjalanan.php
│
├── 📁 sopir/                        → Manajemen sopir (AMT1)
│   ├── 📄 index.php
│   └── 📄 tambah.php
│
├── 📁 kondektur/                    → Manajemen kondektur (AMT2)
│   ├── 📄 index.php
│   └── 📄 tambah.php
│
├── 📁 bongkar/                      → Transaksi bongkar (admin/operator)
│   ├── 📄 index.php
│   └── 📄 tambah.php
│
├── 📁 laporan/                      → Laporan & export PDF
│   ├── 📄 bongkar.php
│   └── 📄 export_bongkar_pdf.php
│
├── 📁 notifikasi/                   → Fungsi notifikasi Telegram
│   └── 📄 kirim_telegram.php
│
├── 📁 cron/                         → Cron job otomatis
│   ├── 📄 notifikasi_stnk.php
│   ├── 📄 laporan_mingguan.php
│   └── 📄 test_notifikasi.php      → Untuk testing
│
├── 📄 .gitignore
└── 📄 README.md                     → File ini
```

---

## ⚙️ Cara Instalasi

### 1. **Persyaratan Sistem**
- PHP 7.4 atau lebih tinggi
- MySQL 5.7+
- Ekstensi PHP: PDO, GD (untuk upload gambar)
- Composer (opsional, untuk DomPDF)

### 2. **Langkah Instalasi**

#### a. Clone atau Upload ke Server
```bash
git clone https://github.com/username/agen-minyak-tanah.git
# atau upload manual via FTP/cPanel
```

#### b. Import Database
1. Buat database MySQL (misal: `agen_minyak`)
2. Import file `database.sql` via phpMyAdmin atau CLI:
   ```bash
   mysql -u username -p agen_minyak < database.sql
   ```

#### c. Konfigurasi Koneksi Database
Edit file `config.php`:

```php
$host = 'localhost';
$dbname = 'agen_minyak';     // Ganti dengan nama database kamu
$username = 'root';          // Ganti dengan username MySQL
$password = '';              // Ganti dengan password MySQL

// Konfigurasi Telegram Bot
define('TELEGRAM_BOT_TOKEN', 'MASUKKAN_TOKEN_BOT_TELEGRAM_KAMU_DI_SINI');
define('TELEGRAM_CHAT_ID', 'MASUKKAN_CHAT_ID_ADMIN_ANDA');
```

#### d. Setup Folder Upload
Buat folder `uploads/` dan beri permission 755:
```bash
mkdir uploads
chmod 755 uploads
```

#### e. Install DomPDF (untuk export PDF)
Jika belum ada, install via Composer:
```bash
composer require dompdf/dompdf
```
Atau download manual dari [DomPDF GitHub](https://github.com/dompdf/dompdf) dan extract ke folder `/vendor/`.

#### f. Akses Aplikasi
Buka di browser:
```
http://localhost/agen-minyak-tanah/index.php
```

#### g. Buat User Admin
Jalankan SQL ini di phpMyAdmin untuk membuat user admin pertama:

```sql
INSERT INTO users (username, password, nama_lengkap, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');
```

> Password default: **password**  
> (Hash di atas adalah hasil dari `password_hash('password', PASSWORD_DEFAULT)`)

---

## 📄 Keterangan Khusus: Laporan PDF

### Fitur:
- Export laporan bongkar muatan ke PDF
- Format siap cetak — kolom "Tanda Tangan Pemilik Pangkalan" sengaja dikosongkan
- Tampilkan: tanggal, plat kendaraan, sopir, jumlah (drom & liter), nama pangkalan & pemilik

### Cara Pakai:
1. Buka `laporan/bongkar.php`
2. Pilih rentang tanggal
3. Klik “Tampilkan Laporan”
4. Klik “Export PDF”
5. Cetak → berikan ke pemilik pangkalan → tanda tangan manual

### Teknologi:
- Menggunakan **DomPDF** — pastikan sudah terinstall
- Template HTML + CSS sederhana — mudah dimodifikasi

---

## 🤖 Konfigurasi Telegram & Cron Job

### 1. Setup Bot Telegram
1. Buka Telegram → cari **@BotFather**
2. Ketik `/newbot` → ikuti petunjuk → dapatkan **BOT TOKEN**
3. Simpan token di `config.php` → `TELEGRAM_BOT_TOKEN`

### 2. Dapatkan Chat ID
1. Kirim pesan apa saja ke bot kamu
2. Akses: `https://api.telegram.org/bot<TOKEN>/getUpdates`
3. Cari `message.from.id` → itulah `chat_id`
4. Simpan di `config.php` → `TELEGRAM_CHAT_ID`

### 3. Setup Cron Job
Edit crontab (`crontab -e`) atau setup di cPanel:

```bash
# Notifikasi STNK setiap pagi jam 8
0 8 * * * /usr/bin/php /path/to/project/cron/notifikasi_stnk.php

# Laporan mingguan setiap Senin jam 8 pagi
0 8 * * 1 /usr/bin/php /path/to/project/cron/laporan_mingguan.php
```

### 4. Testing Notifikasi
Akses via browser:
```
http://domain.com/cron/test_notifikasi.php
```
Pastikan kamu menerima 2 pesan test di Telegram.

---

## 👥 Penggunaan Aplikasi

### Role Pengguna:
- **Admin** → akses semua fitur
- **Operator** → input transaksi, lihat laporan (tidak bisa hapus/edit master data)
- **AMT1 (Sopir)** → hanya bisa input transaksi via `amt/input_bongkar.php`
- **AMT2 (Kondektur)** → hanya bisa input transaksi via `amt/input_bongkar.php`

### Alur Kerja:
1. **Admin** input data master: pangkalan, kendaraan, sopir, kondektur
2. **AMT** login → input transaksi + upload foto bukti → otomatis kirim notifikasi ke Telegram
3. **Admin/Operator** bisa lihat & export laporan
4. **Publik** bisa lihat peta lokasi semua pangkalan

---

## 🛠️ Troubleshooting

### Masalah: Export PDF error
- Pastikan DomPDF sudah terinstall
- Pastikan folder `vendor/` ada dan writable
- Coba install via Composer: `composer require dompdf/dompdf`

### Masalah: Notifikasi Telegram tidak terkirim
- Pastikan BOT TOKEN dan CHAT ID sudah benar
- Pastikan server bisa akses internet (tidak diblokir firewall)
- Coba test via `cron/test_notifikasi.php`

### Masalah: Upload foto gagal
- Pastikan folder `uploads/` ada dan permission 755
- Pastikan ukuran file tidak melebihi `upload_max_filesize` di php.ini

---

## 📜 Lisensi

MIT License — bebas digunakan, dimodifikasi, dan didistribusikan.

---

## 🙏 Kontribusi

Jika kamu ingin berkontribusi:
1. Fork repository ini
2. Buat branch fitur baru (`git checkout -b fitur-baru`)
3. Commit perubahanmu (`git commit -am 'Tambah fitur X'`)
4. Push ke branch (`git push origin fitur-baru`)
5. Buat Pull Request

---

## 💬 Dukungan

Jika ada pertanyaan atau butuh bantuan, silakan buka **Issue** di repository ini.

---

**Dibangun dengan ❤️ untuk mendukung ketahanan energi rakyat Indonesia.**
