#!/usr/bin/env bash
# Idempotent local WordPress + Walkridge theme bootstrap (SQLite, no MySQL).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WP_PATH="${WP_PATH:-$HOME/wp}"
THEME_SLUG="walkridge"
PHP_BIN="${PHP_BIN:-php}"
PORT="${PORT:-8080}"

need() {
  command -v "$1" >/dev/null 2>&1 || {
    echo "Missing required command: $1" >&2
    exit 1
  }
}

need "$PHP_BIN"
need composer
need npm
need wp

cd "$ROOT"
composer install --no-interaction
npm install
npm run build

mkdir -p "$WP_PATH"
if [[ ! -f "$WP_PATH/wp-load.php" ]]; then
  wp core download --path="$WP_PATH" --allow-root
fi

# SQLite drop-in (no MySQL). Safe to re-run.
if [[ ! -d "$WP_PATH/wp-content/plugins/sqlite-database-integration" ]]; then
  wp plugin install sqlite-database-integration --activate --path="$WP_PATH" --allow-root || true
fi

if [[ ! -f "$WP_PATH/wp-config.php" ]]; then
  wp config create \
    --dbname=walkridge \
    --dbuser=root \
    --dbpass="" \
    --skip-check \
    --path="$WP_PATH" \
    --allow-root
  # Prefer SQLite when the integration plugin is present.
  if [[ -f "$WP_PATH/wp-content/plugins/sqlite-database-integration/db.copy" ]]; then
    cp "$WP_PATH/wp-content/plugins/sqlite-database-integration/db.copy" "$WP_PATH/wp-content/db.php"
  fi
fi

if ! wp core is-installed --path="$WP_PATH" --allow-root 2>/dev/null; then
  wp core install \
    --url="http://127.0.0.1:${PORT}" \
    --title="Walkridge Battlefield Tours" \
    --admin_user=admin \
    --admin_password=admin123 \
    --admin_email=admin@walkridge.test \
    --skip-email \
    --path="$WP_PATH" \
    --allow-root
fi

THEME_LINK="$WP_PATH/wp-content/themes/${THEME_SLUG}"
rm -rf "$THEME_LINK"
ln -sfn "$ROOT" "$THEME_LINK"

wp theme activate "$THEME_SLUG" --path="$WP_PATH" --allow-root || true

if ! wp plugin is-installed woocommerce --path="$WP_PATH" --allow-root; then
  wp plugin install woocommerce --activate --path="$WP_PATH" --allow-root
else
  wp plugin activate woocommerce --path="$WP_PATH" --allow-root || true
fi
wp option update woocommerce_coming_soon no --path="$WP_PATH" --allow-root || true

echo
echo "Theme linked at $THEME_LINK"
echo "Start WordPress:"
echo "  wp server --path=\"$WP_PATH\" --host=0.0.0.0 --port=${PORT} --allow-root"
echo "Admin: http://127.0.0.1:${PORT}/wp-admin  (admin / admin123)"
echo "Static HTML preview (no PHP): bin/preview-static.sh"
echo "After Blade edits: wp acorn view:clear --path=\"$WP_PATH\" --allow-root"
