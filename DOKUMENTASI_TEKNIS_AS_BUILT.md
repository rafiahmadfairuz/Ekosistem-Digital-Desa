# Dokumentasi Teknis As-Built — Desa Digital

**Kondisi yang didokumentasikan:** 15 November 2026  
**Tujuan:** ringkasan stack, environment, fitur, integrasi, dan operasi lokal berdasarkan repository saat ini.

> Jangan menyalin credential dari file `.env` ke dokumen, tiket, atau Git. Template `.env.example` dan `.env.docker.example` hanya berisi nama variabel serta nilai contoh.

## 1. Stack teknologi

| Komponen | Versi / pilihan yang teramati | Catatan |
|---|---|---|
| PHP | 8.5 (runtime lokal teramati 8.5.12) | Docker memakai PHP-FPM 8.5 berbasis Debian Bookworm. |
| Laravel | 13.42.0 | Aplikasi server-rendered dengan Blade. |
| Database | MySQL-compatible (`mysql` driver) | Docker Compose memakai MySQL 8.4; pemilik sebelumnya melaporkan MariaDB 13.0.2 di lokal. Verifikasi versi aktual dengan `php artisan db:show`. |
| Node.js | 24 (image build Docker) | Untuk bundling asset frontend. |
| Vite | 8.x (build teramati 8.3.6) | Build memakai Laravel Vite plugin. |
| Tailwind CSS | 4.x (build teramati 4.3.5) | CSS utility framework. |
| Web server | Nginx 1.28 (Docker) | Meneruskan request ke PHP-FPM. |
| Container | Docker Compose | Service aplikasi, worker queue, web server, dan database. |
| Pengujian PHP | PHPUnit 12.5.42 | Test suite Laravel/PHPUnit. |

Versi dependency yang terkunci di `composer.lock` dan lockfile npm adalah acuan untuk instalasi; angka runtime di tabel merupakan kondisi yang teramati saat penyusunan dokumen.

## 2. Environment dan konfigurasi

### Mode pengembangan lokal

Template utama adalah `.env.example`. Nilai default mengarahkan aplikasi ke environment `local`, database MySQL-compatible, mail log, queue/cache/session berbasis database, pembayaran simulasi, dan gateway Wi-Fi simulasi. Buat `.env` privat dari template, isi koneksi database lokal, lalu jalankan setup Laravel sesuai README proyek. Pastikan `APP_KEY` serta kunci enkripsi aplikasi tersedia sebelum memakai fitur yang menyimpan data terenkripsi.

### Mode Docker lokal

Template Docker adalah `.env.docker.example`. Compose mendefinisikan MySQL 8.4, service app/worker/nginx, serta port aplikasi yang dapat dikonfigurasi. Gunakan file environment privat untuk mengganti placeholder password database; jangan menyalin placeholder contoh ke server bersama. Container database menyimpan data di volume Docker.

Contoh alur standar (periksa README dan `docker-compose.yml` untuk nama service/target yang berlaku):

1. Salin template yang sesuai ke file environment privat dan isi konfigurasi lokal.
2. Jalankan build serta service Docker Compose sesuai instruksi repository.
3. Jalankan migrasi hanya setelah memastikan database/volume yang dituju memang database lokal yang benar.
4. Buka `APP_URL`/port lokal yang dikonfigurasi. Untuk memakai ngrok, tunnel harus meneruskan ke port aplikasi lokal tersebut dan `APP_URL`/trusted proxy harus cocok dengan setup yang dipakai.

### Kelompok variabel konfigurasi penting

