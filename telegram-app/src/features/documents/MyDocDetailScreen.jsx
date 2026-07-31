import { useEffect, useState } from 'react';
import { useI18n } from '../../app/providers/I18nProvider';
import { useNavigation } from '../../app/providers/NavigationProvider';
import { MyDocumentsApi } from '../../shared/api/endpoints';
import { useDocumentsStore } from './DocumentsStore';
import { LoadingScreen, ErrorScreen, ConfirmModal } from '../../ui/common';
import Icon from '../../ui/Icon';
import { haptic, openExternalLink } from '../../shared/telegram';

const ACTION_ICONS = {
    send_to_sign:       'send',
    send_to_agreement:  'user-plus',
    send_to_create_res: 'edit-pen',
    edit:               'edit',
    delete:             'trash',
};

/**
 * Детали документа автора («Мои документы»):
 * owner-действия приходят с бэкенда списком { code, label, style, ... }.
 *   send_to_* → POST /send → редирект на sign_url (подтверждение отправки)
 *   edit      → OnlyOffice в браузере (web_url)
 *   delete    → модалка → DELETE → назад в список
 */
export default function MyDocDetailScreen({ jId }) {
    const { t, backendLang } = useI18n();
    const { pop } = useNavigation();
    const { removeMyDocFromCache } = useDocumentsStore();

    const [doc, setDoc] = useState(null);
    const [status, setStatus] = useState('loading');
    const [errorMsg, setErrorMsg] = useState('');
    const [busy, setBusy] = useState(false);
    const [confirmOpen, setConfirmOpen] = useState(false);

    const load = () => {
        setStatus('loading');
        MyDocumentsApi.detail(jId)
            .then((data) => { setDoc(data); setStatus('ready'); })
            .catch((e) => {
                haptic('error');
                setErrorMsg(e.message || t('mini.err_doc_open'));
                setStatus('error');
            });
    };

    useEffect(load, [jId, backendLang]); // eslint-disable-line react-hooks/exhaustive-deps

    const handleAction = async (action) => {
        haptic('light');

        if (action.code === 'delete') {
            setConfirmOpen(true);
            return;
        }
        if (action.code === 'edit') {
            if (action.web_url) openExternalLink(action.web_url);
            return;
        }

        // send_to_sign / send_to_agreement / send_to_create_res
        setBusy(true);
        try {
            const data = await MyDocumentsApi.send(doc.id, action.sign_type);
            if (!data.sign_url) throw new Error(t('mini.err_send'));
            haptic('success');
            window.location.href = data.sign_url;
        } catch (e) {
            haptic('error');
            alert(t('mini.error') + ': ' + e.message);
            setBusy(false);
        }
    };

    const doDelete = async () => {
        setConfirmOpen(false);
        setBusy(true);
        setStatus('loading');
        try {
            const data = await MyDocumentsApi.remove(doc.id);
            if (!data.success) throw new Error(data.message || t('mini.err_save'));
            haptic('success');
            removeMyDocFromCache(doc.id);
            pop();  // назад в список — документа уже нет
        } catch (e) {
            haptic('error');
            alert(t('mini.error') + ': ' + e.message);
            setBusy(false);
            setStatus('ready');
        }
    };

    if (status === 'loading') return <LoadingScreen />;
    if (status === 'error') return <ErrorScreen message={errorMsg} onRetry={load} />;

    const actions = doc.actions || [];
    const statusTxt = doc.status ? ' · ' + doc.status : '';

    return (
        <div className="state active">
            <div className="detail-top">
                <div className="back-btn" onClick={() => { haptic('light'); pop(); }}>
                    <Icon name="arrow-left" strokeWidth={2.6} />
                    <span>{t('action.back')}</span>
                </div>
                <div className="detail-title">{doc.doc_name || '—'}</div>
                <div className="detail-number">{doc.doc_number ? '№ ' + doc.doc_number : ''}</div>
            </div>

            <div className="detail-section doc-info-card">
                <div className="detail-section-title">
                    <Icon name="info" strokeWidth={2.4} />
                    <span>{t('mini.doc_info')}</span>
                </div>
                <div className="info-feed-item">
                    <div className="info-feed-icon"><Icon name="clock" strokeWidth={2} /></div>
                    <div className="info-feed-body">
                        <div className="info-feed-label">{t('field.doc_date')}</div>
                        <div className="info-feed-value">{doc.doc_date || doc.created_at || '—'}</div>
                    </div>
                </div>
                <div className="info-feed-item">
                    <div className="info-feed-icon"><Icon name="check-square" strokeWidth={2} /></div>
                    <div className="info-feed-body">
                        <div className="info-feed-label">{t('mini.actions')}</div>
                        <div className="info-feed-value">{(doc.journal || '—') + statusTxt}</div>
                    </div>
                </div>
            </div>

            {doc.rendered_html ? (
                <div className="detail-section" style={{ padding: 0, overflow: 'hidden' }}>
                    <div className="detail-section-title" style={{ padding: '16px 16px 0' }}>
                        <Icon name="lines" strokeWidth={2.4} />
                        <span>{t('mini.doc_view')}</span>
                    </div>
                    <div className="doc-render-wrap" dangerouslySetInnerHTML={{ __html: doc.rendered_html }} />
                </div>
            ) : (doc.html_doc || doc.doc_text) ? (
                <div className="detail-section">
                    <div className="detail-section-title">
                        <Icon name="lines" strokeWidth={2.4} />
                        <span>{t('mini.doc_text')}</span>
                    </div>
                    {doc.html_doc ? (
                        <div className="doc-body-content" dangerouslySetInnerHTML={{ __html: doc.html_doc }} />
                    ) : (
                        <div className="doc-body-content"><p>{doc.doc_text}</p></div>
                    )}
                </div>
            ) : null}

            {doc.files && doc.files.length > 0 && (
                <div className="detail-section" style={{ marginBottom: 14 }}>
                    <div className="detail-section-title">
                        <Icon name="paperclip" strokeWidth={2.4} />
                        <span>{t('mini.files')}</span>
                    </div>
                    <div>
                        {doc.files.map((f, i) => (
                            <div
                                key={i}
                                className="file-row"
                                onClick={() => openExternalLink(f.url)}
                                style={{
                                    padding: '10px 12px', background: 'rgba(15,23,42,0.04)',
                                    borderRadius: 9, marginBottom: 6, cursor: 'pointer',
                                    display: 'flex', alignItems: 'center', gap: 10,
                                }}
                            >
                                <Icon name="file" size={16} strokeWidth={2} />
                                <span>{f.name}</span>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {actions.length > 0 && (
                <div className="detail-section owner-actions-section active" style={{ display: 'block' }}>
                    <div className="detail-section-title">
                        <Icon name="settings" strokeWidth={2.4} />
                        <span>{t('mini.actions')}</span>
                    </div>
                    <div className="owner-actions-grid">
                        {actions.map((a, idx) => (
                            <button
                                key={idx}
                                className={'action-btn action-btn-' + (a.style || 'primary')}
                                disabled={busy}
                                onClick={() => handleAction(a)}
                            >
                                {ACTION_ICONS[a.code] ? <Icon name={ACTION_ICONS[a.code]} strokeWidth={2.2} /> : null}
                                <span>{a.label}</span>
                            </button>
                        ))}
                    </div>
                </div>
            )}

            <ConfirmModal
                open={confirmOpen}
                title={t('mini.delete_doc_title')}
                text={t('mini.delete_doc_text')}
                okLabel={t('mini.delete')}
                onOk={doDelete}
                onCancel={() => setConfirmOpen(false)}
            />
        </div>
    );
}
