<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#16a34a">
    <title>E-IMZO ga o'tish...</title>
    <style>
        :root {
            --bg:   var(--tg-theme-bg-color, #f5f7fb);
            --card: var(--tg-theme-secondary-bg-color, #ffffff);
            --text: var(--tg-theme-text-color, #0f172a);
            --hint: var(--tg-theme-hint-color, #64748b);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh; padding: 20px;
            display: flex; align-items: center; justify-content: center;
            color: var(--text);
            background:
                radial-gradient(900px 480px at 100% -20%, rgba(22, 163, 74, 0.08), transparent 70%),
                radial-gradient(700px 380px at 0% 100%, rgba(36, 129, 204, 0.07), transparent 70%),
                var(--bg);
        }
        .box {
            background: var(--card);
            border-radius: 22px;
            padding: 40px 24px;
            text-align: center;
            max-width: 360px; width: 100%;
            box-shadow: 0 4px 8px rgba(15, 23, 42, 0.06), 0 24px 48px -16px rgba(15, 23, 42, 0.18);
            animation: box-in 0.4s cubic-bezier(0.22, 1, 0.36, 1);
        }
        @keyframes box-in {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .icon-wrap {
            position: relative;
            width: 92px; height: 92px;
            margin: 0 auto 22px;
            display: flex; align-items: center; justify-content: center;
        }
        .icon-wrap::before {
            content: "";
            position: absolute; inset: 0;
            border-radius: 50%;
            background: conic-gradient(from 0deg, #16a34a, #2481cc, #8b5cf6, #16a34a);
            animation: spin 3.5s linear infinite;
            filter: blur(12px); opacity: 0.45;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .icon-circle {
            position: relative;
            width: 80px; height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 16px 40px -10px rgba(22, 163, 74, 0.55);
        }
        .icon-circle svg {
            width: 40px; height: 40px;
            stroke: #fff; stroke-width: 2.4; fill: none;
            stroke-linecap: round; stroke-linejoin: round;
        }

        .title { font-size: 20px; font-weight: 700; margin-bottom: 8px; letter-spacing: -0.01em; }
        .sub   { font-size: 14px; color: var(--hint); margin-bottom: 28px; line-height: 1.55; }

        .btn {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 16px;
            border-radius: 14px;
            font-size: 16px; font-weight: 700; letter-spacing: -0.01em;
            text-decoration: none;
            transition: transform 0.15s ease;
        }
        .btn:active { transform: scale(0.97); }
        .btn-open {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: #fff;
            box-shadow: 0 8px 24px -8px rgba(22, 163, 74, 0.55);
            margin-bottom: 12px;
        }
        .btn-back {
            background: rgba(148, 163, 184, 0.12);
            color: var(--hint);
        }
        .btn svg { width: 18px; height: 18px; }

        .note {
            font-size: 12px; color: var(--hint);
            margin-top: 18px; line-height: 1.5;
            opacity: 0.85;
        }
    </style>
</head>
<body>
<div class="box">
    <div class="icon-wrap">
        <div class="icon-circle">
            <svg viewBox="0 0 24 24">
                <rect x="5" y="2" width="14" height="20" rx="2"/>
                <line x1="12" y1="18" x2="12.01" y2="18"/>
            </svg>
        </div>
    </div>
    <div class="title">E-IMZO ID-CARD</div>
    <div class="sub">
        Hujjatni imzolash uchun E-IMZO ID-CARD ilovasini oching va ID-kartangizni telefonga qo'ying
    </div>

    <a href="{{ $url }}" id="btn-open" class="btn btn-open">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        E-IMZO ilovasini ochish
    </a>

    <a href="javascript:window.close()" class="btn btn-back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Orqaga qaytish
    </a>

    <div class="note">
        Agar ilova ochilmasa — E-IMZO ID-CARD o'rnatilganligini tekshiring
    </div>
</div>

<script>
    // Авто-открытие через 800ms после загрузки
    setTimeout(function() {
        window.location.href = '{{ $url }}';
    }, 800);
</script>
</body>
</html>
