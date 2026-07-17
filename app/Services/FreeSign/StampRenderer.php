<?php

namespace App\Services\FreeSign;

use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Отрисовка меток на PDF (TCPDF).
 *
 * Добавление нового типа = один case в render() + метод отрисовки.
 * Координаты приходят уже в мм ($rect), пересчёт из долей делает
 * SignatureStampService.
 *
 * Кириллица: шрифт dejavusans встроен в TCPDF, работает из коробки.
 */
class StampRenderer
{
    private const FONT = 'dejavusans';

    /** Акцентный цвет (RGB) — как в редакторе. */
    private const ACCENT = [121, 77, 253];
    private const GREEN  = [32, 163, 123];

    /**
     * @param Fpdi   $pdf   уже спозиционирован на нужной странице
     * @param string $type  тип метки
     * @param array  $rect  ['x'=>мм, 'y'=>мм, 'w'=>мм, 'h'=>мм]
     * @param array  $params доп. параметры метки (font_size, align, text)
     * @param array  $data  подготовленные данные подписи (см. buildContextData)
     */
    public function render(Fpdi $pdf, string $type, array $rect, array $params, array $data): void
    {
        // Видно, ЧТО штампуется у каждого подписанта (SIGN/AGREE/RESOL — не важно,
        // штампуются метки любого участника маршрута).
        \Illuminate\Support\Facades\Log::debug('[free-sign] рисуем метку', [
            'type' => $type,
            'x_mm' => round($rect['x'], 1),
            'y_mm' => round($rect['y'], 1),
            'signer' => $data['full_name'] ?? '',
        ]);

        match ($type) {
            'full_name'       => $this->text($pdf, $rect, $params, $data['full_name'] ?? ''),
            'position'        => $this->text($pdf, $rect, $params, $data['position'] ?? ''),
            'sign_date'       => $this->text($pdf, $rect, $params, $data['sign_date'] ?? ''),
            'sign_time'       => $this->text($pdf, $rect, $params, $data['sign_time'] ?? ''),
            'cert_number'     => $this->text($pdf, $rect, $params, $data['cert_number'] ?? ''),
            'cert_info'       => $this->certInfo($pdf, $rect, $params, $data),
            'free_text'       => $this->text($pdf, $rect, $params, $params['text'] ?? ''),
            'signature'       => $this->signature($pdf, $rect, $data),
            'qr'              => $this->qr($pdf, $rect, $data),
            'stamp_approved'  => $this->stamp($pdf, $rect, 'СОГЛАСОВАНО', self::GREEN),
            'stamp_confirmed' => $this->stamp($pdf, $rect, 'УТВЕРЖДЕНО', self::ACCENT),
            default           => null,   // неизвестный тип — молча пропускаем
        };
    }

    /** Текстовая метка с автоподбором размера под высоту рамки. */
    private function text(Fpdi $pdf, array $rect, array $params, string $value): void
    {
        if ($value === '') return;

        $size  = $params['font_size'] ?? $this->fitSize($rect['h']);
        $align = strtoupper(substr($params['align'] ?? 'L', 0, 1));

        $pdf->SetFont(self::FONT, '', $size);
        $pdf->SetTextColor(20, 25, 35);
        $pdf->SetXY($rect['x'], $rect['y']);
        $pdf->MultiCell($rect['w'], $rect['h'], $value, 0, $align, false, 1, '', '', true, 0, false, true, $rect['h'], 'M');
    }

    /** Инфо сертификата: ФИО + серийник + subject. */
    private function certInfo(Fpdi $pdf, array $rect, array $params, array $data): void
    {
        $lines = array_filter([
            $data['full_name'] ?? '',
            !empty($data['cert_number']) ? 'Сертификат: ' . $data['cert_number'] : '',
            $data['sign_date'] ?? '',
        ]);

        $this->text($pdf, $rect, $params + ['font_size' => min(7, $this->fitSize($rect['h']))], implode("\n", $lines));
    }

