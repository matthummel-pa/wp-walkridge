# Walkridge support

Technical reference for buyers and developers. Product pitch lives in [README.md](README.md). Marketplace HTML copies this into `Documentation/` in the seller pack.

## Where to start

| Need | Go here |
|---|---|
| Install a store zip | README → Quick start — buyer |
| Change brand, phone, hours, logo | **Appearance → Theme Settings** |
| Live-preview the same settings | **Appearance → Customize → Identity** |
| Install a newer zip | **Appearance → Update Theme** |
| Seed page layouts | Theme Settings → Advanced, or **Tools → Walkridge Blocks** |
| Edit page copy | Gutenberg — Walkridge block category |
| Local Sage / WordPress | [DEVELOPMENT.md](DEVELOPMENT.md) |
| Cursor Cloud bootstrap | [AGENTS.md](AGENTS.md) |
| ThemeForest docs hub | `docs/marketplace/` |

## Theme Settings vs Customizer vs Update Theme

Walkridge follows the same split as Acreline / Pressroot:

- **Theme Settings** — graphical cards for identity, contact, header, footer, social. **Advanced settings** stay collapsed (demo badge, credit, gold accent, block seed, Customizer deep links).
- **Customizer** — engine. Same `theme_mod` keys. Use it when you want a live canvas.
- **Update Theme** — version cards, zip overwrite, optional GitHub pull. Not a design screen.

Details: [docs/THEME-SETTINGS.md](docs/THEME-SETTINGS.md), [docs/UPDATE-THEME.md](docs/UPDATE-THEME.md).

## Gutenberg pages

Home, Tours, Guides, Area, Contact, and Refund Policy render `the_content()`. Seeded layouts use Walkridge Gutenberg blocks. Contact form and NAP are the **Contact Desk** block. Refund windows live on the **Refund Policy** block. Do not store page copy in custom fields.

Block catalog: [docs/BLOCKS.md](docs/BLOCKS.md).

## SEO (regular only)

Native title, description, canonical, Open Graph, and Twitter tags yield when Yoast, Rank Math, SEOPress, or AIOSEO is active. No LocalBusiness / NAP schema pack. Put name, address, and phone in Theme Settings and page copy.

## Plugins

- **WooCommerce** — optional; required only to sell tour products. Local `bin/setup-wp.sh` installs it so the shop templates can be checked.
- **Walkridge Bookings** / **Field Map** — companion plugins in the seller pack, not inside the theme zip.
- Do not add a page builder.

## Local development (Sage 11)

Same loop as Acreline:

```bash
bin/setup-wp.sh
wp server --path="$HOME/wp" --host=0.0.0.0 --port=8080 --allow-root
```

- PHP 8.3+, Composer 2, WP-CLI, Node 20+/22+. Missing PHP tools are installed by `bin/install-php-tools.sh`.
- WordPress lives **outside** the repo at `~/wp` (SQLite, no MySQL). Theme folder must stay **`walkridge`**.
- Admin: `http://localhost:8080/wp-admin` — `admin` / `admin123`.
- After Blade edits: `wp acorn view:clear --path="$HOME/wp" --allow-root`.
- Optional HMR: `npm run dev` in a second terminal.
- Full file map and daily commands: [DEVELOPMENT.md](DEVELOPMENT.md).

**Static concept HTML (no PHP):** `bin/preview-static.sh` serves `dist/`. Do not bind it to the same port as `wp server`.

### Before you file an issue

1. Confirm the theme folder is still `walkridge` and you ran `npm run build` (or installed a zip that already includes `public/build`).
2. After Blade edits, clear Acorn views: `wp acorn view:clear --path="$HOME/wp" --allow-root`.
3. Identity lives under **Appearance → Theme Settings** (or Customize → Identity).
4. Include WordPress version, PHP version, theme version, and whether WooCommerce is active.

## Packaging

`bin/build-theme-zip.sh` → `dist-theme/walkridge.zip` (compiled `public/build` + production `vendor`). Host buyers do not run Composer or npm.

## Fiction data

Sample phones use `555`. Sample email uses `@walkridge.test`. Hide the concept badge in Theme Settings → Advanced before a client walkthrough.
