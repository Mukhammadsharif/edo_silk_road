import { useState } from 'react';
import { useI18n } from '../../app/providers/I18nProvider';
import { useNavigation } from '../../app/providers/NavigationProvider';
import { useDocumentsStore } from './DocumentsStore';
import { CATEGORIES, MY_DOCS_FILTERS, roleClass, accentClass } from './constants';
import { LoadingScreen, ErrorScreen, InlineEmpty, FilterPills } from '../../ui/common';
import Icon from '../../ui/Icon';
import { haptic } from '../../shared/telegram';

/**
 * Список документов категории. Один экран на все 4 категории:
 * различаются фильтры (my_docs — жёсткий список, sign — с сервера)
 * и вид карточек.
 */
export default function DocumentsListScreen({ category }) {
    const { t } = useI18n();
    const { push, pop } = useNavigation();
    const {
        lists, listStatus, listError,
        myDocsFilter, setMyDocsFilter,
        signFilters, setSignFilter,
        statusOptions,
        loadCategory, loadMore,
    } = useDocumentsStore();

    const [loadingMore, setLoadingMore] = useState(false);

    const meta = CATEGORIES[category];
    const cache = lists[category];
    const status = listStatus[category];
    const isMyDocs = category === 'my_docs';

    const openDoc = (doc) => {
        haptic('light');
        if (isMyDocs) {
            push('detail', { kind: 'my', id: doc.id, docHash: doc.doc_hash || '' });
        } else {
            push('detail', { kind: 'sign', id: doc.id });
        }
    };

    const onLoadMore = async () => {
        setLoadingMore(true);
        await loadMore(category);
        setLoadingMore(false);
    };

    if (status === 'loading' && cache.items.length === 0) {
        return <LoadingScreen />;
    }
    if (status === 'error') {
        return (
            <ErrorScreen
                message={listError[category]}
                onRetry={() => loadCategory(category, { reset: true })}
            />
        );
    }

    const signOpts = statusOptions[category];

    return (
        <div className="state active">
            <div style={{ marginBottom: 12, display: 'flex', alignItems: 'center', gap: 8 }}>
                <button className="back-btn" style={{ display: 'inline-flex' }} onClick={() => { haptic('light'); pop(); }}>
                    <Icon name="arrow-left" strokeWidth={2.6} />
                    <span>{t('action.back')}</span>
                </button>
                <div style={{ fontSize: 15, fontWeight: 700, color: 'var(--text)' }}>
                    {t(meta.titleKey)}
                </div>
            </div>

            {isMyDocs ? (
                <FilterPills
                    keyField="status"
                    options={MY_DOCS_FILTERS.map((f) => ({ status: f.status, label: t(f.labelKey) }))}
                    current={myDocsFilter}
                    onSelect={(v) => { haptic('light'); setMyDocsFilter(v); }}
                />
            ) : signOpts ? (
                <FilterPills
                    options={signOpts}
                    current={signFilters[category] || '2'}
                    onSelect={(v) => { haptic('light'); setSignFilter(category, v); }}
                />
            ) : (
                <div className="filter-pill-row" style={{ display: 'flex' }}>
                    <div style={{ padding: '8px 4px', color: 'var(--text-muted)', fontSize: 12 }}>
                        {t('mini.filters_loading')}
                    </div>
                </div>
            )}

            <div className="doc-list">
                {cache.items.length === 0 ? (
                    <InlineEmpty title={t('mini.no_documents')} subtitle={t('mini.no_documents_sub')} />
                ) : isMyDocs ? (
                    cache.items.map((d) => <MyDocCard key={d.id} doc={d} onOpen={openDoc} />)
                ) : (
                    cache.items.map((d) => <SignDocCard key={d.id} doc={d} onOpen={openDoc} />)
                )}
            </div>

            {isMyDocs && cache.hasMore && (
                <div className="pagination-bar">
                    <button disabled={loadingMore} onClick={onLoadMore}>
                        {loadingMore ? t('mini.loading') : t('action.load_more')}
                    </button>
                </div>
            )}
        </div>
    );
}

/** Карточка sign-категорий (подпись / согласование / резолюция). */
function SignDocCard({ doc, onOpen }) {
    return (
        <div className={'doc-card ' + accentClass(doc.role)} onClick={() => onOpen(doc)}>
            <div className="doc-card-top">
                <div className="doc-name">{doc.doc_name}</div>
                <div className="doc-arrow"><Icon name="chevron-right" strokeWidth={2.4} /></div>
            </div>
            <div className="doc-meta">
                {doc.doc_number ? <span className="meta-tag">№{doc.doc_number}</span> : null}
                <span className="meta-tag">{doc.created_at}</span>
                <span className={'role-badge ' + roleClass(doc.role)}>
                    {doc.status_label || doc.role_label}
                </span>
            </div>
        </div>
    );
}

/** Карточка «Мои документы». */
function MyDocCard({ doc, onOpen }) {
    const metaItems = [];
    if (doc.journal) metaItems.push(doc.journal);
    if (doc.doc_date) metaItems.push(doc.doc_date);
    if (doc.doc_number) metaItems.push('№ ' + doc.doc_number);

    return (
        <div className="my-doc-card" onClick={() => onOpen(doc)}>
            <div className="my-doc-icon"><Icon name="file" strokeWidth={2} /></div>
            <div className="my-doc-body">
                <div className="my-doc-name">{doc.doc_name || '—'}</div>
                <div className="my-doc-meta">
                    {metaItems.map((m, i) => (
                        <span key={i}>
                            {i > 0 && <span className="sep">·</span>} {m}
                        </span>
                    ))}
                </div>
                {doc.status ? <span className="my-doc-badge">{doc.status}</span> : null}
            </div>
        </div>
    );
}
