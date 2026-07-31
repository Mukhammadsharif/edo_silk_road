import { I18nProvider } from './app/providers/I18nProvider';
import { ThemeProvider } from './app/providers/ThemeProvider';
import { AuthProvider } from './app/providers/AuthProvider';
import { NavigationProvider } from './app/providers/NavigationProvider';
import { DocumentsStoreProvider } from './features/documents/DocumentsStore';
import AppShell from './app/AppShell';

/**
 * Дерево провайдеров (порядок важен):
 *   I18n   — словарь нужен всем, включая экраны авторизации
 *   Theme  — тема применяется до первого рендера контента
 *   Auth   — токен для API
 *   Navigation — вкладки и стек экранов (+ Telegram BackButton)
 *   DocumentsStore — данные вкладки «Документы»
 */
export default function App() {
    return (
        <I18nProvider>
            <ThemeProvider>
                <AuthProvider>
                    <NavigationProvider>
                        <DocumentsStoreProvider>
                            <AppShell />
                        </DocumentsStoreProvider>
                    </NavigationProvider>
                </AuthProvider>
            </ThemeProvider>
        </I18nProvider>
    );
}
