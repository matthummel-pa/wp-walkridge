# Area Map

`walkridge/area-map` · packaged at `blocks/area-map/`

![ Area Map ](screenshots/area-map.png)

## When to use

Area page — meeting points, office hours, and a Gettysburg location map.

## How to insert

1. Inserter → **Walkridge** → **Area Map**.
2. Select the block. The canvas shows a live PHP preview.
3. **Location** — place the pin by **latitude & longitude** or by **ZIP / postal code** (default `17325`). ZIP lookups use OpenStreetMap Nominatim and are cached. Zoom applies to the embed.
4. **Map type** — Static photo + pin, Embedded OpenStreetMap (built from Location unless you paste a custom iframe URL), or the Field Map plugin.
5. **Layout** — map left / cards right, flipped, or stacked. **Icon color** uses theme tokens: gold (accent), lantern, parchment, or brick.
6. Meeting cards: one line per card, `Title | Text | pin|lantern|clock`.

## Images and media

Static: downtown / cannon / wentz or a custom upload, plus pin left/top %. Embed: coordinates, ZIP, or optional iframe URL. Field Map: Walkridge Field Map plugin; lat/lng/zoom are passed through.

## Do not

- Do not paste this layout into a classic HTML widget — it will skip block settings.
- Do not store this copy in page custom fields.

## Related

- Theme package: `blocks/area-map/block.json`
- Collection guide: [README](README.md)
