<?php

namespace App\Services\Integration1C;

use App\Models\Admin\Department;
use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Admin\UserPosition;
use App\Models\Admin\UserWorkHistory;
use App\Models\BitrixHashes;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Синк буфера onec_* → рабочие таблицы EDOLINE через BitrixHashes.
 *
 * Соответствие сущность → рабочая таблица:
 *   companies           → organization_units   (BitrixHashes type 'C')
 *   divisions           → departments          (type 'D')
 *   positions           → user_positions       (type 'POS')  ← НЕ 'P' (конфликт с persons)
 *   persons             → users + user_personal_info (type 'P')
 *   employees           → user_work_history     (type 'E')
 *   stafflist_positions → только буфер (нет рабочей таблицы)
 *   vocations / users   → только буфер
 */
class OnecSyncToWorking
{
    /* ───────────────────────── COMPANIES → organization_units ───────────────────────── */
    public function syncCompany(array $c): string
    {
        if (empty($c['ID'])) return 'skipped';
        try {
            $hash = BitrixHashes::where('type', 'C')->where('hash', $c['ID'])->first();

            // родитель
            $parentId = 0;
            if (!empty($c['parentOrganizationID']) && $c['parentOrganizationID'] !== $c['ID']) {
                $ph = BitrixHashes::where('type', 'C')->where('hash', $c['parentOrganizationID'])->first();
                if ($ph) $parentId = (int) $ph->local_id;
            }

            $name = $c['name'] ?? '';
            $code = $c['taxID'] ?? ('ONEC-' . substr($c['ID'], 0, 12));
            $data = [
                'name_uz'    => $name,
                'name_kr'    => $name,
                'name_ru'    => $name,
                'name_en'    => $name,
                'tax_number' => $c['taxID'] ?? null,
                'parent_id'  => $parentId,
                'level'      => ($c['isBranch'] ?? false) ? 2 : 1,
                'status'     => 1,
            ];

            // Шаг 1. Есть связь в BitrixHashes → обновляем
            if ($hash && ($org = OrganizationUnits::find($hash->local_id))) {
                $org->update($data);
                Log::channel('onec')->info('[1C sync company] updated', ['key' => $c['ID'], 'local_id' => $org->id]);
                return 'updated';
            }

            // Шаг 2. Связи нет, но запись с таким code уже существует → подхватываем и создаём связь
            $existing = OrganizationUnits::where('code', $code)->first();
            if ($existing) {
                $existing->update($data);
                BitrixHashes::firstOrCreate(
                    ['type' => 'C', 'hash' => $c['ID']],
                    ['local_id' => $existing->id]
                );
                Log::channel('onec')->info('[1C sync company] linked existing by code → updated', ['key' => $c['ID'], 'local_id' => $existing->id]);
                return 'updated';
            }

            // Шаг 3. Новой записи нет → создаём
            $data['code'] = $code;
            $org = OrganizationUnits::create($data);
            BitrixHashes::create(['type' => 'C', 'hash' => $c['ID'], 'local_id' => $org->id]);
            Log::channel('onec')->info('[1C sync company] created → organization_units', ['key' => $c['ID'], 'local_id' => $org->id]);
            return 'created';
        } catch (\Throwable $e) {
            Log::channel('onec')->error('[1C sync company] ' . $e->getMessage(), ['id' => $c['ID'] ?? null]);
            return 'error';
        }
    }

