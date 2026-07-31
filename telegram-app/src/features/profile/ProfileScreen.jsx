import { useEffect, useRef, useState } from 'react';
import { useI18n, LANG_OPTIONS } from '../../app/providers/I18nProvider';
import { useTheme } from '../../app/providers/ThemeProvider';
import { ProfileApi } from '../../shared/api/endpoints';
import { LoadingScreen, ErrorScreen, ConfirmModal } from '../../ui/common';
import Icon from '../../ui/Icon';
import { haptic } from '../../shared/telegram';

/**
 * Профиль сотрудника: аватар (загрузка/удаление), основная информация,
 * смена пароля, роли, кадровые приказы, стаж, язык и тема.
 */
export default function ProfileScreen() {
    const { t, lang, setLang } = useI18n();
    const { pref, setPref } = useTheme();

    const [profile, setProfile] = useState(null);
    const [status, setStatus] = useState('loading');
    const [errorMsg, setErrorMsg] = useState('');
    const [confirmAvatarDelete, setConfirmAvatarDelete] = useState(false);

    const [pwCurrent, setPwCurrent] = useState('');
    const [pwNew, setPwNew] = useState('');
    const [pwConfirm, setPwConfirm] = useState('');
    const [pwBusy, setPwBusy] = useState(false);

    const avatarInputRef = useRef(null);

    const load = () => {
        setStatus('loading');
        ProfileApi.get()
            .then((data) => { setProfile(data.profile); setStatus('ready'); })
            .catch((e) => {
                haptic('error');
                setErrorMsg(e.message || t('mini.err_profile_load'));
                setStatus('error');
            });
    };

    useEffect(load, []); // eslint-disable-line react-hooks/exhaustive-deps

    /* ─────────── Аватар ─────────── */
    const onAvatarChange = async (e) => {
        const input = e.target;
        if (!input.files || !input.files.length) return;
        const file = input.files[0];

        if (file.size > 5 * 1024 * 1024) {
            alert(t('mini.err_avatar_too_big'));
            input.value = '';
            return;
        }

        haptic('light');
        try {
            const data = await ProfileApi.uploadAvatar(file);
            if (!data.success) throw new Error(data.message || t('mini.err_upload'));
            haptic('success');
            setProfile((p) => ({ ...p, avatar_url: data.avatar_url }));
        } catch (err) {
            haptic('error');
            alert(t('mini.error') + ': ' + err.message);
        } finally {
            input.value = '';
        }
    };

    const doDeleteAvatar = async () => {
        setConfirmAvatarDelete(false);
        haptic('light');
        try {
            const data = await ProfileApi.deleteAvatar();
            if (!data.success) throw new Error(data.message || t('mini.err_save'));
            haptic('success');
            setProfile((p) => ({ ...p, avatar_url: null }));
        } catch (err) {
            haptic('error');
            alert(t('mini.error') + ': ' + err.message);
        }
    };

    /* ─────────── Пароль ─────────── */
    const changePassword = async () => {
        if (!pwCurrent || !pwNew || !pwConfirm) { alert(t('mini.err_fill_all')); return; }
        if (pwNew.length < 6) { alert(t('mini.err_pw_short')); return; }
        if (pwNew !== pwConfirm) { alert(t('mini.err_pw_mismatch')); return; }

        haptic('light');
        setPwBusy(true);
        try {
            const data = await ProfileApi.changePassword(pwCurrent, pwNew, pwConfirm);
            if (!data.success) throw new Error(data.message || t('mini.err_save'));
            haptic('success');
            alert(data.message || t('mini.pw_changed'));
            setPwCurrent(''); setPwNew(''); setPwConfirm('');
        } catch (err) {
            haptic('error');
            alert(t('mini.error') + ': ' + err.message);
        } finally {
            setPwBusy(false);
        }
    };

    if (status === 'loading') return <LoadingScreen />;
    if (status === 'error') return <ErrorScreen message={errorMsg} onRetry={load} />;

    const p = profile;
    const orgName = p.organization ? p.organization.name_uz : null;
    const nameSrc = p.full_name || p.username || '?';
    const initial = nameSrc.trim().charAt(0).toUpperCase();

    return (
        <div className="state active">
            {/* Карточка с аватаром */}
            <div className="profile-card">
                <div className="profile-avatar-wrap">
                    <div className="profile-avatar">
                        {p.avatar_url ? <img src={p.avatar_url} alt="avatar" /> : initial}
                    </div>
                </div>
                <div className="profile-name">{p.full_name || p.username || '—'}</div>
                <div className="profile-username">@{p.username || ''}</div>
            </div>

            {/* Основные данные */}
            <Section icon="info" title={t('mini.main_info')}>
                <InfoField label={t('mini.fish')} value={p.full_name} />
                <InfoField label={t('mini.org')} value={orgName} />
                <InfoField label={t('mini.department')} value={p.department} />
                <InfoField label={t('mini.position')} value={p.position} />
            </Section>

            {/* Настройки аккаунта */}
            <Section icon="lock-alt" title={t('mini.account_settings')}>
                <InfoField label={t('mini.email')} value={p.email} />
                <InfoField label={t('mini.login')} value={p.username} />
                <InfoField label={t('mini.phone')} value={p.phone} />

                <div style={{ marginTop: 14 }}>
                    <label className="form-field-mini" style={{ marginBottom: 6 }}>{t('mini.profile_photo')}</label>
                    <input
                        ref={avatarInputRef}
                        type="file"
                        accept="image/png,image/jpeg,image/jpg"
                        style={{ display: 'none' }}
                        onChange={onAvatarChange}
                    />
                    <div className="avatar-upload-row">
                        <button className="btn-avatar-action" onClick={() => avatarInputRef.current?.click()}>
                            <Icon name="upload" strokeWidth={2.2} />
                            <span>{t('mini.upload')}</span>
                        </button>
                        <button
                            className="btn-avatar-action danger"
                            onClick={() => { if (p.avatar_url) setConfirmAvatarDelete(true); }}
                        >
                            <Icon name="trash-simple" strokeWidth={2.2} />
                            <span>{t('mini.delete')}</span>
                        </button>
                    </div>
                </div>

                <div style={{ marginTop: 14 }}>
                    <div className="form-field-mini">
                        <label>{t('mini.current_password')}</label>
                        <input type="password" autoComplete="current-password"
                               value={pwCurrent} onChange={(e) => setPwCurrent(e.target.value)} />
                    </div>
                    <div className="form-field-mini">
                        <label>{t('mini.new_password')}</label>
                        <input type="password" autoComplete="new-password"
                               value={pwNew} onChange={(e) => setPwNew(e.target.value)} />
                    </div>
                    <div className="form-field-mini">
                        <label>{t('mini.confirm_password')}</label>
                        <input type="password" autoComplete="new-password"
                               value={pwConfirm} onChange={(e) => setPwConfirm(e.target.value)} />
                    </div>
                    <button className="btn-w btn-w-primary" style={{ width: '100%', padding: 11 }}
                            disabled={pwBusy} onClick={changePassword}>
                        <Icon name="check" size={14} strokeWidth={2.4} />
                        <span>{t('action.save')}</span>
                    </button>
                </div>
            </Section>

            {/* Роли */}
            <Section icon="shield" title={t('mini.my_roles')}>
                {Array.isArray(p.roles) && p.roles.length > 0 ? (
                    <div className="profile-roles">
                        {p.roles.map((r, i) => <span key={i} className="profile-role-chip">{r}</span>)}
                    </div>
                ) : (
                    <div className="table-empty">{t('mini.no_roles')}</div>
                )}
            </Section>

            {/* Кадровые приказы */}
            <Section icon="file-text" title={t('mini.hr_orders')}>
                <ProfileTable
                    items={p.hr_orders || []}
                    columns={[
                        { key: 'ord_code',  label: t('mini.col_type') },
                        { key: 'ord_name',  label: t('mini.col_order_name') },
                        { key: 'ord_num',   label: t('mini.col_number') },
                        { key: 'from_date', label: t('mini.col_date_from') },
                        { key: 'to_date',   label: t('mini.col_date_to') },
                    ]}
                    emptyText={t('field.not_found')}
                />
            </Section>

            {/* Стаж */}
            <Section icon="briefcase" title={t('mini.work_experience')}>
                <ProfileTable
                    items={p.experiences || []}
                    columns={[
                        { key: 'exp_name',  label: t('mini.col_work_place') },
                        { key: 'post_name', label: t('mini.col_position') },
                        { key: 'exp_descr', label: t('mini.col_position_desc') },
                        { key: 'from_date', label: t('mini.col_date_from') },
                        { key: 'to_date',   label: t('mini.col_date_to') },
                    ]}
                    emptyText={t('field.not_found')}
                />
            </Section>

            {/* Язык */}
            <Section icon="globe" title={t('mini.language')}>
                <div className="theme-toggle-row" style={{ flexWrap: 'wrap' }}>
                    {LANG_OPTIONS.map((opt) => (
                        <button
                            key={opt.code}
                            className={'theme-toggle-btn' + (lang === opt.code ? ' active' : '')}
                            style={{ flex: '1 1 45%' }}
                            onClick={() => setLang(opt.code)}
                        >
                            {opt.label}
                        </button>
                    ))}
                </div>
            </Section>

            {/* Тема */}
            <Section icon="sun" title={t('mini.theme')}>
                <div className="theme-toggle-row">
                    <ThemeButton mode="auto" icon="monitor" label={t('mini.theme_auto')} pref={pref} setPref={setPref} />
                    <ThemeButton mode="light" icon="sun-small" label={t('mini.theme_light')} pref={pref} setPref={setPref} />
                    <ThemeButton mode="dark" icon="moon" label={t('mini.theme_dark')} pref={pref} setPref={setPref} />
                </div>
            </Section>

            <ConfirmModal
                open={confirmAvatarDelete}
                title={t('mini.delete')}
                text={t('mini.delete_avatar_text')}
                okLabel={t('mini.delete')}
                onOk={doDeleteAvatar}
                onCancel={() => setConfirmAvatarDelete(false)}
            />
        </div>
    );
}

