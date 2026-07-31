import { useState } from 'react';
import { useI18n } from '../../app/providers/I18nProvider';
import { useNavigation } from '../../app/providers/NavigationProvider';
import { CreateApi } from '../../shared/api/endpoints';
import { LoadingScreen, ErrorScreen } from '../../ui/common';
import Icon from '../../ui/Icon';
import { haptic } from '../../shared/telegram';

const TYPES = [
    { code: 'internal', icon: 'file',        titleKey: 'mini.type_internal', subKey: 'mini.type_internal_sub' },
    { code: 'outgoing', icon: 'send',        titleKey: 'mini.type_outgoing', subKey: 'mini.type_outgoing_sub' },
    { code: 'incoming', icon: 'edit-simple', titleKey: 'mini.type_incoming', subKey: 'mini.type_incoming_sub' },
];

/**
 * Шаг 1 мастера: тип документа. После выбора грузим справочники
 * (init) и переходим в форму — они уезжают параметрами экрана,
 * так что форма открывается уже с данными.
 */
export default function CreateTypeScreen() {
    const { t } = useI18n();
    const { push } = useNavigation();
    const [status, setStatus] = useState('idle');   // idle | loading | error
    const [errorMsg, setErrorMsg] = useState('');

    const selectType = async (type) => {
        haptic('light');
        setStatus('loading');
        try {
            const init = await CreateApi.init(type);
            push('create-wizard', { type, init });
            setStatus('idle');
        } catch (e) {
            haptic('error');
            setErrorMsg(e.message || t('mini.err_init'));
            setStatus('error');
        }
    };

    if (status === 'loading') return <LoadingScreen />;
    if (status === 'error') return <ErrorScreen message={errorMsg} onRetry={() => setStatus('idle')} />;

    return (
        <div className="state active">
            <div className="wizard-stepper">
                <div className="step-pill active" />
                <div className="step-pill" />
                <div className="step-pill" />
            </div>
            <div className="wizard-card">
                <div className="wizard-title">{t('mini.create_title')}</div>
                <div className="wizard-subtitle">{t('mini.create_subtitle')}</div>
                <div className="type-grid">
                    {TYPES.map((tp) => (
                        <button key={tp.code} className="type-card" onClick={() => selectType(tp.code)}>
                            <div className="type-card-icon"><Icon name={tp.icon} strokeWidth={2} /></div>
                            <div className="type-card-body">
                                <div className="type-card-title">{t(tp.titleKey)}</div>
                                <div className="type-card-sub">{t(tp.subKey)}</div>
                            </div>
                        </button>
                    ))}
                </div>
            </div>
        </div>
    );
}
