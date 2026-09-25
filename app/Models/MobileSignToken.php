<?php

namespace App\Models;

use App\Models\Document\DocumentJournals;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Одноразовый токен страницы подписи мобильного приложения.
 */
class MobileSignToken extends Model
{
    protected $fillable = [
        'user_id', 'token', 'j_id', 'doc_id', 'sign_type', 'expires_at', 'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(DocumentJournals::class, 'j_id');
    }

    /**
     * Выдать токен на подпись конкретного документа.
     *
     * Прежние неиспользованные токены этого пользователя на этот же документ
     * гасим: иначе при повторном нажатии копились бы живые ссылки на одно
     * и то же действие.
     */
    public static function issue(int $userId, int $jId, int $docId, string $signType, int $ttl): self
    {
        self::where('user_id', $userId)
            ->where('j_id', $jId)
            ->whereNull('used_at')
            ->delete();

        return self::create([
            'user_id'    => $userId,
            'token'      => Str::random(64),
            'j_id'       => $jId,
            'doc_id'     => $docId,
            'sign_type'  => $signType,
            'expires_at' => now()->addMinutes($ttl),
        ]);
    }

    /** Действующий токен: не просрочен и ещё не использован. */
    public static function findValid(string $token): ?self
    {
        return self::with('user')
            ->where('token', $token)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function markUsed(): void
    {
        $this->update(['used_at' => now()]);
    }
}
