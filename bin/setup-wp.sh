#!/usr/bin/env bash
#
# Bootstrap a local WordPress dev environment for the Walkridge theme (folder: walkridge).
#
# Same flow as Acreline: throwaway WordPress (SQLite, no MySQL) OUTSIDE the repo,
# symlink this theme in, activate it, build Sage assets.
#
# Idempotent: re-running skips work that is already done.
#
# Usage:
#   bin/setup-wp.sh
#   WP_PATH=/custom/path SITE_URL=http://localhost:8082 bin/setup-wp.sh
#
set -euo pipefail

THEME_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if ! command -v php >/dev/null 2>&1 || ! command -v composer >/dev/null 2>&1 || ! command -v wp >/dev/null 2>&1; then
  "$THEME_DIR/bin/install-php-tools.sh"
fi

WP_PATH="${WP_PATH:-$HOME/wp}"
SITE_URL="${SITE_URL:-http://localhost:8080}"
THEME_SLUG="walkridge"
WP="wp --path=$WP_PATH --allow-root"

need() {
  command -v "$1" >/dev/null 2>&1 || {
    echo "Missing required command: $1" >&2
    exit 1
  }
}

need php
need composer
need npm
need wp

echo "==> Theme dir : $THEME_DIR"
echo "==> WP path   : $WP_PATH"
echo "==> Site URL  : $SITE_URL"

echo "==> Installing theme dependencies (composer + npm) and building assets"
if [ -L "$THEME_DIR/vendor" ]; then
  echo "==> vendor/ is a symlink — removing so Composer autoloads App\\ from this worktree"
  rm "$THEME_DIR/vendor"
fi
( cd "$THEME_DIR" && composer install --no-interaction --no-progress )
( cd "$THEME_DIR" && npm install && npm run build )

if [ ! -f "$WP_PATH/wp-load.php" ]; then
  echo "==> Downloading WordPress core"
  mkdir -p "$WP_PATH"
  $WP core download
else
  echo "==> WordPress core already present"
fi

if [ ! -f "$WP_PATH/wp-config.php" ]; then
  echo "==> Creating wp-config.php"
  $WP config create --dbname=wp --dbuser=root --dbpass=root --dbhost=localhost --skip-check
  $WP config set WP_DEBUG true --raw --type=constant
fi

if [ ! -d "$WP_PATH/wp-content/plugins/sqlite-database-integration" ]; then
  echo "==> Installing SQLite Database Integration plugin"
  curl -sL -o /tmp/sqlite.zip https://downloads.wordpress.org/plugin/sqlite-database-integration.latest-stable.zip
  unzip -q -o /tmp/sqlite.zip -d "$WP_PATH/wp-content/plugins"
  rm -f /tmp/sqlite.zip
fi

if [ ! -f "$WP_PATH/wp-content/db.php" ]; then
  echo "==> Installing SQLite db.php drop-in"
  cp "$WP_PATH/wp-content/plugins/sqlite-database-integration/db.copy" "$WP_PATH/wp-content/db.php"
  sed -i "s|{SQLITE_IMPLEMENTATION_FOLDER_PATH}|/wp-content/plugins/sqlite-database-integration|g" "$WP_PATH/wp-content/db.php"
  sed -i "s|{SQLITE_PLUGIN}|sqlite-database-integration/load.php|g" "$WP_PATH/wp-content/db.php"
fi

if ! $WP core is-installed 2>/dev/null; then
  echo "==> Installing WordPress"
  $WP core install \
    --url="$SITE_URL" \
    --title="Walkridge Battlefield Tours" \
    --admin_user=admin \
    --admin_password=admin123 \
    --admin_email=admin@walkridge.test \
    --skip-email
  $WP rewrite structure '/%postname%/' --hard
fi

ln -sfn "$THEME_DIR" "$WP_PATH/wp-content/themes/$THEME_SLUG"
$WP theme activate "$THEME_SLUG"

if ! $WP plugin is-installed woocommerce; then
  echo "==> Installing WooCommerce (optional for tour products)"
  $WP plugin install woocommerce --activate
else
  $WP plugin activate woocommerce || true
fi
$WP option update woocommerce_coming_soon no || true

$WP rewrite flush --hard >/dev/null || true
$WP acorn optimize:clear >/dev/null 2>&1 || true

echo ""
echo "==> Done. Start the dev server with:"
echo "    wp server --path=$WP_PATH --host=0.0.0.0 --port=8080 --allow-root"
echo "    (admin: $SITE_URL/wp-admin  user: admin  pass: admin123)"
echo "    Vite HMR (optional): npm run dev"
