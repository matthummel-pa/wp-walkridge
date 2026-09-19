=== Walkridge ===
Contributors: matthummel
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 8.3
Stable tag: 1.6.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: e-commerce, custom-logo, custom-menu, featured-images, footer-widgets, theme-options, translation-ready, one-column, threaded-comments

WooCommerce-ready WordPress theme for licensed-guide battlefield tours — bookable tours, guides, and area pages.

== Description ==

Walkridge is a classic (non-block) Sage 11 WordPress theme for a licensed-guide battlefield tour company: bookable tours via WooCommerce, guide bios, area context, and a contact desk.

Marketing pages are Walkridge Gutenberg blocks (Home, Tours, Guides, Area, Contact, Refund Policy). Seed them from **Appearance → Theme Settings → Advanced** or **Tools → Walkridge Blocks**. Buyers change brand, phone, email, hours, and the header button from **Appearance → Theme Settings**. Customizer → Identity is the live-preview twin. Upload a logo on Theme Settings or Site Identity. Booking CTAs point at the WooCommerce shop. Optional companion plugins in the seller pack — **Walkridge Bookings** and **Walkridge Field Map** — are sold separately and are not required to run the theme chrome.

The sample office in the preview is named Walkridge — replace it under Theme Settings. This is a concept theme. Sample phones are 555 numbers (office `(717) 555-0100`). Concept emails use `@walkridge.test`. It is not a live ticket desk, licensed park concession, or payment processor by itself.

Live site: https://walkridge.matthummel.com/
Support and ThemeForest docs: https://github.com/matthummel-pa/wp-walkridge/blob/main/SUPPORT.md

= Features =

* WooCommerce shop for tour products (theme support + Blade wrapper)
* Theme Settings: graphical identity, contact desk, header, footer, social, Advanced panel
* Update Theme: zip install and optional GitHub pull
* Customizer Identity remains the live-preview engine for the same theme_mods
* Custom logo on Theme Settings or Site Identity (replaces the compass mark)
* Gutenberg marketing pages: Home, Tours, Guides, Area, Contact, Refund Policy
* WordPress menus (Primary + Footer) with a concept-page fallback
* Self-hosted fonts (Archivo Black, Atkinson Hyperlegible, IBM Plex Mono — SIL OFL)
* Self-hosted Wikimedia / public-domain Gettysburg imagery in `public/images/`
* Translation-ready (`walkridge`)
* Optional pack plugins: Bookings (WooCommerce tour slots) and Field Map (interactive battlefield map) — sold separately

= WordPress.org note =

This theme ships compiled Vite assets and an Acorn/Sage vendor tree. The directory prefers simpler classic or block themes. Treat a WP.org upload as a later lite listing, not a guaranteed first-pass approval. ThemeForest and your own store take the full pack (theme + optional plugins + docs).

== Installation ==

1. Upload `walkridge` to `/wp-content/themes/` (or use the zip from Appearance → Themes → Add New).
2. Activate Walkridge. The folder name must stay `walkridge`.
3. Optional — install and activate WooCommerce for the bookable tour shop. The marketing site, contact form, and blog run without it. If you activate WooCommerce, disable "Coming soon" mode if the shop shows a placeholder screen.
4. Go to Appearance → Theme Settings (identity, logo, Advanced). Customizer is optional.
5. Appearance → Menus — assign Primary and Footer menus.
6. Publish Pages with slugs `tours`, `guides`, `area`, `contact` (plus a static front page) and seed Walkridge blocks.
7. Appearance → Update Theme to install later zips.
8. Optional: install the pack’s companion plugins (Bookings, Field Map) and/or the child theme.

Store zips already include `public/build` and `vendor`. After a git clone run `npm run build` and `composer install --no-dev`. Full ThemeForest documentation: Documentation/index.html in the seller pack.

== Frequently Asked Questions ==

= Do I need extra plugins? =

WooCommerce is optional — required only for the bookable tour shop (products, cart, checkout). The theme marketing pages, contact form, and blog run without it. Optional (sold separately in the pack): **Walkridge Bookings** (advanced tour slots / party pricing) and **Walkridge Field Map** (interactive area map). Regular SEO tags (title, description, social) ship with the theme; a dedicated SEO plugin is not required.

