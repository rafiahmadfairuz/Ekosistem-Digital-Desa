# Dokumentasi UI Desa Digital

Dokumen ini menjadi acuan implementasi antarmuka Platform Digital Desa. Versi ini menggunakan Laravel Blade, Tailwind CSS 4, dan Vite agar sesuai dengan fondasi UI yang terdeteksi pada repository saat audit Phase 0.

Dokumen ini membedakan kondisi kode yang sudah ada dengan target implementasi. Jangan menganggap komponen yang dijelaskan sebagai fitur yang sudah selesai.

Status implementasi per 9 November 2026: `/` adalah homepage publik; `/register` membuat akun Warga dengan status menunggu aktivasi; `/login` menerima nama pengguna atau email untuk akun aktif; dan `/admin/users/pending` dibatasi untuk Super Admin agar dapat mengaktifkan pendaftaran. Halaman terautentikasi menggunakan layout bersama dan navigasi berbasis peran serta policy. Modul layanan desa yang belum tersedia tidak ditampilkan sebagai tautan aktif.

## 1. Teknologi UI

- Laravel Blade sebagai templating engine.
- Tailwind CSS 4 sebagai utility-first CSS framework.
- Vite sebagai development server dan asset bundler.
- JavaScript hanya untuk interaksi yang memang memerlukan perilaku sisi klien.
- Gunakan komponen Blade untuk elemen UI yang dipakai berulang.

Jangan menambahkan Bootstrap atau Sass Bootstrap. Hindari menjalankan dua sistem styling utama secara bersamaan. Jika repository sudah memiliki konfigurasi Tailwind 4, pertahankan dan rapikan konfigurasi tersebut. Jangan melakukan migrasi framework CSS tanpa alasan teknis yang jelas.

Sebelum mengubah konfigurasi, periksa `package.json`, lockfile, `vite.config.js`, entry CSS, dan konfigurasi Tailwind yang benar-benar tersedia. Jangan mengasumsikan nama file atau versi paket tanpa memeriksanya.

## 2. Prinsip desain

Antarmuka harus:
- Sederhana dan mudah dipahami warga dengan tingkat literasi digital yang beragam.
- Konsisten antar modul.
- Responsif untuk ponsel, tablet, dan desktop.
- Mengutamakan keterbacaan, status proses, dan tindakan utama.
- Menampilkan navigasi sesuai peran dan hak akses pengguna.
- Tidak menampilkan tautan, tombol, atau menu yang belum memiliki tujuan fungsional.
- Tidak membocorkan data pribadi pada halaman, URL, notifikasi, atau pesan kesalahan.

BUMDes dapat memiliki tampilan operator untuk membantu warga. Tampilan bantuan harus membedakan identitas warga pemilik permohonan dengan operator yang melakukan input.

## 3. Palet warna

Gunakan palet yang telah ditetapkan proyek.

| Penggunaan | Warna |
| --- | --- |
| Latar halaman | `#F4F6F9` |
| Permukaan, kartu, dan navbar | `#FFFFFF` |
| Teks utama | `#0A192F` |
| Aksi utama | `#059669` |
| Bahaya dan notifikasi kritis | `#DC2626` |
| Teks sekunder | Abu-abu netral dengan kontras yang memadai |
| Garis pembatas | Abu-abu muda dengan kontras yang cukup |

Tetapkan warna sebagai design token melalui CSS variables atau mekanisme theme Tailwind 4 yang sesuai dengan konfigurasi proyek. Hindari mengulang nilai warna secara acak di banyak file.

Contoh penggunaan:
- Hijau utama untuk tindakan utama dan status berhasil.
- Merah untuk kesalahan, penolakan, dan tindakan berisiko.
- Warna netral untuk latar, permukaan, teks sekunder, dan pembatas.
- Jangan mengandalkan warna saja untuk menyampaikan status. Sertakan label atau ikon yang memiliki teks alternatif.

## 4. Kerangka layout

