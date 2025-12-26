<?php

namespace App\Http\Controllers\ECP;

use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateHrOrderPdfJob;
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
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;


class SignDocImzoController extends Controller
{
    //
    protected $eri_srv_ip_loc = 'http://172.16.100.124:8080';
    protected $eri_srv_ip_prod = 'http://192.168.200.251:8080';

    public function signDocuments(Request $request): JsonResponse
    {
        //
        $isDemo = Settings::get('is_demo', false);

        $pk_data = $request->pk_data;

        $ecp_doc_id = $request->ecp_doc_id;

        $ecp_doc_type = $request->ecp_doc_type;

        $ecp_confirm_id = $request->ecp_confirm_id;

        $ecp_r_text = $request->ecp_r_text;

        if (!$isDemo) {
            $eriSign = $this->backendAuth($pk_data);
        } else {
            $eriSign = ['status' => false];
        }

        if ($eriSign || $isDemo) {
            if ($eriSign['status'] == 1 || $isDemo) {
                if (!$isDemo) {
                    $success = $eriSign['status'];
                    $signingTime = $eriSign['signingTime'];
                    $signature = $eriSign['signature'];
                    $serialNumber = $eriSign['serialNumber'];
                    $subjectName = $eriSign['subjectName'];
                    $digest = $eriSign['digest'];
                    $verified = $eriSign['verified'];
                    $certificateVerified = $eriSign['certificateVerified'];

                    $ecp_data =
                        ['success' => $success, 'signingTime' => $signingTime, 'signature' => $signature, 'digest' => $digest,
                            'serialNumber' => $serialNumber, 'subjectName' => $subjectName, 'verified' => $verified,
                            'certificateVerified' => $certificateVerified
                        ];
                } else {
                    $success = 1;
                    $certificateVerified = 0;
                    $ecp_data = [
                        'success' => false, 'signingTime' => Carbon::now()->format('Y-m-d H:i:s'), 'signature' =>  Uuid::uuid4(), 'digest' =>  Uuid::uuid4(),
                        'serialNumber' =>  Uuid::uuid4(), 'subjectName' =>  Uuid::uuid4(), 'verified' => true,
                        'certificateVerified' => $certificateVerified
                    ];
                }

                $working_doc_types = ['send_to_create_res', 'send_to_agreement', 'send_to_sign', 'from_signer_to_ag', 'from_signer_to_c_res', 'from_ag_to_res',
                    'sign_resolution', 'close_exec_doc', 'send_to_ag_out_doc', 'send_sign_out_doc', 'sign_out_doc'];

                if ($success == 1) {
                    if (in_array($ecp_doc_type, $working_doc_types)) {
                        $journal = DocumentJournals::where('doc_id', $ecp_doc_id)->first();

                        if ($journal) {
                            $isInJournalTypeControls = JournalTypeControls::where('journal_type_id', $journal->j_type)->where('status', 1)->first();

                            $sign_step = DocumentExecutionSteps::where('j_id', $journal->id)->whereHas('role', function ($query) {
                                $query->where('doc_code', 'SIGN')->where('status', 1);
                            })->first();

                            $agreement_step = DocumentExecutionSteps::where('j_id', $journal->id)->whereHas('role', function ($query) {
                                $query->where('doc_code', 'AGREE')->where('status', 1);
                            })->get();

                            $resolution_step = DocumentExecutionSteps::where('j_id', $journal->id)->whereHas('role', function ($query) {
                                $query->where('doc_code', 'RESOL')->where('status', 1);
                            })->first();

                            $execution_step = DocumentExecutionSteps::where('j_id', $journal->id)->whereHas('role', function ($query) {
                                $query->where('doc_code', 'EXEC')->where('status', 1);
                            })->get();

                            $current_agreement = DocumentExecutionSteps::where('j_id', $journal->id)
                                ->where('exec_role', $this->getAgreementRole()->id)->whereIn('to_user', $this->authActiveUserIds())->first();

                            $signed_agreement_step = DocumentExecutionSteps::where('j_id', $journal->id)->whereHas('role', function ($query) {
                                $query->where('doc_code', 'AGREE')->where('status', 1);
                            })->where('status', 5)->get();

                            $sign_url = 'documents/view-sign-document/'.$journal->id.'/'.$journal->doc->doc_hash;
                            $agreement_url = 'documents/view-approval-document/'.$journal->id.'/'.$journal->doc->doc_hash;
                            $resolution_url = 'documents/view-confirm-document/'.$journal->id.'/'.$journal->doc->doc_hash;
                            $execution_url = 'documents/view-exec-document/'.$journal->id.'/'.$journal->doc->doc_hash;
                            $control_url = 'documents/view-control-document/'.$journal->id.'/'.$journal->doc->doc_hash;
                            $journalType = $journal->journal->title??'';
                            $docName = $journal->doc->doc_name??'';
                            $fromUser = $journal->fromUser->personal->fullName()??'-';

                            $controller_role = Role::whereIn('name', ['Ijro nazorati', 'Control_Emp'])->first();
                            $controllers = UserWorkHistory::whereHas('user', function ($query) use ($controller_role) {
                                $query->whereHas('userRoles', function ($query2) use ($controller_role) {
                                    $query2->where('role_id', $controller_role->id);
                                });
                            })->get();

                            if ($ecp_doc_type == 'send_to_sign') {
                                $journal->update(['status' => 5]);

                                $sign_step->update(['status' => 2]);

                                $this->createMailCron($sign_step->to_user, $journalType, $fromUser, $sign_url, $docName);

                                $this->createLog($ecp_doc_id, 5, $journalType, $docName);
                            }

                            if ($ecp_doc_type == 'send_to_agreement') {
                                $journal->update(['status' => 6]);

                                foreach ($agreement_step as $agreement) {
                                    $agreement->update(['status' => 2]);

                                    $this->createMailCron($agreement->to_user, $journalType, $fromUser, $agreement_url, $docName);
                                }

                                $this->createLog($ecp_doc_id, 4, $journalType, $docName);
                            }

                            if ($ecp_doc_type == 'send_to_create_res') {
                                $journal->update(['status' => 8]);

                                if ($resolution_step) {
                                    $resolution_step->update(['status' => 2]);

                                    $this->createMailCron($resolution_step->to_user, $journalType, $fromUser, $resolution_url, $docName);

                                    if ($isInJournalTypeControls) {
                                        $journal->update(['status' => 7]);

                                        $resolution_step->update(['status' => 1]);

                                        if ($controllers && $controllers->count()){
                                            foreach ($controllers as $controller) {
                                                $this->createMailCron($controller->id, $journalType, $fromUser, $control_url, $docName);
                                            }
                                        }
                                    } else {
                                        $journal->update(['status' => 8]);

                                        $resolution_step->update(['status' => 2]);

                                        $this->createMailCron($resolution_step->to_user, $journalType, $fromUser, $resolution_url, $docName);
                                    }
                                }

                                $this->createLog($ecp_doc_id, 28, $journalType, $docName);
                            }

                            if ($ecp_doc_type === 'from_signer_to_ag') {
                                $sign_step->update(['status' => 5]);

                                $journal->update(['status' => 6]);

                                foreach ($agreement_step as $agreement) {
                                    $agreement->update(['status' => 2]);

                                    $this->createMailCron($agreement->to_user, $journalType, $fromUser, $agreement_url, $docName);
                                }

                                $replyCommentType = ReplyCommentTypes::where('doc_code', 'DOC_SIGNED')->first();

                                $replyComment = new DocumentReplyComments();
                                $replyComment->doc_exec_step_id = $sign_step->id;
                                $replyComment->j_id = $journal->id;
                                $replyComment->from_dep = $this->authActiveUser()->dep_id;
                                $replyComment->from_user = $this->authActiveUser()->id;
                                $replyComment->reply_comment_type = $replyCommentType->id;
                                $replyComment->res_text = $ecp_r_text??'';
                                $replyComment->status = 5;
                                $replyComment->save();

                                $this->createLog($ecp_doc_id, 4, $journalType, $docName);
                            }

                            if ($ecp_doc_type === 'from_signer_to_c_res') {
                                $sign_step->update(['status' => 5]);

                                $journal->update(['status' => 8]);

                                if (!count($agreement_step)) {
                                    $journalTypeForInfD = $journal->journal->doc_code??'';
                                    if ($journalTypeForInfD == 'INF_DOC') {
                                        $journal->update(['status' => 10]);
                                    }
                                }

                                if ($resolution_step) {
                                    $resolution_step->update(['status' => 2]);

                                    $this->createMailCron($resolution_step->to_user, $journalType, $fromUser, $resolution_url, $docName);
                                }

                                if ($isInJournalTypeControls) {
                                    $journal->update(['status' => 7]);

                                    $resolution_step->update(['status' => 1]);

                                    if ($controllers && $controllers->count()){
                                        foreach ($controllers as $controller) {
                                            $this->createMailCron($controller->id, $journalType, $fromUser, $control_url, $docName);
                                        }
                                    }
                                } else {
                                    $journal->update(['status' => 8]);

                                    $resolution_step->update(['status' => 2]);

                                    $this->createMailCron($resolution_step->to_user, $journalType, $fromUser, $resolution_url, $docName);
                                }

                                $replyCommentType = ReplyCommentTypes::where('doc_code', 'DOC_SIGNED')->first();

                                $replyComment = new DocumentReplyComments();
                                $replyComment->doc_exec_step_id = $sign_step->id;
                                $replyComment->j_id = $journal->id;
                                $replyComment->from_dep = $this->authActiveUser()->dep_id;
                                $replyComment->from_user = $this->authActiveUser()->id;
                                $replyComment->reply_comment_type = $replyCommentType->id;
                                $replyComment->res_text = $ecp_r_text??'';
                                $replyComment->status = 5;
                                $replyComment->save();

                                $this->createLog($ecp_doc_id, 28, $journalType, $docName);
                            }

                            if ($ecp_doc_type === 'from_ag_to_res') {
                                if ($agreement_step->count() - $signed_agreement_step->count() == 1) {
                                    $journal->update(['status' => 8]);

                                    $this->createLog($ecp_doc_id, 28, $journalType, $docName);

                                    $journalTypeForInfD = $journal->journal->doc_code??'';
                                    if ($journalTypeForInfD == 'INF_DOC') {
                                        $journal->update(['status' => 10]);
                                    }

                                    if ($resolution_step) {
                                        $resolution_step->update(['status' => 2]);

                                        $this->createMailCron($resolution_step->to_user, $journalType, $fromUser, $resolution_url, $docName);
                                    }
                                } else {
                                    $this->createLog($ecp_doc_id, 6, $journalType, $docName);
                                }

                                if ($agreement_step->count() - $signed_agreement_step->count() == 1) {
                                    if ($isInJournalTypeControls) {
                                        $journal->update(['status' => 7]);

                                        $resolution_step->update(['status' => 1]);

                                        if ($controllers && $controllers->count()){
                                            foreach ($controllers as $controller) {
                                                $this->createMailCron($controller->id, $journalType, $fromUser, $control_url, $docName);
                                            }
                                        }
                                    } else {
                                        $journal->update(['status' => 8]);

                                        $resolution_step->update(['status' => 2]);

                                        $this->createMailCron($resolution_step->to_user, $journalType, $fromUser, $resolution_url, $docName);
                                    }
                                }

                                $current_agreement->update(['status' => 5]);

                                $replyCommentType = ReplyCommentTypes::where('doc_code', 'DOC_SIGNED')->first();

                                $replyComment = new DocumentReplyComments();
                                $replyComment->doc_exec_step_id = $current_agreement->id;
                                $replyComment->j_id = $journal->id;
                                $replyComment->from_dep = $this->authActiveUser()->dep_id;
                                $replyComment->from_user = $this->authActiveUser()->id;
                                $replyComment->reply_comment_type = $replyCommentType->id;
                                $replyComment->res_text = $ecp_r_text??'';
                                $replyComment->status = 5;
                                $replyComment->save();
                            }

                            if ($ecp_doc_type === 'sign_resolution') {

                                $journal->update(['status' => 9]);

                                if ($resolution_step) {
                                    $resolution_step->update(['status' => 5]);

                                    $resolution = DocumentResolutions::where('j_id', $journal->id)->first();

                                    if ($resolution && $ecp_r_text) {
                                        $resolution->update(['res_text' => $ecp_r_text]);
                                    }
                                }

                                if ($execution_step->count()) {
                                    foreach ($execution_step as $exec) {
                                        $exec->update(['status' => 2]);

                                        $this->createMailCron($exec->to_user, $journalType, $fromUser, $execution_url, $docName);
                                    }
                                }

                                $hrOrderType = JournalTypes::where('doc_code', 'HR_ORD')
                                    ->where('status', 1)
                                    ->first();

                                $hrOrderJournalTypes = JournalTypes::where('parent_id', $hrOrderType->id)
                                    ->where('status', 1)
                                    ->pluck('id')
                                    ->toArray();

                                if (in_array($journal->j_type, $hrOrderJournalTypes)) {
                                    GenerateHrOrderPdfJob::dispatch($journal->id)->onQueue('pdf');
                                }

                                $this->createLog($ecp_doc_id, 10, $journalType, $docName);
                            }


                            if ($ecp_doc_type === 'close_exec_doc') {

                                $journal->update(['status' => 10]);

                                if ($execution_step->count()) {
                                    foreach ($execution_step as $exec) $exec->update(['status' => 5]);
                                }

                                if ($resolution_step) {
                                    $resolution_step->update(['status' => 5]);

                                    $resolution = DocumentResolutions::where('j_id', $journal->id)->first();

                                    $resolution->update(['status' => 5]);
                                }

                                $act_id = $resolution_step->to_user === $this->authActiveUser()->id ? 15 : 19;
                                $this->createLog($ecp_doc_id, $act_id, $journalType, $docName);
                            }

                            if ($ecp_doc_type === 'send_to_ag_out_doc') {

                                $journal->update(['status' => 6]);

                                foreach ($agreement_step as $agreement) {
                                    $agreement->update(['status' => 2]);

                                    $this->createMailCron($agreement->to_user, $journalType, $fromUser, $agreement_url, $docName);
                                }

                                $this->createLog($ecp_doc_id, 4, $journalType, $docName);
                            }

                            if ($ecp_doc_type === 'send_sign_out_doc') {

                                $journal->update(['status' => 8]);

                                if ($resolution_step) {
                                    $resolution_step->update(['status' => 2]);

                                    $this->createMailCron($resolution_step->to_user, $journalType, $fromUser, $resolution_url, $docName);
                                }

                                $this->createLog($ecp_doc_id, 28, $journalType, $docName);
                            }

                            if ($ecp_doc_type === 'sign_out_doc') {

                                $journal->update(['status' => 10]);

                                if ($resolution_step) {
                                    $resolution_step->update(['status' => 5]);
                                }

                                $this->createLog($ecp_doc_id, 10, $journalType, $docName);
                            }

                        }
                    } elseif ($ecp_doc_type == 'protocol_document') {
                        $document = ProtocolDocument::find($ecp_doc_id);
                        $protocolType = $document->docType;

                        $documentMember = ProtocolDocMember::where('doc_id', $ecp_doc_id)
                            ->whereIn('user_id', $this->authActiveUserIds())->where('status', 2)->first();

                        if ($document && $documentMember) {
                            if (in_array($ecp_confirm_id, [4,5,7])) {
                                if (!trim(''.$ecp_r_text.'')){
                                    return response()->json([
                                        'success' => 201,
                                        'title' => __('message.error_message'),
                                        'message' => __('message.comment_on_document')
                                    ]);
                                }
                            }

                            $documentMember->update(['status' => $ecp_confirm_id]);

                            $configs = ProtocolDocConfigs::where('protocol_type_id', $protocolType->id)->orderBy('order')->get();
                            $signer_roles = $configs->pluck('role_id')->toArray();

                            if ($protocolType->sign_type == 1) {
                                // Parallel: check if all members are done
                                $allMemberStatus = ProtocolDocMember::where('doc_id', $ecp_doc_id)->whereIn('role_id', $signer_roles)->whereNotIn('status', [3,5,7])->count();
                                if (!$allMemberStatus) {
                                    $document->update(['status' => 3]);
                                }
                            } else {
                                // Sequential
                                $current_role = $documentMember->role_id;
                                $unfinished_in_role = ProtocolDocMember::where('doc_id', $ecp_doc_id)->where('role_id', $current_role)->whereNotIn('status', [3,5,7])->count();
                                if (!$unfinished_in_role) {
                                    $current_config = $configs->where('role_id', $current_role)->first();
                                    if ($current_config) {
                                        $this->activateNextRole($document, $protocolType, $current_config->order);
                                    }
                                }
                            }

                            $replHis = new ProtocolDocReplyComment();
                            $replHis->doc_id = $ecp_doc_id;
                            $replHis->doc_member_id = $documentMember->id;
                            $replHis->user_id = $this->authActiveUser()->id;
                            $replHis->doc_text = $ecp_r_text;
                            $replHis->status = $ecp_confirm_id;
                            $replHis->save();

                            $log_type = $document->docType->title??'Protocol Document';
                            $doc_name = $document->doc_name??$ecp_doc_id;
                            $this->createLog($ecp_doc_id, 20, $log_type, $doc_name);
                        }
                    } elseif ($ecp_doc_type == 'pr_emp_hr_order') {
                        $document = ProtocolDocument::find($ecp_doc_id);

                        $documentMember = ProtocolDocMember::where('role_id', 4)->where('doc_id', $ecp_doc_id)
                            ->whereIn('user_id', $this->authActiveUserIds())->where('status', 2)->first();

                        if ($document && $documentMember) {
                            if (in_array($ecp_confirm_id, [4,5,7])) {
                                if (!trim(''.$ecp_r_text.'')) {
                                    return response()->json([
                                        'success' => 201,
                                        'title' => __('error_message'),
                                        'message' => __('comment_on_document')
                                    ]);
                                }
                                $document->update(['status' => $ecp_confirm_id]);
                            }

                            $documentMember->update(['status' => $ecp_confirm_id]);

                            $allMemberStatus = ProtocolDocMember::where('role_id', 4)->where('doc_id', $ecp_doc_id)->whereNotIn('status', [3,5,7])->count();
                            if (!$allMemberStatus) {
                                $document->update(['status' => 3]);
                            }

                            $replHis = new ProtocolDocReplyComment();
                            $replHis->doc_id = $ecp_doc_id;
                            $replHis->doc_member_id = $documentMember->id;
                            $replHis->user_id = $this->authActiveUser()->id;
                            $replHis->doc_text = $ecp_r_text;
                            $replHis->status = $ecp_confirm_id;
                            $replHis->save();

                            $log_type = $document->docType->title ?? 'Protocol Hr Emp order';
                            $doc_name = $document->doc_name ?? $ecp_doc_id;
                            $this->createLog($ecp_doc_id, 20, $log_type, $doc_name);
                        }
                    } else {
                        return response()->json([
                            'success' => 201,
                            'title' => __('message.error_message'),
                            'message' => __('message.document_deleted_system')
                        ]);
                    }

                    $user_key = UserSignatureKey::where('user_id', auth()->id())
                        ->where('serial_number', $ecp_data['serialNumber'])->first();

                    if (!$user_key) {
                        $user_key = new UserSignatureKey();
                        $user_key->user_id = auth()->id();
                        $user_key->serial_number = $ecp_data['serialNumber'];
                        $user_key->json_data = json_encode($ecp_data);
                        $user_key->save();
                    }

                    $doc_type = SignatureDocumentType::where('doc_code', $ecp_doc_type)->first();

                    $signatureDocument = new SignatureDocuments();
                    $signatureDocument->doc_id = $ecp_doc_id;
                    $signatureDocument->doc_type = $doc_type->id??1;
                    $signatureDocument->user_id = $this->authActiveUser()->id;
                    $signatureDocument->key_id = $user_key->id;
                    $signatureDocument->signing_time = $ecp_data['signingTime']??Carbon::now();
                    $signatureDocument->save();
                }
            } else {
                return response()->json([
                    'success' => 500,
                    'title' => __('error_message'),
                    'message' => __('Failed to parse SOAP response'),
                ], 500);
            }
        }

        return response()->json([
            'success' => 200,
            'title' => __('message.success_message'),
            'message' => __('message.task_completed_successfully'),
        ]);
    }

