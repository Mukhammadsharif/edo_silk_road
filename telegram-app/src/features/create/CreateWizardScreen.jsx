import { useMemo, useRef, useState } from 'react';
import { useI18n } from '../../app/providers/I18nProvider';
import { useNavigation } from '../../app/providers/NavigationProvider';
import { CreateApi } from '../../shared/api/endpoints';
import { useDocumentsStore } from '../documents/DocumentsStore';
import Icon from '../../ui/Icon';
import { haptic, openExternalLink } from '../../shared/telegram';

/**
 * Шаг 2 мастера: форма создания документа.
 * Справочники (init) приходят параметром экрана из CreateTypeScreen.
 *
 * Поведение из старой версии:
 *  - выбор журнала → подгрузка подтипов; если подтипов нет — сразу
 *    участники маршрута (+организации для incoming/outgoing);
 *  - resolution_user обязателен (валидируется и на бэкенде);
 *  - согласователи — мультивыбор чипами;
 *  - основной файл только .doc/.docx до 50 МБ, доп. файлы любые до 50 МБ;
 *  - «Kompyuterda ochish» — handoff текстовых полей в OnlyOffice web editor;
 *  - после успеха: карточка успеха, кэш my_docs инвалидируется,
 *    счётчик +1, кнопки «Мои документы» / «Ko'rish».
 */
