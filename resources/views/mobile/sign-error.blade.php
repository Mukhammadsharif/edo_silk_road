{{-- resources/views/mobile/sign-error.blade.php --}}
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xatolik</title>
    <style>
        body { font-family: -apple-system, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f0f2f5; padding: 20px; }
        .box { background: #fff; border-radius: 16px; padding: 32px 24px; text-align: center; max-width: 360px; box-shadow: 0 2px 12px rgba(0,0,0,0.1); }
        .icon { font-size: 48px; margin-bottom: 16px; }
        .title { font-size: 18px; font-weight: 700; margin-bottom: 10px; color: #1a1a1a; }
        .msg { font-size: 14px; color: #666; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">⚠️</div>
        <div class="title">Xatolik</div>
        <div class="msg">{{ $message ?? 'Noma\'lum xatolik yuz berdi.' }}</div>
    </div>
</body>
</html>
