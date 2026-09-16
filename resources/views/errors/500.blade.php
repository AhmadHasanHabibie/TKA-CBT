<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Terjadi Kesalahan Server · TKA CBT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: #0f172a; color: #f1f5f9;
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 2rem; overflow: hidden;
        }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; }
        .orb-1 { width: 500px; height: 500px; background: rgba(239,68,68,0.15); top: -150px; right: -150px; }
        .orb-2 { width: 350px; height: 350px; background: rgba(168,85,247,0.10); bottom: -100px; left: -100px; }
        .container { text-align: center; max-width: 520px; position: relative; z-index: 10; animation: slideUp 0.5s cubic-bezier(0.16,1,0.3,1); }
        .code { font-size: clamp(6rem, 18vw, 10rem); font-weight: 900; line-height: 1; background: linear-gradient(135deg, #f43f5e, #e11d48, #be123c); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; letter-spacing: -4px; margin-bottom: 1.5rem; }
        .icon-wrap { width: 72px; height: 72px; background: rgba(244,63,94,0.12); border: 1px solid rgba(244,63,94,0.25); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; }
        h1 { font-size: 1.5rem; font-weight: 800; color: #f1f5f9; margin-bottom: 0.75rem; }
        p { color: #94a3b8; font-size: 0.9rem; line-height: 1.7; margin-bottom: 2rem; }
        .actions { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.5rem; border-radius: 12px; font-size: 0.8rem; font-weight: 700; text-decoration: none; transition: all 0.15s ease; cursor: pointer; border: none; }
        .btn-primary { background: #6366f1; color: #fff; box-shadow: 0 4px 20px rgba(99,102,241,0.3); }
        .btn-primary:hover { background: #4f46e5; transform: translateY(-1px); }
        .btn-secondary { background: rgba(255,255,255,0.06); color: #cbd5e1; border: 1px solid rgba(255,255,255,0.1); }
        .btn-secondary:hover { background: rgba(255,255,255,0.1); color: #f1f5f9; }
        .divider { margin: 2.5rem auto; width: 60px; height: 2px; background: linear-gradient(90deg, transparent, rgba(244,63,94,0.5), transparent); }
        @keyframes slideUp { from { opacity:0; transform: translateY(20px); } to { opacity:1; transform: translateY(0); } }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="container">
        <div class="icon-wrap">
            <svg width="32" height="32" fill="none" stroke="#f43f5e" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="code">500</div>
        <h1>Terjadi Kesalahan Server</h1>
        <p>Maaf, server kami mengalami kendala teknis saat memproses permintaan Anda. Tim kami telah mencatat masalah ini.</p>
        <div class="divider"></div>
        <div class="actions">
            <button onclick="window.location.reload()" class="btn btn-secondary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Muat Ulang
            </button>
            <a href="{{ url('/') }}" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Ke Beranda
            </a>
        </div>
    </div>
</body>
</html>
