import { useEffect } from 'react';
import { useI18n } from '../../app/providers/I18nProvider';
import { useNavigation } from '../../app/providers/NavigationProvider';
import { useDocumentsStore } from '../documents/DocumentsStore';
import { CATEGORIES, CATEGORY_ORDER } from '../documents/constants';
import { haptic } from '../../shared/telegram';
import Icon from '../../ui/Icon';

const CARD_ICONS = {
    for_resolution: 'check-square',
    for_sign:       'edit-pen',
    for_approval:   'users',
    my_docs:        'folder',
};

export default function HomeScreen() {
    const { t, backendLang } = useI18n();
    const { push } = useNavigation();
    const { counts, loadCounts, enterCategory } = useDocumentsStore();

    // Счётчики обновляются при каждом заходе на главную и при смене языка
    useEffect(() => {
        loadCounts().catch(() => { /* не фатально, покажем прошлые значения */ });
    }, [backendLang]); // eslint-disable-line react-hooks/exhaustive-deps

    const openCategory = (cat) => {
        haptic('light');
        enterCategory(cat);
        push('list', { category: cat });
    };

    return (
        <div className="state active">
            <div className="home-grid">
                {CATEGORY_ORDER.map((cat) => (
                    <button
                        key={cat}
                        className={'home-card home-card-' + CATEGORIES[cat].color}
                        onClick={() => openCategory(cat)}
                    >
                        <div className="home-card-blob" />
                        <div className="home-card-icon">
                            <Icon name={CARD_ICONS[cat]} strokeWidth={2.2} />
                        </div>
                        <div>
                            <div className="home-card-count">{counts[cat]}</div>
                            <div className="home-card-label">{t(CATEGORIES[cat].titleKey)}</div>
                        </div>
                        <div className="home-card-arrow"><Icon name="arrow-right" strokeWidth={2.4} /></div>
                    </button>
                ))}
            </div>
        </div>
    );
}
