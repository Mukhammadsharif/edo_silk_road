<?php

namespace App\Services\Documents;

use App\Models\DocumentTypeTemps\DocumentTypeTempMetaData;

class AppDocumentHandler implements DocumentHandlerInterface
{
    public function handle($document, $journal, $request)
    {
        $data = [
            'FROM_DATE' => $request->from_date,
            'TO_DATE' => $request->to_date,
        ];

        DocumentTypeTempMetaData::create([
            'doc_id' => $document->id,
            'meta_data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

}
