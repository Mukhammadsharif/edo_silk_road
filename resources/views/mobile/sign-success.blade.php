{{-- resources/views/mobile/sign-success.blade.php --}}
    <!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#16a34a">
    <title>Imzolandi</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        :root {
            --bg:   var(--tg-theme-bg-color, #f5f7fb);
            --card: var(--tg-theme-secondary-bg-color, #ffffff);
            --text: var(--tg-theme-text-color, #0f172a);
            --hint: var(--tg-theme-hint-color, #94a3b8);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 20px;
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
        }
        .success-icon {
            width: 88px; height: 88px;
            border-radius: 50%;
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 16px 40px -10px rgba(22, 163, 74, 0.55);
            animation: success-pop 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        @keyframes success-pop {
            0%   { transform: scale(0);   opacity: 0; }
            60%  { transform: scale(1.15); opacity: 1; }
            100% { transform: scale(1);   opacity: 1; }
        }
        .success-icon svg { width: 48px; height: 48px; stroke: #fff; stroke-width: 4; fill: none; stroke-linecap: round; stroke-linejoin: round; }
        .success-icon svg path {
            stroke-dasharray: 60; stroke-dashoffset: 60;
            animation: draw-check 0.5s 0.3s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        @keyframes draw-check { to { stroke-dashoffset: 0; } }

        .title { font-size: 22px; font-weight: 700; margin-bottom: 8px; letter-spacing: -0.01em; opacity: 0; animation: fade-up 0.4s 0.5s ease forwards; }
        .sub   { font-size: 14px; color: var(--hint); opacity: 0; animation: fade-up 0.4s 0.6s ease forwards; }
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="box">
    <div class="success-icon">
        <svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
    </div>
    <div class="title">Muvaffaqiyatli imzolandi!</div>
    <div class="sub">Telegramga qaytishingiz mumkin</div>
</div>
<script>
    if (window.Telegram && window.Telegram.WebApp) {
        try { window.Telegram.WebApp.HapticFeedback.notificationOccurred('success'); } catch (e) {}
        setTimeout(function() { window.Telegram.WebApp.close(); }, 2000);
    }
</script>
</body>
</html>
