<?php

namespace App\Services\Documents;

use App\Models\Journal\JournalTypes;

class DocumentFactory
{
    public static function make(JournalTypes $journalType)
    {
        return match ($journalType->doc_code) {
            'BASE_INCOMING' => new IncomingDocumentHandler(),
            'APP' => new AppDocumentHandler(),
            'HR_ORD' => new HrOrderDocumentHandler(),
            'REPORT_FORM' => new ReportFormDocumentHandler(),
            default => app(DefaultDocumentHandler::class),
        };
    }
}