    /* ───────────────────────── DIVISIONS → departments ───────────────────────── */
    public function syncDivision(array $d, ?int $orgUnitId = null): string
    {
        if (empty($d['ID'])) return 'skipped';
        try {
            $hash = BitrixHashes::where('type', 'D')->where('hash', $d['ID'])->first();

            // организация
            $orgId = 0;
            if (!empty($d['companyID'])) {
                $ch = BitrixHashes::where('type', 'C')->where('hash', $d['companyID'])->first();
                if ($ch) $orgId = (int) $ch->local_id;
            }
            // фолбэк: если компания ещё не синхронизирована — берём орг из токена 1С
            if (!$orgId && $orgUnitId) $orgId = $orgUnitId;
            // родительский отдел
            $parentId = 0;
            if (!empty($d['parentDivisionID']) && $d['parentDivisionID'] !== $d['ID']) {
                $ph = BitrixHashes::where('type', 'D')->where('hash', $d['parentDivisionID'])->first();
                if ($ph) $parentId = (int) $ph->local_id;
            }

            $name = $d['name'] ?? '';
            $data = [
                'org_unit_id'  => $orgId,
                'name_uz'      => $name,
                'name_kr'      => $name,
                'name_ru'      => $name,
                'name_en'      => $name,
                'parent_id'    => $parentId,
                'status'       => 1,
            ];

            if ($hash && ($dep = Department::find($hash->local_id))) {
                $dep->update($data);
                Log::channel('onec')->info('[1C sync division] updated', ['key' => $d['ID'], 'local_id' => $dep->id]);
                return 'updated';
            }

            $code = $d['code'] ?? ('ONEC-D-' . substr($d['ID'], 0, 10));

            // запись с таким code уже есть → подхватываем + создаём связь
            $existing = Department::where('code', $code)->first();
            if ($existing) {
                $existing->update($data);
                BitrixHashes::firstOrCreate(['type' => 'D', 'hash' => $d['ID']], ['local_id' => $existing->id]);
                Log::channel('onec')->info('[1C sync division] linked existing by code', ['key' => $d['ID'], 'local_id' => $existing->id]);
                return 'updated';
            }

            $data['code'] = $code;
            $dep = Department::create($data);
            BitrixHashes::create(['type' => 'D', 'hash' => $d['ID'], 'local_id' => $dep->id]);
            Log::channel('onec')->info('[1C sync division] created → departments', ['key' => $d['ID'], 'local_id' => $dep->id]);
            return 'created';
        } catch (\Throwable $e) {
            Log::channel('onec')->error('[1C sync division] ' . $e->getMessage(), ['id' => $d['ID'] ?? null]);
            return 'error';
        }
    }

    /* ───────────────────────── POSITIONS → user_positions ───────────────────────── */
    public function syncPosition(array $p, ?int $orgUnitId = null): string
    {
        if (empty($p['ID'])) return 'skipped';
        try {
            // ВАЖНО: type 'POS' (не 'P' — он у persons)
            $hash = BitrixHashes::where('type', 'POS')->where('hash', $p['ID'])->first();

            $name = $p['name'] ?? '';
            $data = [
                'name_uz' => $name,
                'name_kr' => $name,
                'name_ru' => $name,
                'name_en' => $name,
                'status'  => 1,
            ];

            if ($hash && ($pos = UserPosition::find($hash->local_id))) {
                $pos->update($data);
                Log::channel('onec')->info('[1C sync position] updated', ['key' => $p['ID'], 'local_id' => $pos->id]);
                return 'updated';
            }

            $pos = UserPosition::create(array_merge($data, ['org_id' => $orgUnitId ?? 0]));
            BitrixHashes::create(['type' => 'POS', 'hash' => $p['ID'], 'local_id' => $pos->id]);
            Log::channel('onec')->info('[1C sync position] created → user_positions', ['key' => $p['ID'], 'local_id' => $pos->id]);
            return 'created';
        } catch (\Throwable $e) {
            Log::channel('onec')->error('[1C sync position] ' . $e->getMessage(), ['id' => $p['ID'] ?? null]);
            return 'error';
        }
    }

