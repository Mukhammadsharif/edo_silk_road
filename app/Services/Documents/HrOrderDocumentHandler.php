<?php

namespace App\Services\Documents;

use App\Models\DocumentTypeTemps\DocumentTypeTempMetaData;

class HrOrderDocumentHandler implements DocumentHandlerInterface
{
    public function handle($document, $journal, $request)
    {
        $fromDate  = $request->from_date;
        $toDate  = $request->to_date;
        $documentId = $request->app_id;

        $appMetaData = DocumentTypeTempMetaData::where('doc_id', $documentId)->first();
        $json_data = $appMetaData ? json_decode($appMetaData->meta_data, true) : [];

        $fromDateA = $json_data['FROM_DATE'] ?? '';
        $toDateA = $json_data['TO_DATE'] ?? '';

        $data = [
            'DOCUMENT_ID' => $documentId,
            'FROM_DATE_O' => $fromDate,
            'TO_DATE_O' => $toDate,
            'FROM_DATE_A' => $fromDateA,
            'TO_DATE_A' => $toDateA
        ];

        DocumentTypeTempMetaData::create([
            'doc_id' => $document->id,
            'meta_data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

}
