
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0A192F">
    <meta name="description" content="Portal akun untuk memulai akses layanan digital desa.">
    <title>Desa Digital · Portal layanan warga</title>
   <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        details > summary {
            list-style: none;
        }

        details > summary::-webkit-details-marker {
            display: none;
        }

        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body class="text-slate-900 antialiased overflow-x-hidden">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:bg-white focus:px-4 focus:py-3">Lewati ke konten</a>

    <header id="navbar" class="fixed top-0 left-0 right-0 sm:top-6 sm:left-8 sm:right-8 z-50 transition-all duration-300">
        <div id="nav-container" class="w-full sm:max-w-3xl mx-auto bg-white/10 backdrop-blur-md sm:border sm:border-white/20 shadow-xl rounded-none sm:rounded-2xl md:rounded-full transition-all duration-300 py-3.5 px-6 sm:px-8 flex items-center justify-between">
            <a href="{{ route('home') }}" id="nav-brand" class="font-extrabold tracking-tight text-lg text-white transition-colors duration-300" aria-label="Desa Digital, beranda">
                Desa Digital
            </a>

            <nav class="hidden sm:flex items-center gap-6" aria-label="Navigasi utama">
                <a href="#main" class="nav-link text-sm font-medium text-slate-200 hover:text-emerald-600 transition-colors">Tentang</a>
                <a href="#layanan" class="nav-link text-sm font-medium text-slate-200 hover:text-emerald-600 transition-colors">Layanan</a>
                <a href="#cara-akses" class="nav-link text-sm font-medium text-slate-200 hover:text-emerald-600 transition-colors">Panduan</a>
                <a href="{{ route('login') }}" class="text-sm font-bold bg-emerald-600 text-white px-5 py-2.5 rounded-full hover:bg-emerald-500 transition-all shadow-md">Masuk</a>
            </nav>

            <button id="hamburger-btn" class="sm:hidden text-white focus:outline-none p-1" aria-label="Menu">
                <svg id="hamburger-icon" class="w-6 h-6 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>

        <div id="mobile-menu" class="hidden sm:hidden bg-white/95 backdrop-blur-lg border-b border-slate-200 shadow-xl px-6 py-6 transition-all">
            <nav class="flex flex-col gap-4" aria-label="Navigasi mobile">
                <a href="#main" class="mobile-link text-slate-800 font-semibold text-base py-2 border-b border-slate-100">Tentang</a>
                <a href="#layanan" class="mobile-link text-slate-800 font-semibold text-base py-2 border-b border-slate-100">Layanan</a>
                <a href="#cara-akses" class="mobile-link text-slate-800 font-semibold text-base py-2 border-b border-slate-100">Panduan</a>
                <a href="{{ route('login') }}" class="mt-2 text-center text-sm font-bold bg-emerald-600 text-white px-5 py-3 rounded-xl hover:bg-emerald-500 transition-all shadow-md">Masuk</a>
            </nav>
        </div>
    </header>

    <main id="main" class="w-full">
        <section class="relative w-full h-screen min-h-[700px] overflow-hidden flex flex-col justify-center items-center text-center px-4 sm:px-8">
            <div class="absolute inset-0 z-0">
         <img src="{{ asset('hero.webp') }}"  alt="Hero Illustration Desa Digital" class="w-full h-full object-cover object-center">
                <div class="absolute inset-0 bg-gradient-to-b from-slate-950/70 via-slate-900/50 to-slate-950/80"></div>
            </div>

            <div class="relative z-10 w-full max-w-5xl mx-auto px-4 flex flex-col items-center mt-16">
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight text-white mb-6 leading-[1.15] drop-shadow-[0_10px_25px_rgba(0,0,0,0.8)]">
                    Mulai dari satu akun,<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-green-300 drop-shadow-[0_5px_15px_rgba(16,185,129,0.5)]">Akses seluruh desa.</span>
                </h1>

                <p class="text-base sm:text-xl text-slate-200 mb-10 max-w-2xl mx-auto font-normal leading-relaxed drop-shadow-[0_2px_8px_rgba(0,0,0,0.8)]">
                    Buat akun untuk memulai akses layanan digital desa. Peran dan wilayah tugas menentukan akses yang diberikan secara aman dan terstruktur.
                </p>

                <div class="flex flex-col sm:flex-row items-center gap-4">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 bg-emerald-600 text-white font-bold rounded-full hover:bg-emerald-500 transition-all shadow-[0_10px_25px_rgba(16,185,129,0.4)] hover:shadow-[0_15px_30px_rgba(16,185,129,0.6)] text-base">
                        Daftar Akun Warga
                    </a>

                    <a href="#cara-akses" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 bg-white/10 backdrop-blur-md text-white border border-white/20 font-bold rounded-full hover:bg-white/20 transition-all text-base">
                        Pelajari Panduan
                    </a>
                </div>
            </div>
        </section>

        <section id="layanan" class="relative z-20 bg-white py-24 px-6 sm:px-12 lg:px-20">
            <div class="max-w-[1400px] mx-auto">
                <div class="text-center mb-14 max-w-2xl mx-auto">
                    <span class="text-emerald-600 font-bold text-xs uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-full">Layanan</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-3">Layanan Terpadu</h2>
                    <p class="text-slate-600 mt-3 text-base">Pusat akses cepat untuk administrasi, pelaporan, dan pengelolaan data desa secara mandiri.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

