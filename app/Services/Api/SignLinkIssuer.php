<?php

namespace App\Services\Api;

use App\Models\Document\DocumentJournals;
use App\Models\User;

/**
 * Выдача одноразовой ссылки на страницу подписи E-IMZO.
 *
 * Подпись всегда происходит вне клиента: и мини-апп, и мобильное приложение
 * открывают внешнюю страницу, где работает E-IMZO. Механизмы при этом разные —
 * у мини-аппа токен лежит в telegram_mini_app_tokens и ведёт на /mobile/sign,
 * у мобильного приложения своя таблица mobile_sign_tokens и своя страница.
 *
 * Общая логика документов (DocumentFeedService) про это знать не должна,
 * поэтому выдача ссылки спрятана за интерфейсом.
 */
interface SignLinkIssuer
{
    /**
     * @param  string  $signType  Тип подписи: sign_resolution, from_signer_to_ag и т.п.
     * @return array{url: string, expires_in: int}
     */
    public function issue(User $user, DocumentJournals $journal, string $signType): array;
}
