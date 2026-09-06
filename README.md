# Walkridge — WordPress Theme

**Battlefield tour website for licensed guides** — browse tours, meet guides, explore the area, and book from the WooCommerce shop, without a page builder.

[![License: GPLv2](https://img.shields.io/badge/license-GPLv2-blue.svg)](LICENSE.md)
[![WordPress 6.6+](https://img.shields.io/badge/WordPress-6.6%2B-21759b.svg)](https://wordpress.org/)
[![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777bb3.svg)](https://www.php.net/)
[![Sage 11](https://img.shields.io/badge/Sage-11-6cb2eb.svg)](https://roots.io/sage/)

| | |
|---|---|
| **Live concept** | [matthummel.com/projects/hallowed-ground/](https://matthummel.com/projects/hallowed-ground/) |
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
4. **Appearance → Customize → Identity** — brand name, phone, email, address, hours, and social links. Logo lives under **Customize → Site Identity**.
5. Assign **Primary** and **Footer** nav menus. Publish pages with slugs `tours`, `guides`, `area`, and `contact`.
6. **Tools → Walkridge Blocks** — seed page layouts when pages are blank.

Full buyer walkthrough: [`docs/marketplace/buyer-guide.html`](docs/marketplace/buyer-guide.html).

---

## Quick start — developer (git clone)

**Prerequisites:** PHP 8.3+, Composer 2, WP-CLI, Node 20+.

```bash
git clone https://github.com/matthummel-pa/wp-walkridge.git
cd wp-walkridge
bin/setup-wp.sh          # Idempotent — safe to re-run
wp server --path="$HOME/wp" --host=0.0.0.0 --port=8080 --allow-root
```

`bin/setup-wp.sh` installs theme dependencies, builds assets, stands up WordPress at `~/wp` using the SQLite Database Integration plugin (no MySQL needed), symlinks the theme, installs and activates WooCommerce, seeds three demo tour products, and activates the theme.

**Admin:** `http://localhost:8080/wp-admin` → `admin` / `admin123`

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

## Architecture

```mermaid
graph TD
    WP["WordPress Core"]            -->|theme hooks|        Acorn["Acorn (Laravel IoC)"]
    Acorn                           -->|Blade engine|       Views["resources/views/"]
    Acorn                           -->|service provider|   App["app/"]

    App --> Setup["setup.php\nTheme support · menus · block editor · font preload"]
    App --> Blocks["blocks.php\nDynamic block registration + render callbacks"]
    App --> Forms["forms.php\nContact + newsletter handlers (no plugin)"]
    App --> SEO["Support/Seo.php\nTitle · meta · canonical · OG · Twitter"]
    App --> Identity["Support/Identity.php\nCustomizer theme_mod helpers"]
    App --> Tours["Support/Tours.php\nTour catalog (title · price · category)"]
    App --> Customizer["customizer.php\nAppearance → Customize → Identity"]

    Views --> Layouts["layouts/app.blade.php\nOuter HTML shell"]
    Views --> Sections["sections/header · footer"]
    Views --> Pages["page-{slug}.blade.php\ntours · guides · area · contact"]
    Views --> Partials["partials/\ntour-card · book-band · info-strip"]

    Vite["Vite 8"]                  -->|npm run build|      Build["public/build/\nCSS + JS bundles · WOFF2 fonts"]
    Build                           -->|manifest.json|       Setup

    BlockJS["resources/js/blocks/index.js\nGutenberg edit controls\nURLInput · MediaUpload · InspectorControls"]  --> Editor["Block Editor"]
    Blocks                          -->|PHP render|          Editor

    WooCommerce["WooCommerce\n(optional)"] -->|if active|   Shop["woocommerce.blade.php\nShop wrapper"]
```

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
│       ├── PageFields.php      # Page content defaults keyed by slug
│       ├── Seo.php             # Meta, Open Graph, and Twitter card output
│       └── Tours.php           # Tour catalog data (three demo tours)
├── bin/
│   ├── setup-wp.sh           # One-command local bootstrap (idempotent)
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
├── AGENTS.md                 # Cursor Cloud agent instructions
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

To seed or migrate page layouts: **Tools → Walkridge Blocks**.

---

## Identity (Customizer)

**Appearance → Customize → Identity** controls every office-specific detail:

| Setting | `theme_mod` key | Notes |
|---|---|---|
| Brand / office name | `wr_brand_name` | Falls back to site name |
| Phone | `wr_phone` | Info strip and header rail |
| Email | `wr_email` | Contact page link |
| Address | `wr_address` | Info strip |
| Hours | `wr_hours` | Info strip |
| Header CTA label | `wr_header_cta_label` | |
| Header CTA URL | `wr_header_cta_url` | |
| Twitter / X handle | `wr_social_twitter` | `@handle` — used in Twitter card meta |
| Footer author credit | `wr_show_author_credit` | Toggle |
| Concept demo badge | `wr_show_concept_badge` | Hides the "concept" watermark |

Logo upload lives under **Customize → Site Identity**.

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
| [SUPPORT.md](SUPPORT.md) | Full reference: stack, templates, local dev, packaging, troubleshooting |
| [GitHub Issues](https://github.com/matthummel-pa/wp-walkridge/issues) | Reproducible theme bugs |
| [CHANGELOG.md](CHANGELOG.md) | Version history |
| [BRAND.md](BRAND.md) | Brand kit: palette, typefaces, voice |
| [CREDITS.md](CREDITS.md) | Third-party resource licences |
| [`docs/marketplace/`](docs/marketplace/) | Buyer guide, requirements, selling notes |

---

## License

GPLv2 or later. See [`license.txt`](license.txt), [`LICENSE.md`](LICENSE.md), and [`CREDITS.md`](CREDITS.md). Sage 11 and Acorn remain MIT, which is GPL-compatible.

Author: [Matt Hummel](https://matthummel.com/)
