<!DOCTYPE html>
{{-- ═══════════════════════════════════════════════════════════════════
     ЛИСТ СОГЛАСОВАНИЯ (v62) — самостоятельная печатная страница А4.
     Без app-layout намеренно: чистый лист для печати/PDF.
     Сверху — шапка с информацией о документе, ниже — список
     согласующих (только роль AGREE) со статусами и датами.
     Кнопки печати скрываются в @media print.
     ═══════════════════════════════════════════════════════════════════ --}}
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Лист согласования — {{ $document->doc_name }}</title>
    <style>
        @page { size: A4; margin: 18mm 16mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; color: #111827;
               font-size: 12px; line-height: 1.45; background: #f1f5f9; }
        .sheet { width: 210mm; min-height: 297mm; margin: 20px auto; background: #fff;
                 padding: 18mm 16mm; box-shadow: 0 2px 14px rgba(15,23,42,0.12); }

        /* ── Шапка ── */
        .sheet-title { text-align: center; font-size: 17px; font-weight: 700;
                       letter-spacing: .4px; text-transform: uppercase; margin-bottom: 3mm; }
        .sheet-subtitle { text-align: center; font-size: 11px; color: #6b7280; margin-bottom: 7mm; }
        .doc-info { width: 100%; border-collapse: collapse; margin-bottom: 9mm; }
        .doc-info td { padding: 2.2mm 3mm; border: 1px solid #d1d5db; vertical-align: top; }
        .doc-info td.k { width: 34%; background: #f8fafc; color: #6b7280;
                         font-size: 10.5px; text-transform: uppercase; letter-spacing: .3px; }
        .doc-info td.v { font-weight: 600; }

        /* ── Список согласующих ── */
        .agr-title { font-size: 13px; font-weight: 700; margin-bottom: 3mm;
                     padding-bottom: 1.5mm; border-bottom: 2px solid #111827; }
        .agr { width: 100%; border-collapse: collapse; }
        .agr th { border: 1px solid #d1d5db; background: #f8fafc; padding: 2.2mm 2.5mm;
                  font-size: 10.5px; text-transform: uppercase; letter-spacing: .3px;
                  color: #6b7280; text-align: left; }
        .agr td { border: 1px solid #d1d5db; padding: 2.6mm 2.5mm; vertical-align: top; }
        .agr .num { width: 8mm; text-align: center; color: #6b7280; }
        .agr .who strong { display: block; font-size: 12.5px; }
        .agr .who small { color: #6b7280; font-size: 10.5px; }
        .agr .st { width: 34mm; white-space: nowrap; }
        .agr .dt { width: 26mm; }
        .badge { display: inline-block; padding: 1mm 2.5mm; border-radius: 3mm;
                 font-size: 10px; font-weight: 700; letter-spacing: .3px; }
        .badge.ok      { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .badge.wait    { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
        .badge.pending { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
        .comment { color: #374151; font-size: 11px; }
        .empty { padding: 8mm; text-align: center; color: #6b7280;
                 border: 1px dashed #d1d5db; border-radius: 2mm; }

        /* ── Подвал ── */
        .foot { margin-top: 10mm; display: flex; justify-content: space-between;
                font-size: 10px; color: #9ca3af; }

        /* ── Панель печати (не печатается) ── */
        .print-bar { position: fixed; top: 14px; right: 14px; display: flex; gap: 8px; }
        .print-bar button { border: none; border-radius: 10px; padding: 9px 16px;
                            font-size: 13px; font-weight: 600; cursor: pointer; }
        .print-bar .go   { background: #794DFD; color: #fff; }
        .print-bar .back { background: #e2e8f0; color: #334155; }

        @media print {
            body { background: #fff; }
            .sheet { box-shadow: none; margin: 0; width: auto; min-height: auto; padding: 0; }
            .print-bar { display: none !important; }
            .badge.ok, .badge.wait, .badge.pending { border: 1px solid #9ca3af;
                background: #fff !important; color: #111827 !important; }
        }
    </style>
</head>
<body>
<div class="print-bar">
    <button class="back" onclick="history.back()">← Назад</button>
    <button class="go" onclick="window.print()">🖨 Печать</button>
</div>

<div class="sheet">
    <div class="sheet-title">Лист согласования</div>
    <div class="sheet-subtitle">Kelishuv varag'i / Согласование документа</div>

    <table class="doc-info">
        <tr>
            <td class="k">Тип документа</td>
            <td class="v">{{ $journal->journal?->lang($journal->journal?->lang_id) ?? '-' }}</td>
        </tr>
        <tr>
            <td class="k">Наименование</td>
            <td class="v">{{ $document->doc_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="k">Номер / Дата</td>
            <td class="v">№ {{ $document->doc_number ?? '-' }}
                от {{ $document->doc_date ? \Carbon\Carbon::parse($document->doc_date)->format('d.m.Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="k">Автор (создатель)</td>
            <td class="v">{{ $journal->fromUser?->personal?->fullName() ?? '-' }}
                <small style="color:#6b7280; font-weight:400;">
                    {{ $journal->fromUser?->userPost?->lang($journal->fromUser?->userPost?->id) ?? '' }}
                </small></td>
        </tr>
        <tr>
            <td class="k">Статус документа</td>
            <td class="v">{{ $journal->statusWork?->lang($journal->statusWork?->id) ?? '-' }}</td>
        </tr>
    </table>

    <div class="agr-title">Согласующие</div>

    @if($agreeSteps->isEmpty())
        <div class="empty">В маршруте документа нет участников с ролью «Согласующий»</div>
    @else
        <table class="agr">
            <thead>
            <tr>
                <th class="num">№</th>
                <th>Согласующий</th>
                <th class="st">Статус</th>
                <th class="dt">Дата</th>
                <th>Комментарий</th>
            </tr>
            </thead>
            <tbody>
            @foreach($agreeSteps as $i => $step)
                @php
                    /* 5/6 = согласовано (isStepCompleted-семантика),
                       2 = на согласовании (его очередь), 1 = ожидает. */
                    $done   = in_array((int) $step->status, [5, 6], true);
                    $active = (int) $step->status === 2;
                @endphp
                <tr>
                    <td class="num">{{ $i + 1 }}</td>
                    <td class="who">
                        <strong>{{ $step->toUser?->personal?->fullName() ?? ('#' . $step->to_user) }}</strong>
                        <small>
                            {{ $step->toUser?->userPost?->lang($step->toUser?->userPost?->id) ?? '' }}
                            @if($step->toUser?->department)
                                — {{ $step->toUser?->department?->lang($step->toUser?->department?->id) ?? '' }}
                            @endif
                        </small>
                    </td>
                    <td class="st">
                        @if($done)
                            <span class="badge ok">Согласовано</span>
                        @elseif($active)
                            <span class="badge wait">На согласовании</span>
                        @else
                            <span class="badge pending">Ожидает</span>
                        @endif
                    </td>
                    <td class="dt">{{ $done && $step->updated_at ? $step->updated_at->format('d.m.Y H:i') : '—' }}</td>
                    <td class="comment">{{ $comments[$step->id]->res_text ?? '' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <div class="foot">
        <span>EDOLINE — электронный документооборот</span>
        <span>Сформировано: {{ now()->format('d.m.Y H:i') }}</span>
    </div>
</div>
</body>
</html>
