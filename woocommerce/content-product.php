<?php
/**
 * Walkridge product card — WooCommerce loop template override.
 * Overrides: woocommerce/templates/content-product.php
 *
 * @see     https://woo.com/document/template-structure/
 * @package WooCommerce/Templates
 */

defined('ABSPATH') || exit;

global $product;

if (empty($product) || ! $product->is_visible()) {
    return;
}

/* --- Pull tour meta (set via product edit screen or _wr_* fields) --- */
$duration   = (string) $product->get_meta('_wr_duration');
$capacity   = (string) $product->get_meta('_wr_capacity');
$difficulty = (string) $product->get_meta('_wr_difficulty');
$kicker     = (string) $product->get_meta('_wr_kicker');

/* Fallback: derive chips from short description when meta is empty */
$short_desc = wp_strip_all_tags($product->get_short_description());

/* Primary category label for the thumbnail badge */
$cats        = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'names']);
$primary_cat = (! is_wp_error($cats) && ! empty($cats)) ? reset($cats) : $kicker;

/* Difficulty → CSS modifier */
$diff_lower = strtolower($difficulty);
$diff_class = '';
if (str_contains($diff_lower, 'easy')) {
    $diff_class = 'chip--easy';
} elseif (str_contains($diff_lower, 'strenuous') || str_contains($diff_lower, 'hard')) {
    $diff_class = 'chip--strenuous';
} else {
    $diff_class = 'chip--moderate';
}
?>
<li <?php wc_product_class('woocommerce-loop-product', $product); ?>>

  <!-- Thumbnail -->
  <div class="wr-card__thumb">
    <?php if ($product->get_image_id()) : ?>
      <a href="<?php echo esc_url(get_permalink($product->get_id())); ?>" tabindex="-1" aria-hidden="true">
        <?php echo wp_kses_post($product->get_image('medium', ['class' => 'wr-card__img', 'loading' => 'lazy', 'alt' => ''])); ?>
      </a>
    <?php else : ?>
      <div class="wr-card__img-placeholder" aria-hidden="true">
        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
      </div>
    <?php endif; ?>

    <?php if ($primary_cat) : ?>
      <span class="wr-card__badge"><?php echo esc_html($primary_cat); ?></span>
    <?php endif; ?>
  </div>

  <!-- Card body -->
  <a href="<?php echo esc_url(get_permalink($product->get_id())); ?>"
     class="wr-card__body woocommerce-LoopProduct-link">

    <?php if ($duration || $capacity || $difficulty) : ?>
      <div class="wr-card__chips">
        <?php if ($duration) : ?>
          <span class="chip chip--time"><?php echo esc_html($duration); ?></span>
        <?php endif; ?>
        <?php if ($capacity) : ?>
          <span class="chip chip--group"><?php echo esc_html($capacity); ?></span>
        <?php endif; ?>
        <?php if ($difficulty) : ?>
          <span class="chip <?php echo esc_attr($diff_class); ?>"><?php echo esc_html($difficulty); ?></span>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <h2 class="woocommerce-loop-product__title"><?php the_title(); ?></h2>

    <?php if ($short_desc) : ?>
      <p class="wr-card__excerpt"><?php echo esc_html(wp_trim_words($short_desc, 22, '…')); ?></p>
    <?php endif; ?>

    <?php woocommerce_template_loop_price(); ?>

  </a>

  <!-- CTA -->
  <div class="wr-card__cta">
    <?php woocommerce_template_loop_add_to_cart(); ?>
  </div>

</li>
