<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-IMZO ga o'tish...</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .box {
            background: #fff;
            border-radius: 20px;
            padding: 40px 24px;
            text-align: center;
            max-width: 360px;
            width: 100%;
            box-shadow: 0 4px 24px rgba(0,0,0,0.1);
        }
        .icon  { font-size: 56px; margin-bottom: 16px; }
        .title { font-size: 20px; font-weight: 700; margin-bottom: 8px; color: #1a1a1a; }
        .sub   { font-size: 14px; color: #888; margin-bottom: 28px; line-height: 1.5; }
        .btn-open {
            display: block; width: 100%; padding: 16px;
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: #fff; border-radius: 14px;
            font-size: 16px; font-weight: 700;
            text-decoration: none; margin-bottom: 12px;
        }
        .btn-back {
            display: block; width: 100%; padding: 14px;
            background: #f0f2f5; color: #555;
            border-radius: 14px; font-size: 14px;
            text-decoration: none;
        }
        .note {
            font-size: 12px; color: #aaa;
            margin-top: 16px; line-height: 1.5;
        }
    </style>
</head>
<body>
<div class="box">
    <div class="icon">📱</div>
    <div class="title">E-IMZO ID-CARD</div>
    <div class="sub">
        Hujjatni imzolash uchun E-IMZO ID-CARD ilovasini oching va ID-kartangizni telefonga qo'ying
    </div>

    <a href="{{ $url }}" id="btn-open" class="btn-open">
        📲 E-IMZO ilovasini ochish
    </a>

    <a href="javascript:window.close()" class="btn-back">
        ← Orqaga qaytish
    </a>

    <div class="note">
        Agar ilova ochilmasa — E-IMZO ID-CARD o'rnatilganligini tekshiring
    </div>
</div>

<script>
    // Авто-открытие через 800ms после загрузки страницы
    // В браузере (Safari/Chrome) eimzo:// сработает
    setTimeout(function() {
        window.location.href = '{{ $url }}';
    }, 800);
</script>
</body>
</html>
