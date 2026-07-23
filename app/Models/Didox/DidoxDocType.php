<?php

namespace App\Models\Didox;

/**
 * Справочные константы Didox: коды типов документов и коды статусов
 * (из разделов «06. Документы» и «09. Каталоги» документации).
 * Вынесено отдельно, чтобы не хардкодить числа по коду.
 */
final class DidoxDocType
{
    /* Коды типов документов (docType). */
    public const INVOICE            = '002'; // Счёт-фактура без акта
    public const INVOICE_PHARM      = '008'; // Счёт-фактура (ФАРМ)
    public const INVOICE_HYBRID     = '023'; // Гибридная счёт-фактура
    public const WAYBILL            = '041'; // ТТН
    public const ACT                = '005'; // Акт выполненных работ
    public const EMPOWERMENT        = '006'; // Доверенность
    public const EMPOWERMENT_NEW    = '062'; // Доверенность (новая)

    public const CONTRACT           = '007'; // Договор (ГНК)
    public const FREE               = '000'; // Произвольный документ
    public const FREE_MULTI         = '010'; // Многосторонний произвольный
    public const RECONCILIATION     = '052'; // Акт сверки
    public const ACCEPTANCE         = '054'; // Акт приёма-передачи
    public const FOUNDERS_PROTOCOL  = '075'; // Протокол собрания учредителей
    public const TAX_LETTER         = '031'; // Письмо НК

    public const LABELS = [
        self::INVOICE           => 'Счёт-фактура',
        self::INVOICE_PHARM     => 'Счёт-фактура (ФАРМ)',
        self::INVOICE_HYBRID    => 'Гибридная счёт-фактура',
        self::WAYBILL           => 'ТТН',
        self::ACT               => 'Акт выполненных работ',
        self::EMPOWERMENT       => 'Доверенность',
        self::EMPOWERMENT_NEW   => 'Доверенность (новая)',
        self::CONTRACT          => 'Договор (ГНК)',
        self::FREE              => 'Произвольный документ',
        self::FREE_MULTI        => 'Многосторонний произвольный документ',
        self::RECONCILIATION    => 'Акт сверки',
        self::ACCEPTANCE        => 'Акт приёма-передачи',
        self::FOUNDERS_PROTOCOL => 'Протокол собрания учредителей',
        self::TAX_LETTER        => 'Письмо НК',
    ];

    /*
    | Коды статусов документов Didox (общие для СФ/актов/договоров).
    | Полные таблицы по каждому типу — в разделе «09. Каталоги».
    */
    public const STATUS_DRAFT            = 0;   // Черновик
    public const STATUS_WAIT_PARTNER     = 1;   // Ожидают подписи партнёра
    public const STATUS_WAIT_YOU         = 2;   // Ожидает вашей подписи
    public const STATUS_SIGNED           = 3;   // Подписан
    public const STATUS_REJECTED         = 4;   // Отказ от подписи
    public const STATUS_DELETED          = 5;   // Удалён
    public const STATUS_DRAFT_DELETED    = 55;  // Черновик удалён
    public const STATUS_NOT_VALID        = 40;  // Недействительный
    public const STATUS_CANCELLED_TAX    = 50;  // Аннулирован НК
    public const STATUS_WAIT_AGENT       = 60;  // Ожидают подписи агента (для получателя)

    /**
     * Маппинг числового статуса Didox → локальный list_status EDOLINE
     * (для отображения кружка в списке входящих согласно легенде).
     */
    public static function toListStatus(?int $didoxStatus): string
    {
        return match ($didoxStatus) {
            self::STATUS_SIGNED => DidoxDocument::LIST_SIGNED,
            self::STATUS_REJECTED,
            self::STATUS_NOT_VALID,
            self::STATUS_CANCELLED_TAX => DidoxDocument::LIST_REJECTED,
            default             => DidoxDocument::LIST_NOT_SIGNED,
        };
    }
}
