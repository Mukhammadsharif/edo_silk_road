<?php

namespace App\Services\Integration1C;

/**
 * Автоопределение типа приказа 1С по названию журнала EDOLINE.
 *
 * ЗАЧЕМ.
 * Организаций много, и каждая заводит кадровые журналы сама: doc_code и названия
 * произвольные. Ручная таблица соответствий в конфиге не масштабируется —
 * при подключении новой организации интеграция молча перестала бы работать.
 * Поэтому тип определяется по тексту названия, а администратор правит только
 * то, что классификатор не смог разобрать.
 *
 * КАК.
 * Название нормализуется (регистр, ё→е, апострофы узбекской латиницы, кириллица
 * узбекская → латиница) и проверяется по словарям на трёх языках: русский,
 * узбекская латиница, узбекская кириллица. Каждое совпадение даёт вес,
 * побеждает тип с наибольшим весом.
 *
 * ПОРЯДОК ВАЖЕН.
 * «Снятие дисциплинарного взыскания» содержит слова и «снятие», и «взыскание».
 * Поэтому removal проверяется раньше disciplinary_actions и при совпадении
 * блокирует его. Та же логика у совмещения против перевода.
 */
class OnecOrderTypeClassifier
{
    /**
     * Словари ключевых слов: тип → [вес => [слова]].
     * Вес 10 — однозначный признак, 5 — вспомогательный.
     *
     * Слова хранятся уже в нормализованном виде (нижний регистр, латиница
     * для узбекского), см. normalize().
     */
    private const RULES = [
        'removal_of_disciplinary_actions' => [
            10 => [
                'снятие дисциплинарн', 'снятии дисциплинарн', 'снятие взыскан',
                'о снятии', 'досрочном снятии', 'досрочное снятие',
                'intizomiy jazoni olib tashlash', 'jazoni olib tashlash',
                'jazoni bekor qilish', 'intizomiy jazoni bekor',
            ],
            5 => ['снятие', 'снятии', 'olib tashlash', 'muddatidan oldin'],
        ],

        'disciplinary_actions' => [
            10 => [
                'дисциплинарн', 'взыскан', 'выговор', 'замечание',
                'intizomiy', 'intizomiy jazo', 'hayfsan', 'tanbeh',
            ],
            5 => ['jazo', 'наказан'],
        ],

        'additional_duties' => [
            10 => [
                'совмещен', 'совмещени', 'замещен', 'обязанностей',
                'обязанностi', 'исполнени обязанност', 'возложении исполнени',
                'исполняющ', 'временное исполнение', 'совместительств',
                'orindoshlik', 'ornini bosish', 'vazifasini bajarish',
                'vazifasini vaqtincha bajarish',
                // «Lavozimlarni birlashtirish» — объединение должностей,
                // в контракте 1С это additional_duties (совмещение)
                'lavozimlarni birlashtirish', 'birlashtirish',
                // «Kasblarni qo'shib olib borish» — совмещение профессий
                'qoshib olib borish', 'kasblarni qoshib', 'qoshib olib',
            ],
            5 => ['и.о.', 'вазифасини бажариш'],
        ],

        'hirings' => [
            10 => [
                'прием на работу', 'приеме на работу', 'приём на работу',
                'принятии на работу', 'принят на работу', 'трудоустройств',
                'ishga qabul', 'ishga qabul qilish', 'qabul qilish togrisida',
            ],
            5 => ['прием', 'приеме', 'qabul'],
        ],

        'terminations' => [
            10 => [
                'увольнен', 'увольнени', 'об увольнении', 'расторжении трудового',
                'расторжение трудового', 'прекращении трудового',
                'ishdan boshatish', 'boshatish', 'mehnat shartnomasini bekor',
                'ishdan boshash',
            ],
            5 => ['уволить', 'расторжен', 'bekor qilish togrisida'],
        ],

        'transfers' => [
            10 => [
                'перевод', 'переводе', 'переведен', 'кадровый перевод',
                'о переводе', 'перемещени',
                'otkazish', 'lavozimga otkazish', 'boshqa ishga otkazish',
                // Реальные формулировки EDOLINE: «Boshqa lavozimga o'tish»
                'lavozimga otish', 'boshqa lavozimga', 'boshqa ishga otish',
            ],
            5 => ['otkazish togrisida', 'lavozimga'],
        ],

        'business_trips' => [
            10 => [
                'командировк', 'служебная командировка', 'о командировании',
                'xizmat safari', 'safarga yuborish', 'xizmat safariga',
            ],
            5 => ['safar'],
        ],

        'vacations' => [
            10 => [
                'отпуск', 'об отпуске', 'предоставлении отпуска',
                'tatil', 'mehnat tatili', 'tatil berish',
                // Служба МПР и военные сборы в контракте 1С — подтип отпуска
                // (vacation_type = militaryService), отдельного приказа для них нет
                'военные сборы', 'воинские сборы', 'военных сборов',
                'мобилизационн', 'harbiy xizmat', 'safarbarlik',
            ],
            5 => ['мпр', 'сборы'],
        ],

        'absences' => [
            10 => [
                'отсутстви', 'об отсутствии', 'неявк', 'больничн',
                'нетрудоспособност', 'простой',
                'yoqligi', 'ishga chiqmaslik', 'kasallik varaqasi',
                // «Ishda yo'qlik holati» — отсутствие на работе
                'yoqlik', 'ishda yoqlik', 'yoqlik holati',
            ],
            5 => ['kasallik'],
        ],
    ];

