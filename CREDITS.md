# Credits

Third-party resources bundled with the Walkridge theme.
All items are GPL-compatible (SIL OFL or public domain / U.S. government work).

## Typefaces

| Font | Author | License | Source |
|------|--------|---------|--------|
| Archivo Black | Omnibus-Type | [SIL OFL 1.1](https://openfontlicense.org/) | https://github.com/Omnibus-Type/Archivo |
| Atkinson Hyperlegible | Braille Institute of America | [SIL OFL 1.1](https://openfontlicense.org/) | https://brailleinstitute.org/freefont |
| IBM Plex Mono | IBM Corp. | [SIL OFL 1.1](https://openfontlicense.org/) | https://github.com/IBM/plex |

License text: `resources/fonts/OFL.txt`

## Images

| File | Description | Source | License |
|------|-------------|--------|---------|
| `public/images/gettysburg-cannon.jpg` | Gettysburg cannon | National Archives / Wikimedia Commons | Public domain (U.S. government work) |
| `public/images/wentz-farm.jpg` | Wentz farmstead | Wikimedia Commons | Public domain (U.S. government work) |
| `public/images/downtown-gettysburg.jpg` | Downtown Gettysburg | Wikimedia Commons | Public domain (U.S. government work) |

## Framework & Libraries

| Package | Author | License | Source |
|---------|--------|---------|--------|
| Sage 11 (theme scaffold) | Roots | MIT | https://roots.io/sage/ |
| Acorn (WP Illuminate container) | Roots | MIT | https://roots.io/acorn/ |
| Tailwind CSS | Tailwind Labs | MIT | https://tailwindcss.com/ |
| Vite | Evan You et al. | MIT | https://vitejs.dev/ |

> MIT-licensed framework code ships in `vendor/` and `node_modules/` during
> development only. The distributable theme zip (built by `bin/build-theme-zip.sh`)
> includes only production Composer dependencies under `vendor/`; Node packages
> are not bundled. MIT and SIL OFL are both GPL-compatible.

## Compass brand mark

Inline SVG in `resources/views/sections/header.blade.php` and
`resources/views/sections/footer.blade.php`. Author: Matt Hummel.
Licensed: GNU General Public License v2 or later (same as the theme).

## WooCommerce (optional)

Not bundled. Installed separately by the site owner.
Author: Automattic. License: GPLv3.
