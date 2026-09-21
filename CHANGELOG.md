# Changelog

## Unreleased

- Marketing README, SUPPORT, compass mark, and live screenshots for GitHub / ThemeForest (no theme zip on `main`)

## 1.7.2 — 2026-09-21

Live WCAG 2.2 + light-theme UX pass (local theme only).

- Navbar: brand name ellipsis, phone from 1280px, desktop nav at 1180px, auto header height so actions do not clip
- Skip link stays `position: fixed` on focus; light `:focus-visible` uses gold-800 on parchment
- Denser `.section` padding; mobile sticky-book no longer covers footer; Woo cart/checkout block tokens on light
- Reviews expose a text star rating; footer pay SVGs have `<title>` plus `aria-label`
- Seeders: `serialize_block` / `JSON_HEX_TAG` comments, `wp_slash` on save; piped FAQ/review rows recover stripped `\n`
- Repair existing kses-encoded or en-dash `<!-- wp:` comments (`wr_demo_layouts_v5`); `the_content` normalizes then strips leftover encoded markup
- Front page fallback H1 if home-hero is missing

## 1.7.1 — 2026-09-21

Live error hunt after 1.7.0.

- Primary nav: if a menu item still uses the page title (`Gettysburg Battlefield Tours`, etc.), show the short label (`Tours`). Custom titles stay.
- `UpdateInfoControl` requires `WP_Customize_Control` before extending it so a stray autoload cannot fatal the front end.
- Woo notice overrides: phpcs ignores stay above echo (never after `?>`); string or array notice payloads both kses.
- Sticky header: desktop nav at 1120px; empty Woo cart block titles follow light/dark ink.

## 1.7.0 — 2026-09-19

Navbar, WooCommerce, and light-default follow-up on 1.6.x.

- First paint stays light (`data-theme="light"`). Stale `wr-theme` and `wr-color-scheme` dark values are ignored; only a new header toggle writes `wr-theme-pref`. Dark remains optional.
- Sticky frosted header: logo + name, primary nav + Shop, Book CTA, Woo cart count, theme toggle. Skip link `z-index` sits above the bar.
- Mobile menu: Close control, 44px targets, cart link, theme toggle, existing focus trap + `aria-expanded`.
- Woo shop / product / cart / checkout / account: visible field labels, quantity label, related tours heading, account nav, 44px buttons.
- Light navbar contrast: ink on parchment, gold-800 brand subtitle.

## 1.6.1 — 2026-09-19

WooCommerce tour catalog seed for new installs.

- `Tours::ensureProducts()` creates five published simple products (USD demo prices) in a Tours category when WooCommerce is active
- Tours Gutenberg layout adds a `[products]` shop grid so bookings go to product permalinks / add-to-cart, not static cards only
- Fallback tour cards show concept prices when Woo is off

## 1.6.0 — 2026-09-19

Light default, WCAG 2.2 contrast, denser demo pages, Woo empty states.

### Checked (WCAG 2.2)

- Contrast of body, links, buttons, footer, map chrome, and Woo notices on **light** (default) and dark
- Keyboard: skip link, `:focus` / `:focus-visible` gold ring, mobile nav trap (existing)
- Names: payment SVG `aria-label`s, theme toggle pressed state, form labels
- Target size: header toggle, hamburger, primary buttons ≥44×44
- Landmarks: banner / main / contentinfo (existing); heading colour on light headings

### Fixed

- Light is first-run (`data-theme="light"`, Theme Settings + Customizer `wr_color_scheme`, default `light`). Dark stays optional. Header toggle stores `wr-color-scheme` in the browser.
- Light `--color-ink-soft` darkened to `#3c2d1c` (~7:1 on `#f7f1e3`). Gold-on-parchment body/links use `--gold-800` (`#6b521f`) instead of `--gold-300`/`--gold-500`.
- Footer pay marks sit on white wells with strokes (Visa/Mastercard/Amex/Discover readable on light and dark).
- Newsletter has a visible label; skip link shows on `:focus` as well as `:focus-visible`.
- Woo notices, empty shop, empty cart, and “Woo off” shop wrapper use Walkridge tokens and point guests to `/tours`.
- Concept pages reseed (`wr_demo_layouts_v4`) with extra FAQ / reviews / area-facts / journal blocks. 555 phones and `@walkridge.test` unchanged.

## 1.5.0 — 2026-09-19

Cumulative production zip of all recent Walkridge work:

- Restored Hallowed Ground marketing copy as Gutenberg layouts (home, tours, guides, area, contact, refund policy)
- Walkridge block collection in `blocks/{slug}/` with inspector settings (size, typography, color, images)
- Area Map block: coordinates or ZIP, OSM embed, theme-colored icons, split/stack layouts
- Block SOP docs and screenshots in `docs/blocks/`
- Escape, sanitize, and authorize theme/block output; phpcs:ignore comments no longer render as HTML
- Appearance → Themes and Update Theme install `walkridge.zip` from the GitHub Release
- Sage local loop: `bin/setup-wp.sh` then `wp server` on `~/wp:8080`
- Live site [walkridge.matthummel.com](https://walkridge.matthummel.com/)

## 1.4.1 — 2026-09-19

- Escape, sanitize, and authorize theme and block output (WordPress.Security)
- Stop `phpcs:ignore` comments after `?>` from rendering as homepage HTML
- Appearance → Themes and Update Theme install the compiled zip from the GitHub Release (`walkridge.zip`)

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