    private function activateNextRole($document, $protocolType, $current_order)
    {
        $configs = ProtocolDocConfigs::where('protocol_type_id', $protocolType->id)->orderBy('order')->get();

        $next_config = $configs->filter(function ($c) use ($current_order) {
            return $c->order > $current_order;
        })->sortBy('order')->first();

        while ($next_config) {
            $has_members = ProtocolDocMember::where('doc_id', $document->id)
                ->where('role_id', $next_config->role_id)
                ->exists(); // More efficient than count() > 0

            if ($has_members) {
                ProtocolDocMember::where('doc_id', $document->id)
                    ->where('role_id', $next_config->role_id)
                    ->update(['status' => 2]);

                $next_members = ProtocolDocMember::where('doc_id', $document->id)
                    ->where('role_id', $next_config->role_id)
                    ->get();

                $url = 'protocol/' . $protocolType->doc_code . '/' . $document->id . '/' . $document->doc_hash;

                foreach ($next_members as $member) {
                    $this->createMailCron(
                        $member->user_id,
                        $document->docType->title,
                        $document->created_user ? UserPersonalInfo::where('user_id', $document->created_user)->first()->fullName() ?? '-' : '-',
                        $url,
                        $document->doc_name
                    );
                }

                return true;
            }

            // Move to the next config
            $current_order = $next_config->order;
            $next_config = $configs->filter(function ($c) use ($current_order) {
                return $c->order > $current_order;
            })->sortBy('order')->first();
        }

        // No more roles with members, complete the document
        $document->update(['status' => 3]);

        return false;
    }

