# Marketplace pack

Walkridge listing notes for ThemeForest / your own store. Buyer-facing install and troubleshooting live in [SUPPORT.md](../../SUPPORT.md). Pitch and screenshots: [README.md](../../README.md).

## What to ship

| Piece | Where |
|---|---|
| Inner theme zip | `bin/build-theme-zip.sh` → `dist-theme/walkridge.zip` (**do not commit** to `main`) |
| Child theme | `child-theme/` |
| Documentation | Copy `README.md`, `SUPPORT.md`, and this folder into `Documentation/` of the outer pack |
| Screenshots | [`screenshots/`](screenshots/) — live concept, 1440 desktop, 390 mobile, 1200×900 store crops |
| Compass mark | [`../brand/walkridge-mark.svg`](../brand/walkridge-mark.svg) |

Do not put `SELLING.md` or this developer hub inside the **inner** theme zip. Do not bundle WooCommerce, Bookings, or Field Map inside `walkridge.zip`.

## Screenshots

Captured from https://walkridge.matthummel.com/ (theme **1.7.2**). LiteSpeed combined CSS 404s in some headless clients; shots use the live Vite `app-*.css` plus live HTML. Portfolio write-up: [matthummel.com/projects/walkridge](https://matthummel.com/projects/walkridge/).

| File | Viewport | Route |
|---|---|---|
| `home-desktop.png` | ~1440×900 | `/` |
| `home-mobile.png` | ~390×844 | `/` |
| `home-store-1200x900.png` | 1200×900 | `/` |
| `tours-desktop.png` / `tours-mobile.png` / `tours-store-1200x900.png` | | `/tours/` |
| `shop-desktop.png` / `shop-mobile.png` / `shop-store-1200x900.png` | | `/shop/` |
| `product-desktop.png` / `product-mobile.png` / `product-store-1200x900.png` | | walking-tour product |
| `guides-*` | | `/guides/` |
| `area-*` | | `/area/` |
| `contact-*` | | `/contact/` |
| `cart-*` | | `/cart/` (empty Woo cart) |

README embeds a subset from [`../readme/`](../readme/).

## Honesty for the listing

- Concept demo. `555` phones, `@walkridge.test`.
- Sample reviews on the live site are labeled as sample. Do not invent star ratings.
- Sage + Acorn is a ThemeForest / own-store product. WordPress.org is a later lite listing, not a promised first-pass approval.
