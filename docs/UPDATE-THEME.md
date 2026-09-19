# Update Theme

**Appearance → Update Theme** is the install screen (Acreline-style). It is not Theme Settings.

## What you see

- Theme name and version
- Whether `public/build/manifest.json` is present
- Last recorded rebuild / git hash when available
- Latest GitHub Release version, when the API is reachable

## How to update

WordPress also shows **Update now** on Appearance → Themes when a newer GitHub Release exists.

1. Publish a GitHub Release tagged `vX.Y.Z` with a compiled `walkridge.zip` asset (vendor + `public/build`).
2. On the site, open **Appearance → Update Theme**.
3. Click **Install latest GitHub Release**, or **Upload ZIP**.

The installer overwrites the active theme in place via `Theme_Upgrader`. Keep the folder named `walkridge`.

Old `themes.php?page=hg-update-theme` URLs redirect here.

## Source clones

**Install source zipball** (token required) is for development only. Git pulls still need `composer install --no-dev` and `npm run build` on a machine with Node. Do not run that on shared hosting for marketplace zips.
