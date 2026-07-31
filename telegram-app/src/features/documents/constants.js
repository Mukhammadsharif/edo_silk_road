/**
 * Категории вкладки «Документы».
 * titleKey/emptyKey — ключи i18n (лейблы считаются в момент рендера,
 * а не при загрузке — иначе смена языка их не обновит).
 */
export const CATEGORIES = {
    for_resolution: { role: 'RESOL', titleKey: 'menu.for_resolution',  color: 'purple' },
    for_sign:       { role: 'SIGN',  titleKey: 'menu.for_sign',        color: 'orange' },
    for_approval:   { role: 'AGREE', titleKey: 'menu.for_coillaborate', color: 'blue' },
    my_docs:        { role: null,    titleKey: 'menu.my_docs',         color: 'green' },
};

export const CATEGORY_ORDER = ['for_resolution', 'for_sign', 'for_approval', 'my_docs'];

/** Фильтры статусов для «Мои документы» (жёсткий список, как в старой версии). */
export const MY_DOCS_FILTERS = [
    { status: '',   labelKey: 'mini.all' },
    { status: '1',  labelKey: 'mini.st_new' },
    { status: '9',  labelKey: 'mini.st_in_resol' },
    { status: '10', labelKey: 'mini.st_signed' },
    { status: '12', labelKey: 'mini.st_overdue' },
];

export function roleClass(role) {
    if (role === 'RESOL') return 'role-RESOL';
    if (role === 'AGREE') return 'role-AGREE';
    return 'role-SIGN';
}

export function accentClass(role) {
    if (role === 'RESOL') return 'accent-RESOL';
    if (role === 'AGREE') return 'accent-AGREE';
    return 'accent-SIGN';
}
