import { useAuth } from './providers/AuthProvider';
import { useI18n } from './providers/I18nProvider';
import { useNavigation } from './providers/NavigationProvider';
import { LoadingScreen, ErrorScreen } from '../ui/common';
import Icon from '../ui/Icon';
import { CATEGORIES } from '../features/documents/constants';

import HomeScreen from '../features/home/HomeScreen';
import DocumentsListScreen from '../features/documents/DocumentsListScreen';
import SignDocDetailScreen from '../features/documents/SignDocDetailScreen';
import MyDocDetailScreen from '../features/documents/MyDocDetailScreen';
import ProfileScreen from '../features/profile/ProfileScreen';
import CreateTypeScreen from '../features/create/CreateTypeScreen';
import CreateWizardScreen from '../features/create/CreateWizardScreen';

/**
 * Роутер экранов. Новый экран = запись здесь + push() из любого места.
 */
function ScreenRouter() {
    const { screen } = useNavigation();

    switch (screen.name) {
        case 'home':          return <HomeScreen />;
        case 'list':          return <DocumentsListScreen category={screen.params.category} />;
        case 'detail':
            return screen.params.kind === 'my'
                ? <MyDocDetailScreen jId={screen.params.id} key={'my-' + screen.params.id} />
                : <SignDocDetailScreen docId={screen.params.id} key={'sign-' + screen.params.id} />;
        case 'profile':       return <ProfileScreen />;
        case 'create-type':   return <CreateTypeScreen />;
        case 'create-wizard': return <CreateWizardScreen />;
        default:              return <HomeScreen />;
    }
}

/** Подзаголовок шапки в зависимости от текущего экрана. */
function useHeaderSub() {
    const { t } = useI18n();
    const { screen, tab } = useNavigation();

    if (tab === 'profile') return t('mini.employee_info');
    if (tab === 'create') return t('mini.create');

    if (screen.name === 'list' && screen.params.category) {
        return t(CATEGORIES[screen.params.category].titleKey);
    }
    return t('menu.home');
}

function Header() {
    const sub = useHeaderSub();
    return (
        <div className="header">
            <div className="header-row">
                <div className="header-logo">
                    <div className="logo-icon"><Icon name="file" strokeWidth={2.4} /></div>
                    <span>EdoLine</span>
                </div>
            </div>
            <div className="header-sub">{sub}</div>
        </div>
    );
}

function BottomNav() {
    const { t } = useI18n();
    const { tab, setTab } = useNavigation();

    return (
        <nav className="bottom-nav">
            <button
                className={'nav-tab' + (tab === 'documents' ? ' active' : '')}
                onClick={() => setTab('documents')}
            >
                <Icon name="file" strokeWidth={2} />
                <span>{t('mini.documents')}</span>
            </button>
            <button
                className={'nav-tab' + (tab === 'create' ? ' active' : '')}
                onClick={() => setTab('create')}
            >
                <Icon name="plus" strokeWidth={2} />
                <span>{t('mini.create')}</span>
            </button>
            <button
                className={'nav-tab' + (tab === 'profile' ? ' active' : '')}
                onClick={() => setTab('profile')}
            >
                <Icon name="user" strokeWidth={2} />
                <span>{t('mini.profile')}</span>
            </button>
        </nav>
    );
}

function NotLinkedScreen() {
    const { t } = useI18n();
    return (
        <div className="state active">
            <div className="empty">
                <div className="empty-icon-wrap brand"><Icon name="link" strokeWidth={2} /></div>
                <div className="empty-text">{t('mini.not_linked_title')}</div>
                <div className="empty-sub">{t('mini.not_linked_sub')}</div>
            </div>
        </div>
    );
}

export default function AppShell() {
    const { status, errorMessage, retry } = useAuth();

    return (
        <>
            <div className="bg-blobs" aria-hidden="true">
                <div className="blob blob-1" />
                <div className="blob blob-2" />
                <div className="blob blob-3" />
            </div>

            <Header />

            {status === 'loading' && <LoadingScreen />}
            {status === 'error' && <ErrorScreen message={errorMessage} onRetry={retry} />}
            {status === 'not-linked' && <NotLinkedScreen />}
            {status === 'ready' && <ScreenRouter />}

            {status === 'ready' && <BottomNav />}
        </>
    );
}
