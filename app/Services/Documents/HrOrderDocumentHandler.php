<?php

namespace App\Services\Documents;

use App\Models\DocumentTypeTemps\DocumentTypeTempMetaData;
use Carbon\Carbon;

class HrOrderDocumentHandler implements DocumentHandlerInterface
{
    public function handle($document, $journal, $request)
    {
        $documentId = $request->app_id;
        $fromDate = Carbon::createFromFormat('d.m.Y', $request->from_date)->format('Y-m-d');
        $toDate = Carbon::createFromFormat('d.m.Y', $request->to_date)->format('Y-m-d');

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
