<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Services\FreeSign\FreeSignRouteService;
use App\Models\QrCodeKeys;
use App\Models\Admin\AppTemps;
use App\Models\Admin\Department;
use App\Models\Admin\Role;
use App\Models\Admin\UserRole;
use App\Models\Admin\UserWorkHistory;
use App\Models\Document\DocToDeps;
use App\Models\Document\DocumentExecutionRoles;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentFiles;
use App\Models\Document\DocumentJournals;
use App\Models\Document\DocumentOrganizations;
use App\Models\Document\DocumentReportFormEntry;
use App\Models\Document\Documents;
use App\Models\DocumentTypeTemps\DocumentTypeTempMetaData;
use App\Models\DocumentTypeTemps\DocumentTypeTemps;
use App\Models\FreeSignDocumentField;
use App\Models\Journal\ConfidentialTypes;
use App\Models\Journal\DepartmentJournals;
use App\Models\Journal\JournalAttachedOrgs;
use App\Models\Journal\JournalExecutionStepRoles;
use App\Models\Journal\JournalExecutionSteps;
use App\Models\Journal\JournalTypes;
use App\Models\Messages\OnlyOfficeDoc;
use App\Services\Documents\DocumentFactory;
use App\Services\OnlyOfficeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /** Тип журнала «Внешаблонные документы» (free-sign). */
    private const FREE_SIGN_JOURNAL_TYPE = 407;

    //
    protected OnlyOfficeService $onlyOfficeService;

    public function __construct(OnlyOfficeService $onlyOffice)
    {
        $this->onlyOfficeService = $onlyOffice;
    }

    public function documentCreate(string $type)
    {
        $validTypes = ['incoming', 'outgoing', 'internal'];
        if (!in_array($type, $validTypes)) {
            return back();
        }

        $auth = $this->authActiveUser();

        // Fetch common data
        $data = $this->getCommonData($auth->dep_id);
        $data['type'] = $type;

        // Fetch type-specific data
        $typeSpecificData = $this->getTypeSpecificData($type);
        $data = array_merge($data, $typeSpecificData);

        return view("office.document.create.{$type}", $data);
    }

    /**
     * @throws \Throwable
     */
    public function loadForm($type)
    {
        $confidences = ConfidentialTypes::where('status', 1)->get();

        switch ($type) {
            case str_starts_with($type, 'APP_'):
                return view('office.document.create.document_forms.app', compact('confidences'))->render();

            case 'HR_ORD_MT':
                $labourApps = DocumentJournals::select('document_journals.*')
                    ->leftJoin('journal_types as b', 'document_journals.j_type', '=', 'b.id')
                    ->whereIn('b.parent_id', function ($query) {
                        $query->select('c.id')
                            ->from('journal_types as c')
                            ->whereColumn('c.id', 'b.parent_id')
                            ->where('c.doc_code', 'APP');
                    })->orderBy('id', 'DESC')->get();
                return view('office.document.create.document_forms.hr_ord', compact('confidences', 'labourApps'))->render();

            case 'REPORT_FORM':
                return view('office.document.create.document_forms.report_form', compact('confidences'))->render();

            /* ═══════ FREE-SIGN (внешаблонные документы) ═══════
             | Своя форма БЕЗ блока «Текст документа» (там грузится PDF,
             | а не создаётся текст в редакторе) и БЕЗ резолюции —
             | у внешаблонных её нет, а обязательная валидация
             | resolution_user не давала сохранить документ.
             |
             | Определяем по doc_code типа журнала 351 (см. FREE_SIGN_JOURNAL_TYPE).
             */
            case $this->isFreeSignDocCode($type):
                // Есть ли в организации контролёры (Ijro nazorati / Control_Emp)?
                // От этого зависит доступность чекбокса «Контроль исполнения».
                // Та же логика, что FreeSignRouteService::controllersFor(),
                // но по организации ТЕКУЩЕГО пользователя (журнала ещё нет).
                $fsControlRole = Role::whereIn('name', ['Ijro nazorati', 'Control_Emp'])->first();
                $fsHasControllers = $fsControlRole && $this->organizationId()
                    ? UserWorkHistory::where('org_id', $this->organizationId())
                        ->where('status', 1)
                        ->whereHas('user.userRoles', fn ($q) => $q->where('role_id', $fsControlRole->id))
                        ->exists()
                    : false;

                return view('office.document.create.document_forms.free_sign',
                    compact('confidences', 'fsHasControllers'))->render();

            default:
                return view('office.document.create.document_forms.other_docs', compact('confidences'))->render();
        }
    }

    /**
     * Тип журнала = внешаблонные документы (free-sign)?
     * Сверяем doc_code с кодом типа FREE_SIGN_JOURNAL_TYPE (351).
     */
    private function isFreeSignDocCode(?string $type): bool
    {
        if (!$type) {
            return false;
        }

        $freeSignCode = JournalTypes::where('id', self::FREE_SIGN_JOURNAL_TYPE)->value('doc_code');

        return $freeSignCode && $type === $freeSignCode;
    }

    private function getCommonData(int $departmentId): array
    {
        return [
            'journals' => JournalTypes::where('org_id', $this->organizationId())
                ->where('parent_id', 0)
                ->where('status', 1)
                ->orderBy('order_number')
                ->get(),
            'departments' => Department::whereNot('parent_id', 0)
                ->where('status', 1)
                ->get(),
            'confidences' => ConfidentialTypes::where('status', 1)
                ->get(),
            'departmentJournals' => DepartmentJournals::whereHas('department', function ($query) {
                $query->where('org_unit_id', $this->organizationId());
            })->where('type', 'incoming')->where('status', 1)->get(),
        ];
    }

    private function getTypeSpecificData(string $type): array
    {
        $data = ['journal' => null, 'organizations' => null, 'labourApps' => null];

        switch ($type) {
            case 'incoming':
            case 'outgoing':
                $docCode = $type === 'incoming' ? 'BASE_INCOMING' : 'BASE_OUTGOING';
                $data['journal'] = JournalTypes::where('org_id', $this->organizationId())
                    ->where('doc_code', $docCode)
                    ->where('status', 1)
                    ->first();
                $data['organizations'] = DocumentOrganizations::where('for_doc_type', $data['journal']?->id)
                    ->get();
                break;

            case 'internal':
                $baseJournal = JournalTypes::where('org_id', $this->organizationId())
                    ->where('doc_code', 'BASE_INTERNAL')
                    ->where('status', 1)
                    ->first();
                $data['journals'] = JournalTypes::where('org_id', $this->organizationId())
                    ->where('parent_id', $baseJournal->id ?? 0)
                    ->where('status', 1)
                    ->orderBy('order_number')
                    ->get();
                $data['labourApps'] = DocumentJournals::whereHas('journal', function ($query) {
                    $query->where('doc_code', 'APP_MT_META');
                })->get();
                break;
        }

        return $data;
    }

    public function documentReportFormEntryStore(Request $request)
    {
        //
        $validated = $request->validate([
            'document_report_form_id' => 'required|integer|exists:document_report_forms,id',
            'values' => 'required|array|min:1',
        ]);

        $values = collect($validated['values'])
            ->filter(function ($row) {
                return collect($row)
                    ->filter(fn($v) => !is_null($v) && trim($v) !== '')
                    ->isNotEmpty();
            })
            ->map(function ($row) {
                $ordered = [];
                foreach ($row as $key => $value) {
                    $ordered[] = [
                        'key' => $key,
                        'value' => $value,
                    ];
                }
                return $ordered;
            })
            ->values()
            ->toArray();

        if (empty($values)) {
            return back()->withErrors([
                'values' => 'Kamida bitta maydon to‘ldirilishi kerak.',
            ])->withInput();
        }

        $saveEntry = new DocumentReportFormEntry();
        $saveEntry->document_report_form_id = $validated['document_report_form_id'];
        $saveEntry->values = $values;
        $saveEntry->filled_by = $this->authActiveUser()->id;
        $saveEntry->save();

        return redirect()->back()->with('success', 'Hujjat muvaffaqiyatli yaratildi!');
    }

    public function documentReportFormEntryDelete($id): RedirectResponse
    {
        // Using Eloquent
        $delete = DocumentReportFormEntry::findOrFail($id);
        $delete->delete();

        return redirect()->back()->with('success', 'Yozuv muvaffaqiyatli o`chirildi.');
    }

    public function store(Request $request): RedirectResponse
    {
        /* ═══════════ FREE-SIGN (внешаблонные документы) ═══════════
         | ИСПРАВЛЕНО ДВА БАГА:
         |
         | 1) Было `$this->storeFreeSign($request);` БЕЗ return —
         |    метод отрабатывал, создавал документ, но выполнение шло
         |    ДАЛЬШЕ и создавался ВТОРОЙ документ обычным путём.
         |
         | 2) Валидация 'resolution_user' => 'required' выполнялась и для
         |    free-sign. У внешаблонных документов резолюции нет, поле
         |    пустое -> validate() бросал ошибку -> редирект назад на форму.
         |    Именно поэтому не получалось сохранить.
         |
         | Теперь: free-sign уходит в свой метод и СРАЗУ возвращает результат,
         | не доходя до валидации резолюции.
         */
        if ((int) $request->journal_type === self::FREE_SIGN_JOURNAL_TYPE) {
            return $this->storeFreeSign($request);
        }

        // Резолюция обязательна только для ШАБЛОННЫХ документов.
        $request->validate([
            'resolution_user' => 'required'
        ], [
            'resolution_user.required' => __('field.resolution_user_required'),
        ]);

        $journal_type = JournalTypes::find($request->journal_type);

        $journal_type2 = JournalTypes::find($request->journal_type2);
        // 1️⃣ Asosiy hujjat yaratish
        $document = Documents::create([
            'doc_number' => $request->doc_number,
            // Устойчивый парсинг: поле может прийти как dd.mm.yyyy (datepicker)
            // либо Y-m-d (нативный input[type=date] / старое значение формы).
            'doc_date' => $this->parseDocDate($request->doc_date),
            'doc_name' => $request->doc_name ?? $journal_type->title,
            'doc_hash' => $this->uniqueHash(8),
            'doc_code' => $this->uniqueDocCode(),
            'doc_text' => $request->doc_text ?? '',   // у free-sign текста нет — пусто
            'doc_term' => $request->signing_term,
            'doc_lang' => $request->doc_lang,
        ]);

        // 2️⃣ Asosiy jurnal yaratish
        $journal = DocumentJournals::create([
            'doc_id' => $document->id,
            'j_type' => $journal_type2?->id ?? $journal_type->id,
            'j_number' => $request->j_number ?? 1,
            'org_unit_id' => $this->organizationId(),
            'j_date' => now(),
            'confid_type' => $request->confidence,
            'created_dep' => $this->authActiveUser()->dep_id,
            'created_user' => $this->authActiveUser()->id,
            'status' => 1,
        ]);

        // 3️⃣ Har bir doc_code uchun handler orqali bajarish
        $handler = DocumentFactory::make($journal_type);
        $handler->handle($document, $journal, $request);

        // 4️⃣ Qo‘shimcha fayllarni saqlash
        $this->saveFiles($document->id, $journal->id, $request);

        // 5️⃣ Foydalanuvchi rollarini saqlash
        $this->assignExecutionUsers($journal->id, $request);

        // 6️⃣ Log yaratish
        $this->createLog($journal->doc_id, 1, $journal->journal->title ?? '', $document->doc_name ?? '');

        // Redirect to document view page
        $successMessage = __('message.doc_created_success_message');

        $routeName = match ($journal_type->doc_code) {
            'BASE_OUTGOING' => 'office.view-sent-doc',
            'BASE_INCOMING' => 'office.view-inbox-doc',
            default => 'office.view-work-doc',
        };

        return Redirect::route($routeName, [$journal->id, $document->doc_hash])
            ->with('success', $successMessage);
    }
    /**
     * Парсинг даты документа, устойчивый к формату.
     *
     * Поле doc_date может прийти как:
     *   - '15.07.2026'  — bootstrap-datepicker (format: dd.mm.yyyy)
     *   - '2026-07-15'  — нативный input[type=date] или дефолт формы
     *
     * Жёсткий Carbon::createFromFormat('d.m.Y', ...) на втором варианте падал:
     * "The separation symbol could not be found / Unexpected data found".
     */
    private function parseDocDate(?string $value): string
    {
        if (!$value) {
            return now()->format('Y-m-d');
        }

        foreach (['d.m.Y', 'Y-m-d', 'd/m/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, trim($value));
                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                // пробуем следующий формат
            }
        }

        // Последняя попытка — пусть Carbon разберётся сам.
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[free-sign] не распознан doc_date, ставим сегодня', [
                'value' => $value,
            ]);
            return now()->format('Y-m-d');
        }
    }

    public function storeFreeSign(Request $request): RedirectResponse
    {
        /* ═══════ ОБЯЗАТЕЛЬНЫЕ ПОЛЯ (v44) ═══════
         | Название + дата + PDF. Проверяем ДО создания Documents/
         | DocumentJournals — пустой документ не должен родиться.
         |
         | Почему руками, а не $request->validate():
         | 1) пустой file-инпут (кнопка «+») приходит как null и правило
         |    'file' на нём падает -> ложный отказ С приложенным PDF (v42);
         | 2) все ошибки собираем ОДНИМ пакетом withErrors — пользователь
         |    видит сразу весь список, а не по одной за попытку.
         | HTML-required в форме не спасает: программный form.submit()
         | обходит нативную валидацию браузера.
         */
        $missing = [];

        if (trim((string) $request->doc_name) === '') {
            $missing['doc_name'] = __('field.type_doc_title') ?? 'Укажите название документа';
        }

        if (trim((string) $request->doc_date) === '') {
            $missing['doc_date'] = __('action.choose_date') ?? 'Укажите дату документа';
        }

        $uploaded = collect($request->file('doc_files') ?? [])
            ->filter(fn ($f) => $f !== null && $f->isValid())
            ->values();

        $hasPdf = $uploaded->contains(
            fn ($f) => strtolower($f->getClientOriginalExtension()) === 'pdf'
        );

        if ($uploaded->isEmpty() || !$hasPdf) {
            $missing['doc_files'] = $uploaded->isEmpty()
                ? 'Приложите PDF-файл — без него сохранить внешаблонный документ нельзя'
                : 'Среди приложенных файлов нет PDF — именно он размечается в редакторе подписей';
        }

        if ($missing) {
            return back()->withInput()->withErrors($missing);
        }

        $journal_type = JournalTypes::find($request->journal_type);

        $journal_type2 = JournalTypes::find($request->journal_type2);
        // 1️⃣ Asosiy hujjat yaratish
        $document = Documents::create([
            'doc_number' => $request->doc_number,
            // Устойчивый парсинг даты (dd.mm.yyyy | Y-m-d) — см. parseDocDate().
            'doc_date' => $this->parseDocDate($request->doc_date),
            'doc_name' => $request->doc_name ?? $journal_type->title,
            'doc_hash' => $this->uniqueHash(8),
            'doc_code' => $this->uniqueDocCode(),
            'doc_text' => $request->doc_text??'',
            'doc_term' => $request->signing_term,
            'doc_lang' => $request->doc_lang,
        ]);

        // 2️⃣ Asosiy jurnal yaratish
        $journal = DocumentJournals::create([
            'doc_id' => $document->id,
            'j_type' => $journal_type2?->id ?? $journal_type->id,
            'j_number' => $request->j_number ?? 1,
            'org_unit_id' => $this->organizationId(),
            'j_date' => now(),
            'confid_type' => $request->confidence,
            'created_dep' => $this->authActiveUser()->dep_id,
            'created_user' => $this->authActiveUser()->id,
            'status' => 1,
        ]);

        // Контроль исполнения — ИНДИВИДУАЛЬНЫЙ выбор для документа
        // (чекбокс формы free_sign). Пишем в ОТДЕЛЬНУЮ таблицу
        // free_sign_journal_controls — document_journals и её модель
        // НЕ трогаем. Запись создаётся ВСЕГДА (true/false явно);
        // отсутствие записи = старый документ -> fallback на настройку типа.
        \App\Models\FreeSignJournalControl::create([
            'j_id'    => $journal->id,
            'enabled' => $request->boolean('exec_control'),
        ]);

        // 3️⃣ Har bir doc_code uchun handler orqali bajarish
        $handler = DocumentFactory::make($journal_type);
        $handler->handle($document, $journal, $request);

//        // 4️⃣ Qo‘shimcha fayllarni saqlash
        $this->saveFiles($document->id, $journal->id, $request);

        // 6️⃣ Log yaratish
        $this->createLog($journal->doc_id, 1, $journal->journal->title ?? '', $document->doc_name ?? '');

        // Redirect to document view page
        $successMessage = __('message.doc_created_success_message');

        // ИСПРАВЛЕНО: было `return view('office.journal.work.free-sign')` —
        // вьюха отдавалась НАПРЯМУЮ и БЕЗ данных ($journal, $document,
        // $documentFile, $people, $roles...). Blade падал на Undefined variable,
        // и пользователя выбрасывало обратно на форму создания.
        //
        // Правильно — редирект на viewFreeSign(), который сам соберёт всё
        // нужное: PDF, подписантов, роли, метки, режим, статусы.
        return redirect()
            ->route('office.work.free-sign', [$journal->id, $document->doc_hash])
            ->with('success', $successMessage);
    }
    public function saveFromEditor(Request $request, $documentId): JsonResponse
    {
        $data = $request->validate([
            'sequential'         => 'required|boolean',
            'signers'            => 'required|array|min:1',
            'signers.*.user_id'  => 'required|integer',
            'signers.*.role_id'  => 'required|integer',
            'signers.*.order'    => 'nullable|integer',
            'fields'             => 'present|array',
            'fields.*.page'      => 'required|integer|min:1',
            'fields.*.type'      => 'required|string|max:40',
            'fields.*.signer_id' => 'nullable|integer',
            'fields.*.x'         => 'required|numeric|between:0,1',
            'fields.*.y'         => 'required|numeric|between:0,1',
            'fields.*.width'     => 'required|numeric|between:0,1',
            'fields.*.height'    => 'required|numeric|between:0,1',
            'fields.*.params'    => 'nullable|array',
        ]);

        // journal_id для document_execution_steps.
        // ВАРИАНТ А: журнал уже создан заранее и его id пришёл/хранится — подставь его.
        // ВАРИАНТ Б: если журнала нет — создай его здесь своей логикой и получи id.
        // Пока берём из запроса или из документа — ЗАМЕНИ на свою реальную логику.
        $journalId = DocumentJournals::where('doc_id', $documentId)->firstOrFail()->id;

        // Нормализуем подписантов под saveRouteAndFields (user_id + exec_role_id).
        $signers = collect($data['signers'])->map(function ($s) {
            return [
                'user_id'      => $s['user_id'],
                'exec_role_id' => $s['role_id'],
            ];
        })->toArray();

        try {
            $this->saveRouteAndFields(
                $journalId,
                (int) $documentId,
                $signers,
                $data['fields']
            );
        } catch (\Throwable $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Ошибка сохранения: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Маршрут и поля сохранены',
            'saved'   => [
                'signers' => count($signers),
                'fields'  => count($data['fields']),
            ],
        ]);
    }

    public function saveRouteAndFields(int $journalId, int $documentId, array $signers, array $fields): void
    {
        DB::transaction(function () use ($journalId, $documentId, $signers, $fields) {

            // 1. Шаги маршрута. Создаются со status=1 (спят) — активирует startRoute().
            //
            // ВАЖНО про to_user: в проекте шаги ищутся через
            //   whereIn('to_user', authActiveUserIds())  → это ID из user_work_histories,
            // а extracted() зовёт userWork($signatory_user) = UserWorkHistory::find(...).
            // Значит to_user ДОЛЖЕН быть work_history.id, а НЕ users.id.
            //
            // Редактор отдаёт users.id (список людей строится по users),
            // поэтому здесь конвертируем: users.id → активный work_history.id
            // в текущей организации.
            $orgId = $this->authActiveUser()->org_id ?? auth()->user()->org_unit_id;

            foreach ($signers as $s) {
                // work_history ТОЛЬКО в текущей организации.
                //
                // ⚠ ФОЛБЭКА НА ЧУЖУЮ ОРГАНИЗАЦИЮ БЫТЬ НЕ ДОЛЖНО:
                // списки фильтруют через whereIn('to_user', authActiveUserIds()),
                // а authActiveUserIds() возвращает work_history ТОЛЬКО текущей org.
                // Если создать шаг на work_id из другой организации — шаг будет
                // в БД, но НИКТО его не увидит: документ зависнет навсегда.
                $workId = \App\Models\Admin\UserWorkHistory::where('user_id', $s['user_id'])
                    ->where('org_id', $orgId)
                    ->where('status', 1)
                    ->value('id');

                if (!$workId) {
                    // Честно падаем, а не создаём невидимый шаг.
                    \Illuminate\Support\Facades\Log::error('[free-sign] у подписанта НЕТ активного work_history в этой организации', [
                        'user_id' => $s['user_id'],
                        'org_id'  => $orgId,
                        'hint'    => 'подписант не работает в этой организации — шаг создавать нельзя',
                    ]);

                    throw new \RuntimeException(
                        'Подписант (user_id: ' . $s['user_id'] . ') не имеет активного рабочего места '
                        . 'в текущей организации. Выберите другого подписанта.'
                    );
                }

                $this->extracted($journalId, $workId, $s['exec_role_id']);
            }

            // 2. Поля (метки) — перезаписываем набор документа.
            FreeSignDocumentField::where('document_id', $documentId)->delete();

            $rows = [];
            $now  = now();
            foreach ($fields as $f) {
                $rows[] = [
                    'document_id' => $documentId,
                    'signer_id'   => $f['signer_id'] ?? null,
                    'type'        => $f['type'],
                    'page'        => $f['page'],
                    'x'           => $f['x'],
                    'y'           => $f['y'],
                    'width'       => $f['width'],
                    'height'      => $f['height'],
                    'params'      => isset($f['params']) ? json_encode($f['params']) : null,
                    'z_index'     => $f['z_index'] ?? 0,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
            if (!empty($rows)) {
                FreeSignDocumentField::insert($rows);
            }

            // 3. QR-код проверки для этого документа (doc_type='free_sign').
            //    Штамп типа 'qr' берёт key_code отсюда.
            QrCodeKeys::updateOrCreate(
                ['doc_id' => $documentId, 'doc_type' => 'free_sign'],
                ['key_code' => (string) random_int(1000, 9999), 'key_date' => now()->toDateString()]
            );

            // 4. МАРШРУТ НЕ ЗАПУСКАЕМ.
            //    Документ остаётся в статусе 1 (Yaratilgan), шаги спят (status=1),
            //    письма не уходят. Маршрут стартует ТОЛЬКО когда автор подпишет
            //    (подтвердит отправку) — см. handleFreeSignDocument().
            //
            //    Автор в маршруте НЕ участвует: его подпись — криптографическое
            //    подтверждение отправки, метка на PDF не ставится.
        });
    }

    public function documentStatus($documentId): JsonResponse
    {
        $journal = DB::table('document_journals')
            ->where('doc_id', $documentId)
            ->orderByDesc('id')
            ->first();

        $journalStatus = $journal->status ?? null;
        $journalId     = $journal->id ?? null;

        // Режим: edit — документ создаётся/редактируется; view — уже в маршруте.
        $editStatuses = [1, 3];   // Yaratilgan, Tahrirlash
        $mode   = in_array($journalStatus, $editStatuses, true) ? 'edit' : 'view';
        $isDone = ((int) $journalStatus === FreeSignRouteService::JOURNAL_FINISHED); // 10

        // Статусы шагов: 1 = спит, 2 = активен (его очередь), 5 = подписал.
        $signers = [];
        if ($journalId) {
            $steps = DB::table('document_execution_steps')
                ->where('j_id', $journalId)
                ->orderBy('id')
                ->get(['to_user', 'status']);

            foreach ($steps as $step) {
                $st = (int) $step->status;
                // to_user — work_history.id; фронт оперирует users.id (id карточек).
                $uid = DB::table('user_work_histories')->where('id', $step->to_user)->value('user_id') ?? $step->to_user;
                $signers[] = [
                    'user_id' => $uid,
                    // Завершённым считается 5 (подписал) И 6 (подтвердил исполнение).
                    // Раньше 6 попадал в default => 'pending' — редактор показывал
                    // «не подписано», хотя человек уже подписал.
                    'status'  => match (true) {
                        FreeSignRouteService::isStepCompleted($st)   => 'signed',
                        $st === FreeSignRouteService::STEP_ACTIVE    => 'active',
                        default                                      => 'pending',
                    },
                ];
            }
        }

        return response()->json([
            'mode'                  => $mode,
            'is_done'               => $isDone,
            'document_status'       => $journalStatus,
            'document_status_label' => $this->resolveStatusLabel($journalStatus),
            'signers'               => $signers,
        ]);
    }

    /**
     * Название статуса по коду (из справочника статусов).
     * ЗАМЕНИ на свой запрос к таблице статусов с переводом по локали.
     */
    private function resolveStatusLabel($code): ?string
    {
        if ($code === null) return null;

        // Заглушка-фолбэк. Подставь реальный справочник (status_lists?) с language_lines.
        $map = [
            1 => 'Создан', 2 => 'Отменён', 3 => 'Редактирование', 4 => 'Удалён',
            5 => 'На подписи', 9 => 'На согласовании', 10 => 'Завершён',
        ];
        return $map[$code] ?? ('Статус ' . $code);
    }
    public function saveFiles($document_id, $journal_id, $request)
    {
        $today = Carbon::today();
        $path  = 'doc/' . $today->year . '/' . $today->month . '/' . $today->day . '/';

        // Вариант 1: обычная загрузка
        if ($request->hasFile('doc_files')) {
            foreach ($request->file('doc_files') as $file) {
                // Пустой инпут (кнопка «+» без выбранного файла) приходит
                // как null (UPLOAD_ERR_NO_FILE) — без guard'а упадём
                // на getClientOriginalName() on null.
                if (!$file || !$file->isValid()) continue;

                $documentFile = new DocumentFiles();
                $documentFile->j_id      = $journal_id;
                $documentFile->doc_id    = $document_id;
                $documentFile->from_user = $this->authActiveUser()->id;
                $documentFile->file_name = $file->getClientOriginalName();
                $documentFile->file_hash = $this->uniqueHash(16) . '.' . $file->getClientOriginalExtension();
                $documentFile->file_path = $path;
                $documentFile->file_ext  = $file->getClientOriginalExtension();
                $documentFile->file_size = $file->getSize();
                Storage::disk('ftp_13')->put($path . $documentFile->file_hash, file_get_contents($file->getRealPath()));
                $documentFile->save();
            }
            return;
        }

        // Вариант 2: файлы пришли через OnlyOffice (JSON строка)
        $filesJson = $request->input('doc_files_json');
        if (!$filesJson) return;

        $savedFiles = json_decode($filesJson, true);
        if (empty($savedFiles)) return;

        foreach ($savedFiles as $fileData) {
            // doc_files_json приходит из запроса, поэтому путь нельзя брать на веру.
            // Ожидаем только временные файлы OnlyOffice: temp_uploads/<имя>.
            $relative = is_array($fileData) ? (string) ($fileData['path'] ?? '') : '';
            if ($relative === '' || !str_starts_with($relative, 'temp_uploads/') || str_contains($relative, '..')) {
                continue;
            }

            $tempPath = storage_path('app/' . $relative);

            // is_file(), а не file_exists(): последний возвращает true и для
            // КАТАЛОГА, после чего file_get_contents() падал с
            // "Read of N bytes failed with errno=21 Is a directory".
            if (!is_file($tempPath) || !is_readable($tempPath)) {
                continue;
            }

            // Дополнительная защита от выхода за пределы temp_uploads.
            $real = realpath($tempPath);
            $base = realpath(storage_path('app/temp_uploads'));
            if ($real === false || $base === false || !str_starts_with($real, $base . DIRECTORY_SEPARATOR)) {
                continue;
            }

            $contents = @file_get_contents($real);
            if ($contents === false) {
                continue;
            }

            $documentFile = new DocumentFiles();
            $documentFile->j_id      = $journal_id;
            $documentFile->doc_id    = $document_id;
            $documentFile->from_user = $this->authActiveUser()->id;
            $documentFile->file_name = $fileData['original'] ?? basename($real);
            $documentFile->file_hash = $this->uniqueHash(16) . '.' . ($fileData['ext'] ?? pathinfo($real, PATHINFO_EXTENSION));
            $documentFile->file_path = $path;
            $documentFile->file_ext  = $fileData['ext'] ?? pathinfo($real, PATHINFO_EXTENSION);
            $documentFile->file_size = $fileData['size'] ?? filesize($real);
            Storage::disk('ftp_13')->put($path . $documentFile->file_hash, $contents);
            $documentFile->save();

            // Удаляем временный файл (по уже проверенному относительному пути)
            Storage::disk('local')->delete($relative);
        }
    }

    public function assignExecutionUsers($journal_id, $request)
    {
        $resolution_user = $request->resolution_user;
        $agreement_users = $request->to_agree;
        $signatory_user = $request->to_emp;

        $resolution_role = DocumentExecutionRoles::where('doc_code', 'RESOL')->where('status', 1)->first();
        $signer_role = DocumentExecutionRoles::where('doc_code', 'SIGN')->where('status', 1)->first();
        $agreement_role = DocumentExecutionRoles::where('doc_code', 'AGREE')->where('status', 1)->first();

        if ($resolution_user) {
            $this->extracted($journal_id, $resolution_user, $resolution_role->id);
        }

        if ($agreement_users) {
            foreach ($agreement_users as $agreement_user) {
                $this->extracted($journal_id, $agreement_user, $agreement_role->id);
            }
        }

        if ($signatory_user) {
            $this->extracted($journal_id, $signatory_user, $signer_role->id);
        }
    }

    public function documentEdit($type, $j_id, $hash)
    {
        //
        $authUser = $this->authActiveUser();

        $journal = DocumentJournals::findOrFail($j_id);
        $document = Documents::where('doc_hash', $hash)->firstOrFail();
        $documentFiles = DocumentFiles::where('j_id', $journal->id)->get();

        $journals = JournalTypes::where('org_id', $this->organizationId())
            ->where('parent_id', 0)->where('status', 1)->orderBy('order_number')->get();

        $departments = Department::whereNot('parent_id', 0)->where('status', 1)->get();

        $confidences = ConfidentialTypes::where('status', 1)->get();

        $fromDate = null;
        $toDate = null;
        $labourApps = null;

        $organizations = DocumentOrganizations::where('for_doc_type', $journal->journal->id)->get();

        $departmentJournals = DepartmentJournals::whereHas('department', function ($query) {
            $query->where('org_unit_id', $this->organizationId());
        })->where('status', 1)->get();

        if ($type == 'incoming') {

        } elseif ($type == 'outgoing') {

        } elseif ($type == 'internal') {
            $baseJournal = JournalTypes::where('org_id', $this->organizationId())
                ->where('doc_code', 'BASE_INTERNAL')->where('status', 1)->first();
            $journals = JournalTypes::where('org_id', $this->organizationId())
                ->where('parent_id', $baseJournal->id??0)->where('status', 1)->orderBy('order_number')->get();

            if (str_contains($journal->journal->doc_code, 'APP')){
                $metaData = DocumentTypeTempMetaData::where('doc_id', $document->id)->first();
                $json_data = json_decode($metaData->meta_data, true);

                $fromDate = $json_data['FROM_DATE']??'';
                $toDate = $json_data['TO_DATE']??'';
            }

            if (str_contains($journal->journal->doc_code, 'HR_ORD')){
                $metaData = DocumentTypeTempMetaData::where('doc_id', $document->id)->first();
                $json_data = json_decode($metaData->meta_data, true);

                $fromDate = $json_data['FROM_DATE_O']??'';
                $toDate = $json_data['TO_DATE_O']??'';

                $labourApps = DocumentJournals::whereHas('journal', function ($query) {
                    $query->where('doc_code', 'APP_MT_META');
                })->get();
            }
        } else {
            return back();
        }

        return view('office.document.edit.'.$type, compact('journals', 'departments', 'confidences', 'type', 'authUser',
            'journal', 'document', 'documentFiles', 'fromDate', 'toDate', 'organizations', 'departmentJournals', 'labourApps'));
    }

	public function edit($document_key, $j_id, $hash)
    {
        $authUser = $this->authActiveUser();

        $journals = JournalTypes::where('org_id', $this->organizationId())->where('parent_id', 0)->where('status', 1)->get();
        $departments = Department::whereNot('parent_id', 0)->where('status', 1)->get();
        $confidences = ConfidentialTypes::where('status', 1)->get();

        $journal = DocumentJournals::findOrFail($j_id);
        $document = Documents::where('doc_hash', $hash)->firstOrFail();
        $documentFiles = DocumentFiles::where('j_id', $journal->id)->get();

        $departmentJournals = DepartmentJournals::where('dep_id', $authUser->dep_id)->where('status', 1)->get();

        $this->onlyOfficeService->editWordToHtmlConverted($document_key, $journal->doc_id, 'DOC');

        return view('office.document.edit', compact('journals', 'departments', 'confidences', 'authUser', 'journal', 'document',
            'documentFiles', 'departmentJournals'));
    }

    public function update(Request $request, $j_id, $hash)
    {
        //

        $resolution_user = $request->resolution_user;
        $signatory_user = $request->to_emp;
        $agreement_users = $request->to_agree;
        $confidence = $request->confidence;
        $doc_number = $request->doc_number;
        $doc_date = $request->doc_date;
        $signing_term = $request->signing_term ? date('Y-m-d', strtotime($request->signing_term)) : null;
        $doc_name = $request->doc_name;
        $doc_text = $request->doc_text??'';
        $doc_org = $request->doc_org;
        $to_dep = $request->to_dep;
        $doc_lang = $request->doc_lang;

        $resolution_role = DocumentExecutionRoles::where('doc_code', 'RESOL')->where('status', 1)->first();
        $signer_role = DocumentExecutionRoles::where('doc_code', 'SIGN')->where('status', 1)->first();
        $agreement_role = DocumentExecutionRoles::where('doc_code', 'AGREE')->where('status', 1)->first();

        $journal = DocumentJournals::find($j_id);
        $journal->update([
            'confid_type' => $confidence,
            'status' => 1
        ]);

        $document = Documents::where('id', $journal->doc_id)->where('doc_hash', $hash)->first();
        $document->update([
            'doc_number' => $doc_number??$document->doc_number,
            'doc_date' => $doc_date ? Carbon::createFromFormat('d.m.Y', $doc_date)->format('Y-m-d') : $document->doc_date,
            'doc_name' => $doc_name??$document->doc_name,
            'doc_text' => $doc_text,
            'doc_term' => $signing_term,
            'doc_lang' => $doc_lang
        ]);

        if (str_contains($journal->journal->doc_code, 'APP')){
            $fromDate = Carbon::createFromFormat('d.m.Y', $request->from_date)->format('Y-m-d');
            $toDate = Carbon::createFromFormat('d.m.Y', $request->to_date)->format('Y-m-d');

            $data = ['FROM_DATE' => $fromDate, 'TO_DATE' => $toDate];

            $metaData = DocumentTypeTempMetaData::where('doc_id', $document->id)->first();
            $metaData->update([
                'meta_data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ]);
        } elseif (str_contains($journal->journal->doc_code, 'HR_ORD')) {
            $fromDate = Carbon::createFromFormat('d.m.Y', $request->from_date)->format('Y-m-d');
            $toDate = Carbon::createFromFormat('d.m.Y', $request->to_date)->format('Y-m-d');
            $documentId = $request->app_id;

            $appMetaData = DocumentTypeTempMetaData::where('doc_id', $documentId)->first();
            $json_data = $appMetaData ? json_decode($appMetaData->meta_data, true) : [];

            $fromDateA = $json_data['FROM_DATE'] ?? '';
            $toDateA = $json_data['TO_DATE'] ?? '';

            $data = [
                'FROM_DATE_O' => $fromDate,
                'TO_DATE_O' => $toDate,
                'DOCUMENT_ID' => $documentId,
                'FROM_DATE_A' => $fromDateA,
                'TO_DATE_A' => $toDateA
            ];

            $metaData = DocumentTypeTempMetaData::where('doc_id', $document->id)->first();
            $metaData->update([
                'meta_data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ]);

        } elseif ($journal->journal->doc_code == 'BASE_INCOMING') {
            $journal->update(['dep_j_id' => $request->department_journal]);
        } else {
            // Handle two variants for file saving
            if ($request->has('document_key')) {

                $documentKey = $request->document_key;

                $today = Carbon::today();
                $year = $today->year;
                $month = $today->month;
                $day = $today->day;
                $ood_path = 'ood/' . $year . '/' . $month . '/' . $day . '/';

            } else {

                $documentKey = $this->generateDocumentKey();
                $today = Carbon::today();
                $year = $today->year;
                $month = $today->month;
                $day = $today->day;
                $ood_path = 'ood/' . $year . '/' . $month . '/' . $day . '/';
                $filename = $documentKey . '.docx';
                $file = $request->file('main_file');
                if ($file) {
                    Storage::disk('ftp')->putFileAs($ood_path, $file, $filename);
                }
            }

            if (isset($documentKey) && $documentKey) {
                $onlyOfficeDoc = OnlyOfficeDoc::where('doc_type', 'DOC')->where('doc_id', $document->id)->first();
                $onlyOfficeDoc->update([
                    'doc_key' => $documentKey,
                    'doc_path' => $ood_path,
                ]);

                $this->onlyOfficeService->createWordToHtmlConverted($documentKey);
            }
        }

        $agreementSignatorySteps = DocumentExecutionSteps::where('j_id', $journal->id)
            ->whereIn('exec_role', [$signer_role->id, $agreement_role->id])
            ->get();

        if ($agreementSignatorySteps && $agreementSignatorySteps->count()) {
            foreach ($agreementSignatorySteps as $agreementSignatoryStep) $agreementSignatoryStep->delete();
        }

        if ($resolution_user) {
            $resolutionSteps = DocumentExecutionSteps::where('j_id', $journal->id)->where('exec_role', $resolution_role->id)->get();
            if ($resolutionSteps && $resolutionSteps->count()) {
                foreach ($resolutionSteps as $resolutionStep) $resolutionStep->delete();
            }

            $this->extracted($j_id, $resolution_user, $resolution_role->id);
        }

        if ($agreement_users) {
            foreach ($agreement_users as $agreement_user) {
                $this->extracted($j_id, $agreement_user, $agreement_role->id);
            }
        }

        if ($signatory_user) $this->extracted($j_id, $signatory_user, $signer_role->id);

        $this->saveFiles($document->id, $j_id, $request);

        if ($journal->journal->doc_code === 'ORDER_F' && $to_dep) {
            $doc_to_dep = DocToDeps::where('j_id', $journal->id)->first();
            $doc_to_dep->update(['dep_id' => $to_dep]);
        }

        if (in_array($journal->journal->doc_code, ['BASE_INCOMING', 'BASE_OUTGOING']) && $doc_org){
            $journalAttachedOrg = JournalAttachedOrgs::where('j_id', $journal->id)->first();
            $journalAttachedOrg->update(['doc_org_id' => $doc_org]);
        }

        $journalType = $journal->journal->title??'';
        $docName = $journal->doc->doc_name??'';
        $this->createLog($journal->doc_id, 2, $journalType, $docName);

        if ($journal->journal->doc_code === 'BASE_OUTGOING') {
            return Redirect::route('office.view-sent-doc', [$journal->id, $document->doc_hash])
                ->with('success', __('message.doc_updated_success_message'));
        } elseif (!in_array($journal->journal->doc_code, ['MESS', 'BASE_OUTGOING', 'BASE_INCOMING'])) {
            return Redirect::route('office.view-work-doc', [$journal->id, $document->doc_hash])
                ->with('success', __('message.doc_updated_success_message'));
        }

        $redirect_route_after_create_inbox_doc = 'office.view-inbox-doc';

        return Redirect::route($redirect_route_after_create_inbox_doc, [$journal->id, $document->doc_hash])
            ->with('success', __('message.doc_updated_success_message'));
    }

    public function getJournals($j_type_id): \Illuminate\Http\JsonResponse
    {
        //
        $authRoleName = $this->authActiveUserRoleNames();

        $main_journal = JournalTypes::find($j_type_id);

        $journalTypes = [];

        if ($main_journal) {
            $search = JournalTypes::where('org_id', $this->organizationId())->where('status', 1)->where('parent_id', '!=' ,0);
            if ($main_journal->doc_code == 'MESS') {
                if (in_array($this->chancellery, $authRoleName)) {
                    $search->where('parent_id', $main_journal->id);
                } else {
                    $search->where('parent_id', $main_journal->id)->where('doc_code', '!=', 'BASE_INCOMING');
                }
            } elseif ($main_journal->doc_code == 'APP') {
                $appTemps = AppTemps::select('t_code')->where('status', 1)->pluck('t_code')->toArray();
                $search->whereIn('doc_code', $appTemps);
            } else {
                $search->where('parent_id', $main_journal->id);
            }

            $journalTypes = $search->with('langName')->orderBy('order_number')->get();
        }

        return response()->json($journalTypes);
    }

    public function getEmpAppTemplate($j_id)
    {
        $docTemp = DocumentTypeTemps::where('doc_type_id', $j_id)
            ->where('status', 1)
            ->first();

        $content = $docTemp?->t_text ?? ''; // или название поля, где хранится html

        // ищем все [SOMETHING]
        preg_match_all('/\[([A-Z_]+)\]/', $content, $matches);

        // $matches[1] содержит массив: ['FROM_DATE', 'TO_DATE', ...]
        $placeholders = $matches[1] ?? [];

        return response()->json([
            'docTemp' => $docTemp,
            'placeholders' => $placeholders,
        ]);
    }

    public function getJournalNumber($j_id)
    {
        //
        $lastNumber = 1;

        $model = DocumentJournals::where('j_type', $j_id)->latest()->first();

        if ($model) $lastNumber = $model->j_number + 1;

        return response()->json(array('success' => true, 'j_id' => $j_id,'lastNumber' => $lastNumber));
    }

    public function getExecutionUsers($j_id)
    {
        //
        $journal_type = JournalTypes::find($j_id);

        $resolution_step = JournalExecutionSteps::where('journal_type_id', $journal_type->id)
            ->whereHas('executionRole', function ($query) {
                $query->where('doc_code', 'RESOL');
            })->first();

        $signatory_step = JournalExecutionSteps::where('journal_type_id', $journal_type->id)
            ->whereHas('executionRole', function ($query) {
                $query->where('doc_code', 'SIGN');
            })->first();

        $agreement_step = JournalExecutionSteps::where('journal_type_id', $journal_type->id)
            ->whereHas('executionRole', function ($query) {
                $query->where('doc_code', 'AGREE');
            })->first();

        $resolution_users = collect();
        $signatory_users = collect();
        $agreement_users = collect();

        $super_admin_role = Role::where('name', $this->admin)->first();;
        $super_admins_array = UserWorkHistory::whereHas('user', function ($query) use ($super_admin_role) {
            $query->whereHas('userRoles', function ($query2) use ($super_admin_role) {
                $query2->where('role_id', $super_admin_role->id);
            });
        })->pluck('user_id')->toArray();

        if ($resolution_step) {
            $resolution_roles_ids = JournalExecutionStepRoles::where('j_exec_step_id', $resolution_step->id)->pluck('role_id')->toArray();
            $user_roles = UserRole::whereIn('role_id', $resolution_roles_ids)->pluck('model_id')->toArray();
            $resolution_users = UserWorkHistory::where('user_work_histories.org_id', $this->organizationId())
                ->whereIn('user_work_histories.user_id', $user_roles)->whereHas('user', function ($query) {
                    $query->where('status', 1);
                })->where('user_work_histories.status', 1)
                ->whereNotIn('user_work_histories.user_id', $super_admins_array)
                ->join('user_personal_infos', 'user_personal_infos.user_id', '=', 'user_work_histories.user_id')
                ->orderBy('user_personal_infos.sort')
                ->select('user_work_histories.*')
                ->get();
        }

        if ($signatory_step) {
            $signatory_roles_ids = JournalExecutionStepRoles::where('j_exec_step_id', $signatory_step->id)->pluck('role_id')->toArray();
            $user_roles = UserRole::whereIn('role_id', $signatory_roles_ids)->pluck('model_id')->toArray();
            $signatory_users = UserWorkHistory::where('user_work_histories.org_id', $this->organizationId())
                ->whereIn('user_work_histories.user_id', $user_roles)->whereHas('user', function ($query) {
                    $query->where('status', 1);
                })->where('user_work_histories.status', 1)
                ->whereNotIn('user_work_histories.user_id', $super_admins_array)
                ->join('user_personal_infos', 'user_personal_infos.user_id', '=', 'user_work_histories.user_id')
                ->orderBy('user_personal_infos.sort')
                ->select('user_work_histories.*')
                ->get();
        }

        if ($agreement_step) {
            $agreement_roles_ids = JournalExecutionStepRoles::where('j_exec_step_id', $agreement_step->id)->pluck('role_id')->toArray();
            $user_roles = UserRole::whereIn('role_id', $agreement_roles_ids)->pluck('model_id')->toArray();
            $agreement_users = UserWorkHistory::where('user_work_histories.org_id', $this->organizationId())->
                whereIn('user_work_histories.user_id', $user_roles)->whereHas('user', function ($query) {
                    $query->where('status', 1);
                })->where('user_work_histories.status', 1)
                ->whereNotIn('user_work_histories.user_id', $super_admins_array)
                ->join('user_personal_infos', 'user_personal_infos.user_id', '=', 'user_work_histories.user_id')
                ->orderBy('user_personal_infos.sort')
                ->select('user_work_histories.*')
                ->get();
        }

        $blade = view('office.templates.execution-steps', compact('resolution_users', 'signatory_users', 'agreement_users'))->render();

        return response()->json(['success' => true, 'blade' => $blade]);
    }

    public function getDocOrganizations($j_id)
    {
        $doc_organizations = DocumentOrganizations::where('for_doc_type', $j_id)->where('status', 1)->get();

        $blade = view('office.templates.doc-organizations', compact('doc_organizations'))->render();

        if ($doc_organizations->count()){
            return response()->json(['success' => true, 'blade' => $blade]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function getDocumentText($j_id): JsonResponse
    {
        //
        $journal = DocumentJournals::find($j_id);

        $document = Documents::find($journal->doc_id);

        return response()->json(['success' => true, 'text' => $document->doc_text]);
    }

    public function getExecutionSelectedUsers($j_id, $id): JsonResponse
    {
        //
        $journal_type = JournalTypes::find($j_id);

        $resolution_step = JournalExecutionSteps::where('journal_type_id', $journal_type->id)
            ->whereHas('executionRole', function ($query) {
                $query->where('doc_code', 'RESOL');
            })->first();

        $signatory_step = JournalExecutionSteps::where('journal_type_id', $journal_type->id)
            ->whereHas('executionRole', function ($query) {
                $query->where('doc_code', 'SIGN');
            })->first();

        $agreement_step = JournalExecutionSteps::where('journal_type_id', $journal_type->id)
            ->whereHas('executionRole', function ($query) {
                $query->where('doc_code', 'AGREE');
            })->first();

        $resolution_users = collect();
        $signatory_users = collect();
        $agreement_users = collect();

        $super_admin_role = Role::where('name', $this->admin)->first();;
        $super_admins_array = UserWorkHistory::whereHas('user', function ($query) use ($super_admin_role) {
            $query->whereHas('userRoles', function ($query2) use ($super_admin_role) {
                $query2->where('role_id', $super_admin_role->id);
            });
        })->pluck('user_id')->toArray();

        if ($resolution_step) {
            $resolution_roles_ids = JournalExecutionStepRoles::where('j_exec_step_id', $resolution_step->id)->pluck('role_id')->toArray();
            $user_roles = UserRole::whereIn('role_id', $resolution_roles_ids)->pluck('model_id')->toArray();
            $resolution_users = UserWorkHistory::where('user_work_histories.org_id', $this->organizationId())
                ->whereIn('user_work_histories.user_id', $user_roles)->whereHas('user', function ($query) {
                    $query->where('status', 1);
                })->where('user_work_histories.status', 1)
                ->whereNotIn('user_work_histories.user_id', $super_admins_array)
                ->join('user_personal_infos', 'user_personal_infos.user_id', '=', 'user_work_histories.user_id')
                ->orderBy('user_personal_infos.sort')
                ->select('user_work_histories.*')
                ->get();
        }

        if ($signatory_step) {
            $signatory_roles_ids = JournalExecutionStepRoles::where('j_exec_step_id', $signatory_step->id)->pluck('role_id')->toArray();
            $user_roles = UserRole::whereIn('role_id', $signatory_roles_ids)->pluck('model_id')->toArray();
            $signatory_users = UserWorkHistory::where('user_work_histories.org_id', $this->organizationId())
                ->whereIn('user_work_histories.user_id', $user_roles)->whereHas('user', function ($query) {
                    $query->where('status', 1);
                })->where('user_work_histories.status', 1)
                ->whereNotIn('user_work_histories.user_id', $super_admins_array)
                ->join('user_personal_infos', 'user_personal_infos.user_id', '=', 'user_work_histories.user_id')
                ->orderBy('user_personal_infos.sort')
                ->select('user_work_histories.*')
                ->get();
        }

        if ($agreement_step) {
            $agreement_roles_ids = JournalExecutionStepRoles::where('j_exec_step_id', $agreement_step->id)->pluck('role_id')->toArray();
            $user_roles = UserRole::whereIn('role_id', $agreement_roles_ids)->pluck('model_id')->toArray();
            $agreement_users = UserWorkHistory::where('user_work_histories.org_id', $this->organizationId())
                ->whereIn('user_work_histories.user_id', $user_roles)->whereHas('user', function ($query) {
                    $query->where('status', 1);
                })->where('user_work_histories.status', 1)
                ->whereNotIn('user_work_histories.user_id', $super_admins_array)
                ->join('user_personal_infos', 'user_personal_infos.user_id', '=', 'user_work_histories.user_id')
                ->orderBy('user_personal_infos.sort')
                ->select('user_work_histories.*')
                ->get();
        }

        $signer = DocumentExecutionSteps::where('j_id', $id)->whereHas('role', function ($query) {
            $query->where('doc_code', 'SIGN');
        })->first();

        $agreements = DocumentExecutionSteps::where('j_id', $id)->whereHas('role', function ($query) {
            $query->where('doc_code', 'AGREE');
        })->get();

        $verifier = DocumentExecutionSteps::where('j_id', $id)->whereHas('role', function ($query) {
            $query->where('doc_code', 'RESOL');
        })->first();


        $blade = view('office.templates.edit-execution-steps', compact('resolution_users', 'signatory_users', 'agreement_users',
            'signer', 'agreements', 'verifier'))->render();

        return response()->json(['success' => true, 'blade' => $blade]);
    }

    public function getSelectedDocOrganizations($j_id, $id): JsonResponse
    {
        $doc_organizations = DocumentOrganizations::where('for_doc_type', $j_id)->where('status', 1)->get();

        $journal = DocumentJournals::find($id);

        $blade = view('office.templates.edit-doc-organizations', compact('doc_organizations', 'journal'))->render();

        if ($doc_organizations->count()){
            return response()->json(['success' => true, 'blade' => $blade]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public static function generateDocumentKey()
    {
        $randomString = bin2hex(random_bytes(6)); // 12 characters + 1 extra for odd length
        $randomString = substr($randomString, 0, 13); // Ensure exactly 13 characters

        $timestamp = time();

        return "{$randomString}_{$timestamp}";
    }

    public function extracted($journal_id, $signatory_user, $exec_role_id): void
    {
        $signer_step = new DocumentExecutionSteps();
        $signer_step->j_id = $journal_id;
        $signer_step->from_dep = $this->authActiveUser()->dep_id;
        $signer_step->from_user = $this->authActiveUser()->id;
        $signer_step->to_dep = $this->userWork($signatory_user)->dep_id;
        $signer_step->to_user = $signatory_user;
        $signer_step->exec_role = $exec_role_id;
        $signer_step->res_exec_role = 0;
        $signer_step->status = 1;
        $signer_step->save();
    }
}