function Section({ icon, title, children }) {
    return (
        <div className="profile-section">
            <div className="profile-section-title">
                <Icon name={icon} strokeWidth={2.2} />
                <span>{title}</span>
            </div>
            {children}
        </div>
    );
}

function InfoField({ label, value }) {
    return (
        <div className="form-field-mini" style={{ opacity: value ? 1 : 0.6 }}>
            <label>{label}</label>
            <div style={{
                padding: '10px 12px', border: '1px solid var(--border-soft)',
                borderRadius: 9, fontSize: 13, color: 'var(--text)', background: 'var(--input-bg)',
            }}>
                {value || '—'}
            </div>
        </div>
    );
}

function ProfileTable({ items, columns, emptyText }) {
    if (!items || items.length === 0) {
        return <div className="table-empty">{emptyText}</div>;
    }
    return (
        <div style={{ overflowX: 'auto' }}>
            <table className="profile-table">
                <thead>
                <tr>{columns.map((c) => <th key={c.key}>{c.label}</th>)}</tr>
                </thead>
                <tbody>
                {items.map((item, i) => (
                    <tr key={i}>
                        {columns.map((c) => <td key={c.key}>{item[c.key] || '—'}</td>)}
                    </tr>
                ))}
                </tbody>
            </table>
        </div>
    );
}

function ThemeButton({ mode, icon, label, pref, setPref }) {
    return (
        <button
            className={'theme-toggle-btn' + (pref === mode ? ' active' : '')}
            onClick={() => setPref(mode)}
        >
            <Icon name={icon} strokeWidth={2.4} />
            <span>{label}</span>
        </button>
    );
}