{{-- MARKETPLACE DESA --}}
<a href="{{ route('marketplace.index') }}" class="group relative block h-[400px] rounded-3xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300">
    <img src="{{ asset('images/marketplace.png') }}" alt="Marketplace Desa" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-slate-900/60 group-hover:bg-slate-900/75 transition-colors duration-300"></div>

    <div class="absolute inset-0 p-8 flex flex-col justify-between text-white z-10">
        <div class="flex justify-end">
            <div class="p-3 bg-white/20 backdrop-blur-md rounded-2xl shadow-md border border-white/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l2.4 12.2a2 2 0 002 1.6h8.8a2 2 0 002-1.6L22 7H6M10 21a1 1 0 100-2 1 1 0 000 2zm9 0a1 1 0 100-2 1 1 0 000 2z"></path>
                </svg>
            </div>
        </div>

        <div>
            <h3 class="text-2xl font-bold mb-2">Marketplace Desa</h3>
            <p class="text-slate-200 text-sm mb-6">Platform jual beli produk dan hasil usaha warga desa untuk mendukung UMKM serta meningkatkan perekonomian lokal.</p>

            <div class="flex items-center gap-2 opacity-0 transform translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 text-emerald-400 font-bold bg-white/10 w-fit px-4 py-2 rounded-full backdrop-blur-sm border border-white/10">
                <span>Akses Layanan</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </div>
        </div>
    </div>
</a>


{{-- RT/RW NET --}}
<a href="{{ route('services.index') }}" class="group relative block h-[400px] rounded-3xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300">
    <img src="{{ asset('images/rtrw.png') }}" alt="Layanan RT/RW Net" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-slate-900/60 group-hover:bg-slate-900/75 transition-colors duration-300"></div>

    <div class="absolute inset-0 p-8 flex flex-col justify-between text-white z-10">
        <div class="flex justify-end">
            <div class="p-3 bg-white/20 backdrop-blur-md rounded-2xl shadow-md border border-white/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 8.82a15 15 0 0120 0M5 12.86a9 9 0 0114 0M8.5 16.43a4 4 0 017 0M12 20h.01"></path>
                </svg>
            </div>
        </div>

        <div>
            <h3 class="text-2xl font-bold mb-2">RT/RW Net</h3>
            <p class="text-slate-200 text-sm mb-6">Layanan internet warga dengan sistem pendaftaran pelanggan, pembelian voucher, pembayaran, dan pengelolaan akses jaringan.</p>

            <div class="flex items-center gap-2 opacity-0 transform translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 text-emerald-400 font-bold bg-white/10 w-fit px-4 py-2 rounded-full backdrop-blur-sm border border-white/10">
                <span>Akses Layanan</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </div>
        </div>
    </div>
