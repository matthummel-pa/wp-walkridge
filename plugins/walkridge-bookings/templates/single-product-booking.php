<?php
/**
 * Booking widget template — injected below the single product add-to-cart button
 * for any product with _wrb_enabled = 'yes'.
 *
 * Variables available:
 *   $product    WC_Product
 *   $product_id int
 */
defined('ABSPATH') || exit;
?>
<div class="hgb-booking-root" data-product-id="<?php echo esc_attr($product_id); ?>">
  <noscript>
    <?php esc_html_e('Please enable JavaScript to use the tour booking widget.', 'wr-bookings'); ?>
  </noscript>
</div>
