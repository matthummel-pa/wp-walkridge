#!/usr/bin/env bash
# Serve the restored concept pages (static HTML snapshot) for local preview.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PORT="${1:-8080}"
cd "$ROOT/dist"
echo "Walkridge / Hallowed Ground static preview"
echo "Open http://127.0.0.1:${PORT}/"
echo "Pages: /  /tours.html  /guides.html  /area.html  /contact.html  /shop/"
exec python3 -m http.server "$PORT" --bind 127.0.0.1