</a>


{{-- ADMINISTRASI DESA --}}
<a href="{{ route('correspondence.index') }}" class="group relative block h-[400px] rounded-3xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300">
    <img src="{{ asset('images/administrasi.png') }}" alt="Administrasi Desa" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-slate-900/60 group-hover:bg-slate-900/75 transition-colors duration-300"></div>

    <div class="absolute inset-0 p-8 flex flex-col justify-between text-white z-10">
        <div class="flex justify-end">
            <div class="p-3 bg-white/20 backdrop-blur-md rounded-2xl shadow-md border border-white/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>

        <div>
            <h3 class="text-2xl font-bold mb-2">Administrasi Desa</h3>
            <p class="text-slate-200 text-sm mb-6">Pengajuan surat keterangan domisili, surat pengantar, dan berbagai dokumen resmi kependudukan secara digital.</p>

            <div class="flex items-center gap-2 opacity-0 transform translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 text-emerald-400 font-bold bg-white/10 w-fit px-4 py-2 rounded-full backdrop-blur-sm border border-white/10">
                <span>Akses Layanan</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </div>
        </div>
    </div>
</a>

                </div>
            </div>
        </section>

        <section id="cara-akses" class="relative bg-slate-50 py-24 px-6 sm:px-12 lg:px-20 border-y border-slate-200/60">
            <div class="max-w-[1400px] mx-auto">
                <div class="text-center mb-16 max-w-2xl mx-auto">
                    <span class="text-emerald-600 font-bold text-xs uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-full">Panduan</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-3">Tahapan Akses Akun</h2>
                </div>

                <div class="space-y-20 max-w-5xl mx-auto relative">
                    <div class="hidden lg:block absolute left-1/2 top-10 bottom-10 w-0.5 border-l-2 border-dashed border-slate-300 transform -translate-x-1/2 z-0"></div>

                 <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center relative z-10">
    <div class="lg:pr-12 text-center lg:text-right">
        <div class="inline-flex items-center justify-center w-12 h-12 bg-white border-2 border-emerald-600 text-emerald-600 font-bold text-lg rounded-full shadow-md mb-4 lg:absolute lg:left-1/2 lg:-translate-x-1/2 lg:mt-2">
            1
        </div>

        <h3 class="text-2xl font-bold text-slate-900 mb-3">Pendaftaran Mandiri</h3>

        <p class="text-slate-600 text-base leading-relaxed mb-6">
            Pendaftar secara otomatis mendapat peran Warga. Pilihan peran administratif yang spesifik tidak tersedia secara bebas di formulir publik demi keamanan data desa.
        </p>

        <a href="{{ route('register') }}" class="inline-flex items-center text-sm font-bold text-emerald-600 hover:text-emerald-700">
            Lihat Formulir &rarr;
        </a>
    </div>

    <div class="flex justify-center lg:justify-start lg:pl-12">
        <img
            src="{{ asset('images/pendaftaran2.png') }}"
            alt="Ilustrasi Pendaftaran Mandiri"
            class="rounded-2xl w-full max-w-xs object-cover aspect-[4/3] shadow-md border border-slate-200"
        >
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center relative z-10">
    <div class="order-2 lg:order-1 flex justify-center lg:justify-end lg:pr-12">
        <img
            src="{{ asset('images/aktivasi.png') }}"
            alt="Ilustrasi Menunggu Aktivasi"
            class="rounded-2xl w-full max-w-xs object-cover aspect-[4/3] shadow-md border border-slate-200"
        >
    </div>

    <div class="order-1 lg:order-2 lg:pl-12 text-center lg:text-left">
        <div class="inline-flex items-center justify-center w-12 h-12 bg-white border-2 border-emerald-600 text-emerald-600 font-bold text-lg rounded-full shadow-md mb-4 lg:absolute lg:left-1/2 lg:-translate-x-1/2 lg:mt-2">
            2
        </div>

        <h3 class="text-2xl font-bold text-slate-900 mb-3">Menunggu Aktivasi</h3>

        <p class="text-slate-600 text-base leading-relaxed mb-6">
            Akun baru yang Anda buat belum dapat langsung digunakan. Proses verifikasi identitas warga diperlukan dari petugas desa setempat sebelum fitur penuh terbuka.
        </p>

        <a href="{{ route('login') }}" class="inline-flex items-center text-sm font-bold text-emerald-600 hover:text-emerald-700">
            Cek Status &rarr;
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center relative z-10">
    <div class="lg:pr-12 text-center lg:text-right">
        <div class="inline-flex items-center justify-center w-12 h-12 bg-white border-2 border-emerald-600 text-emerald-600 font-bold text-lg rounded-full shadow-md mb-4 lg:absolute lg:left-1/2 lg:-translate-x-1/2 lg:mt-2">
            3
        </div>

        <h3 class="text-2xl font-bold text-slate-900 mb-3">Masuk dengan Aman</h3>

        <p class="text-slate-600 text-base leading-relaxed mb-6">
            Gunakan nama pengguna atau email serta kata sandi yang Anda buat. Sistem kami telah dilindungi enkripsi terpadu untuk menjaga kerahasiaan data pribadi Anda.
        </p>

        <a href="{{ route('login') }}" class="inline-flex items-center text-sm font-bold text-emerald-600 hover:text-emerald-700">
            Masuk Sekarang &rarr;
        </a>
    </div>

    <div class="flex justify-center lg:justify-start lg:pl-12">
        <img
            src="{{ asset('images/login.png') }}"
            alt="Ilustrasi Masuk dengan Aman"
            class="rounded-2xl w-full max-w-xs object-cover aspect-[4/3] shadow-md border border-slate-200"
        >
    </div>
