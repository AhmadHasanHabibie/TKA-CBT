<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Halaman Tidak Ditemukan · TKA CBT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: #0f172a;
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            overflow: hidden;
        }
        /* Orbs */
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; }
        .orb-1 { width: 500px; height: 500px; background: rgba(99,102,241,0.15); top: -150px; right: -150px; }
        .orb-2 { width: 350px; height: 350px; background: rgba(139,92,246,0.10); bottom: -100px; left: -100px; }

        .container {
            text-align: center;
            max-width: 520px;
            position: relative;
            z-index: 10;
            animation: slideUp 0.5s cubic-bezier(0.16,1,0.3,1);
        }
        .code {
            font-size: clamp(6rem, 18vw, 10rem);
            font-weight: 900;
            line-height: 1;
            background: linear-gradient(135deg, #6366f1, #a78bfa, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -4px;
            margin-bottom: 1.5rem;
        }
        .icon-wrap {
            width: 72px; height: 72px;
            background: rgba(99,102,241,0.15);
            border: 1px solid rgba(99,102,241,0.25);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem;
        }
        h1 {
            font-size: 1.5rem; font-weight: 800;
            color: #f1f5f9;
            margin-bottom: 0.75rem;
        }
        p {
            color: #94a3b8;
            font-size: 0.9rem;
            line-height: 1.7;
            margin-bottom: 2rem;
        }
        .actions { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.65rem 1.5rem;
            border-radius: 12px;
            font-size: 0.8rem; font-weight: 700;
            text-decoration: none;
            transition: all 0.15s ease;
            cursor: pointer; border: none;
        }
        .btn-primary {
            background: #6366f1;
            color: #fff;
            box-shadow: 0 4px 20px rgba(99,102,241,0.3);
        }
        .btn-primary:hover { background: #4f46e5; transform: translateY(-1px); box-shadow: 0 6px 24px rgba(99,102,241,0.4); }
        .btn-secondary {
            background: rgba(255,255,255,0.06);
            color: #cbd5e1;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .btn-secondary:hover { background: rgba(255,255,255,0.1); color: #f1f5f9; }
        .divider {
            margin: 2.5rem auto;
            width: 60px; height: 2px;
            background: linear-gradient(90deg, transparent, rgba(99,102,241,0.5), transparent);
        }
        @keyframes slideUp {
            from { opacity:0; transform: translateY(20px); }
            to   { opacity:1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="container">
        <div class="icon-wrap">
            <svg width="32" height="32" fill="none" stroke="#818cf8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <div class="code">404</div>

        <h1>Halaman Tidak Ditemukan</h1>
        <p>
            Halaman yang Anda cari tidak ada, sudah dipindahkan, atau mungkin URL yang Anda masukkan tidak tepat.
            Coba periksa kembali alamat yang Anda tuju.
        </p>

        <div class="divider"></div>

        <div class="actions">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="btn btn-secondary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
            <a href="{{ url('/') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Ke Beranda
            </a>
        </div>
    </div>
</body>
</html>