    /**
     * Типы, которые «перебивают» другие при одновременном совпадении.
     * Ключ — победитель, значения — что он подавляет.
     */
    private const OVERRIDES = [
        // «Снятие дисциплинарного взыскания» содержит и «взыскание»
        'removal_of_disciplinary_actions' => ['disciplinary_actions'],
        // «Замещение на период отпуска» — это совмещение, а не отпуск
        'additional_duties'               => ['vacations', 'transfers', 'business_trips'],
        // «Отпуск без сохранения зарплаты с последующим увольнением» — отпуск
        'vacations'                       => ['absences'],
    ];

    /** Подтипы отпуска: vacation_type → ключевые слова. */
    private const VACATION_SUBTYPES = [
        'parentalLeave' => [
            'по уходу за ребенком', 'по уходу за ребёнком', 'уходу за ребенком',
            'декрет', 'декретн', 'беременност', 'родам',
            // «Dekret ta'tiliga chiqish» — реальная формулировка EDOLINE
            'dekret', 'bola parvarishi', 'homiladorlik', 'tugruq', 'bola parvarish',
        ],
        'educational' => [
            'учебн', 'ученическ', 'обучени', 'на период сессии',
            'oquv tatili', 'oquv',
        ],
        'militaryService' => [
            'мпр', 'мобилизационн', 'воинск', 'военн', 'военные сборы',
            'сборы', 'harbiy', 'safarbarlik', 'harbiy xizmat',
        ],
        'unpaid' => [
            'без сохранения', 'неоплачиваем', 'без оплаты', 'за свой счет',
            'за свой счёт', 'haqsiz', 'toldirilmagan',
            // «Ish haqqi saqlanmagan holda ta'til» — haqqi с двумя q,
            // поэтому ловим по корню saqlanmagan
            'saqlanmagan', 'ish haqqi saqlanmagan', 'ish haqi saqlanmagan',
        ],
        'paid' => [
            'ежегодн', 'оплачиваем', 'трудовой отпуск', 'основной отпуск',
            'дополнительн', 'yillik', 'mehnat tatili', 'haqli',
        ],
    ];

    /** Подтипы отсутствия: absence_type → ключевые слова. */
    private const ABSENCE_SUBTYPES = [
        'sickLeave' => [
            'больничн', 'нетрудоспособност', 'болезн', 'по болезни',
            'kasallik', 'kasallik varaqasi', 'bemorlik',
        ],
    ];

    /** Виды дисциплинарного взыскания: action_type → ключевые слова. */
    private const ACTION_TYPES = [
        'severeReprimand' => ['строгий выговор', 'строгого выговора', 'qattiq hayfsan'],
        'reprimand'       => ['выговор', 'hayfsan'],
        'remark'          => ['замечание', 'tanbeh'],
    ];

    /**
     * Формулировки, которые НЕЛЬЗЯ автоматически относить к приказам.
     *
     * «Dekret ta'tilidan qaytish» (возвращение из декрета) содержит слово
     * «ta'til» и иначе уехало бы в 1С как приказ на отпуск — то есть система
     * создала бы сотруднику отпуск вместо выхода из него. В контракте 1С
     * документа «возврат из отпуска» нет вовсе, поэтому такие журналы
     * отправляются на ручное решение, а не угадываются.
     */
    private const EXCLUSIONS = [
        'qaytish', 'qaytgan', 'возвращени', 'возврат', 'выход из отпуска',
        'досрочный выход', 'прерывании отпуска', 'отзыв из отпуска',
        'bekor qilish togrisidagi buyruqni',
        // «Ishlarni topshirish-qabul qilish» — приём-передача дел, а не приём
        // на работу. Содержит «qabul qilish» и иначе стало бы hirings
        'topshirish', 'приеме передаче дел', 'приема передачи дел',
    ];

