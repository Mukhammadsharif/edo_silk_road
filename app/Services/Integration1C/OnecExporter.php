<?php

namespace App\Services\Integration1C;

use App\Models\Admin\UserPersonalInfo;
use App\Models\BitrixHashes;
use Carbon\Carbon;

/**
 * Исходящая сторона: EDOLINE → 1С.
 * Читает рабочие таблицы EDOLINE + BitrixHashes и формирует payload в формате 1С.
 */
class OnecExporter
{
    public function __construct(private OnecClient $client) {}

    /**
     * Выгрузить персону в 1С по local user_id.
     * UUID берём из BitrixHashes (type=P); если нет — 1С создаст и вернёт.
     */
    public function exportPerson(int $userId): array
    {
        $info = UserPersonalInfo::where('user_id', $userId)->first();
        if (!$info) {
            throw new \RuntimeException("UserPersonalInfo не найден для user_id={$userId}");
        }

        $hash = BitrixHashes::where('type', 'P')->where('local_id', $userId)->first();

        $payload = [
            'ID'                => $hash?->hash, // UUID 1С (если уже синхронизирован)
            'lastName'          => $info->l_name,
            'firstName'         => $info->f_name,
            'patronymic'        => $info->m_name,
            'gender'            => ((int) $info->gender === 1) ? 'male' : 'female',
            'birthDate'         => $info->birthday ? Carbon::parse($info->birthday)->format('d-m-Y') : null,
            'insuranceNumber'   => $info->doc_pin,
            'mobilePhoneNumber' => $info->phone,
            'eMail'             => $info->user?->email,
        ];
        // убираем null-ID если персона новая
        if (empty($payload['ID'])) unset($payload['ID']);

        $this->ensureAuth();
        return $this->client->createUpdate('persons', $payload);
    }

    /**
     * Пакетная выгрузка персон по списку user_id.
     */
    public function exportPersons(array $userIds): array
    {
        $items = [];
        foreach ($userIds as $uid) {
            $info = UserPersonalInfo::where('user_id', $uid)->first();
            if (!$info) continue;
            $hash = BitrixHashes::where('type', 'P')->where('local_id', $uid)->first();
            $item = [
                'lastName'          => $info->l_name,
                'firstName'         => $info->f_name,
                'patronymic'        => $info->m_name,
                'gender'            => ((int) $info->gender === 1) ? 'male' : 'female',
                'birthDate'         => $info->birthday ? Carbon::parse($info->birthday)->format('d-m-Y') : null,
                'insuranceNumber'   => $info->doc_pin,
                'mobilePhoneNumber' => $info->phone,
                'eMail'             => $info->user?->email,
            ];
            if ($hash) $item['ID'] = $hash->hash;
            $items[] = $item;
        }

        $this->ensureAuth();
        return $this->client->createUpdateList('persons', 'persons', $items);
    }

    private function ensureAuth(): void
    {
        if (config('onec.username')) {
            $this->client->login();
        }
    }
}
