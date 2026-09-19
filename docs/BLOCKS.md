# Walkridge Gutenberg blocks

All marketing pages render `the_content()`. Seed from **Appearance → Theme Settings → Advanced** or **Tools → Walkridge Blocks**.

## Category `walkridge`

| Block | Slug | Page role |
|---|---|---|
| Home Hero | `walkridge/home-hero` | Home — stats, path cards, marquee |
| Page Intro | `walkridge/page-intro` | Inner pages |
| Info Strip | `walkridge/info-strip` | All concept pages |
| About Split | `walkridge/about-split` | Home, Guides |
| Pathway Cards | `walkridge/pathway-cards` | Home |
| Timeline | `walkridge/timeline` | Home — three battle days |
| Tour Grid | `walkridge/tour-grid` | Home, Tours |
| Card Grid | `walkridge/card-grid` | Expect / offer cards |
| Guest Reviews | `walkridge/reviews` | Home, Guides |
| Journal Cards | `walkridge/journal-cards` | Home field notes |
| Book Band | `walkridge/book-band` | Home, Tours, Guides, Contact |
| Copy Section | `walkridge/copy-section` | Area narrative |
| Refund Policy | `walkridge/refund-policy` | Refund page — dates, windows, contact |
| Area Map | `walkridge/area-map` | Area — photo pin, embed, or Field Map |
| Area Facts | `walkridge/area-facts` | Area (text-only facts) |
| Town Grid | `walkridge/town-grid` | Area towns |
| CTA Band | `walkridge/cta-band` | Area |
| Contact Desk | `walkridge/contact-desk` | Contact |
| FAQ List | `walkridge/faq-list` | Contact |
| Guide Roster | `walkridge/guide-roster` | Guides |

## Seeded layouts

- **Home** — hero (paths, stats, marquee), info strip, about, pathways, three-day timeline, featured tours, what to expect, groups/gifts/ADA, book band, reviews, field notes
- **Tours** — intro, info strip, full tour grid + compare, expect cards, book band
- **Guides** — intro, info strip, roster, about split, reviews, book band
- **Area** — intro, info strip, battlefield copy, ridge cards, area map (meeting cards + pin), nearby towns, CTA
- **Contact** — intro, info strip, gifts/groups, contact desk, full FAQ, book band
- **Refund Policy** — intro + Refund Policy block (windows and contact in the block sidebar)

Empty concept pages are filled automatically. **Tools → Walkridge Blocks → Seed demo block layouts** overwrites those pages with the full restored copy.

FAQ, roster, timeline, cards, and reviews use one line per entry with `|` columns.

Do not add page custom fields for marketing copy. Theme Settings / Customizer remain for office identity (phone, hours, logo).

Blade templates only supply breadcrumbs and `the_content()`. Do not put page bodies back into `page-*.blade.php`.
