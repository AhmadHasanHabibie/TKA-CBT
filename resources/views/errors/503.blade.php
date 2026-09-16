<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 — Pemeliharaan Sistem · TKA CBT</title>
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
        .orb-1 { width: 500px; height: 500px; background: rgba(99,102,241,0.15); top: -150px; right: -150px; }
        .orb-2 { width: 350px; height: 350px; background: rgba(56,189,248,0.10); bottom: -100px; left: -100px; }
        .container { text-align: center; max-width: 520px; position: relative; z-index: 10; animation: slideUp 0.5s cubic-bezier(0.16,1,0.3,1); }
        .code { font-size: clamp(6rem, 18vw, 10rem); font-weight: 900; line-height: 1; background: linear-gradient(135deg, #38bdf8, #818cf8, #c084fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; letter-spacing: -4px; margin-bottom: 1.5rem; }
        .icon-wrap { width: 72px; height: 72px; background: rgba(56,189,248,0.12); border: 1px solid rgba(56,189,248,0.25); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; }
        h1 { font-size: 1.5rem; font-weight: 800; color: #f1f5f9; margin-bottom: 0.75rem; }
        p { color: #94a3b8; font-size: 0.9rem; line-height: 1.7; margin-bottom: 2rem; }
        .actions { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.5rem; border-radius: 12px; font-size: 0.8rem; font-weight: 700; text-decoration: none; transition: all 0.15s ease; cursor: pointer; border: none; }
        .btn-primary { background: #6366f1; color: #fff; box-shadow: 0 4px 20px rgba(99,102,241,0.3); }
        .btn-primary:hover { background: #4f46e5; transform: translateY(-1px); }
        .divider { margin: 2.5rem auto; width: 60px; height: 2px; background: linear-gradient(90deg, transparent, rgba(56,189,248,0.5), transparent); }
        @keyframes slideUp { from { opacity:0; transform: translateY(20px); } to { opacity:1; transform: translateY(0); } }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="container">
        <div class="icon-wrap">
            <svg width="32" height="32" fill="none" stroke="#38bdf8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div class="code">503</div>
        <h1>Sedang Dalam Pemeliharaan</h1>
        <p>Sistem sedang ditingkatkan untuk memberikan performa yang lebih optimal. Kami akan segera kembali dalam beberapa saat.</p>
        <div class="divider"></div>
        <div class="actions">
            <button onclick="window.location.reload()" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Coba Lagi
            </button>
        </div>
    </div>
</body>
</html>
