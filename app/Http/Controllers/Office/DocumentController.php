<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Admin\ApprovalGroup;
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
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
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
        $data = $this->getCommonData($auth?->dep_id);
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
                // Динамически показываем поля дат только если в шаблоне
                // подтипа есть плейсхолдеры [FROM_DATE] / [TO_DATE].
                $journalType = JournalTypes::where('org_id', $this->organizationId())
                    ->where('doc_code', $type)
                    ->first();
                $tText = '';
                if ($journalType) {
                    $tpl = DocumentTypeTemps::where('doc_type_id', $journalType->id)->first();
                    $tText = $tpl->t_text ?? '';
                }
                $hasFromDate = str_contains($tText, '[FROM_DATE]');
                $hasToDate = str_contains($tText, '[TO_DATE]');

                return view('office.document.create.document_forms.app', compact('confidences', 'hasFromDate', 'hasToDate'))->render();

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

            default:
                return view('office.document.create.document_forms.other_docs', compact('confidences'))->render();
        }
    }

    private function getCommonData(?int $departmentId): array
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
        //
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
            'doc_date' => $request->doc_date ? Carbon::createFromFormat('d.m.Y', $request->doc_date)->format('Y-m-d') : now(),
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

    public function saveFiles($document_id, $journal_id, $request)
    {
        $today = Carbon::today();
        $path  = 'doc/' . $today->year . '/' . $today->month . '/' . $today->day . '/';

        // Вариант 1: обычная загрузка
        if ($request->hasFile('doc_files')) {
            foreach ($request->file('doc_files') as $file) {
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
            $tempPath = storage_path('app/' . $fileData['path']);
            if (!file_exists($tempPath)) continue;

            $documentFile = new DocumentFiles();
            $documentFile->j_id      = $journal_id;
            $documentFile->doc_id    = $document_id;
            $documentFile->from_user = $this->authActiveUser()->id;
            $documentFile->file_name = $fileData['original'];
            $documentFile->file_hash = $this->uniqueHash(16) . '.' . $fileData['ext'];
            $documentFile->file_path = $path;
            $documentFile->file_ext  = $fileData['ext'];
            $documentFile->file_size = $fileData['size'];
            Storage::disk('ftp_13')->put($path . $documentFile->file_hash, file_get_contents($tempPath));
            $documentFile->save();

            // Удаляем временный файл
            Storage::disk('local')->delete($fileData['path']);
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
        $hasFromDate = null;
        $hasToDate = null;
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

                // Показываем поля дат динамически — по наличию плейсхолдеров
                // [FROM_DATE] / [TO_DATE] в шаблоне подтипа.
                $tpl = DocumentTypeTemps::where('doc_type_id', $journal->j_type)->first();
                $tText = $tpl->t_text ?? '';
                $hasFromDate = str_contains($tText, '[FROM_DATE]');
                $hasToDate = str_contains($tText, '[TO_DATE]');
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
            'journal', 'document', 'documentFiles', 'fromDate', 'toDate', 'hasFromDate', 'hasToDate', 'organizations', 'departmentJournals', 'labourApps'));
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
            $fromDate = $request->from_date
                ? Carbon::createFromFormat('d.m.Y', $request->from_date)->format('Y-m-d')
                : '';
            $toDate = $request->to_date
                ? Carbon::createFromFormat('d.m.Y', $request->to_date)->format('Y-m-d')
                : '';

            $data = ['FROM_DATE' => $fromDate, 'TO_DATE' => $toDate];

            $metaData = DocumentTypeTempMetaData::where('doc_id', $document->id)->first();
            $metaData->update([
                'meta_data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ]);
        } elseif (str_contains($journal->journal->doc_code, 'HR_ORD')) {
            $fromDate = $request->from_date
                ? Carbon::createFromFormat('d.m.Y', $request->from_date)->format('Y-m-d')
                : '';
            $toDate = $request->to_date
                ? Carbon::createFromFormat('d.m.Y', $request->to_date)->format('Y-m-d')
                : '';
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

                if ($onlyOfficeDoc) {
                    // Сначала конвертируем новый docx в HTML. Только если HTML
                    // успешно создан — переключаем документ на новый ключ.
                    // Иначе оставляем прежнюю версию, чтобы документ не пропал.
                    $converted = $this->onlyOfficeService->createWordToHtmlConverted($documentKey);

                    if ($converted) {
                        $onlyOfficeDoc->update([
                            'doc_key' => $documentKey,
                            'doc_path' => $ood_path,
                        ]);
                    } else {
                        \Log::error('Document conversion failed on update, keeping previous version', [
                            'doc_id' => $document->id,
                            'new_key' => $documentKey,
                            'old_key' => $onlyOfficeDoc->doc_key,
                        ]);
                    }
                }
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

        $approval_groups = $this->buildApprovalGroups($this->organizationId());

        $blade = view('office.templates.execution-steps', compact('resolution_users', 'signatory_users', 'agreement_users', 'approval_groups'))->render();

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

        // ── Гарантия сохранности согласующих при редактировании документа ──
        // update() удаляет все AGREE-шаги и заново создаёт их ТОЛЬКО из to_agree[].
        // Значит в списке должны присутствовать (и быть отмечены) ВСЕ уже
        // назначенные согласующие — даже если сейчас у них нет роли AGREE
        // (например, они попали в документ через группу согласующих) или их
        // нет в отфильтрованном списке по иным причинам. Иначе после сохранения
        // такие согласующие молча пропадают.
        $assignedAgreeIds = $agreements->pluck('to_user')->map(fn ($v) => (int) $v)->unique();
        $presentIds = $agreement_users->pluck('id')->map(fn ($v) => (int) $v)->all();
        $missingIds = $assignedAgreeIds->reject(fn ($id) => in_array($id, $presentIds, true))->values();

        if ($missingIds->isNotEmpty()) {
            $extraUsers = UserWorkHistory::whereIn('id', $missingIds->all())->get();
            $agreement_users = $agreement_users->concat($extraUsers);
        }

        $verifier = DocumentExecutionSteps::where('j_id', $id)->whereHas('role', function ($query) {
            $query->where('doc_code', 'RESOL');
        })->first();


        $approval_groups = $this->buildApprovalGroups($this->organizationId());

        $blade = view('office.templates.edit-execution-steps', compact('resolution_users', 'signatory_users', 'agreement_users',
            'signer', 'agreements', 'verifier', 'approval_groups'))->render();

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

    /**
     * Готовые группы согласующих для организации.
     * Участники резолвятся в актуальные work_history id этой организации,
     * чтобы значения совпадали с value чекбоксов to_agree[].
     *
     * @return array [ ['id'=>.., 'name'=>.., 'members'=>[ ['id'=>work_id,'name'=>..,'position'=>..], ... ] ], ... ]
     */
    private function buildApprovalGroups($orgId): array
    {
        $groups = ApprovalGroup::where('org_unit_id', $orgId)->where('status', 1)
            ->orderBy('name')->get();

        $result = [];

        foreach ($groups as $group) {
            $memberUserIds = $group->memberUserIds();
            if (empty($memberUserIds)) {
                continue;
            }

            $members = UserWorkHistory::where('user_work_histories.org_id', $orgId)
                ->where('user_work_histories.status', 1)
                ->whereIn('user_work_histories.user_id', $memberUserIds)
                ->whereHas('user', function ($q) {
                    $q->where('status', 1);
                })
                ->join('user_personal_infos', 'user_personal_infos.user_id', '=', 'user_work_histories.user_id')
                ->orderBy('user_personal_infos.sort')
                ->select('user_work_histories.*')
                ->get()
                ->unique('user_id');

            $memberData = [];
            foreach ($members as $m) {
                $memberData[] = [
                    'id'       => $m->id, // work_history id — совпадает с value чекбокса to_agree[]
                    'name'     => optional($m->personal)->fullName() ?? '-',
                    'position' => optional($m->userPost)->lang(optional($m->userPost)->id) ?? '',
                ];
            }

            if (!empty($memberData)) {
                $result[] = [
                    'id'      => $group->id,
                    'name'    => $group->name,
                    'members' => $memberData,
                ];
            }
        }

        return $result;
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
