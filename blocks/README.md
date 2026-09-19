# Walkridge block package

Theme folder `blocks/` is the Gutenberg collection shipped with Walkridge. Each subfolder is one block:

- `block.json` — name, title, description, category `walkridge`, keywords, inserter example
- `README.md` — short pointer to the SOP

PHP registers every folder with `register_block_type( $theme/blocks/{slug} )`. The editor script (`resources/js/blocks/index.js`) registers the same names and adds inspector UI. `registerBlockCollection( 'walkridge' )` groups them in the block inserter as **Walkridge**.

Do not move these folders out of the theme. They are not a separate plugin.

Operator steps for every block: [docs/blocks/README.md](../docs/blocks/README.md).
