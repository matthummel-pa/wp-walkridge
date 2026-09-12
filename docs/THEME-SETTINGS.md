# Theme Settings

**Appearance → Theme Settings** is the graphical front door for Walkridge identity.

## Why it exists

Customizer persists `theme_mod` values and offers live preview. Buyers should not have to learn it first. Theme Settings edits the same mods with visual cards:

- Brand (site title, office name, subtitle, tagline, logo)
- Contact desk (phone, email, address, hours)
- Header & booking (CTA + rails)
- Footer & social
- **Advanced settings** (collapsed): demo badge, author credit, gold accent override, Customizer deep links, block seeding, Update Theme

Saving here is the same as saving Identity in the Customizer. The two never fight.

## Logo

The logo picker writes WordPress `custom_logo`. Clearing it returns the compass mark.

## Accent color

Advanced → gold accent override (`wr_accent_color`) injects `--gold-500` / `--gold-600` when a valid hex is saved. Leave empty to keep the bundled slate/gold tokens.

## Block seed

Advanced can seed concept page layouts after save. That overwrites Home, Tours, Guides, Area, Contact, and Refund Policy with Walkridge Gutenberg blocks. Use **Tools → Walkridge Blocks** for migrate-only runs.
