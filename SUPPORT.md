# Walkridge support

Technical help for buyers and developers. Product pitch: [README.md](README.md). Live concept: [walkridge.matthummel.com](https://walkridge.matthummel.com/).

This page is the support document for ThemeForest-style packs and GitHub. Marketplace HTML copies should point here rather than duplicating a second source of truth.

## Contents

1. [Where to start](#where-to-start)
2. [Requirements](#requirements)
3. [Install](#install)
4. [Theme Settings](#theme-settings)
5. [Update Theme](#update-theme)
6. [Gutenberg blocks](#gutenberg-blocks)
7. [WooCommerce shop](#woocommerce-shop)
8. [Child theme](#child-theme)
9. [SEO](#seo)
10. [FAQ](#faq)
11. [Troubleshooting](#troubleshooting)
12. [Concept vs production](#concept-vs-production)
13. [How to file an issue](#how-to-file-an-issue)

---

## Where to start

| Need | Go here |
|---|---|
| Live concept | [walkridge.matthummel.com](https://walkridge.matthummel.com/) |
| Install a store zip | [Install](#install) |
| Brand, phone, hours, logo | **Appearance → Theme Settings** · [docs/THEME-SETTINGS.md](docs/THEME-SETTINGS.md) |
| Live preview of the same settings | **Appearance → Customize → Identity** |
| Newer zip | **Appearance → Update Theme** · [docs/UPDATE-THEME.md](docs/UPDATE-THEME.md) |
| Seed page layouts | Theme Settings → Advanced, or **Tools → Walkridge Blocks** · [docs/BLOCKS.md](docs/BLOCKS.md) |
| Local Sage | [DEVELOPMENT.md](DEVELOPMENT.md) |
| Cursor Cloud | [AGENTS.md](AGENTS.md) |
| Marketplace hub | [docs/marketplace/](docs/marketplace/) |

---

## Requirements

| | Minimum |
|---|---|
| WordPress | **6.6** (tested through 6.8) |
| PHP | **8.3** |
| WooCommerce | Optional. Required only to sell bookable tour products |
| Node / Composer on the host | **Not required** for a marketplace zip |
| Theme folder name | **`walkridge`** |

Local / git clones also need Composer 2, Node 20 or 22, and WP-CLI. `bin/setup-wp.sh` installs missing PHP tools.

---

## Install

### Marketplace zip

You receive a **pre-built** inner `walkridge.zip` (compiled `public/build/` + production `vendor/`).

1. Unzip the outer pack. Do not upload the outer file to WordPress.
2. **Appearance → Themes → Add New → Upload Theme** → inner `walkridge.zip`.
3. Activate. Confirm the directory is `wp-content/themes/walkridge`.
4. Optional: install WooCommerce from wordpress.org. Disable **Coming soon** if the shop shows a placeholder.
5. **Appearance → Theme Settings** — name, phone, email, hours, logo, header CTA.
6. **Appearance → Menus** — Primary and Footer.
7. Publish Pages with slugs `tours`, `guides`, `area`, `contact` (plus a static front page).
8. Seed layouts: Theme Settings → Advanced, or Tools → Walkridge Blocks.

### Git clone (developers)

```bash
bin/setup-wp.sh
wp server --path="$HOME/wp" --host=0.0.0.0 --port=8080 --allow-root
```

Admin: `admin` / `admin123`. Full loop: [DEVELOPMENT.md](DEVELOPMENT.md).

---

## Theme Settings

**Appearance → Theme Settings** is the graphical front door. It writes the same `theme_mod` keys as **Customize → Identity**. Customizer is the live-preview engine; the two do not fight.

| Card | What it covers |
|---|---|
| Brand | Site title, office name, subtitle, logo (or compass mark) |
| Contact desk | Phone, email, address, hours |
| Header & booking | CTA label/URL (empty URL uses the Woo shop) |
| Footer & social | Author credit, Twitter/X, Facebook, Instagram, TripAdvisor |
| Advanced (collapsed) | Light/dark default, concept badge, gold accent hex, block seed, Customizer links, Update Theme |

Light parchment is first-run. The header sun/moon control stores a visitor preference in the browser and does not change the site-wide default.

Details: [docs/THEME-SETTINGS.md](docs/THEME-SETTINGS.md).

---

## Update Theme

**Appearance → Update Theme** installs a production zip or the latest public GitHub Release asset named `walkridge.zip`.

1. Tag a Release `vX.Y.Z` with the output of `bin/build-theme-zip.sh`.
2. Do **not** commit `dist-theme/walkridge.zip` to `main` (it is large and gitignored).
3. On the site, open Update Theme → **Install latest GitHub Release**, or **Upload ZIP**.

Source zipballs still need `composer install --no-dev` and `npm run build`. Do not run that on shared hosting for marketplace installs.

Details: [docs/UPDATE-THEME.md](docs/UPDATE-THEME.md). Repo: [`matthummel-pa/wp-walkridge`](https://github.com/matthummel-pa/wp-walkridge/releases).

---

## Gutenberg blocks

Home, Tours, Guides, Area, Contact, and Refund Policy render `the_content()`. Seeded layouts use the **Walkridge** block category (`walkridge/*`). Do not store marketing copy in custom fields.

Catalog and seeded page map: [docs/BLOCKS.md](docs/BLOCKS.md). Per-block notes: [docs/blocks/](docs/blocks/).

Editor canvas uses `editor.css` so colours match the front end. Header and footer are hidden while editing.

---

## WooCommerce shop

WooCommerce is optional. Without it, marketing pages, contact, and the blog still run; booking CTAs fall back to `/shop` or the Theme Settings CTA URL.

With WooCommerce active:

- Tours seed as published simple products in a Tours category (`Tours::ensureProducts()`)
- `/tours` includes a product grid so bookings go to product permalinks
- Shop / product / cart / checkout / account wrap through `woocommerce.blade.php`
- Header cart count appears when a cart URL exists

Turn off WooCommerce **Coming soon** mode. Demo prices are fiction. Checkout on the live concept is sample-only.

Companion plugins (Bookings, Field Map) are **not** in the theme zip.

---

## Child theme

`child-theme/` in this repo. Template: `walkridge`. Activate after the parent. Keep the parent folder named `walkridge`. See `child-theme/readme.txt`.

---

## SEO

Native title, description, canonical, Open Graph, and Twitter tags **yield** when Yoast, Rank Math, SEOPress, or AIOSEO is active. No LocalBusiness / NAP schema pack. Put name, address, and phone in Theme Settings and page copy.

---

## FAQ

**Do I need extra plugins?**  
WooCommerce only if you sell tours. Regular SEO ships with the theme. Bookings and Field Map are optional pack plugins.

**Where do I change the phone number?**  
Appearance → Theme Settings. Demo numbers stay in the `555` range until you replace them.

**How do I hide the concept badge?**  
Theme Settings → Advanced → uncheck “Show concept demo badge.” Uncheck the credit box to drop the footer author line.

**The folder got renamed and assets 404.**  
Rename it back to `walkridge`. Vite `base` is `/wp-content/themes/walkridge/public/build/`.

**White screen: Vite manifest not found.**  
You installed a git clone without `npm run build`, or a zip that omitted `public/build`. Run `npm run build` locally, or install a Release zip.

**Gutenberg comments leaked as page text (`<!-- wp:walkridge/...` or `&lt;!-- wp:`).**  
Fixed in 1.7.1–1.7.2 (`serialize_block` / `JSON_HEX_TAG`, `wr_demo_layouts_v5`, `the_content` normalizer). Re-seed from Tools → Walkridge Blocks, or update to 1.7.2 and view the page once so the repair filter can strip leftover encoded markup. LiteSpeed page cache may still serve the old HTML until purged.

**Can I use Elementor?**  
The theme is not built around a page builder. Marketing pages expect Walkridge blocks.

---

## Troubleshooting

### Vite manifest not found

`public/build/manifest.json` is gitignored. Marketplace zips include it. Git clones must run `npm run build` before the first page load.

### Blade changes do not appear (Acorn)

```bash
wp acorn view:clear --path="$HOME/wp" --allow-root
# or
wp acorn optimize:clear --path="$HOME/wp" --allow-root
```

### LiteSpeed / Hostinger cache

The live demo sits behind LiteSpeed on Hostinger. Combined CSS files can 404 after a theme swap; purge LiteSpeed (and CDN) after Update Theme. Guest-mode / bot checks can delay headless screenshots; real browsers load the parchment UI.

If Combined CSS 404s, disable CSS combine for the theme or purge `wp-content/litespeed/`. The Vite file under `public/build/assets/app-*.css` is the real stylesheet.

### Block comment leak

Symptoms: visitors see raw `<!-- wp:walkridge/home-hero ... -->` or HTML-encoded comments. Cause: kses / JSON quoting on seeded block comments. Fix: theme **1.7.2** + re-seed (`wr_demo_layouts_v5`). Do not paste repaired HTML into the database by hand if Tools → Walkridge Blocks can seed.

### Customizer fatal (`UpdateInfoControl`)

1.7.1 loads `WP_Customize_Control` before extending it. Update if a stray autoload fatals the front end.

### Shop is “Coming soon”

`wp option update woocommerce_coming_soon no` (or WooCommerce setup wizard).

### PHP / WordPress too old

Requires PHP 8.3 and WordPress 6.6+. Sage 11 / Acorn will not run on 8.1.

---

## Concept vs production

| Concept (this demo) | Your production site |
|---|---|
| `555` phones, `@walkridge.test` | Real NAP in Theme Settings |
| Sample reviews labeled as sample | Your guests, or empty the reviews block |
| Concept badge on the canvas | Hide in Theme Settings → Advanced |
| Live checkout is fiction | Real Woo payments + tax/shipping |
| Gettysburg copy as a ThemeForest-style demo | Your battlefield, your licensing, your prices |
| Theme 1.7.2 in git; live host may lag | Ship a Release zip via Update Theme |

Do not present the live concept as a working ticket office. Do not invent guest reviews.

---

## How to file an issue

Use [GitHub Issues](https://github.com/matthummel-pa/wp-walkridge/issues). Include:

1. Theme version (`style.css`) and whether you used a Release zip or a git clone
2. WordPress version, PHP version, WooCommerce yes/no
3. Host (LiteSpeed / nginx / local `wp server`)
4. Steps, expected, actual
5. Confirm the folder is still `walkridge` and `public/build/manifest.json` exists
6. After Blade edits, confirm you cleared Acorn views

This theme does **not** include paid plugin support SLAs, live booking inventory, or park-concession licensing.