### 4.1 Navbar atas

Target komponen:
- Identitas aplikasi dan nama “Desa Digital”.
- Nama desa atau identitas instansi jika datanya tersedia.
- Navigasi atau breadcrumb sesuai halaman.
- Indikator notifikasi jika fitur notifikasi sudah aktif.
- Menu akun pengguna yang menampilkan informasi minimum.
- Tombol menu mobile yang dapat dioperasikan dengan keyboard.

Navbar dapat dibuat sticky jika tidak mengganggu ruang konten dan aksesibilitas.

Jangan menggunakan nama, avatar, atau data pengguna contoh pada production. Ambil informasi dari akun yang sedang login dan tampilkan hanya data yang diperlukan.

### 4.2 Sidebar desktop

Gunakan sidebar untuk navigasi modul pada layar desktop jika jumlah menu sudah cukup banyak.

Menu harus mengikuti role dan permission pengguna. Menyembunyikan menu bukan mekanisme keamanan. Setiap route dan aksi tetap harus dilindungi oleh middleware, policy, atau pemeriksaan otorisasi server-side.

### 4.3 Area konten

- Gunakan lebar konten yang nyaman dibaca.
- Gunakan spacing dan hierarki heading yang konsisten.
- Setiap halaman memiliki judul dan penjelasan singkat jika diperlukan.
- Tampilkan breadcrumb pada alur yang bertingkat.
- Gunakan card untuk mengelompokkan informasi yang berkaitan.
- Hindari dashboard yang penuh angka tanpa konteks atau tindakan lanjutan.

#### Implementasi layout terautentikasi

- Layout bersama berada di `resources/views/components/layouts/app.blade.php` dan digunakan oleh dashboard, halaman admin, staf BUMDes, profil, serta daftar warga wilayah.
- Layar desktop menggunakan sidebar selebar 240 px. Pada layar lebih kecil, sidebar dilipat menjadi header 48 px dengan dialog navigasi native yang mendukung Escape, perangkap fokus browser, dan pengembalian fokus ke tombol pembuka.
- Menu dibangun oleh `App\Services\NavigationMenu`. Rute staf BUMDes memakai policy, rute RT/RW membutuhkan akses `viewAny` yang didukung penugasan aktif, dan rute admin tetap dilindungi middleware server.
- Tautan menu dideduplikasi lintas role. Role tanpa tujuan Phase 2 mendapat empty state; modul masa depan tidak dipasang sebagai tautan aktif.
- Menu akun menampilkan username, nama peran, dan aksi keluar. Data profil sensitif tidak dimasukkan ke atribut HTML atau payload JavaScript.

Komponen Blade yang tersedia: `x-ui.page-heading`, `x-ui.card`, `x-ui.alert`, `x-ui.breadcrumbs`, `x-ui.button`, `x-ui.empty-state`, dan `x-ui.status-badge`. Status form asynchronous memakai label live dan indikator proses; halaman validasi memfokuskan field invalid atau ringkasan error.

### 4.4 Navigasi mobile

Pada layar kecil:
- Gunakan menu drawer atau navigasi bawah sesuai kebutuhan dan jumlah menu.
- Pastikan menu tidak menutupi konten penting.
- Berikan penanda menu aktif yang jelas.
- Pastikan seluruh menu dapat diakses dengan keyboard dan pembaca layar.
- Jangan menampilkan placeholder sebagai tautan aktif.

Navigasi bawah Beranda, Transaksi, dan Profil dari rancangan awal hanya boleh dipertahankan jika setiap item memiliki route dan fungsi yang nyata. Jika belum tersedia, jangan tampilkan sebagai tautan aktif.

## 5. Struktur halaman berdasarkan modul

### 5.1 Autentikasi

Target halaman:
- Login.
- Pemulihan password.
- Reset password.
- Verifikasi akun jika kebijakan aplikasi membutuhkannya.

