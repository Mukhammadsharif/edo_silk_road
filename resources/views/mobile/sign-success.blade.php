{{-- resources/views/mobile/sign-success.blade.php --}}
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imzolandi</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        body { font-family: -apple-system, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f0f2f5; padding: 20px; }
        .box { background: #fff; border-radius: 16px; padding: 40px 24px; text-align: center; max-width: 360px; box-shadow: 0 2px 12px rgba(0,0,0,0.1); }
        .icon { font-size: 56px; margin-bottom: 16px; }
        .title { font-size: 20px; font-weight: 700; margin-bottom: 8px; color: #1a1a1a; }
        .sub { font-size: 14px; color: #888; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">✅</div>
        <div class="title">Muvaffaqiyatli imzolandi!</div>
        <div class="sub">Telegramga qaytishingiz mumkin</div>
    </div>
    <script>
        if (window.Telegram && window.Telegram.WebApp) {
            setTimeout(function() { window.Telegram.WebApp.close(); }, 2000);
        }
    </script>
</body>
</html>
