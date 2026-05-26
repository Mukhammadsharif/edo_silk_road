<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TelegramMiniAppToken extends Model
{
    protected $fillable = [
        'user_id',
        'telegram_user_id',
        'auth_token',
        'sign_token',
        'sign_doc_id',
        'sign_doc_type',
        'auth_token_expires_at',
        'sign_token_expires_at',
    ];

    protected $casts = [
        'auth_token_expires_at'  => 'datetime',
        'sign_token_expires_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Создать или обновить auth token для пользователя
     */
    public static function createAuthToken(int $userId, string $telegramUserId): self
    {
        // Удаляем старые токены этого пользователя
        self::where('telegram_user_id', $telegramUserId)->delete();

        return self::create([
            'user_id'                => $userId,
            'telegram_user_id'       => $telegramUserId,
            'auth_token'             => Str::random(64),
            'auth_token_expires_at'  => now()->addHours(24),
        ]);
    }

    /**
     * Создать one-time sign token (15 минут)
     */
    public function createSignToken(int $docId, string $docType): string
    {
        $token = Str::random(64);
        $this->update([
            'sign_token'             => $token,
            'sign_doc_id'            => $docId,
            'sign_doc_type'          => $docType,
            'sign_token_expires_at'  => now()->addMinutes(15),
        ]);
        return $token;
    }

    public function isAuthValid(): bool
    {
        return $this->auth_token_expires_at->isFuture();
    }

    public function isSignTokenValid(): bool
    {
        return $this->sign_token && $this->sign_token_expires_at?->isFuture();
    }

    /**
     * Найти по auth token с проверкой срока
     */
    public static function findValidAuth(string $token): ?self
    {
        return self::where('auth_token', $token)
            ->where('auth_token_expires_at', '>', now())
            ->with('user')
            ->first();
    }

    /**
     * Найти по sign token с проверкой срока
     */
    public static function findValidSign(string $token): ?self
    {
        return self::where('sign_token', $token)
            ->where('sign_token_expires_at', '>', now())
            ->with('user')
            ->first();
    }
}