</div>
                </div>
            </div>
        </section>

        <section class="relative bg-white py-24 px-6 sm:px-12 lg:px-20">
            <div class="max-w-[1400px] mx-auto">
                <div class="max-w-2xl mx-auto text-center mb-10">
                    <span class="text-emerald-600 font-bold text-xs uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-full">Lokasi</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-3">Peta Ekosistem Digital</h2>
                    <p class="text-slate-600 text-base mt-2">Jelajahi lokasi pusat layanan publik dan sentra administrasi di wilayah Anda.</p>
                </div>

                <div class="relative w-full h-[450px] bg-slate-100 rounded-3xl overflow-hidden shadow-md border border-slate-200">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126920.24009669642!2d106.75883832145322!3d-6.229746522501683!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f3e945e34b9d%3A0x5371bf0fdad786a2!2sJakarta%2C%20Daerah%20Khusus%20Ibukota%20Jakarta!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </section>

        <section class="relative bg-slate-50 py-24 px-6 sm:px-12 lg:px-20 border-t border-slate-200/60">
            <div class="max-w-3xl mx-auto">
                <div class="text-center mb-12">
                    <span class="text-emerald-600 font-bold text-xs uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-full">Bantuan</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-3">
                        Frequently Asked Questions
                    </h2>
                    <p class="text-slate-600 mt-3 text-base">Eksplorasi koleksi pertanyaan yang sering diajukan untuk menemukan jawaban dan solusi penggunaan.</p>
                </div>

                <div class="divide-y divide-slate-200">
                    <details class="group py-6 cursor-pointer" open>
                        <summary class="flex justify-between items-center font-bold text-lg text-slate-900 focus:outline-none hover:text-emerald-600 transition-colors">
                            <span class="flex items-center gap-3">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                Siapa saja yang dapat mendaftar akun?
                            </span>
                            <span class="text-emerald-600 group-open:rotate-45 transition-transform text-2xl font-light">+</span>
                        </summary>
                        <div class="text-slate-600 pt-3 pl-5 text-base leading-relaxed">
                            Seluruh warga yang terdaftar dalam administrasi desa dapat membuat akun. Pendaftaran terbuka secara publik, namun setiap akun baru akan melalui tahap peninjauan dan aktivasi oleh admin desa.
                        </div>
                    </details>

                    <details class="group py-6 cursor-pointer">
                        <summary class="flex justify-between items-center font-bold text-lg text-slate-900 focus:outline-none hover:text-emerald-600 transition-colors">
                            <span class="flex items-center gap-3">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                Berapa lama proses aktivasi akun?
                            </span>
                            <span class="text-emerald-600 group-open:rotate-45 transition-transform text-2xl font-light">+</span>
                        </summary>
                        <div class="text-slate-600 pt-3 pl-5 text-base leading-relaxed">
                            Proses aktivasi biasanya memakan waktu 1x24 jam pada hari kerja. Pastikan data yang Anda masukkan sesuai dengan kartu identitas untuk mempercepat verifikasi.
                        </div>
                    </details>

                    <details class="group py-6 cursor-pointer">
                        <summary class="flex justify-between items-center font-bold text-lg text-slate-900 focus:outline-none hover:text-emerald-600 transition-colors">
                            <span class="flex items-center gap-3">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                Apakah satu akun bisa memiliki akses petugas RT/RW?
                            </span>
                            <span class="text-emerald-600 group-open:rotate-45 transition-transform text-2xl font-light">+</span>
                        </summary>
                        <div class="text-slate-600 pt-3 pl-5 text-base leading-relaxed">
                            Satu akun dapat memiliki lebih dari satu peran. Namun, peran administratif seperti petugas RT atau RW ditambahkan secara manual oleh pengurus desa berdasarkan penugasan wilayah.
                        </div>
                    </details>

                    <details class="group py-6 cursor-pointer">
                        <summary class="flex justify-between items-center font-bold text-lg text-slate-900 focus:outline-none hover:text-emerald-600 transition-colors">
                            <span class="flex items-center gap-3">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                Bagaimana jika saya lupa kata sandi akun?
                            </span>
                            <span class="text-emerald-600 group-open:rotate-45 transition-transform text-2xl font-light">+</span>
                        </summary>
                        <div class="text-slate-600 pt-3 pl-5 text-base leading-relaxed">
                            Anda dapat menggunakan fitur pemulihan kata sandi di halaman masuk dengan memasukkan email terdaftar, atau menghubungi admin desa untuk reset manual.
                        </div>
                    </details>

                    <details class="group py-6 cursor-pointer">
                        <summary class="flex justify-between items-center font-bold text-lg text-slate-900 focus:outline-none hover:text-emerald-600 transition-colors">
                            <span class="flex items-center gap-3">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                Apakah data pribadi warga dijamin kerahasiaannya?
                            </span>
                            <span class="text-emerald-600 group-open:rotate-45 transition-transform text-2xl font-light">+</span>
                        </summary>
                        <div class="text-slate-600 pt-3 pl-5 text-base leading-relaxed">
                            Ya, seluruh data warga dilindungi dengan enkripsi ketat dan hanya digunakan untuk keperluan layanan internal administrasi desa resmi.
                        </div>
                    </details>

                    <details class="group py-6 cursor-pointer">
                        <summary class="flex justify-between items-center font-bold text-lg text-slate-900 focus:outline-none hover:text-emerald-600 transition-colors">
                            <span class="flex items-center gap-3">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                Bisakah mengakses portal melalui smartphone?
                            </span>
                            <span class="text-emerald-600 group-open:rotate-45 transition-transform text-2xl font-light">+</span>
                        </summary>
                        <div class="text-slate-600 pt-3 pl-5 text-base leading-relaxed">
                            Tentu, portal ini dirancang responsif dan dapat diakses dengan mudah melalui browser handphone manapun tanpa perlu instalasi aplikasi tambahan.
                        </div>
                    </details>
                </div>
            </div>
        </section>

        <section class="relative py-28 px-6 sm:px-12 lg:px-20 text-center overflow-hidden">
            <div class="absolute inset-0 z-0">
                <img src="{{ asset('images/desa.png') }}" alt="Background Desa" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-slate-950/65"></div>
            </div>

            <div class="relative z-10 max-w-2xl mx-auto">
                <h2 class="text-3xl sm:text-5xl font-black text-white mb-4 drop-shadow-[0_4px_12px_rgba(0,0,0,0.8)]">Siap membangun desa?</h2>
                <p class="text-slate-300 text-base sm:text-lg mb-8 drop-shadow-[0_2px_6px_rgba(0,0,0,0.8)]">Isi data akun Anda sekarang dan nikmati kemudahan akses administratif.</p>
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-9 py-4 bg-emerald-600 text-white font-bold rounded-full hover:bg-emerald-500 transition-all shadow-[0_10px_25px_rgba(16,185,129,0.6)] hover:shadow-[0_15px_35px_rgba(16,185,129,0.8)] text-base">
                    Daftar Akun Warga
                </a>
            </div>
        </section>
    </main>

    <footer class="bg-white border-t border-slate-200 text-slate-800 pt-12 pb-8 px-6 sm:px-12">
        <div class="max-w-[1400px] mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
            <a href="{{ route('home') }}" class="font-extrabold text-slate-900 text-xl tracking-tight">
                Desa Digital
            </a>

            <nav class="flex flex-wrap justify-center gap-x-8 gap-y-4 text-slate-600 font-medium text-sm" aria-label="Tautan footer">
                <a href="{{ route('register') }}" class="hover:text-emerald-600 transition-colors">Daftar</a>
                <a href="{{ route('login') }}" class="hover:text-emerald-600 transition-colors">Masuk</a>
                <a href="#cara-akses" class="hover:text-emerald-600 transition-colors">Cara Akses</a>
            </nav>

            <p class="text-xs text-slate-500">&copy; 2026 Portal layanan digital warga. Hak cipta dilindungi.</p>
        </div>
    </footer>

    <script>
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const navbar = document.getElementById('nav-container');
        const navBrand = document.getElementById('nav-brand');
        const navLinks = document.querySelectorAll('.nav-link');
        const hamburgerIcon = document.getElementById('hamburger-icon');

        hamburgerBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        document.querySelectorAll('.mobile-link').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
            });
        });

        window.addEventListener('scroll', function() {
            if (window.scrollY > window.innerHeight - 100) {
                navbar.classList.remove('bg-white/10', 'border-white/20');
                navbar.classList.add('bg-white/95', 'border-slate-200', 'shadow-2xl');
                navBrand.classList.remove('text-white');
                navBrand.classList.add('text-slate-900');
                hamburgerIcon.classList.remove('text-white');
                hamburgerIcon.classList.add('text-slate-900');
                navLinks.forEach(link => {
                    link.classList.remove('text-slate-200');
                    link.classList.add('text-slate-700');
                });
            } else {
                navbar.classList.add('bg-white/10', 'border-white/20');
                navbar.classList.remove('bg-white/95', 'border-slate-200', 'shadow-2xl');
                navBrand.classList.add('text-white');
                navBrand.classList.remove('text-slate-900');
                hamburgerIcon.classList.add('text-white');
                hamburgerIcon.classList.remove('text-slate-900');
                navLinks.forEach(link => {
                    link.classList.add('text-slate-200');
                    link.classList.remove('text-slate-700');
                });
            }
        });
    </script>
</body>
</html>
