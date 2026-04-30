{{-- resources/views/mobile/sign-error.blade.php --}}
    <!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ef4444">
    <title>Xatolik</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        :root {
            --bg:   var(--tg-theme-bg-color, #f5f7fb);
            --card: var(--tg-theme-secondary-bg-color, #ffffff);
            --text: var(--tg-theme-text-color, #0f172a);
            --hint: var(--tg-theme-hint-color, #64748b);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 20px;
            color: var(--text);
            background:
                radial-gradient(900px 480px at 100% -20%, rgba(239, 68, 68, 0.07), transparent 70%),
                radial-gradient(700px 380px at 0% 100%, rgba(245, 158, 11, 0.07), transparent 70%),
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

        .err-icon {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 22px;
            box-shadow: 0 16px 40px -10px rgba(239, 68, 68, 0.45);
            animation: err-pop 0.5s cubic-bezier(0.22, 1, 0.36, 1);
        }
        @keyframes err-pop {
            0%   { transform: scale(0);    opacity: 0; }
            60%  { transform: scale(1.12); opacity: 1; }
            100% { transform: scale(1);    opacity: 1; }
        }
        .err-icon svg {
            width: 44px; height: 44px;
            stroke: #fff; stroke-width: 3.5; fill: none;
            stroke-linecap: round; stroke-linejoin: round;
        }

        .title { font-size: 20px; font-weight: 700; margin-bottom: 10px; letter-spacing: -0.01em; }
        .msg   { font-size: 14px; color: var(--hint); line-height: 1.55; }
    </style>
</head>
<body>
<div class="box">
    <div class="err-icon">
        <svg viewBox="0 0 24 24">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
    </div>
    <div class="title">Xatolik</div>
    <div class="msg">{{ $message ?? 'Noma\'lum xatolik yuz berdi.' }}</div>
</div>
<script>
    if (window.Telegram && window.Telegram.WebApp) {
        window.Telegram.WebApp.ready();
        try { window.Telegram.WebApp.HapticFeedback.notificationOccurred('error'); } catch (e) {}
    }
</script>
</body>
</html>
