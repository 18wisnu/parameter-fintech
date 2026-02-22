<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Parameter Fintech – Sistem Manajemen Keuangan</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Outfit', sans-serif;
            background: #f0f7ff;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── Animated background ── */
        .bg-animated {
            position: fixed; inset: 0; z-index: 0;
            background: linear-gradient(135deg, #0369a1 0%, #0284c7 35%, #0ea5e9 65%, #38bdf8 100%);
            overflow: hidden;
        }
        .bg-animated::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(ellipse at 20% 50%, rgba(255,255,255,0.15) 0%, transparent 60%),
                        radial-gradient(ellipse at 80% 20%, rgba(255,255,255,0.10) 0%, transparent 50%);
            animation: shimmer 8s ease-in-out infinite alternate;
        }
        @keyframes shimmer {
            0%   { opacity: 0.6; transform: scale(1); }
            100% { opacity: 1;   transform: scale(1.05); }
        }

        /* Floating blobs */
        .blob {
            position: absolute; border-radius: 50%; filter: blur(60px); opacity: 0.25;
            animation: float 12s ease-in-out infinite;
        }
        .blob-1 { width: 500px; height: 500px; background: #bae6fd; top: -100px; left: -150px; animation-duration: 14s; }
        .blob-2 { width: 350px; height: 350px; background: #e0f2fe; bottom: -80px; right: -80px; animation-duration: 11s; animation-delay: -4s; }
        .blob-3 { width: 250px; height: 250px; background: #f0f9ff; top: 40%; left: 60%; animation-duration: 9s; animation-delay: -7s; }
        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50%       { transform: translateY(-30px) scale(1.05); }
        }

        /* ── Layout ── */
        .page-wrapper {
            position: relative; z-index: 1;
            min-height: 100vh;
            display: flex; flex-direction: column;
        }

        /* Top Navigation */
        .top-nav {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.25rem 2.5rem;
        }
        .brand {
            display: flex; align-items: center; gap: 0.75rem;
            text-decoration: none;
        }
        .brand-icon {
            width: 42px; height: 42px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }
        .brand-icon svg { width: 22px; height: 22px; color: white; }
        .brand-name { font-size: 1.15rem; font-weight: 700; color: white; letter-spacing: 0.03em; }
        .brand-sub { font-size: 0.65rem; color: rgba(255,255,255,0.75); font-weight: 400; letter-spacing: 0.05em; display: block; }

        .nav-buttons { display: flex; gap: 0.75rem; align-items: center; }
        .btn-outline-white {
            padding: 0.5rem 1.25rem;
            border: 1.5px solid rgba(255,255,255,0.5);
            border-radius: 8px;
            color: white; font-family: 'Outfit', sans-serif; font-size: 0.9rem; font-weight: 500;
            background: transparent; cursor: pointer; text-decoration: none;
            transition: all 0.2s;
        }
        .btn-outline-white:hover {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.8);
        }
        .btn-solid-white {
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            color: #0369a1; font-family: 'Outfit', sans-serif; font-size: 0.9rem; font-weight: 700;
            background: white; cursor: pointer; text-decoration: none;
            border: 1.5px solid white;
            transition: all 0.2s;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
        }
        .btn-solid-white:hover { background: #f0f9ff; transform: translateY(-1px); }

        /* ── Hero Section ── */
        .hero {
            flex: 1;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-align: center;
            padding: 3rem 2rem 5rem;
        }

        .hero-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 999px;
            padding: 0.35rem 1rem;
            color: white; font-size: 0.8rem; font-weight: 500;
            margin-bottom: 1.75rem;
            backdrop-filter: blur(10px);
            animation: fadeInUp 0.6s ease both;
        }
        .hero-badge-dot { width: 7px; height: 7px; background: #86efac; border-radius: 50%; animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

        .hero-title {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 800;
            color: white;
            line-height: 1.1;
            letter-spacing: -0.02em;
            margin-bottom: 1.25rem;
            animation: fadeInUp 0.7s 0.1s ease both;
        }
        .hero-title span {
            background: linear-gradient(135deg, #fef08a, #fde047);
            -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: clamp(1rem, 2.5vw, 1.2rem);
            color: rgba(255,255,255,0.85);
            max-width: 560px; line-height: 1.65; margin-bottom: 2.5rem;
            animation: fadeInUp 0.7s 0.2s ease both;
        }

        .hero-cta {
            display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;
            animation: fadeInUp 0.7s 0.3s ease both;
        }
        .btn-cta-primary {
            padding: 0.9rem 2.25rem;
            background: white;
            color: #0369a1; font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 700;
            border-radius: 12px; border: none; cursor: pointer; text-decoration: none;
            box-shadow: 0 4px 24px rgba(0,0,0,0.2);
            transition: all 0.25s;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .btn-cta-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(0,0,0,0.25); }
        .btn-cta-secondary {
            padding: 0.9rem 2.25rem;
            background: rgba(255,255,255,0.12);
            color: white; font-family: 'Outfit', sans-serif; font-size: 1rem; font-weight: 600;
            border-radius: 12px;
            border: 1.5px solid rgba(255,255,255,0.4);
            cursor: pointer; text-decoration: none;
            backdrop-filter: blur(10px);
            transition: all 0.25s;
        }
        .btn-cta-secondary:hover { background: rgba(255,255,255,0.2); transform: translateY(-2px); }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Feature Cards ── */
        .features-section {
            position: relative; z-index: 1;
            background: white;
            border-radius: 2rem 2rem 0 0;
            padding: 3rem 2rem 4rem;
            margin-top: -2rem;
        }
        .features-title {
            text-align: center; font-size: 1.6rem; font-weight: 700; color: #0c4a6e; margin-bottom: 2rem;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            max-width: 1000px; margin: 0 auto;
        }
        .feature-card {
            background: #f0f9ff;
            border: 1px solid #e0f2fe;
            border-radius: 1.25rem;
            padding: 1.75rem 1.5rem;
            display: flex; flex-direction: column; gap: 0.75rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .feature-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(3,105,161,0.1); }
        .feature-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, #0369a1, #0ea5e9);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .feature-icon svg { width: 24px; height: 24px; color: white; }
        .feature-name { font-size: 1rem; font-weight: 700; color: #0c4a6e; }
        .feature-desc { font-size: 0.88rem; color: #64748b; line-height: 1.5; }

        /* Footer */
        .footer-bar {
            position: relative; z-index: 1;
            text-align: center;
            padding: 1.25rem;
            background: white;
            color: #94a3b8; font-size: 0.78rem;
            border-top: 1px solid #e2e8f0;
        }

        /* Mobile */
        @media (max-width: 640px) {
            .top-nav { padding: 1rem 1.25rem; }
            .brand-name { font-size: 1rem; }
            .btn-outline-white, .btn-solid-white { padding: 0.45rem 0.9rem; font-size: 0.82rem; }
            .hero { padding: 2rem 1.25rem 4rem; }
            .features-section { padding: 2rem 1.25rem 3rem; }
        }
    </style>
</head>
<body>

<div class="bg-animated">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
</div>

<div class="page-wrapper">

    <!-- Top Navigation -->
    <nav class="top-nav">
        <a href="/" class="brand">
            <div class="brand-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <span class="brand-name">PARAMETER</span>
                <span class="brand-sub">FINTECH SYSTEM</span>
            </div>
        </a>

        <div class="nav-buttons">
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-solid-white">Ke Dashboard</a>
            @else
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="btn-outline-white">Masuk</a>
                @endif
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-solid-white">Daftar</a>
                @endif
            @endauth
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-badge">
            <span class="hero-badge-dot"></span>
            Sistem Manajemen Keuangan Modern
        </div>

        <h1 class="hero-title">
            Kelola Keuangan<br>
            <span>Lebih Cerdas & Efisien</span>
        </h1>

        <p class="hero-subtitle">
            Parameter Fintech hadir sebagai solusi sistem manajemen keuangan terpadu untuk bisnis internet service provider Anda. Pantau pemasukan, pengeluaran, laporan bagi hasil, dan data pelanggan dalam satu platform.
        </p>

        <div class="hero-cta">
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-cta-primary">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Buka Dashboard
                </a>
            @else
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="btn-cta-primary">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Masuk Sekarang
                    </a>
                @endif
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-cta-secondary">Buat Akun Gratis</a>
                @endif
            @endauth
        </div>
    </section>

    <!-- Features -->
    <section class="features-section">
        <h2 class="features-title">Fitur Unggulan Platform</h2>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="feature-name">Data Pelanggan</div>
                <div class="feature-desc">Kelola seluruh data pelanggan ISP Anda secara terpusat, lengkap dengan riwayat pembayaran.</div>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="feature-name">Laporan Bagi Hasil</div>
                <div class="feature-desc">Hitung dan cetak laporan bagi hasil secara otomatis dengan format yang profesional.</div>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="feature-name">Setoran & Keuangan</div>
                <div class="feature-desc">Catat setoran harian dari kolektor lapangan dan pantau arus kas secara real-time.</div>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="feature-name">Invoice & Slip Gaji</div>
                <div class="feature-desc">Generate invoice pelanggan dan slip gaji teknisi secara otomatis dengan satu klik.</div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-bar">
        &copy; {{ date('Y') }} Parameter Fintech System &mdash; Hak cipta dilindungi undang-undang.
    </footer>

</div>

</body>
</html>