    /* ───────────────────────── PERSONS → users + user_personal_info ───────────────────────── */
    public function syncPerson(array $person, ?int $orgUnitId = null): string
    {
        if (empty($person['ID'])) return 'skipped';
        try {
            $hash = BitrixHashes::where('type', 'P')->where('hash', $person['ID'])->first();

            // Разбор с фолбэком и записью в лог: раньше пустой catch
            // молча превращал непонятую дату в null
            $birthday = $this->parseDate($person['birthDate'] ?? null, 'birthDate', $person['ID']);
            $docNumber = '';
            $docType = 2; // по умолчанию паспорт
            if (!empty($person['identityDocument'])) {
                $docNumber = ($person['identityDocument']['serias'] ?? '') . ($person['identityDocument']['number'] ?? '');
                $idType = $person['identityDocument']['identityDocumentType'] ?? null;
                // карта типов документов 1С → коды EDOLINE (расширишь при необходимости)
                $docType = match ($idType) {
                    'passport'  => 2,
                    'idCard'    => 3,
                    'birthCert' => 4,
                    default     => 2,
                };
            }
            $personalData = [
                'doc_pin'    => $this->cutWithWarning(
                    isset($person['insuranceNumber']) ? (string) $person['insuranceNumber'] : null,
                    14, 'insuranceNumber', $person['ID']
                ),
                'doc_number' => $this->cutWithWarning($docNumber ?: null, 9, 'doc_number', $person['ID']),
                'doc_type'   => $docType,
                'birthday'   => $birthday,
                'gender'     => $this->normalizeGender($person['gender'] ?? null),
                'phone'      => $this->normalizePhone($person['mobilePhoneNumber'] ?? ''),
                'f_name'     => $person['firstName'] ?? null,
                'l_name'     => $person['lastName'] ?? null,
                'm_name'     => $person['patronymic'] ?? null,
                'lang_code'  => 'uz',
            ];

            if ($hash) {
                $user = User::find($hash->local_id);
                if ($user) {
                    $upd = [];
                    if (!empty($person['eMail'])) {
                        $upd['username'] = $person['eMail'];
                        $upd['email'] = $person['eMail'];
                    }
                    if ($orgUnitId && empty($user->org_unit_id)) {
                        $upd['org_unit_id'] = $orgUnitId;
                    }
                    if (!empty($upd)) $user->update($upd);
                    UserPersonalInfo::where('user_id', $hash->local_id)->update($personalData);
                }
                return 'updated';
            }

            // Без организации создать пользователя нельзя (users.org_unit_id NOT NULL)
            if (empty($orgUnitId)) {
                Log::channel('onec')->warning('[1C sync person] org_unit_id отсутствует — пропуск', ['key' => $person['ID']]);
                return 'skipped';
            }

            // eMail необязателен в 1С, но users.email NOT NULL + unique.
            // Если eMail есть — используем; иначе синтезируем уникальный по onec_id.
            if (!empty($person['eMail'])) {
                $email    = $person['eMail'];
                $existing = User::where('email', $email)->first();

                /*
                 * Пользователь с таким email уже заведён — обычно вручную,
                 * до подключения интеграции.
                 *
                 * Раньше здесь стоял return 'skipped', и это было опасно:
                 * связь в bitrix_hashes не создавалась, поэтому syncEmployee
                 * потом не находил физлицо и тоже пропускал сотрудника.
                 * В выгрузке приказов employee_ID оставался null, а 1С при этом
                 * получала ответ об успехе — сбой был не виден никому.
                 *
                 * Поэтому связываем существующего пользователя с GUID из 1С
                 * и обновляем его персональные данные.
                 */
                if ($existing) {
                    BitrixHashes::create([
                        'type'     => 'P',
                        'hash'     => $person['ID'],
                        'local_id' => $existing->id,
                    ]);

                    UserPersonalInfo::updateOrCreate(
                        ['user_id' => $existing->id],
                        array_merge($personalData, [
                            'is_emp'        => 2,
                            'doc_sign_type' => 1,
                            'sort'          => 1,
                        ])
                    );

                    if ($orgUnitId && empty($existing->org_unit_id)) {
                        $existing->update(['org_unit_id' => $orgUnitId]);
                    }

                    Log::channel('onec')->info('[1C sync person] привязан существующий пользователь', [
                        'key'      => $person['ID'],
                        'local_id' => $existing->id,
                        'email'    => $email,
                    ]);

                    return 'linked';
                }
            } else {
                $email = 'onec+' . substr($person['ID'], 0, 24) . '@noemail.local';
            }

            $username = !empty($person['eMail']) ? $person['eMail'] : ('onec_' . substr($person['ID'], 0, 18));
            // на случай коллизии username
            if (User::where('username', $username)->exists()) {
                $username .= '_' . substr($this->uniqueId(), 0, 6);
            }

            $user = new User();
            $user->username    = $username;
            $user->email       = $email;
            $user->user_hash   = $this->uniqueId();
            $user->password    = Hash::make(bin2hex(random_bytes(8)));
            $user->org_unit_id = $orgUnitId; // организация из токена 1С
            $user->status      = 2;
            $user->save();

            BitrixHashes::create(['type' => 'P', 'hash' => $person['ID'], 'local_id' => $user->id]);
            Log::channel('onec')->info('[1C sync person] created → users + user_personal_info', ['key' => $person['ID'], 'local_id' => $user->id]);

            $info = new UserPersonalInfo(array_merge($personalData, [
                'user_id'       => $user->id,
                'is_emp'        => 2,
                'doc_sign_type' => 1,
                'sort'          => 1,
            ]));
            $info->save();
            return 'created';
        } catch (\Throwable $e) {
            Log::channel('onec')->error('[1C sync person] ' . $e->getMessage(), ['id' => $person['ID'] ?? null]);
            return 'error';
        }
    }

