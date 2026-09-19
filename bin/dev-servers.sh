#!/usr/bin/env bash
# Start WordPress the same way Acreline does. Vite is optional in another terminal.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WP_PATH="${WP_PATH:-$HOME/wp}"
PORT="${PORT:-8080}"

if [[ ! -f "$WP_PATH/wp-load.php" ]]; then
  "$ROOT/bin/setup-wp.sh"
fi

ln -sfn "$ROOT" "$WP_PATH/wp-content/themes/walkridge"

exec wp server --path="$WP_PATH" --host=0.0.0.0 --port="$PORT" --allow-root
