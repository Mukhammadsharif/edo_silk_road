<?php

namespace App\Services;
use App\Models\DocumentTypeTemps\DocumentTypeTempMetaData;
use App\Models\DocumentTypeTemps\DocumentTypeTemps;
use App\Models\Messages\OnlyOfficeDoc;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    /**
     * * Получить HTML-документ по журналу и документу
     * *
     * * @param object $journal
     * * @param object $document
     * * @return string|null */
    public function getHtmlDoc(object $journal, object $document): ?string
    {
        $html_doc = null;

        if (str_contains($journal->journal->doc_code, 'APP') || str_contains($journal->journal->doc_code, 'HR_ORD')) {
            $temp = DocumentTypeTemps::where('doc_type_id', $journal->j_type)->first();

            if (!$temp) {
                return null;
            }

            $metaData = DocumentTypeTempMetaData::where('doc_id', $document->id)->first();
            $json_data = $metaData ? json_decode($metaData->meta_data, true) : [];

            $fromDate = $json_data['FROM_DATE'] ?? '';
            $toDate = $json_data['TO_DATE'] ?? '';
            $fromDateO = $json_data['FROM_DATE_O'] ?? '';
            $toDateO = $json_data['TO_DATE_O'] ?? '';
            $fromDateA = $json_data['FROM_DATE_A'] ?? '';
            $toDateA = $json_data['TO_DATE_A'] ?? '';
            $empFullName = $journal->fromUser->personal->fullName()??'';
            $empPosition = $journal->fromUser->userPost->lang($journal->fromUser->userPost->id)??'';
            $empShortName = $journal->fromUser->personal->shortName()??'';
            $empDepartment = $journal->fromUser->department->lang($journal->fromUser->department->id)??'';
            $documents = "";
            $docNumber = $document->doc_number??1;

            $search_words = [
                "[FROM_DATE]",
                "[TO_DATE]",
                "[FROM_DATE_O]",
                "[TO_DATE_O]",
                "[FROM_DATE_A]",
                "[TO_DATE_A]",
                "[EMP_FULLNAME]",
                "[EMP_POSITION]",
                "[EMP_SHORTNAME]",
                "[DOCUMENTS]",
                "[DOC_NUMBER]"
            ];

            $replace_words = [
                $fromDate ? Carbon::parse($fromDate)->format('d.m.Y') : '',
                $toDate ? Carbon::parse($toDate)->format('d.m.Y') : '',
                $fromDateO ? Carbon::parse($fromDateO)->format('d.m.Y') : '',
                $toDateO ? Carbon::parse($toDateO)->format('d.m.Y') : '',
                $fromDateA ? Carbon::parse($fromDateA)->format('d.m.Y') : '',
                $toDateA ? Carbon::parse($toDateA)->format('d.m.Y') : '',
                $empFullName,
                $empDepartment.' '.$empPosition,
                $empShortName,
                $documents,
                $docNumber
            ];

            $html_doc = str_replace($search_words, $replace_words, $temp->t_text);

        } else {
            $model = OnlyOfficeDoc::where('doc_type', 'DOC') ->where('doc_id', $journal->doc_id) ->first();

            if ($model) {

                $path = $model->doc_path . $model->doc_key . '.html';

                if (Storage::disk('public')->exists($path)) {
                    $html_doc = Storage::disk('public')->get($path);
                }
            }
        }

        return $html_doc;
    }
}
