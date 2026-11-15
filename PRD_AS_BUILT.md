# PRD As-Built — Platform Digital Desa

**Status dokumen:** kondisi implementasi lokal yang teramati 15 November 2026  
**Jenis:** spesifikasi produk berdasarkan aplikasi yang telah direalisasikan  
**Sumber utama:** route Laravel, controller, policy, service, job, Blade, model, migration, dan tes fitur.

Dokumen ini merekam fungsi yang benar-benar tersedia dalam repository. Perilaku yang masih berupa simulasi, memerlukan konfigurasi operator, atau belum diuji secara independen diberi label demikian. Ini bukan komitmen bahwa seluruh integrasi sudah siap produksi.

## 1. Ringkasan produk

Platform web satu desa ini menggabungkan akun dan akses berbasis peran, profil warga dan wilayah, persuratan, layanan internet/Wi-Fi, Pasar Desa, bantuan digital oleh BUMDes, notifikasi, laporan operasional, dan pencatatan audit. Antarmuka menggunakan Laravel Blade, Tailwind CSS 4, dan Vite. Backend berupa Laravel modular monolith dengan database relasional.

Pengguna mengakses layanan mandiri melalui web. Staf BUMDes dapat membantu warga melalui sesi bantuan yang tercatat, tetapi warga tetap menjadi pemilik permohonan dan harus mengonfirmasi data yang dibantu.

## 2. Pengguna, peran, dan batas kewenangan

| Peran | Kemampuan utama yang tersedia |
|---|---|
| Resident | Mengelola profil sendiri, mengajukan/melacak surat, mengakses layanan internet/Wi-Fi, membeli produk, melihat pesanan/notifikasi, dan memakai bantuan digital. |
| RT / RW | Melihat warga sesuai wilayah penugasan aktif dan melakukan tahapan pemeriksaan yang diizinkan. |
| Village Admin | Pengaturan desa dan verifikasi wilayah warga. Tidak otomatis mendapat semua kewenangan Super Admin. |
| Super Admin | Aktivasi akun, akses/peran, wilayah dan penugasan, audit log, konfigurasi kesiapan Xendit. |
| BUMDes Manager | Operasi layanan dan pasar yang diizinkan, moderasi/persetujuan penjual, laporan usaha, dan laporan bantuan. |
| BUMDes Operator | Sesi bantuan warga dan kegiatan operasional sesuai penugasan. Tidak dapat memutuskan persetujuan surat resmi atau melihat data di luar cakupan. |
| Seller | Mengelola produk, stok, pesanan, rekening payout dan permintaan pencairan miliknya setelah pengajuan penjual disetujui. |

Satu akun dapat memiliki beberapa peran. Menu hanya membantu navigasi; middleware, policy, dan pemeriksaan kepemilikan server-side tetap menjadi batas keamanan.

## 3. Kemampuan yang telah direalisasikan

### 3.1 Identitas dan akses

- Pendaftaran akun warga, status aktivasi, login dengan username/email, logout, verifikasi email, kirim ulang verifikasi, pemulihan dan reset password.
- Pengelolaan role, status akun, profil warga, hierarki desa/RW/RT, verifikasi wilayah, dan penugasan petugas.
- Rate limit untuk login, pendaftaran, reset password, dan beberapa operasi layanan.

### 3.2 Persuratan dan bantuan digital

- Pengajuan surat, data bertipe dinamis berdasarkan jenis surat, revisi berversi, lampiran privat, alur pemeriksaan, konfirmasi warga, dokumen hasil, dan unduhan dengan pemeriksaan otorisasi.
- Bantuan BUMDes berupa sesi yang memiliki pemilik warga, petugas, status, log, percakapan terenkripsi, referensi layanan, permintaan konfirmasi, dan laporan.
- Proses tanda tangan elektronik adalah workflow/prototipe aplikasi; integrasi PSrE tersertifikasi tidak dinyatakan tersedia.

### 3.3 Internet, tagihan, dan Wi-Fi

- Paket internet, permintaan pemasangan, review dan riwayat status, langganan, tagihan, catatan pembayaran, permintaan layanan/keluhan, serta operasi paket dan jaringan.
- Permintaan sesi Wi-Fi dan pencatatan akses tersedia sebagai **simulasi**. Belum ada bukti enforcement router/captive portal nyata.

### 3.4 Pasar Desa dan keuangan penjual

- Pengajuan dan pemeriksaan penjual/dokumen, kategori, katalog, foto privat, moderasi produk, pengarsipan/pemulihan, keranjang, checkout multi-penjual, snapshot pesanan, status/pengiriman, COD dan histori transaksi.
- Integrasi checkout Xendit memakai Payment Session satu kali untuk checkout; notifikasi callback diverifikasi dengan token, dicocokkan ke ID sesi/referensi/jumlah/mata uang dan diproses idempoten. Keberhasilan akun/SMTP yang dilaporkan pemilik dicatat sebagai verifikasi manual, bukan pengujian eksternal yang dilakukan dokumen ini.
- Saldo penjual bersumber dari ledger setelah syarat pembayaran dan penyelesaian pesanan dipenuhi. Permintaan payout menahan saldo, menggunakan rekening payout terverifikasi, mencatat event dan memproses callback secara idempoten. Payout otomatis hanya berjalan jika kredensial/fitur payout benar-benar dikonfigurasi.
- Integrasi split XenPlatform tidak digunakan dalam alur checkout saat ini. Jangan menyamakan pembayaran masuk ke akun merchant dengan payout otomatis ke rekening masing-masing seller.

