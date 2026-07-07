<?php

namespace App\Services\Documents;

use App\Models\DocumentTypeTemps\DocumentTypeTempMetaData;
use Carbon\Carbon;

class AppDocumentHandler implements DocumentHandlerInterface
{
    public function handle($document, $journal, $request)
    {
        // Даты опциональны: поля показываются только если в шаблоне есть
        // [FROM_DATE] / [TO_DATE]. Если поля нет — сохраняем пустое значение.
        $fromDate = $request->from_date
            ? Carbon::createFromFormat('d.m.Y', $request->from_date)->format('Y-m-d')
            : '';
        $toDate = $request->to_date
            ? Carbon::createFromFormat('d.m.Y', $request->to_date)->format('Y-m-d')
            : '';

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
