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
| ThemeForest docs hub | `docs/marketplace/` |

## Theme Settings vs Customizer vs Update Theme

Walkridge follows the same split as Acreline / Pressroot:

- **Theme Settings** — graphical cards for identity, contact, header, footer, social. **Advanced settings** stay collapsed (demo badge, credit, gold accent, block seed, Customizer deep links).
- **Customizer** — engine. Same `theme_mod` keys. Use it when you want a live canvas.
- **Update Theme** — version cards, zip overwrite, optional GitHub pull. Not a design screen.

Details: [docs/THEME-SETTINGS.md](docs/THEME-SETTINGS.md), [docs/UPDATE-THEME.md](docs/UPDATE-THEME.md).

## Gutenberg pages

Home, Tours, Guides, Area, Contact, and Refund Policy render `the_content()`. Seeded layouts use only Walkridge blocks (plus one core paragraph on Refund Policy). Contact form and NAP are the **Contact Desk** block — they are not hardcoded in Blade.

Block catalog: [docs/BLOCKS.md](docs/BLOCKS.md).

## SEO (regular only)

Native title, description, canonical, Open Graph, and Twitter tags yield when Yoast, Rank Math, SEOPress, or AIOSEO is active. No LocalBusiness / NAP schema pack. Put name, address, and phone in Theme Settings and page copy.

## Plugins

- **WooCommerce** — optional; required only to sell tour products.
- **Walkridge Bookings** / **Field Map** — companion plugins in the seller pack, not inside the theme zip.
- Do not add a page builder.

## Local development (Sage 11)

PHP 8.3+, Composer 2, Node 20+. `bin/setup-wp.sh` then `wp server`. Folder name must stay `walkridge`. After Blade edits: `wp acorn view:clear`.

**Preview the restored concept pages without PHP:** `bin/preview-static.sh` serves `dist/` at http://127.0.0.1:8080/ (home, tours, guides, area, contact).

## Packaging

`bin/build-theme-zip.sh` → `dist-theme/walkridge.zip` (compiled `public/build` + production `vendor`). Host buyers do not run Composer or npm.

## Fiction data

Sample phones use `555`. Sample email uses `@walkridge.test`. Hide the concept badge in Theme Settings → Advanced before a client walkthrough.
