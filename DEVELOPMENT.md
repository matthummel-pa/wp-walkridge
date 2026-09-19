# Walkridge — Developer reference

Local Sage 11 workflow. Same shape as Acreline: one command to bootstrap, one command to serve. Live site: [walkridge.matthummel.com](https://walkridge.matthummel.com/). Cloud bootstrap notes: [`AGENTS.md`](AGENTS.md). Product copy: [`README.md`](README.md). Support: [`SUPPORT.md`](SUPPORT.md).

## Stack

| Layer | Technology |
| --- | --- |
| Theme framework | [Roots Sage 11](https://roots.io/sage/) + [Acorn](https://roots.io/acorn/) 6 |
| Templating | Blade |
| CSS | Tailwind CSS v4 + `resources/css/walkridge.css` |
| Build tool | Vite 8 |
| Block editor | Gutenberg — Walkridge dynamic blocks in `app/blocks.php` |
| PHP style | Laravel Pint |
| WordPress | 6.6+, PHP 8.3+, SQLite locally (MySQL on a host) |

## Quick start

```bash
git clone https://github.com/matthummel-pa/wp-walkridge.git
cd wp-walkridge
bin/setup-wp.sh
wp server --path="$HOME/wp" --host=0.0.0.0 --port=8080 --allow-root
```

Admin: `http://localhost:8080/wp-admin` — `admin` / `admin123`.

That is the whole setup. `bin/setup-wp.sh` is safe to re-run.

If PHP, Composer, or WP-CLI are missing, the script installs them via `bin/install-php-tools.sh`.

If port 8080 or `~/wp` is already used by another theme (matthummel or Acreline):

```bash
WP_PATH="$HOME/wp-walkridge-site" SITE_URL="http://localhost:8082" bin/setup-wp.sh
wp server --path="$HOME/wp-walkridge-site" --host=0.0.0.0 --port=8082 --allow-root
```

Keep the theme folder name **`walkridge`**.

## Daily commands

```bash
npm run build            # production assets (required before first page load)
npm run dev              # Vite HMR next to wp server
./vendor/bin/pint        # auto-fix PHP
./vendor/bin/pint --test # CI check
wp acorn view:clear --path="$HOME/wp" --allow-root   # after Blade edits
```

## What setup-wp.sh does

1. `composer install` and `npm install && npm run build` in the theme
2. Downloads WordPress to `~/wp` if needed
3. Creates `wp-config.php` with `WP_DEBUG`
4. Unzips SQLite Database Integration, copies `db.php`, fills plugin path placeholders
5. `wp core install` (only if not already installed)
6. Symlinks this repo to `~/wp/wp-content/themes/walkridge` and activates it
7. Installs/activates WooCommerce and turns off Coming soon

## Static HTML (no PHP)

`bin/preview-static.sh` serves the `dist/` concept snapshot. Use this only when you do not need Gutenberg or Blade. Default port 8080 — do not run it at the same time as `wp server`.

## Packaging

```bash
bin/build-theme-zip.sh   # → dist-theme/walkridge.zip
```