Ketentuan:
- Berikan pesan validasi yang jelas dan tidak membocorkan apakah akun tertentu terdaftar.
- Jangan menampilkan password dalam teks biasa.
- Jangan menyimpan password, OTP, atau token sensitif pada local storage.
- Terapkan rate limiting di server.
- Jangan membuat halaman autentikasi hanya sebagai mockup tanpa integrasi backend.

### 5.2 Beranda warga

Tampilkan ringkasan yang relevan:
- Status permohonan surat.
- Status tagihan atau layanan internet jika digunakan.
- Ringkasan pesanan marketplace.
- Pengumuman resmi yang memang tersedia.
- Akses cepat ke layanan.

Jangan menampilkan data milik warga lain. Semua ringkasan harus berasal dari query yang dibatasi berdasarkan identitas pengguna.

### 5.3 Persuratan

Tampilkan:
- Daftar permohonan milik warga.
- Form pengajuan berdasarkan jenis surat.
- Status dan tahapan persetujuan.
- Catatan revisi yang boleh dilihat warga.
- Dokumen final yang boleh diunduh.

Untuk petugas, tampilkan antrean sesuai kewenangan dan wilayah penugasannya. Pisahkan tindakan verifikasi RT, RW, Admin Desa, dan Kepala Desa. Jangan memberi BUMDes kewenangan persetujuan resmi secara otomatis.

### 5.4 RT/RW Net dan Wi-Fi

Tampilkan:
- Paket layanan.
- Form permintaan pemasangan.
- Status langganan.
- Tagihan dan riwayat pembayaran.
- Pengaduan dan status penanganan.
- Informasi akses Wi-Fi publik sesuai kebijakan desa.

Labeli fitur simulasi secara jelas. Jangan menampilkan seolah-olah aplikasi sudah mengendalikan router atau captive portal jika integrasi gateway belum tersedia.

### 5.5 Marketplace

Tampilkan:
- Katalog dan kategori produk.
- Detail produk dan stok.
- Keranjang.
- Checkout.
- Pesanan dan status pengiriman.
- Riwayat transaksi.

Untuk penjual, tampilkan pengelolaan produk, stok, dan pesanan miliknya sendiri. Jangan membuka data penjual atau pesanan lain tanpa kewenangan.

### 5.6 Panel BUMDes

Tampilkan:
- Ringkasan layanan yang sedang dibantu.
- Sesi bantuan warga.
- Status tindak lanjut.
- Riwayat aktivitas operator.
- Laporan operasional sesuai permission.

Catat operator sebagai pelaku tindakan dan warga sebagai pemilik permohonan. Operator tidak boleh meminta atau menyimpan password, OTP, PIN, maupun kredensial pembayaran warga.

## 6. Komponen UI yang dapat digunakan ulang

Bangun komponen Blade secara bertahap sesuai kebutuhan nyata, misalnya:
- Button.
- Form input dan textarea.
- Select.
- Badge status.
- Alert.
- Card.
- Modal atau dialog.
- Table.
- Pagination.
- Breadcrumb.
- Empty state.
- Loading state.
- Confirmation dialog.

Gunakan nama dan struktur komponen yang konsisten. Jangan membuat abstraksi komponen yang terlalu umum sebelum ada pola penggunaan berulang.

Setiap komponen interaktif harus memiliki state:
- Default.
- Hover.
- Focus.
- Disabled.
- Loading jika relevan.
- Error atau validation jika relevan.

## 7. Aksesibilitas dan responsivitas

Wajib:
- Gunakan elemen HTML semantik.
- Hubungkan label dengan input.
- Tampilkan error validasi di dekat field terkait.
- Pastikan fokus keyboard terlihat.
- Sediakan navigasi keyboard untuk menu, modal, dan kontrol.
- Gunakan kontras warna yang memadai.
- Berikan nama aksesibel pada tombol ikon.
- Jangan menggunakan warna sebagai satu-satunya penanda status.
- Target area sentuh minimal 44 x 44 CSS pixel pada perangkat mobile.
- Jangan menonaktifkan zoom browser.
- Hormati preferensi reduced motion.
- Uji layout pada viewport mobile, tablet, dan desktop.

