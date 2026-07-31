import { useEffect, useState } from 'react';
import { useI18n } from '../../app/providers/I18nProvider';
import { useNavigation } from '../../app/providers/NavigationProvider';
import { DocumentsApi } from '../../shared/api/endpoints';
import { roleClass } from './constants';
import { LoadingScreen, ErrorScreen } from '../../ui/common';
import Icon from '../../ui/Icon';
import { haptic } from '../../shared/telegram';

/**
 * Детали документа из sign-категорий (RESOL/SIGN/AGREE)
 * с кнопкой «Imzolash» → одноразовая ссылка E-IMZO.
 */
export default function SignDocDetailScreen({ docId }) {
    const { t, backendLang } = useI18n();
    const { pop } = useNavigation();

    const [doc, setDoc] = useState(null);
    const [status, setStatus] = useState('loading');
    const [errorMsg, setErrorMsg] = useState('');
    const [signing, setSigning] = useState(false);

    const load = () => {
        setStatus('loading');
        DocumentsApi.detail(docId, backendLang)
            .then((data) => { setDoc(data); setStatus('ready'); })
            .catch((e) => {
                haptic('error');
                setErrorMsg(e.message || t('mini.no_documents'));
                setStatus('error');
            });
    };

    useEffect(load, [docId, backendLang]); // eslint-disable-line react-hooks/exhaustive-deps

    const openSignPage = async () => {
        if (!doc || signing) return;
        haptic('medium');
        setSigning(true);
        try {
            const data = await DocumentsApi.signToken(doc.id);
            if (!data.sign_url) throw new Error(t('mini.err_sign_url'));
            // Страница подписи открывается в том же окне — flow как в старой версии
            window.location.href = data.sign_url;
        } catch (e) {
            haptic('error');
            alert(t('mini.error') + ': ' + e.message);
            setSigning(false);
        }
    };

    if (status === 'loading') return <LoadingScreen />;
    if (status === 'error') return <ErrorScreen message={errorMsg} onRetry={load} />;

    const info = doc.info || {};

    return (
        <div className="state active">
            <div className="detail-top">
                <div className="back-btn" onClick={() => { haptic('light'); pop(); }}>
                    <Icon name="arrow-left" strokeWidth={2.6} />
                    <span>{t('action.back')}</span>
                </div>
                <div className="detail-title">{doc.doc_name || '—'}</div>
                <div className="detail-number">{doc.doc_number ? '№ ' + doc.doc_number : ''}</div>
                <span className={'role-badge ' + roleClass(doc.role)}>{doc.role_label}</span>
            </div>

            <div className="detail-section doc-info-card">
                <div className="detail-section-title">
                    <Icon name="info" strokeWidth={2.4} />
                    <span>{t('mini.doc_info')}</span>
                </div>

                <InfoRow icon="flag" label={t('mini.doc_type')} value={info.journal_type} />
                <InfoRow icon="file" label={t('field.doc_name')} value={info.doc_name} />
                <InfoRow icon="clock" label={t('field.doc_date')} value={info.doc_date || doc.created_at || '—'} always />
                <InfoRow icon="user" label={t('mini.signed_by')} value={info.signed_by} />
                <InfoRow icon="pen" label={t('mini.signed_at')} value={info.signed_at} />
                <InfoRow icon="user" label={t('mini.creator')} value={info.creator} />
                <InfoRow icon="check-square" label={t('mini.actions')} value={doc.role_label || '—'} always />
            </div>

            {doc.rendered_html ? (
                <div className="detail-section" style={{ padding: 0, overflow: 'hidden' }}>
                    <div className="detail-section-title" style={{ padding: '16px 16px 0' }}>
                        <Icon name="lines" strokeWidth={2.4} />
                        <span>{t('mini.doc_view')}</span>
                    </div>
                    {/* HTML документа рендерит НАШ бэкенд (те же blade-шаблоны, что на сайте) */}
                    <div className="doc-render-wrap" dangerouslySetInnerHTML={{ __html: doc.rendered_html }} />
                </div>
            ) : doc.html_doc ? (
                <div className="detail-section">
                    <div className="detail-section-title">
                        <Icon name="lines" strokeWidth={2.4} />
                        <span>{t('mini.doc_text')}</span>
                    </div>
                    <div className="doc-body-content" dangerouslySetInnerHTML={{ __html: doc.html_doc }} />
                </div>
            ) : null}

            {doc.files && doc.files.length > 0 && (
                <div className="detail-section" style={{ marginBottom: 90 }}>
                    <div className="detail-section-title">
                        <Icon name="paperclip" strokeWidth={2.4} />
                        <span>{t('mini.files')}</span>
                    </div>
                    <div>
                        {doc.files.map((f, i) => (
                            <div key={i} className="file-row">
                                <div className="file-icon"><Icon name="file" strokeWidth={2.2} /></div>
                                <span className="file-name">{f.name}</span>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            <div className="sign-btn-wrap visible">
                <button className="btn-sign" disabled={signing} onClick={openSignPage}>
                    <Icon name="lock" strokeWidth={2.2} />
                    <span>{signing ? t('mini.loading') : t('mini.sign_eimzo')}</span>
                </button>
            </div>
        </div>
    );
}

function InfoRow({ icon, label, value, always = false }) {
    if (!always && !(value && String(value).trim())) return null;
    return (
        <div className="info-feed-item">
            <div className="info-feed-icon"><Icon name={icon} strokeWidth={2} /></div>
            <div className="info-feed-body">
                <div className="info-feed-label">{label}</div>
                <div className="info-feed-value">{value}</div>
            </div>
        </div>
    );
}
