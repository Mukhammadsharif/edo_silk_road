# EDOLINE Telegram Mini App (React)

Замена монолитного `public/telegram-app/index.html` (4200+ строк HTML/JS) на React-проект. Дизайн, API и поведение сохранены 1-в-1, CSS перенесён без изменений.

## Структура

```
telegram-app/                  ← исходники (эта папка, лежит в корне Laravel)
├── index.html                 ← входная точка Vite (Telegram SDK + фолбэк)
├── vite.config.js             ← сборка в ../public/telegram-app
└── src/
    ├── config.js              ← BASE_URL API
    ├── App.jsx                ← дерево провайдеров
    ├── app/
    │   ├── AppShell.jsx       ← шапка, нижняя навигация, роутер экранов
    │   └── providers/         ← I18n, Theme, Auth, Navigation
    ├── shared/
    │   ├── telegram.js        ← обёртка Telegram WebApp SDK
    │   └── api/
    │       ├── client.js      ← fetch с Bearer-токеном
    │       └── endpoints.js   ← ВСЯ API-поверхность по доменам
    ├── ui/                    ← Icon, Loading/Error, ConfirmModal, FilterPills
    ├── features/
    │   ├── home/              ← главная (4 карточки)
    │   ├── documents/         ← стор, списки, детали (sign + my)
    │   ├── profile/           ← профиль
    │   └── create/            ← мастер создания
    └── styles/app.css         ← CSS из старой версии (классы 1-в-1)
```

## Сборка и деплой

```bash
cd telegram-app
npm install        # один раз
npm run build      # соберёт прямо в ../public/telegram-app
```

URL не меняется: `https://edoline.uz/telegram-app/`.
На сервер выкладывается только содержимое `public/telegram-app/` — Node.js на сервере не нужен.

Локальная разработка: `npm run dev` (создайте `.env` с `VITE_API_BASE_URL=...` при необходимости).

## Как добавить новую фичу

1. Эндпоинты — новый блок в `src/shared/api/endpoints.js`.
2. Каталог `src/features/<название>/` с экраном.
3. Имя экрана в `ScreenRouter` (`src/app/AppShell.jsx`) — и открывайте его через `push('имя', params)` из `useNavigation()`.
4. Если нужна вкладка в нижней навигации — `ROOT_SCREENS` в `NavigationProvider.jsx` + кнопка в `BottomNav`.

## Что улучшено против старой версии

- **Telegram BackButton**: аппаратная «назад» теперь работает (стек экранов в `NavigationProvider`).
- **Смена языка** перерисовывает интерфейс через React-state — без ручного `applyI18n()` по DOM.
- **Исправлен CSS-баг оригинала**: блок переменных `--brand-1`, `--r-md`, `--spring` и др. был вне селектора и игнорировался браузером — обёрнут в `:root` (см. комментарий в `app.css`).
- Защита от гонок при быстрых переключениях категорий — через порядковый номер запроса в сторе.