    /* ───────────────────────── EMPLOYEES → user_work_history ───────────────────────── */
    public function syncEmployee(array $e, ?int $orgUnitId = null): string
    {
        if (empty($e['ID'])) return 'skipped';
        try {
            $hash = BitrixHashes::where('type', 'E')->where('hash', $e['ID'])->first();

            // person → user
            $userId = null;
            if (!empty($e['personID'])) {
                $uh = BitrixHashes::where('type', 'P')->where('hash', $e['personID'])->first();
                $userId = $uh?->local_id;
            }
            if (!$userId) {
                Log::channel('onec')->warning('[1C sync employee] user not found', ['person' => $e['personID'] ?? null]);
                return 'skipped';
            }

            /*
             * division → department, position → user_position.
             *
             * Ноль означает «не сопоставлено». Раньше это происходило молча,
             * и понять, что подразделение потерялось, можно было только по
             * пустому GUID в выгрузке приказа. Теперь пишем предупреждение:
             * обычно причина в порядке загрузки — divisions и positions должны
             * приезжать из 1С раньше employees.
             */
            $depId = 0;
            if (!empty($e['divisionID'])) {
                $dh = BitrixHashes::where('type', 'D')->where('hash', $e['divisionID'])->first();

                if ($dh) {
                    $depId = (int) $dh->local_id;
                } else {
                    Log::channel('onec')->warning('[1C sync employee] division не сопоставлен', [
                        'key'        => $e['ID'],
                        'divisionID' => $e['divisionID'],
                    ]);
                }
            }

            $postId = 0;
            if (!empty($e['positionID'])) {
                $ph = BitrixHashes::where('type', 'POS')->where('hash', $e['positionID'])->first();

                if ($ph) {
                    $postId = (int) $ph->local_id;
                } else {
                    Log::channel('onec')->warning('[1C sync employee] position не сопоставлен', [
                        'key'        => $e['ID'],
                        'positionID' => $e['positionID'],
                    ]);
                }
            }
            // организация: сначала по companyID-хэшу, иначе из токена 1С
            $orgId = 0;
            if (!empty($e['companyID'])) {
                $ch = BitrixHashes::where('type', 'C')->where('hash', $e['companyID'])->first();
                if ($ch) $orgId = (int) $ch->local_id;
            }
            if (!$orgId && $orgUnitId) $orgId = $orgUnitId;

            $fromDate = $this->parseDate($e['employmentDate'] ?? null, 'employmentDate', $e['ID']);

            /*
             * from_date NOT NULL, поэтому при отсутствии даты подставляем сегодня.
             * Это выдуманное значение: сотрудник, работающий с 2019 года, получит
             * дату приёма сегодняшним числом, и она же уедет обратно в 1С
             * в приказе о приёме. Поэтому фиксируем в логе — расхождение должно
             * быть видно, а не растворяться в данных.
             */
            if (!$fromDate) {
                $fromDate = now()->format('Y-m-d');

                Log::channel('onec')->warning('[1C sync employee] employmentDate отсутствует, подставлена текущая дата', [
                    'key' => $e['ID'],
                ]);
            }

            // user_work_histories.to_date NOT NULL.
            // Работающий сотрудник: заглушка 9999-12-31 («бессрочно»).
            // Уволенный: реальная дата (transferDate, иначе сегодня).
            $isDismissed = (bool) ($e['dismissed'] ?? false);
            $toDate = '9999-12-31';

            if ($isDismissed) {
                $toDate = $this->parseDate($e['transferDate'] ?? null, 'transferDate', $e['ID'])
                    ?? now()->format('Y-m-d');
            }

            $data = [
                'user_id'   => $userId,
                'org_id'    => $orgId,
                'dep_id'    => $depId,
                'post_id'   => $postId,
                'from_date' => $fromDate,
                'to_date'   => $toDate,
                'status'    => $isDismissed ? 2 : 1,
            ];

            if ($hash && ($wh = UserWorkHistory::find($hash->local_id))) {
                $wh->update($data);
                Log::channel('onec')->info('[1C sync employee] updated', ['key' => $e['ID'], 'local_id' => $wh->id]);
                return 'updated';
            }

            $wh = UserWorkHistory::create($data);
            BitrixHashes::create(['type' => 'E', 'hash' => $e['ID'], 'local_id' => $wh->id]);
            Log::channel('onec')->info('[1C sync employee] created → user_work_history', ['key' => $e['ID'], 'local_id' => $wh->id]);
            return 'created';
        } catch (\Throwable $ex) {
            Log::channel('onec')->error('[1C sync employee] ' . $ex->getMessage(), ['id' => $e['ID'] ?? null]);
            return 'error';
        }
    }

