<?php

namespace App\Services\Documents;

use App\Models\Document\DocToDeps;
use App\Models\Journal\JournalAttachedOrgs;
use App\Models\Messages\OnlyOfficeDoc;
use App\Services\OnlyOfficeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DefaultDocumentHandler implements DocumentHandlerInterface
{
    protected OnlyOfficeService $onlyOfficeService;

    public function __construct(OnlyOfficeService $onlyOffice)
    {
        $this->onlyOfficeService = $onlyOffice;
    }

    public function handle($document, $journal, $request)
    {
        //
        $today = Carbon::today();
        $year = $today->year;
        $month = $today->month;
        $day = $today->day;
        $ood_path = 'ood/' . $year . '/' . $month . '/' . $day . '/';

        if ($request->has('document_key')) {
            $documentKey = $request->document_key;
        } else {
            $documentKey = $this->generateDocumentKey();
            $filename = $documentKey . '.docx';
            $file = $request->file('main_file');
            if ($file && $file->isValid()) {
                Storage::disk('ftp')->putFileAs($ood_path, $file, $filename);
            }
        }

        if (!empty($documentKey)) {
            $onlyOfficeDoc = new OnlyOfficeDoc();
            $onlyOfficeDoc->doc_type = "DOC";
            $onlyOfficeDoc->doc_id = $document->id;
            $onlyOfficeDoc->doc_key = $documentKey;
            $onlyOfficeDoc->doc_path = $ood_path;
            $onlyOfficeDoc->save();

            $this->onlyOfficeService->createWordToHtmlConverted($documentKey);
        }

        if ($request->doc_org){
            $journalAttachedOrg = new JournalAttachedOrgs();
            $journalAttachedOrg->j_id = $journal->id;
            $journalAttachedOrg->doc_org_id = $request->doc_org;
            $journalAttachedOrg->save();
        }

        if ($journal->journal->doc_code === 'ORDER_F' && $request->to_dep) {
            $doc_to_dep = new DocToDeps();
            $doc_to_dep->j_id = $journal->id;
            $doc_to_dep->dep_id = $request->to_dep;
            $doc_to_dep->save();
        }
    }

    public static function generateDocumentKey(): string
    {
        $randomString = bin2hex(random_bytes(6)); // 12 characters + 1 extra for odd length
        $randomString = substr($randomString, 0, 13); // Ensure exactly 13 characters

        $timestamp = time();

        return "{$randomString}_{$timestamp}";
    }

}