## 8. Keamanan pada lapisan UI

UI bukan batas keamanan utama. Semua pemeriksaan harus tetap dilakukan di server.

- Jangan mengandalkan conditional rendering untuk melindungi route atau data.
- Jangan menaruh data pribadi yang tidak diperlukan ke dalam HTML atau JavaScript.
- Jangan menyimpan secret, API key, token pembayaran, atau kredensial router pada frontend.
- Gunakan CSRF protection Laravel untuk form yang mengubah data.
- Escape output secara default. Gunakan raw HTML hanya jika konten sudah disanitasi dan alasannya jelas.
- Gunakan endpoint unduhan privat yang memeriksa otorisasi.
- Jangan memasukkan data sensitif ke query string.
- Jangan menampilkan stack trace atau detail internal pada pesan error pengguna.

## 9. Struktur file

Struktur berikut adalah target yang dapat disesuaikan dengan kondisi repository. Periksa struktur aktual sebelum membuat atau memindahkan file.

- `resources/views/components/layouts/` untuk layout utama.
- `resources/views/components/` untuk komponen Blade.
- `resources/views/pages/` atau direktori modul untuk halaman.
- `resources/css/app.css` sebagai entry CSS Tailwind.
- `resources/js/app.js` sebagai entry JavaScript.
- `vite.config.js` untuk konfigurasi Vite.
- `package.json` untuk dependensi frontend.

`resources/css/app.scss` sebelumnya mengimpor Bootstrap Sass. Setelah seluruh referensi source diperiksa, file dan dependensi Bootstrap/Sass dihapus agar hanya Tailwind CSS 4 yang menjadi framework styling aktif. Build produksi diverifikasi kembali setelah penghapusan.

## 10. Menjalankan dan membangun UI

Periksa scripts yang tersedia di `package.json` terlebih dahulu. Gunakan perintah yang sesuai dengan repository.

Umumnya:
```bash
npm install
npm run dev
npm run build
```

Jalankan Laravel pada terminal terpisah setelah dependensi backend dan konfigurasi database siap:
```bash
php artisan serve
```

Jangan mengunduh paket secara manual dari sumber tidak resmi. Gunakan package manager resmi dan lockfile yang sesuai. Jangan menghapus atau mengganti lockfile tanpa alasan teknis dan tinjauan dampak.

## 11. Kriteria penerimaan UI

UI dinyatakan memenuhi tahap implementasi jika:
1. Tailwind CSS 4 dan Vite terintegrasi tanpa konflik framework CSS.
2. Layout responsif pada mobile, tablet, dan desktop.
3. Navigasi hanya menampilkan tujuan yang benar-benar tersedia dan sesuai permission.
4. Setiap form memiliki validasi dan pesan kesalahan yang jelas.
5. Komponen interaktif dapat digunakan dengan keyboard.
6. Tidak ada data contoh atau data pribadi yang bocor ke pengguna yang tidak berhak.
7. Tidak ada tombol mati atau tautan placeholder.
8. Build frontend berhasil.
9. Perubahan UI memiliki pengujian yang relevan atau langkah verifikasi manual yang terdokumentasi.

## 12. Catatan kondisi awal

Berdasarkan audit Phase 0, repository terdeteksi menggunakan Tailwind CSS 4 dan `resources/css/app.css`, sedangkan dokumentasi UI sebelumnya menyebut Bootstrap 5 dan Sass. Dokumen ini memperbarui arah UI agar konsisten dengan Tailwind.

Audit awal juga menyatakan bahwa layout lengkap dan halaman layanan belum tersedia. Karena itu, Codex harus memeriksa ulang kondisi repository sebelum menyatakan suatu komponen sudah ada atau menghapus file lama.
