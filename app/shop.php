<?php

declare(strict_types=1);

/**
 * Shop-page layout helpers.
 *
 * Keeps 3-column grid and 12 products per page.
 * Schema and heavy SEO are not needed for walkridge at this stage.
 */

namespace App;

defined('ABSPATH') || exit;

/* Products per page */
add_filter('loop_shop_per_page', fn(): int => 12);

/* Column count — CSS Grid does the real work; this keeps WC li classes sane */
add_filter('loop_shop_columns', fn(): int => 3);

add_filter('woocommerce_output_related_products_args', static function (array $args): array {
    $args['posts_per_page'] = 3;
    $args['columns'] = 3;

    return $args;
});

add_filter('woocommerce_product_related_products_heading', static fn (): string => __('More tours to walk', 'walkridge'));

/**
 * Keep checkout / account field labels visible (never screen-reader-only).
 *
 * @param  array<string, mixed>  $args
 * @return array<string, mixed>
 */
add_filter('woocommerce_form_field_args', static function (array $args): array {
    $classes = array_values(array_filter(
        (array) ($args['label_class'] ?? []),
        static fn ($class): bool => $class !== 'screen-reader-text'
    ));
    $args['label_class'] = $classes;

    return $args;
});

add_action('woocommerce_before_quantity_input_field', static function (): void {
    echo '<span class="wr-qty-label">'.esc_html__('Quantity', 'walkridge').'</span>';
});

add_filter('woocommerce_add_to_cart_fragments', static function (array $fragments): array {
    $count = (function_exists('WC') && WC()->cart) ? (int) WC()->cart->get_cart_contents_count() : 0;
    $fragments['span[data-wr-cart-count]'] = '<span class="header-cart__count" data-wr-cart-count>'.esc_html((string) $count).'</span>';

    return $fragments;
});
