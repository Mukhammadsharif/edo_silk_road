import Icon from './Icon';
import { useI18n } from '../app/providers/I18nProvider';

/** Экран загрузки со скелетоном (разметка старой версии 1-в-1). */
export function LoadingScreen({ text }) {
    const { t } = useI18n();
    return (
        <div className="state active">
            <div className="loader">
                <div className="spinner-wrap"><div className="spinner" /></div>
                <div className="loader-text">{text || t('mini.loading')}</div>
            </div>
            <div className="skeleton-list" aria-hidden="true">
                <div className="skeleton-card">
                    <div className="skel-line skel-w-70" style={{ marginBottom: 12 }} />
                    <div style={{ display: 'flex', gap: 6 }}>
                        <div className="skel-line skel-w-30" style={{ height: 18 }} />
                        <div className="skel-line skel-w-30" style={{ height: 18 }} />
                    </div>
                </div>
                <div className="skeleton-card">
                    <div className="skel-line skel-w-50" style={{ marginBottom: 12 }} />
                    <div style={{ display: 'flex', gap: 6 }}>
                        <div className="skel-line skel-w-30" style={{ height: 18 }} />
                        <div className="skel-line skel-w-30" style={{ height: 18 }} />
                    </div>
                </div>
            </div>
        </div>
    );
}

export function ErrorScreen({ message, onRetry }) {
    const { t } = useI18n();
    return (
        <div className="state active">
            <div className="error-card">
                <div className="error-icon-wrap"><Icon name="warning" strokeWidth={2} /></div>
                <div className="error-text">{message || t('mini.err_generic')}</div>
                {onRetry && (
                    <button className="btn-retry" onClick={onRetry}>
                        <Icon name="retry" strokeWidth={2.4} />
                        <span>{t('mini.retry')}</span>
                    </button>
                )}
            </div>
        </div>
    );
}

/** Инлайн-пустышка внутри списка (пилюли фильтров остаются видимыми). */
export function InlineEmpty({ title, subtitle }) {
    return (
        <div style={{ textAlign: 'center', padding: '48px 20px', color: 'var(--text-muted)' }}>
            <div style={{
                width: 64, height: 64, borderRadius: '50%',
                background: 'var(--surface-2)', border: '1px solid var(--border-soft)',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                margin: '0 auto 16px', color: 'var(--text-muted)',
            }}>
                <Icon name="file" size={28} strokeWidth={1.8} />
            </div>
            <div style={{ fontSize: 15, fontWeight: 600, color: 'var(--text)', marginBottom: 6 }}>{title}</div>
            <div style={{ fontSize: 13, maxWidth: 260, margin: '0 auto' }}>{subtitle}</div>
        </div>
    );
}

/** Модалка подтверждения (та же .confirm-overlay из CSS). */
export function ConfirmModal({ open, title, text, okLabel, onOk, onCancel, danger = true }) {
    const { t } = useI18n();
    return (
        <div className={'confirm-overlay' + (open ? ' active' : '')}>
            <div className="confirm-modal">
                <div className="confirm-icon"><Icon name="warning" strokeWidth={2.4} /></div>
                <div className="confirm-title">{title}</div>
                <div className="confirm-text">{text}</div>
                <div className="confirm-actions">
                    <button
                        className="action-btn"
                        style={{ background: 'rgba(15,23,42,0.06)', color: 'var(--text)' }}
                        onClick={onCancel}
                    >
                        {t('mini.cancel')}
                    </button>
                    <button
                        className={'action-btn' + (danger ? ' action-btn-danger' : ' action-btn-primary')}
                        onClick={onOk}
                    >
                        {okLabel || t('mini.delete')}
                    </button>
                </div>
            </div>
        </div>
    );
}

/** Ряд пилюль-фильтров. options: [{code|status, label}] */
export function FilterPills({ options, current, onSelect, keyField = 'code' }) {
    return (
        <div className="filter-pill-row" style={{ display: 'flex' }}>
            {options.map((opt) => {
                const val = opt[keyField];
                return (
                    <button
                        key={val}
                        className={'filter-pill' + (val === current ? ' active' : '')}
                        onClick={() => { if (val !== current) onSelect(val); }}
                    >
                        {opt.label}
                    </button>
                );
            })}
        </div>
    );
}