| Kelompok | Variabel utama | Fungsi |
|---|---|---|
| Aplikasi | `APP_ENV`, `APP_DEBUG`, `APP_URL`, `APP_KEY`, `TRUSTED_PROXIES` | Mode, URL publik/lokal, enkripsi Laravel, dan proxy yang dipercaya. |
| Database | `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Koneksi database. Kredensial harus privat. |
| Session/cache/queue | `SESSION_DRIVER`, `SESSION_ENCRYPT`, `CACHE_STORE`, `QUEUE_CONNECTION` | Penyimpanan sesi, cache, dan pekerjaan asynchronous. |
| Email | `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` | Verifikasi email dan notifikasi. Template lokal memakai log mailer. |
| Pembayaran | `MARKETPLACE_PAYMENT_MODE`, `MARKETPLACE_XENDIT_ENABLED`, `XENDIT_SECRET_KEY`, `XENDIT_WEBHOOK_TOKEN`, `XENDIT_BASE_URL` | Memilih simulasi atau Xendit dan memverifikasi callback. |
| Payout | `XENDIT_PAYOUTS_ENABLED`, `XENDIT_PAYOUT_WEBHOOK_TOKEN`, `XENDIT_PAYOUT_SOURCE_OF_FUND`, `XENDIT_PAYOUT_PURPOSE_CODE` | Mengaktifkan dan mengonfigurasi payout jika akun/provider siap. |
| Data sensitif | `DATA_ENCRYPTION_KEY`, `IDENTITY_BLIND_INDEX_KEY` | Enkripsi data privat dan pencarian/blind index. Jaga nilainya tetap stabil dan rahasia. |
| Wi-Fi | `WIFI_GATEWAY`, `WIFI_ACCESS_LOG_RETENTION_DAYS` | Adapter gateway dan retensi log akses. Default pengembangan adalah simulasi. |

Untuk ngrok, URL callback Xendit perlu menunjuk ke endpoint aplikasi yang dapat dijangkau publik, misalnya `/api/marketplace/xendit/webhook`. Jangan menaruh secret/token di URL; gunakan header callback dan konfigurasi token yang sesuai. Nilai `APP_URL` dan proxy HTTPS harus disetel konsisten agar URL callback/redirect yang dibuat aplikasi tidak kembali menjadi HTTP lokal.

## 3. Fitur aplikasi

| Area | Fitur yang tersedia |
|---|---|
| Akun dan akses | Registrasi warga, verifikasi email, aktivasi akun, login username/email, pemulihan password, banyak peran, pembatasan akses server-side. |
| Profil dan wilayah | Profil warga, hierarki wilayah, verifikasi wilayah, penugasan RT/RW dan pembatasan cakupan petugas. |
| Persuratan | Pengajuan, lampiran privat, revisi, pemeriksaan bertahap, konfirmasi warga, dokumen keluaran, dan unduh terotorisasi. |
| Bantuan BUMDes | Sesi bantuan warga, petugas, status, log, percakapan privat, konfirmasi pemohon, dan laporan. |
| Internet dan Wi-Fi | Paket, pemasangan, langganan, tagihan, pembayaran, keluhan/permintaan layanan, sesi Wi-Fi. Integrasi router saat ini simulasi/fail-closed. |
| Pasar Desa pembeli | Katalog produk, kategori/filter, keranjang, checkout, order multi-penjual, COD atau checkout Xendit, notifikasi pesanan. |
| Pasar Desa penjual | Pendaftaran dan persetujuan seller, moderasi produk, stok, pesanan, dashboard pendapatan/saldo, rekening payout, dan permintaan pencairan. |
| Administrasi | Dashboard sesuai peran, pengelolaan akun/peran/wilayah, audit log, notifikasi, laporan usaha/biaya dan laporan operasional. |

Peran yang teramati mencakup Resident, RT/RW, Village Admin, Super Admin, BUMDes Manager, BUMDes Operator, dan Seller. Satu akun dapat memiliki lebih dari satu peran; menu navigasi bukan pengganti middleware/policy dan pemeriksaan kepemilikan di backend.

## 4. Integrasi dan status

| Integrasi | Implementasi / status |
|---|---|
| Xendit checkout | Hosted Payment Session untuk satu checkout dan webhook/callback untuk konfirmasi. Callback harus diverifikasi dan pemrosesan idempoten. Pemilik melaporkan Xendit test berhasil; hal itu belum diuji ulang secara eksternal saat dokumen ini dibuat. |
| Xendit payout | Kode menyediakan alur saldo/ledger dan permintaan payout. Pengiriman payout provider bergantung pada credential, aktivasi fitur, dan konfigurasi akun Xendit; default template menonaktifkannya. |
| SMTP | Pengaturan SMTP tersedia. Pemilik melaporkan email SMTP terverifikasi; jangan simpan App Password di repository atau dokumen. |
| Wi-Fi | Adapter/sesi simulasi. Enforcement captive portal/router/RADIUS nyata belum tersedia. |
| Penyimpanan file | Disk Laravel; lampiran privat dilayani melalui pemeriksaan otorisasi. Konfigurasi object storage dapat disediakan melalui variabel AWS/S3. |
| Tanda tangan elektronik | Alur aplikasi/prototipe; bukan integrasi PSrE tersertifikasi. |
| Pengiriman barang | Status pemenuhan dikelola di aplikasi; tidak ada integrasi kurir teramati. |

Pembayaran masuk ke akun merchant tidak otomatis membagi uang ke rekening seller. Saldo seller berasal dari transaksi yang memenuhi aturan ledger; pencairan merupakan proses payout terpisah. Integrasi split XenPlatform tidak menjadi prasyarat pada alur checkout saat ini.

## 5. Data dan keamanan operasional

- Data pembayaran dan saldo harus berasal dari order, pembayaran yang terverifikasi, dan ledger; jangan mengubah saldo hanya berdasarkan redirect browser.
- Callback harus memvalidasi token/signature sesuai skema provider, referensi transaksi yang unik, jumlah/mata uang, status event, dan idempotensi.
- Pengajuan payout perlu menahan saldo secara transaksional agar tidak terjadi pencairan ganda; callback sukses/gagal menyelesaikan atau melepas penahanan.
- Data profil/rekening dan lampiran privat memerlukan enkripsi/otorisasi sesuai implementasi model, migration, policy, dan storage.
- Jangan mengirim `.env`, kredensial SMTP/Xendit, kunci enkripsi, database dump berisi data pribadi, atau log sensitif ke Git/tiket publik.
- Untuk deployment HTTPS, tinjau `APP_DEBUG=false`, trusted proxies, secure cookies, CSRF, backup dan pemulihan database, retensi data, serta akses file privat.

## 6. Catatan skema database

Migration adalah sumber skema yang dapat direproduksi. Pemilik pernah melaporkan database lokal MariaDB 13.0.2, koneksi Laravel `mysql`, dan 60 tabel. Jumlah tabel dari migration repository yang didokumentasikan pada PRD/ERD sebelumnya tidak sepenuhnya cocok dengan angka runtime tersebut. Jalankan `php artisan db:show` dan `php artisan migrate:status` terhadap database lokal yang benar untuk memastikan kondisi aktual; jangan menyimpulkan semua tabel runtime berasal dari migration sebelum perbedaan itu direkonsiliasi.

## 7. Pemeriksaan yang tercatat

Pemeriksaan pada dokumentasi sebelumnya mencatat build asset frontend berhasil, Composer audit bersih, serta sebagian test suite dapat berjalan menggunakan SQLite (113 lulus dari 897 test yang ditemukan). Sebagian pengujian bergantung pada database/lingkungan dan tidak semuanya telah dibuktikan berjalan pada MySQL. Hasil tersebut bukan klaim bahwa seluruh suite lulus. Untuk perubahan backend, jalankan test yang relevan di database target sebelum rilis.

## 8. Sumber teknis

- Konfigurasi dependency: `composer.json`, `composer.lock`, `package.json`, lockfile npm.
- Variabel environment: `.env.example`, `.env.docker.example`.
- Container dan web server: `Dockerfile`, `docker-compose.yml`, konfigurasi Nginx.
- Fitur dan model data: `PRD_AS_BUILT.md`, `ERD_AS_BUILT.md`, routes, controllers, policies, models, migrations, dan tests.

Dokumen ini mencatat kondisi repository lokal pada tanggal di atas. Jika ada perbedaan, kode, migration, konfigurasi yang aman dibagikan, dan hasil pemeriksaan environment aktual perlu dipakai untuk memperbaruinya.
