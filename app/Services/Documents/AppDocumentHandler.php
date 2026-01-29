<?php

namespace App\Services\Documents;

use App\Models\DocumentTypeTemps\DocumentTypeTempMetaData;
use Carbon\Carbon;

class AppDocumentHandler implements DocumentHandlerInterface
{
    public function handle($document, $journal, $request)
    {
        $fromDate = Carbon::createFromFormat('d.m.Y', $request->from_date)->format('Y-m-d');
        $toDate = Carbon::createFromFormat('d.m.Y', $request->to_date)->format('Y-m-d');

        $data = [
            'FROM_DATE' => $fromDate,
            'TO_DATE' => $toDate,
        ];

        DocumentTypeTempMetaData::create([
            'doc_id' => $document->id,
            'meta_data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

}
