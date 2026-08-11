#!/usr/bin/env bash
#
# Атомарный деплой React Telegram Mini App (EDOLINE) в public/telegram-app-v2.
# Сервер БЕЗ Docker: системные nginx + php-fpm, владелец файлов ubuntu:www-data.
#
# Собирает во временный каталог и подменяет боевой одним mv — пользователь
# никогда не видит полусобранную папку. Предыдущая сборка сохраняется рядом
# для мгновенного отката.
#
# Старый HTML/CSS-апп в public/telegram-app НЕ ЗАТРАГИВАЕТСЯ.
#
# Запуск:
#   cd /var/www/edo && ./deploy-telegram-app.sh
#
# Если проект в другом каталоге:
#   PROJECT_DIR=/srv/edoline ./deploy-telegram-app.sh
#
set -euo pipefail

# Путь проекта: из переменной, иначе — каталог, где лежит сам скрипт.
PROJECT_DIR="${PROJECT_DIR:-$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)}"

SRC_DIR="$PROJECT_DIR/telegram-app"
PUBLIC_DIR="$PROJECT_DIR/public"

TARGET="$PUBLIC_DIR/telegram-app-v2"
STAGING="$PUBLIC_DIR/.telegram-app-v2.new"
PREVIOUS="$PUBLIC_DIR/.telegram-app-v2.prev"

LEGACY="$PUBLIC_DIR/telegram-app"          # старый апп — только читаем
OWNER="${OWNER:-ubuntu:www-data}"

red()  { printf '\033[31m%s\033[0m\n' "$*"; }
grn()  { printf '\033[32m%s\033[0m\n' "$*"; }
info() { printf '\033[36m→ %s\033[0m\n' "$*"; }

# sudo используем только если скрипт запущен не от root.
SUDO=''
[ "$(id -u)" -ne 0 ] && SUDO='sudo'

# ── Проверки окружения ────────────────────────────────────────────────
[ -d "$SRC_DIR" ]    || { red "Нет каталога исходников: $SRC_DIR"; exit 1; }
[ -d "$PUBLIC_DIR" ] || { red "Нет каталога public: $PUBLIC_DIR"; exit 1; }

command -v node >/dev/null || {
    red "Node.js не установлен. Поставьте v18+:"
    red "  curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash - && sudo apt-get install -y nodejs"
    exit 1
}
NODE_MAJOR=$(node -p 'process.versions.node.split(".")[0]')
[ "$NODE_MAJOR" -ge 18 ] || { red "Node.js v$NODE_MAJOR — Vite 5 требует v18+"; exit 1; }

# Страховка: конфиг не должен собирать в каталог старого аппа.
if grep -qE "outDir:[[:space:]]*'\.\./public/telegram-app'" "$SRC_DIR/vite.config.js"; then
    red "vite.config.js всё ещё собирает в public/telegram-app — это затрёт старый апп."
    red "Поменяйте base на '/telegram-app-v2/' и outDir на '../public/telegram-app-v2'."
    exit 1
fi

# ── Разовый бэкап старого аппа ────────────────────────────────────────
if [ -d "$LEGACY" ] && ! compgen -G "$PUBLIC_DIR/telegram-app.bak-*" >/dev/null; then
    BAK="$PUBLIC_DIR/telegram-app.bak-$(date +%Y%m%d-%H%M)"
    info "Разовый бэкап старого аппа → $(basename "$BAK")"
    cp -a "$LEGACY" "$BAK"
fi

# ── Сборка ────────────────────────────────────────────────────────────
cd "$SRC_DIR"

if [ -f package-lock.json ]; then
    info "npm ci"
    npm ci --no-audit --no-fund
else
    info "npm install"
    npm install --no-audit --no-fund
fi

rm -rf "$STAGING"
info "vite build → $(basename "$STAGING")"
npx vite build --outDir "$STAGING" --emptyOutDir

# ── Валидация сборки ──────────────────────────────────────────────────
[ -f "$STAGING/index.html" ] || { red "В сборке нет index.html"; rm -rf "$STAGING"; exit 1; }

if ! grep -q '/telegram-app-v2/assets/' "$STAGING/index.html"; then
    red "index.html не ссылается на /telegram-app-v2/assets/ — неверный base в vite.config.js"
    rm -rf "$STAGING"
    exit 1
fi

# ── Атомарная подмена ─────────────────────────────────────────────────
info "Подмена боевого каталога"
rm -rf "$PREVIOUS"
[ -d "$TARGET" ] && mv "$TARGET" "$PREVIOUS"
mv "$STAGING" "$TARGET"

$SUDO chown -R "$OWNER" "$TARGET"
$SUDO chmod -R ug+rX "$TARGET"

# ── Диагностика прав: дойдёт ли nginx до файла ────────────────────────
# Частая причина 403 — отсутствие x на родительском каталоге, а не на самом
# файле. namei показывает всю цепочку прав разом.
info "Права по цепочке каталогов:"
namei -l "$TARGET/index.html" | sed 's/^/    /'

# ── Итог ──────────────────────────────────────────────────────────────
grn "Готово. Перезагружать nginx не нужно — статика читается с диска."
echo
echo "  Новый:  https://edoline.uz/telegram-app-v2/"
echo "  Старый: https://edoline.uz/telegram-app/   (не тронут)"
echo
echo "  Откат:  rm -rf $TARGET && mv $PREVIOUS $TARGET"
echo
echo "Проверьте:"
echo "  curl -I https://edoline.uz/telegram-app-v2/"
echo "  curl -I https://edoline.uz/telegram-app/"
