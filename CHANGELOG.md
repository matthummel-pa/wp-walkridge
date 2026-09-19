# Changelog

## 1.4.0 — 2026-09-19

- Gutenberg blocks packaged in theme `blocks/{slug}/` with `block.json` and a Walkridge inserter collection
- New `walkridge/area-map` block (Hallowed Ground location grid: static pin, embed, or Field Map plugin)
- Area Map location can be set by coordinates or ZIP code; map icons and layout follow Walkridge gold / lantern / parchment / brick tokens
- SOP for every block in `docs/blocks/` with screenshots
- Area page seed uses the area map block

## 1.3.1 — 2026-09-18

- Live site is [walkridge.matthummel.com](https://walkridge.matthummel.com/)
- Local Sage setup matches Acreline: `bin/setup-wp.sh` then `wp server` on `~/wp:8080` (SQLite drop-in before install)
- Vite 8 + laravel-vite-plugin 3 + Acorn 6; Gutenberg editor packages pinned like Acreline
- Cloud/agent notes in `AGENTS.md`; developer loop in `DEVELOPMENT.md`
- `bin/install-php-tools.sh` installs PHP 8.3, Composer, and WP-CLI when they are missing

## 1.3.0 — 2026-09-18

- Restored full Hallowed Ground marketing copy as Gutenberg layouts (home, tours, guides, area, contact)
- New blocks: timeline, card grid, reviews, journal cards, town grid, copy section
- Home hero again includes path cards, stats, and place-name marquee
- Concept pages auto-seed when empty; one-time v2 reseed of demo layouts
- Gutenberg `walkridge/refund-policy` block replaces the Refund Policy custom-field metabox
- Page intro copy is no longer written to `wr_page_*` post meta
- Restore Sage toolchain files (`composer.json`, `package.json`, `vite.config.js`, `bin/`)
- Static HTML preview: `bin/preview-static.sh` serves `dist/`
- Local images fall back to public-domain Wikimedia stills when `public/images/` is missing
- `theme.json` no longer requires a Vite build to exist

## 1.2.0 — 2026-09-12

- Graphical **Appearance → Theme Settings** with Advanced panel; writes the same theme_mods as Customize → Identity
- **Appearance → Update Theme** (replaces Theme Status) with status cards, zip install, GitHub pull
- Gutenberg blocks: Contact Desk, FAQ List, Guide Roster, Area Facts
- Contact page is block-only; seeded layouts for all concept pages
- Docs: SUPPORT.md, docs/THEME-SETTINGS.md, docs/UPDATE-THEME.md, docs/BLOCKS.md

## 1.1.0

- WooCommerce optional; Gutenberg API v3; native SEO; marketplace CSS/JS/PHP cleanup

## 1.0.0

- Marketplace-ready Sage 11 concept theme
