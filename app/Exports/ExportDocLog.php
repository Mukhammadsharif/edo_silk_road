<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExportDocLog implements FromCollection, WithColumnWidths, WithColumnFormatting
{
    protected $doc_logs;

    public function __construct($doc_logs)
    {
        $this->doc_logs = $doc_logs;
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }


    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 35,
            'C' => 55,
            'D' => 25,
            'E' => 20,
            'F' => 30,
            'G' => 30,
            'H' => 30,
            'I' => 20,
            'J' => 20,
        ];
    }

    public function collection()
    {
        return collect($this->doc_logs);
    }
}