= Where do I change copy without editing templates? =

Appearance → Theme Settings for brand, phone, email, address, hours, CTA, social, and the concept badge. Page layouts are Walkridge Gutenberg blocks. Customize → Identity is live preview for the same settings.

= Where do I change the phone number? =

Appearance → Theme Settings.

= How do I hide the concept demo badge? =

Theme Settings → Advanced → uncheck “Show concept demo badge.” Uncheck the credit box to drop the footer author line (WordPress.org expects the buyer’s copyright only).

= How do I update the theme? =

Appearance → Update Theme. Upload a production zip, or configure GitHub (dev). Marketplace zips already include vendor and compiled assets.

= Are the Bookings and Field Map plugins included in the theme zip? =

No. They ship beside the theme in the seller pack and are sold separately. Do not bundle plugin zips inside the theme folder for WordPress.org.

= Can I use a child theme? =

Yes. The marketplace pack includes `walkridge-child`.

= Does this theme process live tour payments by itself? =

No. WooCommerce (and your payment gateway) handle checkout. Sample products and 555 phones are concept data.

= How do I update from a git clone? =

Pull the branch, run `composer install --no-dev` if needed, then `npm run build`. Keep the install folder named `walkridge`. After Blade edits: `wp acorn view:clear`. Hosted store zips already include compiled assets — do not run npm on the client host unless you are developing from source.

== Screenshots ==

