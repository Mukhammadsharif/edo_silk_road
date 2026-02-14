<?php

namespace App\Jobs;

use App\Models\Document\DocumentJournals;
use App\Models\Document\DocumentExecutionSteps;
use App\Models\Document\Documents;
use App\Models\DocumentTypeTemps\DocumentTypeTempMetaData;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class GenerateHrOrderPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $documentId;

    public function __construct($documentId)
    {
        $this->documentId = $documentId;
    }

    public function handle(): void
    {
        $document = DocumentJournals::with(['doc'])->find($this->documentId);
        if (!$document || !$document->doc?->doc_code) {
            Log::warning("Document not found or no doc_code: ID {$this->documentId}");
            return;
        }

        $path = "hr_orders/{$document->doc->doc_code}.pdf";
        $cacheKey = "hr_order_pdf_path:{$document->id}";

        if (Storage::exists('public/' . $path)) {
            Cache::forever($cacheKey, $path);
            return;
        }

        try {
            Log::info("Starting PDF generation for document: {$document->doc->doc_code}");

            $directorDocument = DocumentExecutionSteps::resolution()
                ->where('j_id', $document->id)
                ->first();

            $metaData = DocumentTypeTempMetaData::where('doc_id', $document->doc_id ?? 0)->first();
            $json_data = $metaData ? json_decode($metaData->meta_data, true) : [];
            $app_id = $json_data['DOCUMENT_ID'] ?? '';

            $appDocumentJournal = DocumentJournals::where('doc_id', $app_id)->first();
            if (!$appDocumentJournal) {
                Log::warning("App document not found: {$app_id}");
            }

            $documentA = Documents::find($document->id);
            $html_doc = app(DocumentService::class)->getHtmlDoc($document, $documentA);

            $fullPath = storage_path('app/public/' . $path);

            $directory = dirname($fullPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            Pdf::view('pdf.doc-hr-ord', compact('document', 'directorDocument', 'html_doc'))
                ->format('a4')
                ->save($fullPath);

            Cache::forever($cacheKey, $path);

            Log::info("PDF generated: " . Storage::url($path));

        } catch (\Exception $e) {
            Log::error("PDF Generation FAILED (ID: {$document->id}): " . $e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}
