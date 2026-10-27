# Savve: Aplikasi Windows & Android (WiFi Lokal, Tanpa Internet)

Repository ini mengubah web app **Savve** (Laravel) menjadi:

| Folder     | Isi                                                                                   |
|------------|----------------------------------------------------------------------------------------|
| `web/`     | Kode Laravel Savve (asli dari Dany, dengan sedikit penyesuaian, lihat bawah)          |
| `desktop/` | Aplikasi **Windows** (Electron). Berisi PHP + database SQLite, sekaligus jadi **server WiFi** |
| `android/` | Aplikasi **Android** (.apk) untuk HP kasir, tersambung ke komputer server lewat WiFi  |

## Cara kerja

```
              WiFi lokal / hotspot (tanpa internet)
   ┌──────────────────────────┐
   │  Laptop/PC Windows        │  https://192.168.x.x:8443
   │  Aplikasi "Savve"         │◄─────────────┬─────────────────┐
   │  (mode Server)            │              │                 │
   │  - PHP + Laravel          │        HP Android        HP Android / laptop lain
   │  - Database SQLite        │        app "Savve"       (app Savve / Chrome)
   │  - Foto barang            │        (kasir 1)         (kasir 2, admin, ...)
   └──────────────────────────┘
```

* **Satu** komputer Windows menjalankan aplikasi Savve dalam mode **Server**. Semua data
  (transaksi, foto, akun) tersimpan di komputer ini.
* HP kasir memakai aplikasi **Savve Android**, lalu menyambung ke komputer server lewat WiFi yang sama.
  Bisa juga lewat hotspot dari HP atau laptop. Internet tidak diperlukan.
* Koneksi memakai **HTTPS** dengan sertifikat lokal supaya **kamera** (foto barang, scan QR) bisa dipakai di HP.

## Cara pakai di lapangan

1. Install `Savve-Setup-x.x.x.exe` di laptop/PC utama, lalu buka aplikasinya dan pilih **Komputer utama (Server)**.
   Saat diminta izin firewall Windows, pilih **Allow / Izinkan**.
2. Muncul jendela **Sambungkan HP kasir** yang menampilkan alamat server, misalnya `192.168.1.10:8443`.
   Jendela ini bisa dibuka lagi kapan saja dengan menekan **F2** atau lewat menu **Server**.
3. Install `Savve.apk` di HP kasir. Buka aplikasinya, tekan **Cari Otomatis**, atau ketik alamat server.
4. Login. Akun awal:
   * Admin: `admin@savve.com` / `password123`
   * Kasir: `kasir1@savve.com` / `password123`

   **Segera ganti password kedua akun ini** lewat menu Kelola Kasir / Profil.
5. Selama dipakai, **aplikasi Savve di komputer server harus tetap terbuka**. Kalau ditutup, HP kasir tidak bisa transaksi.

Tips:
* Untuk mengganti server di HP: tekan tombol **Kembali** sampai muncul menu, lalu pilih **Ganti server**.
* Laptop atau tablet tanpa aplikasi bisa membuka `https://ALAMAT-SERVER:8443` di Chrome.
  Saat muncul peringatan keamanan, pilih *Lanjutan → Lanjutkan*.
* Laptop Windows lain juga bisa menginstal Savve dan memilih mode **Klien**.

### Backup data

Di komputer server, buka menu **Server → Backup Data…**. Hasilnya sebuah folder `Savve-Backup-YYYYMMDD-HHMM`
berisi database dan semua foto. Simpan di flashdisk. Untuk memulihkan: **Server → Pulihkan Data dari Backup…**

Lokasi data: `%APPDATA%\Savve\data` (bisa dibuka lewat **Server → Buka Folder Data**).

### Cetak nota

* **Windows:** tombol cetak nota membuka dialog print Windows seperti biasa.
* **Android:** tombol cetak membuka layanan print Android. Printer thermal Bluetooth biasanya butuh aplikasi
  *print service* dari merek printernya, atau aplikasi seperti **RawBT**, supaya muncul di daftar printer.

## Cara membuat file installer (.exe) dan APK

### Cara A: otomatis di GitHub (paling mudah)

1. Upload folder ini ke repository GitHub.
2. Buka tab **Actions → Build aplikasi Savve → Run workflow**.
3. Setelah selesai (±10–15 menit), unduh hasilnya di bagian **Artifacts**:
   * `Savve-Windows`: installer `.exe`
   * `Savve-Android`: `Savve.apk`

Supaya APK bisa di-*update* tanpa uninstall, atau untuk Play Store, buat keystore sendiri lalu isi secrets
`ANDROID_KEYSTORE_BASE64`, `ANDROID_KEYSTORE_PASSWORD`, `ANDROID_KEY_ALIAS`, `ANDROID_KEY_PASSWORD`.
Tanpa secrets ini, APK ditandatangani dengan debug key: tetap bisa di-install, tapi hanya cocok untuk pemakaian sendiri.

### Cara B: manual di komputer Windows

Butuh: [PHP 8.3](https://windows.php.net/download/) dan [Composer](https://getcomposer.org/) di PATH,
serta [Node.js 22](https://nodejs.org/).

```powershell
cd desktop
npm ci
npm run dist:win
# hasil: desktop\dist\Savve Setup 1.0.0.exe
```

Untuk mencoba tanpa membuat installer (memakai PHP dari PATH):

```powershell
cd web;     composer install; npm install; npm run build
cd ..\desktop; npm ci; npm start
```

### APK manual

Buka folder `android/` di **Android Studio**, lalu pilih *Build → Build App Bundle(s) / APK(s) → Build APK(s)*.
Bisa juga lewat terminal: `cd android && ./gradlew assembleRelease`.

## Perubahan pada kode web (`web/`)

Perubahannya kecil dan tetap kompatibel dengan MySQL/XAMPP:

* `database/migrations/2026_05_12_145509_update_status_transaksi_table.php`: perintah `ALTER ... MODIFY ENUM`
  khusus MySQL diberi alternatif untuk SQLite.
* `database/seeders/DesktopSeeder.php` (baru): data awal aplikasi (akun + master barang), tanpa event uji coba.
* `config/database.php`: `busy_timeout`, `journal_mode` dan `synchronous` SQLite bisa diatur lewat `.env`.
* `config/app.php`: zona waktu bisa diatur dengan `APP_TIMEZONE`. Aplikasi desktop memakai `Asia/Jakarta`.
  Sebelumnya jam disimpan dalam UTC tapi ditampilkan dengan label "WIB", jadi meleset 7 jam.
* `bootstrap/app.php`: mempercayai proxy `127.0.0.1` (server HTTPS lokal di aplikasi desktop).
* View: `images/logo.png` diganti menjadi `images/Logo.png` sesuai nama file asli. Sebelumnya logo tidak tampil di
  server Linux/hosting.
* `package-lock.json` diperbarui karena sebelumnya tidak sinkron dengan `package.json`.

## Catatan teknis

* `desktop/src/server.js`: menjalankan 4 proses `php -S` (Laravel) di belakang server HTTPS Node.js pada port
  **8443**. Port **8080** hanya mengalihkan ke HTTPS. Sertifikat self-signed dibuat sekali saat pertama jalan.
* Aplikasi Android mempercayai sertifikat server berdasarkan **sidik jari SHA-256** yang disimpan saat pertama
  kali tersambung. Kalau aplikasi server diinstal ulang, sertifikatnya berubah, jadi HP harus **Ganti server** dan
  menyambung ulang.
* Installer Windows menambahkan aturan firewall `Savve Server` untuk port 8443 dan 8080.