    /** Подпись ЭЦП: рамка + ФИО + пометка. */
    private function signature(Fpdi $pdf, array $rect, array $data): void
    {
        [$r, $g, $b] = self::ACCENT;

        $pdf->SetAlpha(0.06);
        $pdf->SetFillColor($r, $g, $b);
        $pdf->Rect($rect['x'], $rect['y'], $rect['w'], $rect['h'], 'F');
        $pdf->SetAlpha(1);

        $pdf->SetDrawColor($r, $g, $b);
        $pdf->SetLineWidth(0.25);
        $pdf->Rect($rect['x'], $rect['y'], $rect['w'], $rect['h'], 'D');

        // ФИО.
        $pdf->SetFont(self::FONT, '', min($rect['h'] * 0.30, 11));
        $pdf->SetTextColor($r, $g, $b);
        $pdf->SetXY($rect['x'] + 1.5, $rect['y'] + $rect['h'] * 0.18);
        $pdf->Cell($rect['w'] - 3, $rect['h'] * 0.35, $data['full_name'] ?? '', 0, 0, 'L');

        // Пометка.
        $pdf->SetFont(self::FONT, '', min($rect['h'] * 0.16, 6));
        $pdf->SetTextColor(110, 110, 120);
        $pdf->SetXY($rect['x'] + 1.5, $rect['y'] + $rect['h'] * 0.58);
        $pdf->Cell($rect['w'] - 3, $rect['h'] * 0.3, 'Подписано ЭЦП · E-IMZO', 0, 0, 'L');

        if (!empty($data['sign_date'])) {
            $pdf->SetXY($rect['x'] + 1.5, $rect['y'] + $rect['h'] * 0.76);
            $pdf->Cell($rect['w'] - 3, $rect['h'] * 0.2, $data['sign_date'] . ' ' . ($data['sign_time'] ?? ''), 0, 0, 'L');
        }
    }

    /** QR-код (TCPDF рисует нативно, без внешних библиотек). */
    private function qr(Fpdi $pdf, array $rect, array $data): void
    {
        $payload = $data['qr_payload'] ?? '';

        if ($payload === '') {
            // Раньше тут был тихий return — QR молча не рисовался,
            // и понять причину было невозможно.
            \Illuminate\Support\Facades\Log::warning(
                '[free-sign] QR не отрисован: пустой payload (нет doc_code у документа?)'
            );
            return;
        }

        $side  = min($rect['w'], $rect['h']);
        $style = [
            'border'  => 0,
            'padding' => 0,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => [255, 255, 255],
        ];

        $pdf->write2DBarcode($payload, 'QRCODE,M', $rect['x'], $rect['y'], $side, $side, $style);
    }

    /** Штамп («СОГЛАСОВАНО»/«УТВЕРЖДЕНО»): заливка + рамка + текст по центру. */
    private function stamp(Fpdi $pdf, array $rect, string $label, array $rgb): void
    {
        [$r, $g, $b] = $rgb;

        $pdf->SetAlpha(0.10);
        $pdf->SetFillColor($r, $g, $b);
        $pdf->Rect($rect['x'], $rect['y'], $rect['w'], $rect['h'], 'F');
        $pdf->SetAlpha(1);

        $pdf->SetDrawColor($r, $g, $b);
        $pdf->SetLineWidth(0.4);
        $pdf->Rect($rect['x'], $rect['y'], $rect['w'], $rect['h'], 'D');

        $pdf->SetFont(self::FONT, 'B', min($rect['h'] * 0.38, 12));
        $pdf->SetTextColor($r, $g, $b);
        $pdf->SetXY($rect['x'], $rect['y']);
        $pdf->Cell($rect['w'], $rect['h'], $label, 0, 0, 'C', false, '', 0, false, 'T', 'M');
    }

    /** Подбор кегля под высоту рамки (мм → pt приблизительно). */
    private function fitSize(float $heightMm): float
    {
        return max(5, min($heightMm * 2.0, 12));
    }
}