    /**
     * Документы, которых в контракте 1С нет вовсе.
     *
     * Отличаются от EXCLUSIONS: там неоднозначность, требующая решения человека,
     * а здесь ясно, что выгружать нечего. Такие журналы сразу помечаются
     * «не выгружать» и не висят в списке спорных.
     */
    private const NOT_IN_CONTRACT = [
        'ishlab chiqarish'  => 'производственные приказы, не кадровые',
        'oylik ozgartirish' => 'изменение оклада — в контракте 1С нет такого документа',
        'ish haqini ozgartirish' => 'изменение оклада — в контракте 1С нет',
        'shaxsiy malumotlar' => 'изменение личных данных — идёт из 1С в EDOLINE, не обратно',
        'ragbatlantirish'   => 'поощрение — в контракте 1С нет такого документа',
        'mukofotlash'       => 'премирование — в контракте 1С нет такого документа',
        'buyruqlari'        => 'похоже на раздел, а не на журнал приказа',
        'премировани'       => 'премирование — в контракте 1С нет',
        'поощрени'          => 'поощрение — в контракте 1С нет',
        'изменении оклада'  => 'изменение оклада — в контракте 1С нет',
    ];

    /** Ниже этого порога результат помечается как требующий проверки человеком. */
    public const CONFIDENCE_THRESHOLD = 60;

    /**
     * Определить тип приказа.
     *
     * @param  string  $title     Название журнала
     * @param  string|null  $docCode  doc_code журнала (доп. подсказка)
     * @return array{order_type: ?string, subtype: array, confidence: int, matched_by: ?string}
     */
    public function classify(string $title, ?string $docCode = null): array
    {
        $haystack = $this->normalize($title . ' ' . (string) $docCode);

        if ($haystack === '') {
            return $this->emptyResult();
        }

        // Документы вне контракта — сразу «не выгружать», без ручного разбора
        foreach (self::NOT_IN_CONTRACT as $stop => $reason) {
            if (str_contains($haystack, $stop)) {
                return [
                    'order_type' => null,
                    'subtype'    => [],
                    'confidence' => 0,
                    'ignored'    => true,
                    'matched_by' => $reason,
                ];
            }
        }

        // Возврат/отзыв из отпуска — не приказ из контракта. Угадывать нельзя:
        // ошибка здесь означает лишний отпуск сотруднику в 1С
        foreach (self::EXCLUSIONS as $stop) {
            if (str_contains($haystack, $stop)) {
                return [
                    'order_type' => null,
                    'subtype'    => [],
                    'confidence' => 0,
                    'ignored'    => false,
                    'matched_by' => "исключение «{$stop}» — возврат/отзыв, нужно решение человека",
                ];
            }
        }

        // 1. Считаем вес каждого типа
        $scores  = [];
        $matches = [];

        foreach (self::RULES as $type => $weightedWords) {
            $hits = [];

            foreach ($weightedWords as $weight => $words) {
                foreach ($words as $word) {
                    if (str_contains($haystack, $word)) {
                        $hits[$word] = max($hits[$word] ?? 0, (int) $weight);
                    }
                }
            }

            if (empty($hits)) {
                continue;
            }

            // Поглощённые совпадения не должны удваивать вес:
            // «увольнением» подходит и под «увольнен», и под «увольнени» —
            // это одно попадание, а не два. Оставляем самое длинное.
            foreach (array_keys($hits) as $shorter) {
                foreach (array_keys($hits) as $longer) {
                    if ($shorter !== $longer && str_contains($longer, $shorter)) {
                        unset($hits[$shorter]);
                        break;
                    }
                }
            }

            $scores[$type]  = array_sum($hits);
            $matches[$type] = array_keys($hits);
        }

        if (empty($scores)) {
            return $this->emptyResult();
        }

        // 2. Применяем приоритеты: победитель подавляет конкурентов
        foreach (self::OVERRIDES as $winner => $suppressed) {
            if (!isset($scores[$winner])) {
                continue;
            }

            foreach ($suppressed as $loser) {
                unset($scores[$loser]);
            }
        }

        arsort($scores);
        $orderType = array_key_first($scores);
        $topScore  = $scores[$orderType];

        // 3. Уверенность: вес совпадений плюс отрыв от второго места.
        // Если два типа набрали поровну — название неоднозначное, нужен человек.
        $second     = count($scores) > 1 ? array_values($scores)[1] : 0;
        $confidence = $this->confidence($topScore, $second);

        return [
            'order_type' => $orderType,
            'subtype'    => $this->detectSubtype($orderType, $haystack),
            'confidence' => $confidence,
            'ignored'    => false,
            'matched_by' => mb_substr(implode(', ', array_unique($matches[$orderType] ?? [])), 0, 200),
        ];
    }