Desktop captures of the seeded concept demo (also used on https://walkridge.matthummel.com/ when published):

1. Homepage — compass mark, hero, and path to book a tour. (`docs/marketplace/screenshots/01-homepage.png`)
2. Tours — sample walking / bus / lantern offerings. (`02-tours.png`)
3. Shop — WooCommerce tour products. (`03-shop.png`)
4. Guides — licensed-guide concept bios. (`04-guides.png`)
5. Area — area context and field notes. (`05-area.png`)
6. Contact — phone and address from Customize → Identity. (`06-contact.png`)
7. Book band / CTA — shop-linked booking call to action. (`07-book.png`)

Theme thumbnail: screenshot.png (1200×900). Extra captures: docs/marketplace/screenshots/ (copied into Documentation/screenshots/ in the seller pack).

== Branding ==

Original compass mark (inline SVG in the header/footer; GPLv2 with the theme):

* Replace with your logo under Customize → Site Identity
* Palette sample: slate `#0c1218` / `#17212c`, gold `#e0be72`, parchment `#f7f1e3`

See docs/marketplace/branding.html (Documentation/branding.html in the seller pack) and BRAND.md.

== Changelog ==

= 1.6.1 =
* Seed five WooCommerce tour products (Tours category) when WooCommerce is active
* Tours layout includes a WooCommerce product shortcode alongside the tour-grid

= 1.6.0 =
* Light parchment is the default colour theme; dark slate remains a header toggle and Theme Settings option
* WCAG 2.2 contrast pass on light surfaces (body, footer links, Woo notices, gold-on-parchment text)
* Skip link, 44px targets, named payment icons, visible newsletter label
* Denser Gutenberg demo layouts (FAQ, reviews, area facts) on concept pages
* WooCommerce empty shop/cart templates that sell booking a tour

= 1.5.0 =
* Cumulative production release of restored marketing pages, Walkridge Gutenberg blocks, Area Map, output escaping, and GitHub Release updates
* Home, Tours, Guides, Area, Contact, and Refund Policy layouts as Gutenberg blocks (not Blade/page meta)
* Walkridge block collection in theme blocks/ with inspector options (size, type, color, images)
* Area Map: coordinates or ZIP, OSM/Nominatim, theme icon colors and layouts
* Security: escaped/sanitized/authorized theme output; phpcs:ignore comments no longer print as HTML
* Appearance → Themes / Update Theme install compiled walkridge.zip from the GitHub Release
* Live site: https://walkridge.matthummel.com/

= 1.4.1 =
* Security: escape, sanitize, and authorize block and Blade output
* Fix phpcs:ignore comments leaking into page HTML
* Theme updates install the compiled walkridge.zip from the GitHub Release

= 1.4.0 =
* Gutenberg blocks in theme blocks/{slug}/ with a Walkridge inserter collection
* Area Map block (coordinates or ZIP; theme-colored icons and layouts)
* Block SOP docs in docs/blocks/

= 1.3.1 =
* Live site: https://walkridge.matthummel.com/
* Local Sage setup matches Acreline: bin/setup-wp.sh then wp server on ~/wp port 8080
* Vite 8, Acorn 6, DEVELOPMENT.md and AGENTS.md for clone and Cloud Agent boots
* Customizer update-info control no longer fatals on the front end

= 1.2.0 =
* Appearance → Theme Settings graphical interface with Advanced panel (same theme_mods as Customizer)
* Appearance → Update Theme replaces Theme Status; zip + GitHub installers stay
* New Gutenberg blocks: Contact Desk, FAQ List, Guide Roster, Area Facts
* Contact page is block content only (form no longer hardcoded in Blade)
* Seeded layouts for Home, Tours, Guides, Area, Contact, Refund Policy
* Documentation: SUPPORT.md, docs/THEME-SETTINGS.md, docs/UPDATE-THEME.md, docs/BLOCKS.md

= 1.1.0 =
* WooCommerce is now optional — marketing site, contact form, and blog run without it
* Gutenberg blocks upgraded to API v3 with ServerSideRender, InspectorControls, MediaUpload, HeadingLevelDropdown, AlignmentControl, and URLInput
* Editor CSS mirrors theme design tokens and critical layout rules for accurate block preview
* Native SEO: noindex on 404 / search pages, og:image dimensions + alt, twitter:site handle
* Contact form: optional phone field, translated sprintf strings, trimmed phone logic
* Translation strings: full walkridge.pot generated; translator comments on all sprintf calls
* CSS bloat removed (~1,100 lines of dead rules); JS bloat removed (~74 lines); PHP bloat removed (~118 lines)
* Inline styles converted to semantic CSS classes
* Accessibility: aria-label localised on navigation elements, rel="nofollow noopener" on external credit link, payment icon SVG aria hygiene
* window.hgForms renamed to window.wrForms (namespace alignment)
* Self-hosted font OFL.txt attribution added
* Style.css Theme URI, Version, and Tags updated for marketplace submission

= 1.0.0 =
* Marketplace readiness: GPLv2+ packaging, Customizer Identity, accessibility pass, self-hosted fonts and Wikimedia images
* Docs hub under docs/marketplace/, child theme starter, companion plugin headers GPLv2+
* Removed insecure exec-based rebuild path from theme runtime
* Text domain and install folder `walkridge`

== Developer notes ==

Git is Sage 11 source (Blade, Vite 8, Acorn). Store and host zips already include compiled assets — do not run Composer or npm on the client host.

Local clone checklist: https://github.com/matthummel-pa/wp-walkridge/blob/main/README.md

Do not rename the install folder (Vite `base` depends on `walkridge`). Version in style.css must match this Stable tag.

== Documentation ==

Source hub: `docs/marketplace/` (seller pack copies it to Documentation/):

* index.html — contents and screenshots (start here on ThemeForest)
* buyer-guide.html — install, WooCommerce, Customizer, menus, pages, child theme, updates, FAQ
* branding.html — compass mark, slate/gold/parchment palette
* customizer.html, faq.html, requirements.html, support.html, sources.html, changelog.html
* screenshots/ — item images
* assets/ — docs CSS

GitHub: README.md, SUPPORT.md, docs/marketplace/. Live site: https://walkridge.matthummel.com/

== Resources ==

* Archivo Black, SIL Open Font License, self-hosted in resources/fonts/
* Atkinson Hyperlegible, SIL Open Font License, self-hosted
* IBM Plex Mono, SIL Open Font License, self-hosted
* Sage / Acorn, Roots, MIT, https://roots.io/sage/
* Gettysburg cannon photograph — National Archives / Wikimedia Commons (public domain / U.S. government work), bundled as public/images/gettysburg-cannon.jpg
* Wentz farm photograph — Wikimedia Commons (public domain / U.S. government work), bundled as public/images/wentz-farm.jpg
* Downtown Gettysburg photograph — Wikimedia Commons (public domain / U.S. government work), bundled as public/images/downtown-gettysburg.jpg
* Compass brand mark SVG language — Matt Hummel, GPLv2 or later
* WooCommerce — Automattic, GPLv3 (optional dependency; not bundled)
