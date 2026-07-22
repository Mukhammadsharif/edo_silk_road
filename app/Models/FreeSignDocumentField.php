<?php

namespace App\Models;

use App\Models\Document\Documents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreeSignDocumentField extends Model
{
    protected $table = 'free_sign_document_fields';

    protected $fillable = [
        'document_id', 'signer_id', 'type', 'page',
        'x', 'y', 'width', 'height', 'params', 'z_index',
    ];

    protected $casts = [
        'page'    => 'integer',
        'x'       => 'float',
        'y'       => 'float',
        'width'   => 'float',
        'height'  => 'float',
        'params'  => 'array',    // jsonb → массив
        'z_index' => 'integer',
    ];

    /* ── Связи ── */

    // signer_id → users.id (подписант = пользователь напрямую)
    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signer_id');
    }

    // document_id → documents.id
    public function document(): BelongsTo
    {
        return $this->belongsTo(Documents::class, 'document_id');
    }

    /* ── Хелпер: доли → миллиметры для штампа ── */

    /**
     * Перевод нормализованных координат в мм для страницы размера pageW × pageH.
     * TCPDF считает Y сверху вниз (как редактор), поэтому переворот НЕ нужен.
     *
     * @return array{x: float, y: float, w: float, h: float}
     */
    public function toMm(float $pageW, float $pageH): array
    {
        return [
            'x' => $this->x * $pageW,
            'y' => $this->y * $pageH,
            'w' => $this->width  * $pageW,
            'h' => $this->height * $pageH,
        ];
    }
}