    public function backendAuth($data)
    {
        $path = '/backend/auth';
        $curlData = $this->curlPost($path, $data);
        $status = 0;
        $message = '';
        if ($curlData) {
            $jsonDecode = json_decode($curlData, true);
            $status = $jsonDecode['status'];
            $message = $jsonDecode['message'];
            if ($status == -20) {
                return $this->frontendPkcs7($data);
            }
        }

        return ['status' => $status, 'message' => $message];
    }

    public function frontendPkcs7($data)
    {
        $path = '/frontend/timestamp/pkcs7';
        $curlPostFront = $this->curlPost($path, $data);
        $status = 0;
        $message = '';
        if ($curlPostFront) {
            $jsonDecodeFront = json_decode($curlPostFront, true);
            $status = $jsonDecodeFront['status'];
            $message = $jsonDecodeFront['message'];
            if ($status == 1) {
                $pkcs7b64 = $jsonDecodeFront['pkcs7b64'];
                return $this->backendAttached($pkcs7b64);
            }
        }

        return ['status' => $status, 'message' => $message];
    }

    public function backendAttached($data)
    {
        $path = '/backend/pkcs7/verify/attached';
        $curlPostBack = $this->curlPost($path, $data);
        $status = 0;
        $message = '';
        if ($curlPostBack) {
            $jsonDecodeBack = json_decode($curlPostBack, true);
            $status = $jsonDecodeBack['status'];
            $message = $jsonDecodeBack['message'];
            if ($status == 1) {
                $pkcs7Info = $jsonDecodeBack['pkcs7Info']['signers'][0];
                $signingTime = $pkcs7Info['signingTime'];
                $signature = $pkcs7Info['signature'];
                $digest = $pkcs7Info['digest'];
                $serialNumber = $pkcs7Info['certificate'][0]['serialNumber'];
                $subjectName = $pkcs7Info['certificate'][0]['subjectName'];
                $verified = $pkcs7Info['verified'];
                $certificateVerified = $pkcs7Info['certificateVerified'];

                return ['status' => $status, 'signingTime' => $signingTime, 'signature' => $signature,
                    'serialNumber' => $serialNumber, 'subjectName' => $subjectName, 'digest' => $digest,
                    'verified' => $verified, 'certificateVerified' => $certificateVerified];
            }
        }

        return ['status' => $status, 'message' => $message];

    }

    public function curlPost($path, $postData)
    {
        $curl = curl_init();

        $url = $this->eri_srv_ip_prod.$path;

        if (in_array($_SERVER['HTTP_HOST'], ['edo-demo.loc','172.17.110.44'])) {
            $url = $this->eri_srv_ip_loc.$path;
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => "".$postData."",
            CURLOPT_HTTPHEADER => array(
                'X-Real-IP: 84.54.116.179',
                'Host: edo.eai.uz',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

}
