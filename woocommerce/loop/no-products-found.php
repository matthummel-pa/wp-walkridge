<?php
/**
 * Empty shop — sell the tour desk instead of a blank Woo message.
 *
 * @package Walkridge
 */

defined('ABSPATH') || exit;

use App\Support\Identity;

$shop_url = Identity::shopUrl();
$tours_url = home_url('/tours');
$contact_url = home_url('/contact');
?>
<section class="wr-empty-shop" aria-labelledby="wr-empty-shop-heading">
  <p class="eyebrow"><?php esc_html_e('Book a Tour', 'walkridge'); ?></p>
  <h2 id="wr-empty-shop-heading"><?php esc_html_e('No tour products are listed yet.', 'walkridge'); ?></h2>
  <p>
    <?php esc_html_e('When WooCommerce is active, each walking, bus, and lantern tour becomes a product here. Until then, start from the Tours page or call the sample desk.', 'walkridge'); ?>
  </p>
  <ul class="wr-empty-shop__points">
    <li><?php esc_html_e('Small-group licensed-guide walks on Cemetery Ridge, Little Round Top, and the High Water Mark.', 'walkridge'); ?></li>
    <li><?php esc_html_e('ADA-accessible deluxe bus loop and a lantern walk downtown after dark.', 'walkridge'); ?></li>
    <li><?php esc_html_e('Sample checkout only — phones are 555 numbers, email is tours@walkridge.test.', 'walkridge'); ?></li>
  </ul>
  <p class="wr-empty-shop__actions">
    <a class="btn btn-primary" href="<?php echo esc_url($tours_url); ?>"><?php esc_html_e('See all tours', 'walkridge'); ?></a>
    <a class="btn btn-outline" href="<?php echo esc_url(Identity::phoneHref()); ?>"><?php echo esc_html(sprintf(/* translators: %s: phone */ __('Call %s', 'walkridge'), Identity::phone())); ?></a>
    <a class="btn btn-ghost" href="<?php echo esc_url($contact_url); ?>"><?php esc_html_e('Contact the desk', 'walkridge'); ?></a>
  </p>
  <?php if ($shop_url) { ?>
    <p class="nap-note"><?php esc_html_e('Shop permalink stays available for when products are published.', 'walkridge'); ?></p>
  <?php } ?>
</section>