### 3.5 Administrasi, notifikasi, dan audit

- Dashboard dan navigasi berbasis hak akses, kotak masuk notifikasi in-app, tanda baca/aksi buka, laporan usaha berbasis biaya, log audit, halaman error ramah pengguna, dan header keamanan.

## 4. Alur bisnis utama

1. **Akun:** daftar → verifikasi email → aktivasi akun oleh petugas berwenang → profil warga/wilayah → layanan sesuai role.
2. **Surat mandiri:** warga memilih jenis surat → mengirim data/lampiran → tahapan pemeriksaan berwenang → revisi atau keputusan → dokumen keluaran.
3. **Surat dibantu:** staf membuat/menerima sesi bantuan → warga memberi informasi → staf menyiapkan draft atas nama pemohon → warga meninjau dan mengonfirmasi → workflow surat berlanjut.
4. **Pasar:** seller mengajukan diri → pengelola menyetujui → seller mengirim produk untuk moderasi → pembeli checkout → pembayaran/cod dicatat → order diteruskan → proses pemenuhan dan penerimaan → saldo seller dibukukan sesuai aturan transaksi.
5. **Payout seller:** rekening disimpan dan diverifikasi → seller meminta pencairan berdasarkan saldo ledger → saldo dicadangkan secara atomik → provider mengirim hasil → callback terverifikasi memperbarui status atau melepaskan cadangan bila gagal.
6. **Internet/Wi-Fi:** warga mengajukan layanan/tagihan atau sesi Wi-Fi; perubahan status dan pembayaran dicatat. Otorisasi router nyata belum tersedia.

## 5. Data dan privasi

- Data demografi warga dan telepon disimpan terenkripsi sesuai implementasi model/migration; NIK tidak termasuk kolom profil pada skema akhir.
- Lampiran persuratan dan foto produk menggunakan penyimpanan privat serta handler unduh berotorisasi.
- Rekening payout dienkripsi pada model; ledger, order, payment, dan withdrawal mempertahankan histori finansial dan menggunakan soft/hard-delete restrictions sesuai migration.
- Log audit tidak dimaksudkan menyimpan password, token, OTP, credential pembayaran, atau plaintext sensitif.
- Sesi browser dan CSRF melindungi alur web. Cookie keamanan, HTTPS, proxy, backup, dan retensi tetap harus disetel serta diuji sesuai deployment.

## 6. Batasan dan status integrasi

| Area | Status as-built |
|---|---|
| Database | Migration Laravel adalah sumber skema. Lingkungan pemilik pernah melaporkan MariaDB 13.0.2 melalui koneksi driver `mysql`; ini bukan verifikasi MySQL 8. |
| SMTP | Konfigurasi SMTP tersedia; pemilik melaporkan pengiriman/verifikasi berhasil. Tidak diuji dari proses dokumentasi ini. |
| Xendit checkout | Adapter Payment Session dan webhook tersedia. Kredensial, callback yang dapat dijangkau, mode, settlement, dan rekonsiliasi harus diverifikasi per lingkungan. |
| Seller payout | Alur ledger dan payout tersedia di kode; kesiapan payout provider bergantung konfigurasi dan status integrasi akun. |
| Wi-Fi gateway | Simulasi; tidak melakukan enforcement jaringan nyata. |
| Delivery/courier | Status pengiriman dikelola seller; tidak ada integrasi kurir.
| Signature | Workflow prototipe; tidak mengklaim tanda tangan tersertifikasi.
| Multi-tenant | Satu desa; bukan platform multi-village.

## 7. Kriteria operasional minimum

- Semua route mutasi memerlukan autentikasi/otorisasi yang sesuai dan validasi input.
- Kepemilikan warga, seller, order, file, dan jangkauan petugas diverifikasi server-side.
- Pembayaran dan payout tidak boleh dinyatakan berhasil hanya dari query string atau browser return; callback harus tervalidasi dan idempoten.
- Integrasi simulasi harus diberi label agar tidak disalahartikan sebagai layanan nyata.
- Production readiness membutuhkan uji browser/staging, database dan backup-restore pada lingkungan yang tepat, secrets/dependency scan, HTTPS/proxy/cookie verification, serta rekonsiliasi pembayaran.

## 8. Sumber as-built

Detail struktur data ada di [ERD_AS_BUILT.md](ERD_AS_BUILT.md). Daftar fitur dan route dapat berubah; jika dokumen berbeda dari kode, migration dan implementasi aktual menjadi objek audit yang harus diperbarui sebelum mengklaim perilaku baru.
