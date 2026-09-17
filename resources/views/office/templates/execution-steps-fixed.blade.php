{{-- ═══ ФИКСИРОВАННЫЙ МАРШРУТ (v61): read-only превью вместо селектов ═══
     Отдаётся из getExecutionUsers() для типов с целями шагов. Инпутов
     участников НЕТ намеренно: store() создаёт шаги из целей типа сам.
     $fixedRoute — из DocumentController::resolveFixedRoute(). --}}
<div class="form-group-modern full-width" id="fixedRouteBlock">
    <div style="border:1px solid rgba(121,77,253,0.25); border-radius:12px; background:rgba(121,77,253,0.04); padding:14px 16px;">
        <div style="display:flex; align-items:center; gap:8px; font-weight:600; font-size:14px; color:#1e293b; margin-bottom:4px;">
            <i data-lucide="route" style="width:16px;height:16px;color:#794DFD;"></i>
            Маршрут документа задан типом
        </div>
        <div style="font-size:12.5px; color:#94a3b8; margin-bottom:10px;">
            Участники подставлены автоматически — после сохранения документ
            сразу пойдёт по цепочке
        </div>

        @foreach($fixedRoute as $i => $entry)
            <div style="display:flex; gap:10px; align-items:flex-start; padding:8px 0;
                        {{ $loop->last ? '' : 'border-bottom:1px dashed rgba(15,23,42,0.08);' }}">
                <span style="flex:0 0 auto; width:22px; height:22px; border-radius:50%;
                             background:#794DFD; color:#fff; font-size:12px; font-weight:600;
                             display:flex; align-items:center; justify-content:center;">{{ $i + 1 }}</span>
                <div>
                    <div style="font-size:13px; font-weight:600; color:#1e293b;">
                        {{ $entry['step']->executionRole?->lang($entry['step']->executionRole?->lang_id) ?? $entry['role_code'] }}
                    </div>
                    @php
                        /* SIGN/RESOL единичны в штатном движке — идёт ПЕРВЫЙ.
                           Показываем именно его, чтобы превью не врало. */
                        $single = in_array($entry['role_code'], ['SIGN', 'RESOL'], true);
                        $shown  = $single ? $entry['users']->take(1) : $entry['users'];
                    @endphp
                    <div style="font-size:12.5px; color:#64748b;">
                        @foreach($shown as $wh)
                            {{ $wh->personal?->fullName() ?? ('#' . $wh->user_id) }}{{ $loop->last ? '' : ', ' }}
                        @endforeach
                        @if($single && $entry['users']->count() > 1)
                            <span style="color:#f59e0b; font-size:11.5px;">
                                (роль единичная — из должности взят первый по списку)
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
<script>if (typeof lucide !== 'undefined') lucide.createIcons();</script>
