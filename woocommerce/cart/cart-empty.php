<?php
/**
 * Empty cart — point guests back to booking a tour.
 */
defined('ABSPATH') || exit;

use App\Support\Identity;

do_action('woocommerce_cart_is_empty');

$catalog_url = Identity::shopUrl();
$shop_url = function_exists('wc_get_page_permalink') ? (string) wc_get_page_permalink('shop') : '';
$show_shop = $shop_url !== '' && untrailingslashit($shop_url) !== untrailingslashit($catalog_url);
?>
<section class="wr-empty-shop wr-empty-cart" aria-labelledby="wr-empty-cart-heading">
  <p class="eyebrow"><?php esc_html_e('Your cart', 'walkridge'); ?></p>
  <h2 id="wr-empty-cart-heading"><?php esc_html_e('No tours in the cart yet.', 'walkridge'); ?></h2>
  <p>
    <?php esc_html_e('Pick a date on a tour product, or browse the five concept walks first. Checkout uses WooCommerce when the shop is active — Walkridge is not a payment processor by itself.', 'walkridge'); ?>
  </p>
  <p class="wr-empty-shop__actions">
    <a class="btn btn-primary" href="<?php echo esc_url($catalog_url); ?>"><?php esc_html_e('Browse tours', 'walkridge'); ?></a>
    <?php if ($show_shop) { ?>
      <a class="btn btn-outline" href="<?php echo esc_url($shop_url); ?>"><?php esc_html_e('Return to shop', 'walkridge'); ?></a>
    <?php } ?>
  </p>
</section>
