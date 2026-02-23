/**
 * table-sort.js
 * Клиентская сортировка таблицы .modern-table без изменений в контроллере.
 * Подключить перед </body> или через @section / @push('scripts').
 *
 * Поддерживает: текст, даты формата DD.MM.YYYY и DD.MM.YY, числа, смешанный контент.
 */
document.addEventListener('DOMContentLoaded', function () {

    const table = document.querySelector('.modern-table');
    if (!table) return;

    const thead = table.querySelector('thead');
    const tbody = table.querySelector('tbody');

    // Состояние: какая колонка активна и направление
    let activeCol  = null;
    let activeDir  = 'asc'; // 'asc' | 'desc'

    // ── Добавляем стили ────────────────────────────────────────────────────────
    const style = document.createElement('style');
    style.textContent = `
        .modern-table thead th {
            cursor: pointer;
            user-select: none;
            white-space: nowrap;
            transition: background 0.15s;
            position: relative;
        }
        .modern-table thead th:hover {
            background: rgba(99, 102, 241, 0.08);
        }
        .modern-table thead th.ts-sorted {
            background: rgba(99, 102, 241, 0.12);
        }
        .ts-sort-icon {
            display: inline-flex;
            align-items: center;
            margin-left: 5px;
            opacity: 0.35;
            transition: opacity 0.15s;
            vertical-align: middle;
            pointer-events: none;
        }
        .modern-table thead th:hover .ts-sort-icon {
            opacity: 0.65;
        }
        .modern-table thead th.ts-sorted .ts-sort-icon {
            opacity: 1;
            color: #6366f1;
        }
    `;
    document.head.appendChild(style);

    // ── SVG-иконки (Lucide-совместимые, inline) ───────────────────────────────
    const ICON_NEUTRAL = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/>
    </svg>`;
    const ICON_ASC = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="m18 15-6-6-6 6"/>
    </svg>`;
    const ICON_DESC = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="m6 9 6 6 6-6"/>
    </svg>`;

    // ── Получаем все заголовки ─────────────────────────────────────────────────
    const ths = Array.from(thead.querySelectorAll('th'));

    ths.forEach(function (th, colIndex) {
        // Добавляем иконку
        const iconSpan = document.createElement('span');
        iconSpan.className = 'ts-sort-icon';
        iconSpan.innerHTML = ICON_NEUTRAL;
        th.appendChild(iconSpan);

        th.addEventListener('click', function () {
            // Если кликаем по той же колонке — переключаем направление
            if (activeCol === colIndex) {
                activeDir = activeDir === 'asc' ? 'desc' : 'asc';
            } else {
                activeCol = colIndex;
                activeDir = 'asc';
            }

            sortTable(colIndex, activeDir);
            updateIcons(colIndex, activeDir);
        });
    });

    // ── Определяем тип ячейки ─────────────────────────────────────────────────
    function getCellValue(row, colIndex) {
        const cell = row.cells[colIndex];
        if (!cell) return '';
        // Берём текстовое содержимое (без HTML-тегов)
        return cell.innerText.trim();
    }

    function parseValue(str) {
        // Дата DD.MM.YYYY или DD.MM.YY
        const dateMatch = str.match(/^(\d{2})\.(\d{2})\.(\d{2,4})(?:\s+(\d{2}:\d{2}))?$/);
        if (dateMatch) {
            let year = dateMatch[3];
            if (year.length === 2) year = '20' + year;
            const dateStr = `${year}-${dateMatch[2]}-${dateMatch[1]}` +
                (dateMatch[4] ? ' ' + dateMatch[4] : '');
            const ts = Date.parse(dateStr);
            if (!isNaN(ts)) return ts;
        }
        // Число
        const num = parseFloat(str.replace(/\s/g, '').replace(',', '.'));
        if (!isNaN(num) && str !== '') return num;
        // Строка (нижний регистр для регистронезависимой сортировки)
        return str.toLowerCase();
    }

    function compare(a, b) {
        if (typeof a === 'number' && typeof b === 'number') return a - b;
        if (a < b) return -1;
        if (a > b) return 1;
        return 0;
    }

    // ── Сортировка строк ───────────────────────────────────────────────────────
    function sortTable(colIndex, direction) {
        const rows = Array.from(tbody.querySelectorAll('tr.table-row-modern'));

        rows.sort(function (rowA, rowB) {
            const valA = parseValue(getCellValue(rowA, colIndex));
            const valB = parseValue(getCellValue(rowB, colIndex));
            const result = compare(valA, valB);
            return direction === 'asc' ? result : -result;
        });

        // Перемещаем строки в отсортированном порядке
        rows.forEach(function (row) {
            tbody.appendChild(row);
        });
    }

    // ── Обновляем иконки ───────────────────────────────────────────────────────
    function updateIcons(activeIndex, dir) {
        ths.forEach(function (th, i) {
            const icon = th.querySelector('.ts-sort-icon');
            if (i === activeIndex) {
                th.classList.add('ts-sorted');
                icon.innerHTML = dir === 'asc' ? ICON_ASC : ICON_DESC;
            } else {
                th.classList.remove('ts-sorted');
                icon.innerHTML = ICON_NEUTRAL;
            }
        });

        // Переинициализируем lucide если используется (иконки внутри th)
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
});
