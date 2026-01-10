<?php

namespace App\Http\Controllers\ECP;

use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateHrOrderPdfJob;
use App\Models\Admin\Department;
use App\Models\Admin\OrganizationUnits;
use App\Models\Admin\Role;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Admin\UserWorkHistory;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentJournals;
use App\Models\Document\DocumentReplyComments;
use App\Models\Document\DocumentResolutions;
use App\Models\ECP\SignatureDocuments;
use App\Models\ECP\SignatureDocumentType;
use App\Models\ECP\UserSignatureKey;
use App\Models\Journal\JournalTypeControls;
use App\Models\Journal\JournalTypes;
use App\Models\Protocol\ProtocolDocConfigs;
use App\Models\Protocol\ProtocolDocMember;
use App\Models\Protocol\ProtocolDocReplyComment;
use App\Models\Protocol\ProtocolDocument;
use App\Models\Resolution\ReplyCommentTypes;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class SignDocImzoController extends Controller
{
    private array $workingDocTypes = [
        'send_to_create_res', 'send_to_agreement', 'send_to_sign',
        'from_signer_to_ag', 'from_signer_to_c_res', 'from_ag_to_res',
        'sign_resolution', 'close_exec_doc',
        'send_to_ag_out_doc', 'send_sign_out_doc', 'sign_out_doc'
    ];

    private string $eriSrvIpLoc  = 'http://172.16.100.124:8080';
    private string $eriSrvIpProd = 'http://192.168.200.251:8080';

    public function signDocuments(Request $request): JsonResponse
    {
        $isDemo     = Settings::get('is_demo', false);
        $pkData     = $request->pk_data;
        $docId      = $request->ecp_doc_id;
        $docType    = $request->ecp_doc_type;
        $confirmId  = $request->ecp_confirm_id ?? null;
        $resText    = $request->ecp_r_text;

        $eriSign = $isDemo ? ['status' => 1] : $this->backendAuth($pkData);

        if ($eriSign['status'] != 1 && !$isDemo) {
            return $this->errorResponse(__('Failed to parse SOAP response'), 500);
        }

        $ecpData = $this->prepareEcpData($eriSign, $isDemo);

        $result = match ($docType) {
            'protocol_document' => $this->handleProtocolDocument($docId, $confirmId, $resText),
            'pr_emp_hr_order'   => $this->handleHrEmployeeOrder($docId, $confirmId, $resText),
            default => in_array($docType, $this->workingDocTypes)
                ? $this->handleInternalDocument($docId, $docType, $resText)
                : $this->errorResponse(__('message.document_deleted_system'))
        };

        if ($result instanceof JsonResponse) {
            return $result;
        }

        $this->saveSignatureData($docId, $docType, $ecpData);

        return $this->successResponse();
    }

    private function handleInternalDocument(int $docId, string $docType, ?string $resText): ?JsonResponse
    {
        $journal = DocumentJournals::where('doc_id', $docId)->firstOrFail();

        $steps = [
            'sign'       => $this->getStep($journal, 'SIGN'),
            'agreement'  => $this->getSteps($journal, 'AGREE'),
            'resolution' => $this->getStep($journal, 'RESOL'),
            'execution'  => $this->getSteps($journal, 'EXEC'),
        ];

        $currentAgreement = DocumentExecutionSteps::where('j_id', $journal->id)
            ->where('exec_role', $this->getAgreementRole()->id)
            ->whereIn('to_user', $this->authActiveUserIds())
            ->first();

        $signedAgreements = $steps['agreement']->where('status', 5);

        $urls = $this->buildUrls($journal);
        $meta = [
            'journalType' => $journal->journal->title ?? '',
            'docName'     => $journal->doc->doc_name ?? '',
            'fromUser'    => $journal->fromUser->personal->fullName() ?? '-',
        ];

        $hasControl   = JournalTypeControls::where('journal_type_id', $journal->j_type)->where('status', 1)->exists();
        $controllers  = $this->getControllers();
        $isInfDoc     = ($journal->journal->doc_code ?? '') === 'INF_DOC';

        switch ($docType) {
            case 'send_to_sign':
                $journal->update(['status' => 5]);
                $this->updateStepStatus($steps['sign'], 2);
                $this->sendNotifications([$steps['sign']->to_user], $urls['sign'], $meta);
                $this->logAction($docId, 5, $meta);
                break;

            case 'send_to_agreement':
            case 'send_to_ag_out_doc':
                $journal->update(['status' => 6]);
                $this->activateSteps($steps['agreement'], 2, $urls['agreement'], $meta);
                $this->logAction($docId, 4, $meta);
                break;

            case 'send_to_create_res':
            case 'send_sign_out_doc':
                $this->handleSendToResolution($journal, $steps['resolution'], $hasControl, $controllers, $urls, $meta);
                $this->logAction($docId, 28, $meta);
                break;

            case 'from_signer_to_ag':
                $this->updateStepStatus($steps['sign'], 5);
                $journal->update(['status' => 6]);
                $this->activateSteps($steps['agreement'], 2, $urls['agreement'], $meta);
                $this->createReplyComment($steps['sign'], $resText, 'DOC_SIGNED');
                $this->logAction($docId, 4, $meta);
                break;

            case 'from_signer_to_c_res':
                $this->updateStepStatus($steps['sign'], 5);
                if ($steps['agreement']->isEmpty() && $isInfDoc) {
                    $journal->update(['status' => 10]);
                }
                $this->handleSendToResolution($journal, $steps['resolution'], $hasControl, $controllers, $urls, $meta);
                $this->createReplyComment($steps['sign'], $resText, 'DOC_SIGNED');
                $this->logAction($docId, 28, $meta);
                break;

            case 'from_ag_to_res':
                $this->updateStepStatus($currentAgreement, 5);
                $this->createReplyComment($currentAgreement, $resText, 'DOC_SIGNED');

                if ($steps['agreement']->count() - $signedAgreements->count() === 1) {
                    if ($isInfDoc) $journal->update(['status' => 10]);
                    $this->handleSendToResolution($journal, $steps['resolution'], $hasControl, $controllers, $urls, $meta);
                    $this->logAction($docId, 28, $meta);
                } else {
                    $this->logAction($docId, 6, $meta);
                }
                break;

            case 'sign_resolution':
                $journal->update(['status' => 9]);
                $this->updateStepStatus($steps['resolution'], 5);

                if ($resText) {
                    DocumentResolutions::where('j_id', $journal->id)->first()?->update(['res_text' => $resText]);
                }

                $this->activateSteps($steps['execution'], 2, $urls['execution'], $meta);

                if ($this->isHrOrderType($journal->j_type)) {
                    GenerateHrOrderPdfJob::dispatch($journal->id)->onQueue('pdf');
                }

                $this->logAction($docId, 10, $meta);
                break;

            case 'close_exec_doc':
                $journal->update(['status' => 10]);
                $this->updateStepsStatus($steps['execution'], 5);
                $this->updateStepStatus($steps['resolution'], 5);
                DocumentResolutions::where('j_id', $journal->id)->first()?->update(['status' => 5]);

                $logId = $steps['resolution']->to_user === $this->authActiveUser()->id ? 15 : 19;
                $this->logAction($docId, $logId, $meta);
                break;

            case 'sign_out_doc':
                $journal->update(['status' => 10]);
                $this->updateStepStatus($steps['resolution'], 5);
                $this->logAction($docId, 10, $meta);
                break;
        }

        return null;
    }

    private function handleProtocolDocument(int $docId, ?int $confirmId, ?string $resText): ?JsonResponse
    {
        $document = ProtocolDocument::findOrFail($docId);
        $member   = ProtocolDocMember::where('doc_id', $docId)
            ->whereIn('user_id', $this->authActiveUserIds())
            ->where('status', 2)
            ->firstOrFail();

        if (in_array($confirmId, [4, 5, 7]) && empty(trim($resText ?? ''))) {
            return $this->errorResponse(__('message.comment_on_document'));
        }

        $member->update(['status' => $confirmId]);

        $this->processProtocolSigning($document, $member, $confirmId);

        ProtocolDocReplyComment::create([
            'doc_id'        => $docId,
            'doc_member_id' => $member->id,
            'user_id'       => $this->authActiveUser()->id,
            'doc_text'      => $resText ?? '',
            'status'        => $confirmId,
        ]);

        $this->logAction($docId, 20, $document->docType->title ?? 'Protocol Document', $document->doc_name ?? $docId);

        return null;
    }

    private function handleHrEmployeeOrder(int $docId, ?int $confirmId, ?string $resText): ?JsonResponse
    {
        $document = ProtocolDocument::findOrFail($docId);
        $member   = ProtocolDocMember::where('role_id', 4)
            ->where('doc_id', $docId)
            ->whereIn('user_id', $this->authActiveUserIds())
            ->where('status', 2)
            ->firstOrFail();

        if (in_array($confirmId, [4, 5, 7]) && empty(trim($resText ?? ''))) {
            return $this->errorResponse(__('comment_on_document'));
        }

        $member->update(['status' => $confirmId]);

        if (ProtocolDocMember::where('role_id', 4)->where('doc_id', $docId)->whereNotIn('status', [3, 5, 7])->count() === 0) {
            $document->update(['status' => 3]);
        }

        if (in_array($confirmId, [4, 5, 7])) {
            $document->update(['status' => $confirmId]);
        }

        ProtocolDocReplyComment::create([
            'doc_id'        => $docId,
            'doc_member_id' => $member->id,
            'user_id'       => $this->authActiveUser()->id,
            'doc_text'      => $resText ?? '',
            'status'        => $confirmId,
        ]);

        $this->logAction($docId, 20, $document->docType->title ?? 'Protocol Hr Emp order', $document->doc_name ?? $docId);

        return null;
    }

    private function processProtocolSigning($document, $member, $confirmId): void
    {
        $configs     = ProtocolDocConfigs::where('protocol_type_id', $document->docType->id)->orderBy('order')->get();
        $signerRoles = $configs->pluck('role_id')->toArray();

        if ($document->docType->sign_type == 1) {
            $pending = ProtocolDocMember::where('doc_id', $document->id)
                ->whereIn('role_id', $signerRoles)
                ->whereNotIn('status', [3, 5, 7])
                ->count();

            if (!$pending) {
                $document->update(['status' => 3]);
            }
        } else {
            $unfinished = ProtocolDocMember::where('doc_id', $document->id)
                ->where('role_id', $member->role_id)
                ->whereNotIn('status', [3, 5, 7])
                ->count();

            if (!$unfinished) {
                $currentConfig = $configs->where('role_id', $member->role_id)->first();
                if ($currentConfig) {
                    $this->activateNextRole($document, $document->docType, $currentConfig->order);
                }
            }
        }
    }

    private function saveSignatureData(int $docId, string $docType, array $ecpData): void
    {
        $userKey = UserSignatureKey::where('user_id', auth()->id())
            ->where('serial_number', $ecpData['serialNumber'])
            ->firstOrNew(['user_id' => auth()->id(), 'serial_number' => $ecpData['serialNumber']]);

        $userKey->json_data = json_encode($ecpData);
        $userKey->save();

        $docTypeModel = SignatureDocumentType::where('doc_code', $docType)->firstOrNew(['doc_code' => $docType], ['id' => 1]);

        SignatureDocuments::create([
            'doc_id'        => $docId,
            'doc_type'      => $docTypeModel->id,
            'user_id'       => $this->authActiveUser()->id,
            'key_id'        => $userKey->id,
            'signing_time'  => $ecpData['signingTime'] ?? now(),
        ]);
    }

    private function prepareEcpData(array $eriSign, bool $isDemo): array
    {
        if ($isDemo) {
            return [
                'success'             => 1,
                'signingTime'         => Carbon::now()->format('Y-m-d H:i:s'),
                'signature'           => Uuid::uuid4()->toString(),
                'digest'              => Uuid::uuid4()->toString(),
                'serialNumber'        => Uuid::uuid4()->toString(),
                'subjectName'         => Uuid::uuid4()->toString(),
                'verified'            => true,
                'certificateVerified' => 0,
            ];
        }

        return [
            'success'             => $eriSign['status'],
            'signingTime'         => $eriSign['signingTime'],
            'signature'           => $eriSign['signature'],
            'digest'              => $eriSign['digest'],
            'serialNumber'        => $eriSign['serialNumber'],
            'subjectName'         => $eriSign['subjectName'],
            'verified'            => $eriSign['verified'],
            'certificateVerified' => $eriSign['certificateVerified'],
        ];
    }

    private function getStep($journal, string $code): ?DocumentExecutionSteps
    {
        return DocumentExecutionSteps::where('j_id', $journal->id)
            ->whereHas('role', fn($q) => $q->where('doc_code', $code)->where('status', 1))
            ->first();
    }

    private function getSteps($journal, string $code)
    {
        return DocumentExecutionSteps::where('j_id', $journal->id)
            ->whereHas('role', fn($q) => $q->where('doc_code', $code)->where('status', 1))
            ->get();
    }

    private function updateStepStatus(?DocumentExecutionSteps $step, int $status): void
    {
        $step?->update(['status' => $status]);
    }

    private function updateStepsStatus($steps, int $status): void
    {
        $steps->each(fn($s) => $s->update(['status' => $status]));
    }

    private function activateSteps($steps, int $status, string $url, array $meta): void
    {
        $steps->each(fn($step) => $this->createMailCron($step->to_user, $meta['journalType'], $meta['fromUser'], $url, $meta['docName']));
        $this->updateStepsStatus($steps, $status);
    }

    private function sendNotifications(array $userIds, string $url, array $meta): void
    {
        foreach ($userIds as $id) {
            $this->createMailCron($id, $meta['journalType'], $meta['fromUser'], $url, $meta['docName']);
        }
    }

    private function handleSendToResolution($journal, $resolutionStep, bool $hasControl, $controllers, array $urls, array $meta): void
    {
        if ($hasControl) {
            $journal->update(['status' => 7]);
            $this->updateStepStatus($resolutionStep, 1);
            $this->sendNotifications($controllers->pluck('id')->toArray(), $urls['control'], $meta);
        } else {
            $journal->update(['status' => 8]);
            $this->updateStepStatus($resolutionStep, 2);
            $this->createMailCron($resolutionStep?->to_user, $meta['journalType'], $meta['fromUser'], $urls['resolution'], $meta['docName']);
        }
    }

    private function createReplyComment($step, ?string $text, string $code): void
    {
        $type = ReplyCommentTypes::where('doc_code', $code)->firstOrFail();

        DocumentReplyComments::create([
            'doc_exec_step_id'   => $step?->id,
            'j_id'               => $step?->j_id,
            'from_dep'           => $this->authActiveUser()->dep_id,
            'from_user'          => $this->authActiveUser()->id,
            'reply_comment_type' => $type->id,
            'res_text'           => $text ?? '',
            'status'             => 5,
        ]);
    }

    private function getControllers(): \Illuminate\Support\Collection
    {
        $role = Role::whereIn('name', ['Ijro nazorati', 'Control_Emp'])->first();
        return $role
            ? UserWorkHistory::whereHas('user.userRoles', fn($q) => $q->where('role_id', $role->id))->get()
            : collect();
    }

    private function isHrOrderType(int $jType): bool
    {
        $hrOrder = JournalTypes::where('doc_code', 'HR_ORD')->where('status', 1)->first();
        return $hrOrder && JournalTypes::where('parent_id', $hrOrder->id)->where('status', 1)->pluck('id')->contains($jType);
    }

    private function buildUrls($journal): array
    {
        $base = "{$journal->id}/{$journal->doc->doc_hash}";
        return [
            'sign'       => "documents/view-sign-document/{$base}",
            'agreement'  => "documents/view-approval-document/{$base}",
            'resolution' => "documents/view-confirm-document/{$base}",
            'execution'  => "documents/view-exec-document/{$base}",
            'control'    => "documents/view-control-document/{$base}",
        ];
    }

    private function logAction(int $docId, int $actionId, array|string $logType, ?string $docName = null): void
    {
        $type = is_array($logType) ? ($logType['journalType'] ?? '') : $logType;
        $name = $docName ?? (is_array($logType) ? ($logType['docName'] ?? '') : '');

        parent::createLog($docId, $actionId, $type, $name);
    }

    private function successResponse(): JsonResponse
    {
        return response()->json([
            'success' => 200,
            'title'   => __('message.success_message'),
            'message' => __('message.task_completed_successfully'),
        ]);
    }

    private function errorResponse(string $message, int $code = 201): JsonResponse
    {
        return response()->json([
            'success' => $code,
            'title'   => __('message.error_message'),
            'message' => $message,
        ], $code === 500 ? 500 : 200);
    }

    private function activateNextRole($document, $protocolType, int $current_order): void
    {
        $configs = ProtocolDocConfigs::where('protocol_type_id', $protocolType->id)->orderBy('order')->get();

        $next = $configs->where('order', '>', $current_order)->sortBy('order')->first();

        while ($next) {
            if (ProtocolDocMember::where('doc_id', $document->id)->where('role_id', $next->role_id)->exists()) {
                ProtocolDocMember::where('doc_id', $document->id)->where('role_id', $next->role_id)->update(['status' => 2]);

                $members = ProtocolDocMember::where('doc_id', $document->id)->where('role_id', $next->role_id)->get();
                $url = "protocol/{$protocolType->doc_code}/{$document->id}/{$document->doc_hash}";
                $fromUser = $document->created_user
                    ? UserPersonalInfo::where('user_id', $document->created_user)->first()?->fullName() ?? '-'
                    : '-';

                foreach ($members as $m) {
                    $this->createMailCron($m->user_id, $document->docType->title ?? '', $fromUser, $url, $document->doc_name ?? '');
                }

                return;
            }

            $current_order = $next->order;
            $next = $configs->where('order', '>', $current_order)->sortBy('order')->first();
        }

        $document->update(['status' => 3]);
    }

    private function backendAuth($data): array
    {
        $response = $this->curlPost('/backend/auth', $data);
        $json = json_decode($response, true);

        return ($json['status'] ?? 0) == -20
            ? $this->frontendPkcs7($data)
            : ['status' => $json['status'] ?? 0, 'message' => $json['message'] ?? ''];
    }

    private function frontendPkcs7($data): array
    {
        $response = $this->curlPost('/frontend/timestamp/pkcs7', $data);
        $json = json_decode($response, true);

        if (($json['status'] ?? 0) == 1) {
            return $this->backendAttached($json['pkcs7b64']);
        }

        return ['status' => $json['status'] ?? 0, 'message' => $json['message'] ?? ''];
    }

    private function backendAttached($pkcs7b64): array
    {
        $response = $this->curlPost('/backend/pkcs7/verify/attached', $pkcs7b64);
        $json = json_decode($response, true);

        if (($json['status'] ?? 0) == 1) {
            $info = $json['pkcs7Info']['signers'][0];
            return [
                'status'              => 1,
                'signingTime'         => $info['signingTime'],
                'signature'           => $info['signature'],
                'digest'              => $info['digest'],
                'serialNumber'        => $info['certificate'][0]['serialNumber'],
                'subjectName'         => $info['certificate'][0]['subjectName'],
                'verified'            => $info['verified'],
                'certificateVerified' => $info['certificateVerified'],
            ];
        }

        return ['status' => $json['status'] ?? 0, 'message' => $json['message'] ?? ''];
    }

    public function frontChallenge()
    {
        return Http::get($this->eriSrvIpLoc.'/frontend/challenge?_uc='.now().'_'.rand());
    }

    public function backendAuthLogin(Request $request)
    {
        $pkcs7 = $request->pkcs7;

        $path = '/backend/auth';

        $pkInfo = $this->curlPost($path, $pkcs7);

        $jsonDecodeBack = json_decode($pkInfo, true);
        $pin = $jsonDecodeBack['subjectCertificateInfo']['subjectName']['1.2.860.3.16.1.2'];

        $user = UserPersonalInfo::where('doc_pin', $pin)->first();

        $status = 0;
        $message = 'User topilmadi';

        if (!$user) {
            $subjectName = $jsonDecodeBack['subjectCertificateInfo']['subjectName'];
            $isOrg = !empty($subjectName['O']);

            if ($isOrg) {
                $organization = OrganizationUnits::create([
                    'code' => $subjectName['UID'],
                    'name_uz' => $subjectName['O'],
                    'name_ru' => $subjectName['O'],
                    'name_kr' => $subjectName['O'],
                    'name_en' => $subjectName['O'],
                    'tax_number' => $subjectName['1.2.860.3.16.1.1'],
                    'owner_user_id' => 1,
                    'parent_id' => 0,
                    'level' => 1,
                    'status' => 1,
                    'meta_data' => null,
                ]);

                $jsonFile1 = base_path('database/seeders/data/departments.json');
                $jsonData1 = json_decode(File::get($jsonFile1), true);
                $item1 = $jsonData1['departments'][0];
                $department = Department::create([
                    'code' => $item1['code'].'_'.$subjectName['UID'],
                    'org_unit_id' => $organization->id,
                    'name_uz' => $item1['name_uz'],
                    'name_kr' => $item1['name_kr'],
                    'name_ru' => $item1['name_ru'],
                    'name_en' => $item1['name_en'],
                    'parent_id' => $item1['parent_id'],
                    'order_number' => $item1['order_number'],
                    'level_number' => $item1['level_number'],
                    'status' => $item1['status'],
                ]);
            }

            $user = User::create([
                'username' => strtolower($subjectName['Name'])[0].'_'.strtolower($subjectName['SURNAME']),
                'email' => strtolower($subjectName['Name']).'_'.strtolower($subjectName['SURNAME']).'@gmail.com',
                'password' => Hash::make($jsonDecodeBack['subjectCertificateInfo']['serialNumber']),
                'user_hash' => $this->uniqueId(),
                'org_unit_id' => $isOrg ? $organization->id : 1,
                'status' => 1
            ]);

            $cn = $subjectName['CN'];
            [$lastName, $firstName, $middleName] = explode(' ', $cn, 3);

            $officeEmpPersonal = new UserPersonalInfo();
            $officeEmpPersonal->user_id = $user->id;
            $officeEmpPersonal->is_emp = 2;
            $officeEmpPersonal->doc_pin = $pin;
            $officeEmpPersonal->doc_number = 'AD1234567';
            $officeEmpPersonal->doc_type = 2;
            $officeEmpPersonal->birthday = '1991-01-01';
            $officeEmpPersonal->gender = 1;
            $officeEmpPersonal->phone = 971253456;
            $officeEmpPersonal->l_name = $lastName;
            $officeEmpPersonal->f_name = $firstName;
            $officeEmpPersonal->m_name = $middleName;
            $officeEmpPersonal->lang_code = 'uz';
            $officeEmpPersonal->doc_sign_type = 1;
            $officeEmpPersonal->sort = 1;
            $officeEmpPersonal->save();

            $nextYear = Carbon::now()->addYear()->format('Y-m-d');

            $officeEmpWork = new UserWorkHistory();
            $officeEmpWork->user_id = $user->id;
            $officeEmpWork->org_id = $isOrg ? $organization->id : 1;
            $officeEmpWork->dep_id = $isOrg ? $department->id : 2;
            $officeEmpWork->post_id = $isOrg ? 1 : 3;
            $officeEmpWork->from_date = Carbon::now();
            $officeEmpWork->to_date = $nextYear;
            $officeEmpWork->status = 1;
            $officeEmpWork->save();

            if ($isOrg) {
                $role = Role::where('name', 'IT_Admin')->first();
            } else {
                $role = Role::where('name', 'Dep_Emp')->first();
            }

            if ($role) {
                $user->assignRole([$role->id]);
            }

            if ($organization) {
                $organization->update(['owner_user_id' => $user->id]);
            }

            Auth::loginUsingId($user->id);

            $status = 1;
            $message = 'ok';
        }

        if ($user) {
            Auth::loginUsingId($user->user_id);

            $status = 1;
            $message = 'ok';
        }

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    private function curlPost(string $path, string $postData): ?string
    {
        $url = (in_array($_SERVER['HTTP_HOST'] ?? '', ['edo-demo.loc', '172.17.110.44', 'localhost:8000']))
            ? $this->eriSrvIpLoc . $path
            : $this->eriSrvIpProd . $path;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_HTTPHEADER     => [
                'X-Real-IP: 84.54.116.179',
                'Host: edo.eai.uz',
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response ?: null;
    }
}
