<?php

/**
 * WooCommerce template.
 *
 * WooCommerce locates a physical `woocommerce.php` in the theme root via
 * locate_template(). Sage's templates are Blade files under resources/views,
 * so this thin shim renders the `woocommerce` Blade view (which extends the
 * app layout and calls woocommerce_content()) to keep shop, product, cart,
 * and checkout pages inside the theme layout.
 */
echo \Roots\view('woocommerce')->render();
