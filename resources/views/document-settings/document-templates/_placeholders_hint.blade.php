{{-- Шпаргалка допустимых плейсхолдеров для шаблона заявки.
     Клик по тегу копирует его в буфер обмена. --}}
<div class="placeholder-hint-box">
    <div class="placeholder-hint-head">
        <i data-lucide="info"></i>
        <span>{{ __('field.placeholders_hint_title') ?? 'Допустимые параметры шаблона (нажмите, чтобы скопировать)' }}</span>
    </div>
    <div class="placeholder-hint-grid">
        @php
            $placeholders = [
                '[FROM_DATE]'    => __('field.ph_from_date')    ?? 'Дата начала',
                '[TO_DATE]'      => __('field.ph_to_date')      ?? 'Дата окончания',
                '[FROM_DATE_O]'  => __('field.ph_from_date_o')  ?? 'Дата начала (О)',
                '[TO_DATE_O]'    => __('field.ph_to_date_o')    ?? 'Дата окончания (О)',
                '[FROM_DATE_A]'  => __('field.ph_from_date_a')  ?? 'Дата начала (А)',
                '[TO_DATE_A]'    => __('field.ph_to_date_a')    ?? 'Дата окончания (А)',
                '[EMP_FULLNAME]' => __('field.ph_emp_fullname') ?? 'ФИО сотрудника',
                '[EMP_POSITION]' => __('field.ph_emp_position') ?? 'Отдел и должность',
                '[EMP_SHORTNAME]'=> __('field.ph_emp_shortname')?? 'Фамилия И.О.',
                '[DOCUMENTS]'    => __('field.ph_documents')    ?? 'Документы',
                '[DOC_NUMBER]'   => __('field.ph_doc_number')   ?? 'Номер документа',
                '[USER_ADDRESS]' => __('field.ph_user_address') ?? 'Адрес проживания',
            ];
        @endphp
        @foreach($placeholders as $tag => $label)
            <button type="button" class="placeholder-chip" data-tag="{{ $tag }}" title="{{ $label }}">
                <code>{{ $tag }}</code>
                <span>{{ $label }}</span>
            </button>
        @endforeach
    </div>
</div>

<style>
    .placeholder-hint-box {
        margin: 10px 0 18px;
        padding: 14px 16px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
    }
    .placeholder-hint-head {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 13px;
        color: #334155;
        margin-bottom: 12px;
    }
    .placeholder-hint-head i { width: 16px; height: 16px; color: #2563eb; }
    .placeholder-hint-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 8px;
    }
    .placeholder-chip {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
        padding: 7px 10px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
        transition: all .15s ease;
        text-align: left;
    }
    .placeholder-chip:hover {
        border-color: #2563eb;
        background: #eff6ff;
        transform: translateY(-1px);
    }
    .placeholder-chip code {
        font-family: 'Courier New', monospace;
        font-size: 12px;
        font-weight: 700;
        color: #1d4ed8;
        background: transparent;
        padding: 0;
    }
    .placeholder-chip span {
        font-size: 11px;
        color: #64748b;
    }
    .placeholder-chip.copied {
        border-color: #16a34a;
        background: #f0fdf4;
    }
    .placeholder-chip.copied code { color: #16a34a; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.placeholder-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                var tag = chip.getAttribute('data-tag');
                var done = function () {
                    chip.classList.add('copied');
                    setTimeout(function () { chip.classList.remove('copied'); }, 1200);
                };
                // navigator.clipboard работает только по HTTPS/localhost — фолбэк через execCommand
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(tag).then(done).catch(function () { fallbackCopy(tag, done); });
                } else {
                    fallbackCopy(tag, done);
                }
            });
        });

        function fallbackCopy(text, cb) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            try { document.execCommand('copy'); } catch (e) {}
            document.body.removeChild(ta);
            if (cb) cb();
        }
    });
</script>