export default function CreateWizardScreen() {
    const { t } = useI18n();
    const { screen, pop, setTab, resetTab } = useNavigation();
    const { invalidateMyDocs, enterCategory } = useDocumentsStore();

    const type = screen.params.type;
    const init = screen.params.init || {};

    const formTitle = t('mini.form_title_' + type);

    /* ─────────── Состояние формы ─────────── */
    const today = useMemo(() => {
        const d = new Date();
        return String(d.getDate()).padStart(2, '0') + '.'
            + String(d.getMonth() + 1).padStart(2, '0') + '.'
            + d.getFullYear();
    }, []);

    const [fields, setFields] = useState({
        journal_type: '', journal_type2: '',
        doc_date: today, doc_name: '', doc_number: '',
        confidence: '', doc_lang: (init.languages && init.languages[0]?.code) || 'uz',
        signing_term: '', doc_text: '',
        resolution_user: '', to_emp: '', doc_organization: '',
    });
    const setField = (name, value) => setFields((f) => ({ ...f, [name]: value }));

    const [journals2, setJournals2] = useState(null);       // подтипы | null
    const [execUsers, setExecUsers] = useState(null);       // {resolution_users, signatory_users, agreement_users}
    const [orgs, setOrgs] = useState(null);
    const [agreeIds, setAgreeIds] = useState([]);
    const [mainFile, setMainFile] = useState(null);
    const [docFiles, setDocFiles] = useState([]);
    const [submitting, setSubmitting] = useState(false);
    const [success, setSuccess] = useState(null);           // { message, journal_id }

    const mainFileRef = useRef(null);
    const docFilesRef = useRef(null);

    /* ─────────── Журнал и участники ─────────── */
    const loadExecUsersAndOrgs = async (jId) => {
        try {
            const eu = await CreateApi.executionUsers(jId);
            setExecUsers(eu);
            setAgreeIds([]);
            setField('resolution_user', '');
            setField('to_emp', '');

            if (type === 'incoming' || type === 'outgoing') {
                const oData = await CreateApi.docOrganizations(jId);
                setOrgs(oData.organizations || []);
            }
        } catch (e) {
            console.error('Failed to load exec users / orgs', e);
        }
    };

    const onJournalChange = async (jId) => {
        setField('journal_type', jId);
        setField('journal_type2', '');
        setJournals2(null);
        setExecUsers(null);
        setOrgs(null);

        if (!jId) return;
        try {
            const data = await CreateApi.journals(jId);
            const children = data.journals || [];
            if (children.length > 0) {
                setJournals2(children);
            } else {
                await loadExecUsersAndOrgs(jId);
            }
        } catch (e) {
            console.error(e);
        }
    };

    const onJournal2Change = async (jId2) => {
        setField('journal_type2', jId2);
        if (jId2) await loadExecUsersAndOrgs(jId2);
    };

    /* ─────────── Файлы ─────────── */
    const onMainFileChange = (e) => {
        const input = e.target;
        if (!input.files || !input.files.length) { setMainFile(null); return; }
        const file = input.files[0];
        const ext = file.name.split('.').pop().toLowerCase();
        if (['doc', 'docx'].indexOf(ext) === -1) {
            alert(t('mini.err_only_doc'));
            input.value = '';
            return;
        }
        if (file.size > 50 * 1024 * 1024) {
            alert(t('mini.err_file_too_big'));
            input.value = '';
            return;
        }
        setMainFile(file);
    };

    const onDocFilesChange = (e) => {
        const input = e.target;
        if (!input.files || !input.files.length) return;
        const accepted = [];
        for (const f of input.files) {
            if (f.size > 50 * 1024 * 1024) { alert(t('mini.err_file_too_big')); continue; }
            accepted.push(f);
        }
        setDocFiles((prev) => prev.concat(accepted));
        input.value = '';
    };

    /* ─────────── Отправка ─────────── */
    const submit = async () => {
        if (!fields.journal_type) { alert(t('mini.err_select_journal2')); return; }
        if (execUsers && (execUsers.resolution_users || []).length > 0 && !fields.resolution_user) {
            alert(t('mini.err_select_resol'));
            return;
        }

        setSubmitting(true);
        try {
            const fd = new FormData();
            fd.append('journal_type', fields.journal_type);
            if (fields.journal_type2) fd.append('journal_type2', fields.journal_type2);
            fd.append('doc_name', fields.doc_name);
            fd.append('doc_number', fields.doc_number);
            fd.append('doc_date', fields.doc_date);
            fd.append('doc_text', fields.doc_text);
            fd.append('doc_lang', fields.doc_lang || 'uz');
            fd.append('confidence', fields.confidence);
            fd.append('signing_term', fields.signing_term);
            if (fields.resolution_user) fd.append('resolution_user', fields.resolution_user);
            if (fields.to_emp) fd.append('to_emp', fields.to_emp);
            agreeIds.forEach((id) => fd.append('to_agree[]', id));
            if (fields.doc_organization) fd.append('doc_organization', fields.doc_organization);
            if (mainFile) fd.append('main_file', mainFile);
            docFiles.forEach((f) => fd.append('doc_files[]', f));
            fd.append('type', type);

            const data = await CreateApi.store(fd);
            if (!data.success) {
                let msg = data.message || t('mini.err_save');
                if (data.messages) msg += '\n' + Object.values(data.messages).flat().join('\n');
                throw new Error(msg);
            }

            haptic('success');
            invalidateMyDocs();
            setSuccess({ message: data.message || '', journalId: data.journal_id });
        } catch (e) {
            haptic('error');
            let msg = e.message || t('mini.err_save');
            if (e.data && e.data.messages) {
                msg += '\n' + Object.values(e.data.messages).flat().join('\n');
            }
            alert(msg);
        } finally {
            setSubmitting(false);
        }
    };

    /* ─────────── OnlyOffice handoff ─────────── */
    const openOnComputer = async () => {
        if (!fields.journal_type) { alert(t('mini.err_select_journal')); return; }
        haptic('light');
        try {
            const data = await CreateApi.webEditor({
                type,
                journal_type: fields.journal_type,
                journal_type2: fields.journal_type2 || '',
                doc_name: fields.doc_name,
                doc_number: fields.doc_number,
                doc_date: fields.doc_date,
                doc_text: fields.doc_text,
                doc_lang: fields.doc_lang || 'uz',
                confidence: fields.confidence,
                signing_term: fields.signing_term,
                resolution_user: fields.resolution_user,
                to_emp: fields.to_emp,
                to_agree: agreeIds,
                doc_organization: fields.doc_organization,
            });
            if (!data.success || !data.url) throw new Error(data.message || t('mini.err_sign_url'));
            openExternalLink(data.url);
        } catch (e) {
            haptic('error');
            alert(t('mini.error') + ': ' + e.message);
        }
    };

    /* ─────────── Успех: карточка ─────────── */
    const goMyDocs = () => {
        haptic('light');
        setTab('documents');
        enterCategory('my_docs');
        resetTab('documents', [
            { name: 'home', params: {} },
            { name: 'list', params: { category: 'my_docs' } },
        ]);
        resetTab('create');
    };

    const viewCreated = (jId) => {
        haptic('light');
        setTab('documents');
        enterCategory('my_docs');
        resetTab('documents', [
            { name: 'home', params: {} },
            { name: 'list', params: { category: 'my_docs' } },
            { name: 'detail', params: { kind: 'my', id: jId } },
        ]);
        resetTab('create');
    };

    if (success) {
        return (
            <div className="state active">
                <div className="wizard-stepper">
                    <div className="step-pill done" />
                    <div className="step-pill done" />
                    <div className="step-pill done" />
                </div>
                <div className="wizard-card" style={{ textAlign: 'center', padding: '32px 20px' }}>
                    <div style={{
                        width: 64, height: 64, borderRadius: '50%',
                        background: 'rgba(16,185,129,0.10)', color: '#10b981',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        margin: '0 auto 14px',
                    }}>
                        <Icon name="check" size={32} strokeWidth={2.5} />
                    </div>
                    <div className="wizard-title" style={{ textAlign: 'center' }}>{t('mini.created_title')}</div>
                    <div className="wizard-subtitle" style={{ textAlign: 'center' }}>{success.message}</div>
                    <div className="wizard-actions">
                        <button className="btn-w btn-w-secondary" onClick={goMyDocs}>{t('menu.my_docs')}</button>
                        <button className="btn-w btn-w-primary" onClick={() => viewCreated(success.journalId)}>{t('mini.view')}</button>
                    </div>
                </div>
            </div>
        );
    }

    const eu = execUsers || {};

    return (
        <div className="state active">
            <div className="wizard-stepper">
                <div className="step-pill done" />
                <div className="step-pill active" />
                <div className="step-pill" />
            </div>

            <div className="wizard-card">
                {init && !init.has_subscription && (
                    <div className="alert-warn">
                        <Icon name="info" strokeWidth={2} />
                        <span>{t('mini.no_subscription')}</span>
                    </div>
                )}

                <div className="wizard-title">{formTitle}</div>
                <div className="wizard-subtitle">{t('mini.form_subtitle')}</div>

                <Field label={t('mini.f_journal')} required>
                    <select className="field-select" value={fields.journal_type}
                            onChange={(e) => onJournalChange(e.target.value)}>
                        <option value="">{t('mini.select_placeholder')}</option>
                        {(init.journals || []).map((j) => (
                            <option key={j.id} value={j.id}>{j.title}</option>
                        ))}
                    </select>
                </Field>

                {journals2 && (
                    <Field label={t('mini.f_journal2')} required>
                        <select className="field-select" value={fields.journal_type2}
                                onChange={(e) => onJournal2Change(e.target.value)}>
                            <option value="">{t('mini.select_placeholder')}</option>
                            {journals2.map((c) => (
                                <option key={c.id} value={c.id}>{c.title}</option>
                            ))}
                        </select>
                    </Field>
                )}

                <Field label={t('mini.f_doc_date')}>
                    <input type="text" className="field-input" placeholder="DD.MM.YYYY"
                           value={fields.doc_date} onChange={(e) => setField('doc_date', e.target.value)} />
                </Field>

                <Field label={t('mini.f_doc_name')}>
                    <input type="text" className="field-input" placeholder={t('mini.f_doc_name_ph')} maxLength={500}
                           value={fields.doc_name} onChange={(e) => setField('doc_name', e.target.value)} />
                </Field>

                <Field label={t('mini.f_doc_number')}>
                    <input type="text" className="field-input" placeholder="123/45" maxLength={100}
                           value={fields.doc_number} onChange={(e) => setField('doc_number', e.target.value)} />
                </Field>

                <Field label={t('mini.f_confidence')}>
                    <select className="field-select" value={fields.confidence}
                            onChange={(e) => setField('confidence', e.target.value)}>
                        <option value="">{t('mini.select_placeholder')}</option>
                        {(init.confidences || []).map((c) => (
                            <option key={c.id} value={c.id}>{c.title}</option>
                        ))}
                    </select>
                </Field>

                <Field label={t('mini.f_lang')}>
                    <select className="field-select" value={fields.doc_lang}
                            onChange={(e) => setField('doc_lang', e.target.value)}>
                        {(init.languages || []).map((l) => (
                            <option key={l.code} value={l.code}>{l.label}</option>
                        ))}
                    </select>
                </Field>

                <Field label={t('mini.f_signing_term')}>
                    <input type="text" className="field-input" placeholder={t('mini.f_signing_term_ph')}
                           value={fields.signing_term} onChange={(e) => setField('signing_term', e.target.value)} />
                </Field>

                <Field label={t('mini.f_doc_text')}>
                    <textarea className="field-textarea" placeholder={t('mini.f_doc_text_ph')}
                              value={fields.doc_text} onChange={(e) => setField('doc_text', e.target.value)} />
                </Field>

                {/* Основной файл */}
                <Field label={t('mini.f_main_file')}>
                    <div className={'file-picker' + (mainFile ? ' has-file' : '')}
                         onClick={() => mainFileRef.current?.click()}>
                        <Icon name="upload" strokeWidth={2} />
                        <div className="file-picker-text">{mainFile ? mainFile.name : t('mini.f_select_file')}</div>
                        <div className="file-picker-hint">{t('mini.f_file_hint')}</div>
                    </div>
                    <input ref={mainFileRef} type="file" accept=".doc,.docx"
                           style={{ display: 'none' }} onChange={onMainFileChange} />
                </Field>

                {/* Дополнительные файлы */}
                <Field label={t('mini.f_extra_files')}>
                    <div className="file-picker" onClick={() => docFilesRef.current?.click()}>
                        <Icon name="paperclip" strokeWidth={2} />
                        <div className="file-picker-text">{t('mini.f_add_file')}</div>
                        <div className="file-picker-hint">{t('mini.f_files_hint')}</div>
                    </div>
                    <input ref={docFilesRef} type="file" multiple
                           style={{ display: 'none' }} onChange={onDocFilesChange} />
                    <ChipList
                        items={docFiles.map((f, i) => ({ id: i, name: f.name }))}
                        onRemove={(id) => setDocFiles((prev) => prev.filter((_, i) => i !== id))}
                    />
                </Field>

                {/* Участники маршрута */}
                {execUsers && (
                    <>
                        {(eu.resolution_users || []).length > 0 ? (
                            <Field label={t('mini.f_resol_user')} required>
                                <UserSelect users={eu.resolution_users} value={fields.resolution_user}
                                            onChange={(v) => setField('resolution_user', v)} />
                            </Field>
                        ) : (
                            <div className="alert-warn">
                                <Icon name="info" strokeWidth={2} />
                                <span>{t('mini.no_resol_users')}</span>
                            </div>
                        )}

                        {(eu.signatory_users || []).length > 0 && (
                            <Field label={t('mini.f_signatory')}>
                                <UserSelect users={eu.signatory_users} value={fields.to_emp}
                                            onChange={(v) => setField('to_emp', v)} />
                            </Field>
                        )}

                        {(eu.agreement_users || []).length > 0 && (
                            <Field label={t('mini.f_agreement')}>
                                <select
                                    className="field-select"
                                    value=""
                                    onChange={(e) => {
                                        const val = e.target.value;
                                        if (val && agreeIds.indexOf(val) === -1) {
                                            setAgreeIds((prev) => [...prev, val]);
                                        }
                                    }}
                                >
                                    <option value="">{t('mini.f_agreement_add')}</option>
                                    {eu.agreement_users.map((u) => (
                                        <option key={u.id} value={u.id}>
                                            {u.name}{u.position ? ' (' + u.position + ')' : ''}
                                        </option>
                                    ))}
                                </select>
                                <ChipList
                                    items={agreeIds.map((id) => {
                                        const u = (eu.agreement_users || []).find((x) => String(x.id) === String(id));
                                        return {
                                            id,
                                            name: u ? u.name + (u.position ? ' (' + u.position + ')' : '') : id,
                                        };
                                    })}
                                    onRemove={(id) => setAgreeIds((prev) => prev.filter((x) => x !== id))}
                                />
                            </Field>
                        )}
                    </>
                )}

                {/* Внешняя организация */}
                {orgs && orgs.length > 0 && (
                    <Field label={type === 'incoming' ? t('mini.f_org_from') : t('mini.f_org_to')}>
                        <select className="field-select" value={fields.doc_organization}
                                onChange={(e) => setField('doc_organization', e.target.value)}>
                            <option value="">{t('mini.select_placeholder')}</option>
                            {orgs.map((o) => (
                                <option key={o.id} value={o.id}>{o.title}</option>
                            ))}
                        </select>
                    </Field>
                )}

                <div className="wizard-actions">
                    <button type="button" className="btn-w btn-w-secondary"
                            onClick={() => { haptic('light'); pop(); }}>
                        <Icon name="arrow-left" size={14} strokeWidth={2.4} />
                        {t('action.back')}
                    </button>
                    <button type="button" className="btn-w btn-w-primary"
                            disabled={submitting || !init.has_subscription} onClick={submit}>
                        <Icon name="check" size={14} strokeWidth={2.4} />
                        {submitting ? t('mini.saving') : t('action.save')}
                    </button>
                </div>

                <div className="web-editor-card">
                    <div className="web-editor-card-title">{t('mini.web_editor_title')}</div>
                    <div className="web-editor-card-sub">{t('mini.web_editor_sub')}</div>
                    <button type="button" className="btn-w btn-w-secondary" style={{ width: '100%' }}
                            onClick={openOnComputer}>
                        <Icon name="monitor-stand" size={14} strokeWidth={2.4} />
                        {t('mini.open_on_computer')}
                    </button>
                </div>
            </div>
        </div>
    );
}

function Field({ label, required, children }) {
    return (
        <div className="field-group">
            <label className="field-label">
                {label}{required ? <span className="required"> *</span> : null}
            </label>
            {children}
        </div>
    );
}

function UserSelect({ users, value, onChange }) {
    return (
        <select className="field-select" value={value} onChange={(e) => onChange(e.target.value)}>
            <option value="">{t('mini.select_placeholder')}</option>
            {users.map((u) => (
                <option key={u.id} value={u.id}>
                    {u.name}{u.position ? ' (' + u.position + ')' : ''}
                </option>
            ))}
        </select>
    );
}

function ChipList({ items, onRemove }) {
    if (!items.length) return null;
    return (
        <div className="selected-list">
            {items.map((item) => (
                <div key={item.id} className="selected-chip">
                    <span className="selected-chip-name">{item.name}</span>
                    <button type="button" className="selected-chip-remove" onClick={() => onRemove(item.id)}>
                        <Icon name="x" strokeWidth={2.4} />
                    </button>
                </div>
            ))}
        </div>
    );
}
