# Update Theme

**Appearance → Update Theme** is the install screen (Acreline-style). It is not Theme Settings.

## What you see

- Theme name and version
- Whether `public/build/manifest.json` is present
- Last recorded rebuild / git hash when available

## How to update

1. Download a production `walkridge.zip` (compiled assets + `vendor`).
2. Open **Appearance → Update Theme**.
3. Use **Rebuild & Install from ZIP**, or configure GitHub (dev) and **Update from Repo**.

The installer overwrites the active theme in place via `Theme_Upgrader`. Keep the folder named `walkridge`.

Old `themes.php?page=hg-update-theme` URLs redirect here.

## Source clones

Git pulls still need `composer install --no-dev` and `npm run build` on a machine with Node. Do not run that on shared hosting for marketplace zips.
