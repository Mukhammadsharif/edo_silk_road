<!DOCTYPE html>
<html lang="ru">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; color:#1f2937; line-height:1.5;">
<div style="max-width:560px; margin:0 auto; padding:24px;">
    <h2 style="color:#794DFD;">Токен интеграции с 1С</h2>

    @if($orgName)
        <p>Организация: <strong>{{ $orgName }}</strong></p>
    @endif

    <p>Используйте этот токен в 1С для отправки данных в систему EDOLINE.
        Передавайте его в заголовке <code>X-1C-Token</code>.</p>

    <div style="background:#f3f4f6; border:1px solid #e5e7eb; border-radius:8px;
                    padding:16px; font-family:monospace; font-size:14px; word-break:break-all;
                    margin:16px 0;">
        {{ $token }}
    </div>

    <p style="color:#dc2626;"><strong>Внимание:</strong> токен действует до
        <strong>{{ \Carbon\Carbon::parse($expiresAt)->format('d.m.Y H:i') }}</strong>
        (1 час). После истечения сгенерируйте новый в кабинете.</p>

    <hr style="border:none; border-top:1px solid #e5e7eb; margin:24px 0;">
    <p style="font-size:12px; color:#6b7280;">
        Это автоматическое письмо EDOLINE. Если вы не запрашивали токен — проигнорируйте письмо.
    </p>
</div>
</body>
</html>