    /** Подтип внутри типа: vacation_type / absence_type / action_type. */
    private function detectSubtype(string $orderType, string $haystack): array
    {
        return match ($orderType) {
            'vacations' => $this->firstMatch(self::VACATION_SUBTYPES, $haystack, 'vacation_type', 'paid'),
            'absences'  => $this->firstMatch(self::ABSENCE_SUBTYPES, $haystack, 'absence_type', 'other'),
            'disciplinary_actions' => $this->firstMatch(self::ACTION_TYPES, $haystack, 'action_type', null),
            default     => [],
        };
    }

    /**
     * Первое совпадение по словарю подтипов.
     * Порядок ключей в словаре = приоритет: специфичное раньше общего
     * («отпуск по уходу за ребёнком» не должен стать просто «оплачиваемым»).
     */
    private function firstMatch(array $dictionary, string $haystack, string $field, ?string $default): array
    {
        foreach ($dictionary as $value => $words) {
            foreach ($words as $word) {
                if (str_contains($haystack, $word)) {
                    return [$field => $value];
                }
            }
        }

        return $default !== null ? [$field => $default] : [];
    }

    private function confidence(int $top, int $second): int
    {
        // Базовая уверенность от силы совпадения
        $base = min(100, $top * 8);

        // Штраф за конкурента: чем ближе второй, тем ниже уверенность
        if ($second > 0) {
            $gap = $top - $second;
            $base = $gap <= 0 ? 40 : max(40, $base - (50 - min(50, $gap * 10)));
        }

        return (int) max(0, min(100, $base));
    }

    /**
     * Нормализация текста под сравнение.
     *
     * Узбекская кириллица приводится к латинице, чтобы один словарь работал
     * для обоих написаний: организации ведут журналы и так, и так.
     */
    private function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));

        // Русская ё и узбекские апострофы (o‘, g‘, ’) — источник расхождений
        // ў → o, а не u: узбекская латиница пишет o' (bo'shatish, o'tkazish),
        // и словари построены именно под это написание
        $text = str_replace(
            ['ё', 'ў', 'қ', 'ғ', 'ҳ', 'ъ', '‘', '’', "'", '`', '"'],
            ['е', 'o', 'q', 'g', 'h', '',  '',  '',  '',  '',  ''],
            $text
        );

        // Узбекская кириллица → латиница (только буквы, отсутствующие в русском
        // словаре; общие с русским оставляем — они нужны для русских правил)
        $uzbekCyrillic = [
            'ш' => 'sh', 'ч' => 'ch', 'я' => 'ya', 'ю' => 'yu',
            'ж' => 'j',  'х' => 'x',  'ц' => 's',  'щ' => 'sh',
        ];

        // Применяем только если строка выглядит узбекской кириллицей:
        // содержит характерные сочетания. Иначе испортим русские слова
        // («жалоба» → «jaloba»), и русские правила перестанут срабатывать.
        // Триггеры проверяем ПОСЛЕ замены выше: ў/қ/ғ/ҳ здесь уже латинские
        if (preg_match('/(иш|бош|тог|отк|таtil|вазифас|жазо|haqида|қилиш|qилиш)/u', $text)) {
            $map = [
                    'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','з'=>'z',
                    'и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o',
                    'п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','ь'=>'',
                    'ы'=>'i','э'=>'e',
                ] + $uzbekCyrillic;

            $text = strtr($text, $map);
        }

        // Схлопываем пробелы и пунктуацию
        $text = preg_replace('/[^\p{L}\p{N}\s.]+/u', ' ', $text);
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim((string) $text);
    }

    private function emptyResult(): array
    {
        return [
            'order_type' => null,
            'subtype'    => [],
            'confidence' => 0,
            'ignored'    => false,
            'matched_by' => null,
        ];
    }
}
