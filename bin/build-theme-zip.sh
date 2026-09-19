#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
composer install --no-dev --no-interaction --optimize-autoloader
npm install
npm run build
mkdir -p dist-theme
STAGE="$(mktemp -d)"
rsync -a --delete \
  --exclude '.git' \
  --exclude 'node_modules' \
  --exclude 'dist' \
  --exclude 'dist-theme' \
  --exclude 'plugins' \
  --exclude '.cursor' \
  "$ROOT/" "$STAGE/walkridge/"
# Buyer zip should not include companion plugins or static HTML snapshots.
(cd "$STAGE" && zip -qr "$ROOT/dist-theme/walkridge.zip" walkridge)
rm -rf "$STAGE"
echo "Wrote dist-theme/walkridge.zip"