    /**
     * Телефон: последние 9 цифр (формат Узбекистана, без кода страны).
     *
     * Раньше короткая строка вроде '12345' проходила как есть и оседала
     * в базе заведомо неверным номером. Теперь короткие отбрасываются.
     */
    private function normalizePhone($phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if (strlen((string) $digits) < 9) {
            return null;
        }

        return substr($digits, -9);
    }

    /**
     * Разбор даты из 1С.
     *
     * Основной формат — d-m-Y, но 1С может прислать и ISO. Раньше в syncPerson
     * ошибка разбора глушилась пустым catch: дата рождения молча становилась
     * null, и понять это по логам было нельзя.
     *
     * @param  string  $field  Название поля — только для сообщения в логе
     */
    private function parseDate($value, string $field, $key = null): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
        } catch (\Throwable) {
            // Фолбэк: ISO и прочие распознаваемые форматы
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            Log::channel('onec')->warning("[1C sync] не разобрана дата «{$field}»", [
                'key'   => $key,
                'value' => $value,
            ]);

            return null;
        }
    }

    /**
     * Пол: 1 — мужской, 2 — женский.
     *
     * Раньше сравнение было строгим с 'male', поэтому 'Male', 'M' или 'м'
     * молча давали женский пол.
     */
    private function normalizeGender($gender): ?int
    {
        if (!isset($gender) || $gender === '') {
            return null;
        }

        $value = mb_strtolower(trim((string) $gender));

        return in_array($value, ['male', 'm', 'м', 'муж', 'мужской', '1'], true) ? 1 : 2;
    }

    /** Обрезка с предупреждением: молча портить номер документа нельзя. */
    private function cutWithWarning(?string $value, int $limit, string $field, $key = null): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (mb_strlen($value) > $limit) {
            Log::channel('onec')->warning("[1C sync] «{$field}» длиннее {$limit} символов, обрезано", [
                'key'      => $key,
                'original' => $value,
            ]);
        }

        return mb_substr($value, 0, $limit);
    }

    /* ───────────────────────── USERS → users (логин/пароль) ───────────────────────── */
    public function syncUser(array $u): string
    {
        if (empty($u['personID'])) return 'skipped';
        try {
            // Находим локального пользователя по person (type P)
            $hash = BitrixHashes::where('type', 'P')->where('hash', $u['personID'])->first();
            if (!$hash) {
                Log::channel('onec')->warning('[1C sync user] person не найден', ['personID' => $u['personID']]);
                return 'skipped';
            }
            $user = User::find($hash->local_id);
            if (!$user) return 'skipped';

            $update = [];
            if (!empty($u['username'])) {
                $update['username'] = $u['username'];
            }

            /*
             * Смена пароля из 1С — рискованный сценарий: пароль приходит
             * открытым текстом, и при каждой синхронизации пользователь
             * терял бы уже установленный им пароль.
             *
             * Поэтому пароль ставим только пока пользователь не активирован:
             * syncPerson создаёт его со status = 2, и до активации
             * администратором собственного пароля у него ещё нет.
             *
             * Если нужно разрешить перезапись всегда — включите
             * ONEC_ALLOW_PASSWORD_OVERWRITE=true в .env.
             */
            if (!empty($u['password'])) {
                $allowOverwrite = (bool) env('ONEC_ALLOW_PASSWORD_OVERWRITE', false);

                if ($allowOverwrite || (int) $user->status === 2) {
                    $update['password'] = Hash::make($u['password']);
                } else {
                    Log::channel('onec')->info('[1C sync user] пароль не перезаписан: пользователь уже активирован', [
                        'personID' => $u['personID'],
                        'local_id' => $user->id,
                    ]);
                }
            }
            if (!empty($update)) {
                $user->update($update);
            }
            return 'updated';
        } catch (\Throwable $e) {
            Log::channel('onec')->error('[1C sync user] ' . $e->getMessage(), ['personID' => $u['personID'] ?? null]);
            return 'error';
        }
    }

    private function uniqueId(): string
    {
        return bin2hex(random_bytes(16));
    }
}
