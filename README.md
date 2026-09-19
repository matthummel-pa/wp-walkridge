# Walkridge — WordPress Theme

## About the WordPress Theme

WalkRidge is a custom WordPress theme for tour operators and historical sites. It ships a clean, responsive layout optimized for performance and SEO, with straightforward content tools so editors can publish walking tours, home tours, and other guided experiences without fighting the theme.

### Features (drop under the intro)

- Built for walking tours, historical home tours, and similar guided-tour sites
- Performance-first markup and assets for faster loads
- Responsive layout across phone, tablet, and desktop
- SEO-friendly structure (clean headings, semantic HTML)
- Simple content management for non-technical editors

**Battlefield tour website for licensed guides** — browse tours, meet guides, explore the area, and book from the WooCommerce shop, without a page builder.

[![License: GPLv2](https://img.shields.io/badge/license-GPLv2-blue.svg)](LICENSE.md)
[![WordPress 6.6+](https://img.shields.io/badge/WordPress-6.6%2B-21759b.svg)](https://wordpress.org/)
[![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777bb3.svg)](https://www.php.net/)
[![Sage 11](https://img.shields.io/badge/Sage-11-6cb2eb.svg)](https://roots.io/sage/)

| | |
|---|---|
| **Live site** | [walkridge.matthummel.com](https://walkridge.matthummel.com/) |
| **Author** | [Matt Hummel](https://matthummel.com/) |
| **Install folder** | **`walkridge`** (keep this exact name) |
| **Support** | [SUPPORT.md](SUPPORT.md) · [GitHub Issues](https://github.com/matthummel-pa/wp-walkridge/issues) |
| **Version** | See `style.css` |

> **Fiction only.** Tours, guides, phones, and checkout in the demo are sample data — not a live ticket desk or park concession. Demo phones use `555` numbers; demo emails use `@walkridge.test`.

---

## Requirements

| | Minimum |
|---|---|
| WordPress | 6.6 |
| PHP | 8.3 |
| WooCommerce | Optional — the theme loads and looks correct without it; WooCommerce is needed only to sell bookable tour products |

---

## Quick start — buyer (marketplace zip)

You receive a **pre-built zip** that includes compiled assets and a production `vendor/`. Composer and npm are **not** required on the host.

1. Unzip the outer `walkridge-*.zip` — do not upload that outer file.
2. **Appearance → Themes → Add New → Upload Theme** → inner `walkridge.zip`. Activate. The folder must stay named **`walkridge`**.
3. Install WooCommerce and add tour products. If the shop shows a "Coming soon" placeholder, turn that option off inside WooCommerce.
4. **Appearance → Theme Settings** — brand, phone, email, hours, logo, and Advanced controls. Customizer is optional live preview.
5. Assign **Primary** and **Footer** nav menus. Publish pages with slugs `tours`, `guides`, `area`, and `contact`.
6. **Appearance → Theme Settings → Advanced** or **Tools → Walkridge Blocks** — seed Gutenberg layouts on those pages.

Full buyer walkthrough: [`docs/marketplace/buyer-guide.html`](docs/marketplace/buyer-guide.html).

---

## Quick start — developer (git clone)

Same two-command loop as Acreline. Full notes: [`DEVELOPMENT.md`](DEVELOPMENT.md) · Cloud: [`AGENTS.md`](AGENTS.md).

```bash
git clone https://github.com/matthummel-pa/wp-walkridge.git
cd wp-walkridge
bin/setup-wp.sh
wp server --path="$HOME/wp" --host=0.0.0.0 --port=8080 --allow-root
```

`bin/setup-wp.sh` is idempotent. It installs PHP tools if they are missing, runs Composer and npm, builds Vite assets, stands up WordPress at `~/wp` on SQLite (no MySQL), symlinks the theme as **`walkridge`**, and activates WooCommerce.

**Admin:** `http://localhost:8080/wp-admin` → `admin` / `admin123`

If `~/wp` or port 8080 is already used by another product:

```bash
WP_PATH="$HOME/wp-walkridge-site" SITE_URL="http://localhost:8082" bin/setup-wp.sh
wp server --path="$HOME/wp-walkridge-site" --host=0.0.0.0 --port=8082 --allow-root
```

**Preview without WordPress:** `bin/preview-static.sh` serves `dist/` (do not run it on the same port as `wp server`).

### Development commands

```bash
npm run build                                                           # Build assets (required before first page load)
npm run dev                                                             # Vite HMR — run alongside wp server
./vendor/bin/pint                                                       # PHP style auto-fix
./vendor/bin/pint --test                                                # PHP style check only
wp acorn view:clear --path="$HOME/wp" --allow-root                     # Clear Blade cache after template edits
wp acorn optimize:clear --path="$HOME/wp" --allow-root                 # Full Acorn cache clear
```

> **Important:** You must run `npm run build` before any page will load. Vite outputs a `manifest.json` that the theme uses to resolve hashed asset filenames. Without it every page throws a "Vite manifest not found" error.

---
## Theme structure

```
walkridge/
├── app/
│   ├── blocks.php            # Dynamic block registration; render callbacks for all walkridge/* blocks
│   ├── customizer.php        # Appearance → Customize → Identity controls
│   ├── forms.php             # Contact and newsletter POST/AJAX handlers
│   ├── marketplace.php       # Admin tools page (Tools → Walkridge Blocks, Block Generator)
│   ├── page-fields.php       # Page seeding and nav menu scaffolding (idempotent)
│   ├── setup.php             # Theme support, menus, block editor settings, font preload
│   └── Support/
│       ├── BlockMigration.php  # Legacy field → block migration logic
│       ├── Identity.php        # Customizer helper methods (brand, phone, email…)
│       ├── PageFields.php      # Seed copy for page-intro blocks (not post meta)
│       ├── Seo.php             # Meta, Open Graph, and Twitter card output
│       └── Tours.php           # Tour catalog data (three demo tours)
├── bin/
│   ├── install-php-tools.sh  # PHP 8.3, Composer 2, WP-CLI if missing
│   ├── setup-wp.sh           # Acreline-style Sage + SQLite WordPress bootstrap
│   ├── dev-servers.sh        # wp server on :8080
│   └── build-theme-zip.sh    # Build the distributable installable zip
├── resources/
│   ├── css/
│   │   ├── app.css           # Tailwind CSS v4 entrypoint + @import walkridge.css
│   │   ├── walkridge.css     # Design tokens, layout, components, utilities
│   │   └── editor.css        # Block editor canvas — mirrors walkridge.css; hides site header/footer
│   ├/js/
│   │   ├── app.js            # Front-end JS: scroll shadow, reveal animations, mobile nav, theme toggle, tour filter
│   │   ├── editor.js         # Block editor entrypoint (imports blocks/index.js)
│   │   └── blocks/
│   │       └── index.js      # Gutenberg block edit controls (API v3; HeadingLevelDropdown, AlignmentControl, URLInput, MediaUpload)
│   └── views/
│       ├── layouts/           # app.blade.php (outer HTML shell with skip-link, theme toggle)
│       ├── sections/          # header.blade.php, footer.blade.php
│       ├── partials/          # tour-card, book-band, info-strip, page-header, content-*
│       ├── front-page.blade.php
│       ├── page-tours.blade.php
│       ├── page-guides.blade.php
│       ├── page-area.blade.php
│       ├── page-contact.blade.php
│       ├── page-refund-policy.blade.php
│       └── woocommerce.blade.php   # Shop/product/cart/checkout wrapper; graceful fallback if WC inactive
├── public/
│   ├── build/                # Compiled assets (git-ignored — run npm run build or use prebuilt zip)
│   └── images/               # Self-hosted public-domain Gettysburg photographs
├── AGENTS.md                 # Cursor Cloud install/start
├── DEVELOPMENT.md            # Local Sage workflow
├── BRAND.md                  # Brand kit: name, palette, typefaces, voice
├── CHANGELOG.md              # Version history
├── CREDITS.md                # Third-party resource licenses
└── SUPPORT.md                # Buyer and developer reference
```

---

## Gutenberg blocks

All blocks are **dynamic** (PHP render callbacks) using Block API v3. The block editor canvas mirrors the front-end colour palette via `editor.css`; the site header and footer are hidden while editing.

| Block slug | `walkridge/…` | Editor controls |
|---|---|---|
| Home hero | `home-hero` | Image (MediaUpload), primary and secondary URL (URLInput) |
| Page intro | `page-intro` | Eyebrow, heading, supporting text |
| Section heading | `section-heading` | Heading level h2–h5 (HeadingLevelDropdown), alignment (AlignmentControl), eyebrow, anchor |
| Tour grid | `tour-grid` | Visible tours, compare table toggle |
| Pathway cards | `pathway-cards` | Two card URLs |
| About split | `about-split` | Custom image (MediaUpload), layout flip, primary/secondary URL |
| CTA band | `cta-band` | URL, label |
| Book band | `book-band` | Shop URL, label |
| Info strip | `info-strip` | Show/hide phone, address, and hours |
| Custom block | `custom` | Block Generator field definitions |
| Contact desk | `contact-desk` | NAP + contact form (Identity-driven) |
| FAQ list | `faq-list` | Piped `Question \| Answer` lines |
| Guide roster | `guide-roster` | Piped `Name \| Role \| Bio` lines |
| Area facts | `area-facts` | Parking, meeting point, directions |

To seed or migrate page layouts: **Tools → Walkridge Blocks**.

---

## Theme Settings

**Appearance → Theme Settings** is the graphical front door (same pattern as Acreline / Pressroot). It writes the same `theme_mod` keys as **Customize → Identity**. Customizer stays the live-preview engine. **Advanced settings** on that screen cover the demo badge, author credit, optional gold accent, block seeding, and Update Theme.

| Setting | `theme_mod` key | Notes |
|---|---|---|
| Brand / office name | `wr_brand_name` | Falls back to site name |
| Phone | `wr_phone` | Info strip and contact desk |
| Email | `wr_email` | Contact desk + form recipient |
| Address | `wr_address` | Info strip |
| Hours | `wr_hours` | Info strip |
| Header CTA label | `wr_cta_label` | |
| Header CTA URL | `wr_cta_url` | Empty uses the WooCommerce shop |
| Twitter / X handle | `wr_social_twitter` | `@handle` — Twitter card meta |
| Footer author credit | `wr_show_credit` | Removable for marketplace installs |
| Concept demo badge | `wr_show_demo_chrome` | Hide before a client walkthrough |
| Gold accent override | `wr_accent_color` | Advanced — optional hex |

Logo upload lives on Theme Settings and under **Customize → Site Identity**.

**Appearance → Update Theme** installs a production zip or pulls from GitHub. Docs: [`docs/THEME-SETTINGS.md`](docs/THEME-SETTINGS.md), [`docs/UPDATE-THEME.md`](docs/UPDATE-THEME.md), [`docs/BLOCKS.md`](docs/BLOCKS.md).

---

## SEO

Native SEO tags yield automatically when Yoast, Rank Math, SEOPress, or AIOSEO is active — no duplicate tags.

- `<title>` parts, meta description, canonical URL
- Open Graph — `og:title`, `og:description`, `og:image` with width, height, and alt
- Twitter card — `summary_large_image`, `twitter:site`
- `noindex, follow` on 404 and search result pages

No LocalBusiness JSON-LD. Put NAP in Customizer Identity and page copy; use a dedicated local SEO plugin for structured local data.

---

## Packaging

```bash
bin/build-theme-zip.sh     # → dist-theme/walkridge.zip
```

The zip includes compiled `public/build/` assets and a production (no-dev) `vendor/`. Host buyers need neither Composer nor npm. Install the zip via **Appearance → Themes → Upload Theme**, or:

```bash
wp theme install dist-theme/walkridge.zip --activate --allow-root
```

---

## Support and documentation

| | |
|---|---|
| [SUPPORT.md](SUPPORT.md) | Theme Settings, Update Theme, blocks, local Sage, packaging |
| [GitHub Issues](https://github.com/matthummel-pa/wp-walkridge/issues) | Reproducible theme bugs |
| [CHANGELOG.md](CHANGELOG.md) | Version history |
| [BRAND.md](BRAND.md) | Brand kit: palette, typefaces, voice |
| [CREDITS.md](CREDITS.md) | Third-party resource licences |
| [`docs/marketplace/`](docs/marketplace/) | Buyer guide, requirements, selling notes |

---

## License

GPLv2 or later. See [`license.txt`](license.txt), [`LICENSE.md`](LICENSE.md), and [`CREDITS.md`](CREDITS.md). Sage 11 and Acorn remain MIT, which is GPL-compatible.

Author: [Matt Hummel](https://matthummel.com/)
