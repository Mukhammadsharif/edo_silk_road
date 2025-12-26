<?php

namespace App\Http\Controllers\ECP;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Models\Admin\Role;
use App\Models\Admin\UserPersonalInfo;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\DocumentJournals;
use App\Models\Document\DocumentReplyComments;
use App\Models\Document\DocumentResolutions;
use App\Models\ECP\SignatureDocumentInfo;
use App\Models\ECP\SignatureDocuments;
use App\Models\ECP\SignatureDocumentType;
use App\Models\Protocol\ProtocolDocConfigs;
use App\Models\Protocol\ProtocolDocMember;
use App\Models\Protocol\ProtocolDocReplyComment;
use App\Models\Protocol\ProtocolDocument;
use App\Models\Resolution\ReplyCommentTypes;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class EcpController extends Controller
{
    //
    public function signDocuments(Request $request): JsonResponse
    {
        //
        $isDemo = Settings::get('is_demo', false);

        $pk_data = $request->pk_data;

        $ecp_doc_id = $request->ecp_doc_id;

        $ecp_doc_type = $request->ecp_doc_type;

        $ecp_confirm_id = $request->ecp_confirm_id;

        $ecp_r_text = $request->ecp_r_text;

        if ($isDemo === false) {
            $soap_response = $this->curlPost($pk_data);

            $xml = simplexml_load_string($soap_response);

            if ($xml === false) {
                \Log::error('Failed to parse SOAP response: ' . $soap_response);
                return response()->json([
                    'success' => 500,
                    'title' => __('error_message'),
                    'message' => __('Failed to parse SOAP response'),
                ], 500);
            }

            $xmlObject = $xml->children('S', true)->Body->children('ns2', true)->verifyPkcs7Response->children();
            $xmlObjectToArray = json_decode(json_encode($xmlObject), TRUE);
            $xmlObjectToArrayJson = $xmlObjectToArray['return'];

            $xmlObjectToArrayJsonDecode = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $xmlObjectToArrayJson), true );

            $success = $xmlObjectToArrayJsonDecode['success'];
            $pkcs7Info = $xmlObjectToArrayJsonDecode['pkcs7Info']['signers'][0];
            $certificateVerified = $pkcs7Info['certificateVerified'];

            $signingTime = $pkcs7Info['signingTime'];
            $signature = $pkcs7Info['signature'];
            $digest = $pkcs7Info['digest'];

            $serialNumber = $pkcs7Info['certificate'][0]['serialNumber'];
            $subjectName = $pkcs7Info['certificate'][0]['subjectName'];

            $verified = $pkcs7Info['verified'];

            $ecp_data =
                ['success' => $success, 'signingTime' => $signingTime, 'signature' => $signature, 'digest' => $digest,
                    'serialNumber' => $serialNumber, 'subjectName' => $subjectName, 'verified' => $verified,
                    'certificateVerified' => $certificateVerified
                ];
        } else {
            $success = false;
            $certificateVerified = 0;
            $ecp_data = [
                'success' => false, 'signingTime' => Carbon::now()->format('Y-m-d H:i:s'), 'signature' =>  Uuid::uuid4(), 'digest' =>  Uuid::uuid4(),
                'serialNumber' =>  Uuid::uuid4(), 'subjectName' =>  Uuid::uuid4(), 'verified' => true,
                'certificateVerified' => $certificateVerified
            ];
        }

        $working_doc_types = ['send_to_create_res', 'send_to_agreement', 'send_to_sign', 'from_signer_to_ag', 'from_signer_to_c_res', 'from_ag_to_res',
            'sign_resolution', 'close_exec_doc', 'send_to_ag_out_doc', 'send_sign_out_doc', 'sign_out_doc'];

        if (($success && $certificateVerified == 1) || $isDemo === true) {
            if (in_array($ecp_doc_type, $working_doc_types)) {
                $journal = DocumentJournals::where('doc_id', $ecp_doc_id)->first();

                if ($journal) {

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
                    $journalType = $journal->journal->title??'';
                    $docName = $journal->doc->doc_name??'';
                    $fromUser = $journal->fromUser->personal->fullName()??'-';

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
//            } elseif ($ecp_doc_type == 'protocol_document') {
//                $document = ProtocolDocument::find($ecp_doc_id);
//
//                $documentMember = ProtocolDocMember::where('role_id', '!=', 4)->where('doc_id', $ecp_doc_id)
//                    ->whereIn('user_id', $this->authActiveUserIds())->where('status', 2)->first();
//
//                if ($document && $documentMember) {
//                    if (in_array($ecp_confirm_id, [4,5,7])) {
//                        if (!trim(''.$ecp_r_text.'')){
//                            return response()->json([
//                                'success' => 201,
//                                'title' => __('message.error_message'),
//                                'message' => __('message.comment_on_document')
//                            ]);
//                        }
//                    }
//
//                    $documentMember->update(['status' => $ecp_confirm_id]);
//
//                    $allMemberStatus = ProtocolDocMember::where('role_id', '!=', 4)->where('doc_id', $ecp_doc_id)->whereNotIn('status', [3,5,7])->count();
//                    if (!$allMemberStatus) {
//                        $isEmp = ProtocolDocMember::where('role_id', 4)->where('doc_id', $ecp_doc_id)->count();
//                        if ($isEmp) {
//                            ProtocolDocMember::where('doc_id', $document->id)->where('role_id', 4)->update(['status' => 2]);
//                        } else {
//                            $document->update(['status' => 3]);
//                        }
//                    }
//
//                    $replHis = new ProtocolDocReplyComment();
//                    $replHis->doc_id = $ecp_doc_id;
//                    $replHis->doc_member_id = $documentMember->id;
//                    $replHis->user_id = $this->authActiveUser()->id;
//                    $replHis->doc_text = $ecp_r_text;
//                    $replHis->status = $ecp_confirm_id;
//                    $replHis->save();
//
//                    $log_type = $document->docType->title??'Protocol Document';
//                    $doc_name = $document->doc_name??$ecp_doc_id;
//                    $this->createLog($ecp_doc_id, 20, $log_type, $doc_name);
//                }
//
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
                            // Parallel: check if all members are signed
                            $allMemberStatus = ProtocolDocMember::where('doc_id', $ecp_doc_id)->whereIn('role_id', $signer_roles)->whereNotIn('status', [3,5,7])->count();
                            if (!$allMemberStatus) {
                                $document->update(['status' => 3]);
                            }
                        } else {
                            // Sequential: check if current role is complete, then activate next if exists
                            $current_role = $documentMember->role_id;
                            $unfinished_in_role = ProtocolDocMember::where('doc_id', $ecp_doc_id)->where('role_id', $current_role)->whereNotIn('status', [3,5,7])->count();
                            if (!$unfinished_in_role) {
                                $current_config = $configs->where('role_id', $current_role)->first();
                                $next_order = $current_config->order + 1;
                                $next_config = $configs->where('order', $next_order)->first();
                                if ($next_config) {
                                    ProtocolDocMember::where('doc_id', $document->id)->where('role_id', $next_config->role_id)->update(['status' => 2]);

                                    $next_members = ProtocolDocMember::where('doc_id', $document->id)->where('role_id', $next_config->role_id)->get();

                                    $url = 'protocol/' . $protocolType->doc_code . '/' . $ecp_doc_id . '/' . $document->doc_hash;

                                    foreach ($next_members as $member) {
                                        $this->createMailCron(
                                            $member->user_id,
                                            $document->docType->title,
                                            $document->created_user ? UserPersonalInfo::where('user_id', $document->created_user)->first()->fullName() ?? '-' : '-',
                                            $url,
                                            $document->doc_name
                                        );
                                    }
                                } else {
                                    $document->update(['status' => 3]);
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
                } else {
                    return response()->json([
                        'success' => 201,
                        'title' => __('message.error_message'),
                        'message' => __('message.document_deleted_system')
                    ]);
            }



            $doc_type = SignatureDocumentType::where('doc_code', $ecp_doc_type)->first();

            $signatureDocument = new SignatureDocuments();
            $signatureDocument->doc_id = $ecp_doc_id;
            $signatureDocument->doc_type = $doc_type->id??1;
            $signatureDocument->user_id = $this->authActiveUser()->id;
            $signatureDocument->save();

            $signatureDocumentInfos = new SignatureDocumentInfo();
            $signatureDocumentInfos->signature_doc_id = $signatureDocument->id;
            $signatureDocumentInfos->json_data = json_encode($ecp_data);
            $signatureDocumentInfos->save();
        }

        return response()->json([
            'success' => 200,
            'title' => __('message.success_message'),
            'message' => __('message.task_completed_successfully'),
        ]);
    }

    private function curlPost($pk_data): bool|string
    {
        $curl = curl_init();

        $url = 'http://192.168.200.253:8012/mecp/public/api/v1/ecp';

        if (in_array($_SERVER['HTTP_HOST'], ['eai.loc', 'edo-euroasia.local', 'edo-eauroasia.local', 'project.local'])) {
            $url = 'http://172.16.4.248:8012/mecp/public/api/v1/ecp';
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
            //CURLOPT_PROXY => '192.168.45.101:8012',
            CURLOPT_POSTFIELDS =>'{
                "param_1": "'.$pk_data.'"
            } ',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
