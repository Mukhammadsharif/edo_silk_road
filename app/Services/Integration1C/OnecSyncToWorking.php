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

            $birthday = null;
            if (!empty($person['birthDate'])) {
                try { $birthday = Carbon::createFromFormat('d-m-Y', $person['birthDate'])->format('Y-m-d'); }
                catch (\Throwable $e) {}
            }
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
                'doc_pin'    => isset($person['insuranceNumber']) ? mb_substr((string) $person['insuranceNumber'], 0, 14) : null,
                'doc_number' => $docNumber ? mb_substr($docNumber, 0, 9) : null,
                'doc_type'   => $docType,
                'birthday'   => $birthday,
                'gender'     => isset($person['gender']) ? (($person['gender'] === 'male') ? 1 : 2) : null,
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
                $email = $person['eMail'];
                if (User::where('email', $email)->exists()) return 'skipped';
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

            // division → department
            $depId = 0;
            if (!empty($e['divisionID'])) {
                $dh = BitrixHashes::where('type', 'D')->where('hash', $e['divisionID'])->first();
                if ($dh) $depId = (int) $dh->local_id;
            }
            // position → user_position (type POS)
            $postId = 0;
            if (!empty($e['positionID'])) {
                $ph = BitrixHashes::where('type', 'POS')->where('hash', $e['positionID'])->first();
                if ($ph) $postId = (int) $ph->local_id;
            }
            // организация: сначала по companyID-хэшу, иначе из токена 1С
            $orgId = 0;
            if (!empty($e['companyID'])) {
                $ch = BitrixHashes::where('type', 'C')->where('hash', $e['companyID'])->first();
                if ($ch) $orgId = (int) $ch->local_id;
            }
            if (!$orgId && $orgUnitId) $orgId = $orgUnitId;

            $fromDate = null;
            if (!empty($e['employmentDate'])) {
                try { $fromDate = Carbon::createFromFormat('d-m-Y', $e['employmentDate'])->format('Y-m-d'); }
                catch (\Throwable $ex) { try { $fromDate = Carbon::parse($e['employmentDate'])->format('Y-m-d'); } catch (\Throwable $ex2) {} }
            }
            // user_work_histories.from_date NOT NULL — дефолт на сегодня, если 1С не прислал дату
            if (!$fromDate) $fromDate = now()->format('Y-m-d');

            // user_work_histories.to_date NOT NULL.
            // Работающий сотрудник: заглушка 9999-12-31 («бессрочно»).
            // Уволенный: реальная дата (transferDate, иначе сегодня).
            $isDismissed = (bool) ($e['dismissed'] ?? false);
            $toDate = '9999-12-31';
            if ($isDismissed) {
                $toDate = now()->format('Y-m-d');
                if (!empty($e['transferDate'])) {
                    try { $toDate = Carbon::createFromFormat('d-m-Y', $e['transferDate'])->format('Y-m-d'); }
                    catch (\Throwable $ex) { try { $toDate = Carbon::parse($e['transferDate'])->format('Y-m-d'); } catch (\Throwable $ex2) {} }
                }
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

    private function normalizePhone($phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        return $digits ? substr($digits, -9) : null;
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
            if (!empty($u['password'])) {
                $update['password'] = Hash::make($u['password']);
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
