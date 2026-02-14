<?php

namespace App\Services\Documents;

use App\Models\Document\DocumentReportForm;

class ReportFormDocumentHandler implements DocumentHandlerInterface
{
    public function handle($document, $journal, $request)
    {
        //
        DocumentReportForm::create([
            'document_id' => $document->id,
            'fields' => $request->fields,
        ]);
    }
}
