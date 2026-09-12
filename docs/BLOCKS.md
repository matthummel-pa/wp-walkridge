# Walkridge Gutenberg blocks

All marketing pages render `the_content()`. Seed from **Appearance → Theme Settings → Advanced** or **Tools → Walkridge Blocks**.

## Category `walkridge`

| Block | Slug | Page role |
|---|---|---|
| Home Hero | `walkridge/home-hero` | Home |
| Info Strip | `walkridge/info-strip` | All concept pages |
| About Split | `walkridge/about-split` | Home, Guides |
| Pathway Cards | `walkridge/pathway-cards` | Home, Area |
| Tour Grid | `walkridge/tour-grid` | Home, Tours |
| Book Band | `walkridge/book-band` | Home, Tours, Guides |
| Page Intro | `walkridge/page-intro` | Inner pages |
| Section Heading | `walkridge/section-heading` | Any |
| CTA Band | `walkridge/cta-band` | Area and CTAs |
| Contact Desk | `walkridge/contact-desk` | Contact form + NAP |
| FAQ List | `walkridge/faq-list` | Contact |
| Guide Roster | `walkridge/guide-roster` | Guides |
| Area Facts | `walkridge/area-facts` | Area |
| Custom | `walkridge/custom` | Block Generator |

## Seeded layouts

- **Home** — hero, info strip, about, pathways, featured tour grid, book band
- **Tours** — intro, info strip, tour grid, book band
- **Guides** — intro, info strip, roster, about split, book band
- **Area** — intro, info strip, facts, pathways, CTA
- **Contact** — intro, info strip, contact desk, FAQ
- **Refund Policy** — intro + a core paragraph you replace

FAQ and roster items use one line per entry: `Label | Detail` (guides add a third `| Bio` column).

Blade templates only supply breadcrumbs and `the_content()`. Do not put page bodies back into `page-*.blade.php`.
