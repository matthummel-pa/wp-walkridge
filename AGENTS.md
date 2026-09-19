# Walkridge — WordPress Theme

A [Roots Sage 11](https://roots.io/sage/) theme (Blade + Tailwind CSS v4 + Vite 8, powered by Acorn) for **licensed-guide battlefield tours**. Live site: [walkridge.matthummel.com](https://walkridge.matthummel.com/). GitHub repo: [`matthummel-pa/wp-walkridge`](https://github.com/matthummel-pa/wp-walkridge).

## Cursor Cloud

This repository is only the **theme** (`wp-content/themes/walkridge`). WordPress core is not in the repo.

### System prerequisites (VM snapshot)

PHP 8.3+ (`mbstring xml curl zip gd intl sqlite3 bcmath`), Composer 2, WP-CLI (`wp`), Node 20+/22+. If those are missing on a new machine, `bin/setup-wp.sh` runs `bin/install-php-tools.sh` once.

Cloud `install` should be:

```bash
bin/setup-wp.sh
```

Cloud `start` should be:

```bash
wp server --path="$HOME/wp" --host=0.0.0.0 --port=8080 --allow-root
```

Do not put `composer install` / `npm install` in `start`. Do not start Vite from `install`.

### First-time / re-run

```bash
bin/setup-wp.sh
```

Idempotent. Installs theme deps, builds Vite assets, stands up WordPress at `~/wp` with SQLite (no MySQL), symlinks the theme as `walkridge`, activates it, and installs WooCommerce for tour products.

Admin: `/wp-admin` — user `admin`, password `admin123`.

### Running the site

```bash
wp server --path="$HOME/wp" --host=0.0.0.0 --port=8080 --allow-root
```

Then browse `http://localhost:8080/` (home), `/tours`, `/guides`, `/area`, `/contact`, `/refund-policy`.

Optional HMR:

```bash
npm run dev
```

### Build / lint

- `npm run build` — required before the first page load (Vite manifest)
- `npm run dev` — asset HMR alongside `wp server`
- `./vendor/bin/pint --test` — PHP style check

### Gotchas

- Theme folder and Vite `base` must stay **`walkridge`**.
- Do not symlink `vendor/` from another worktree. Run `composer install` in this theme directory.
- After Blade edits: `wp acorn view:clear --path="$HOME/wp" --allow-root`
- SQLite drop-in must exist **before** `wp core install`. `bin/setup-wp.sh` copies `db.copy` and fills `{SQLITE_IMPLEMENTATION_FOLDER_PATH}` / `{SQLITE_PLUGIN}`.
- Copy lives in Gutenberg (`the_content()`). Seed from Theme Settings → Advanced or Tools → Walkridge Blocks.
- Demo phones are `555`; demo email is `@walkridge.test`.

### Packaging

- Theme zip: `bin/build-theme-zip.sh` → `dist-theme/walkridge.zip`
