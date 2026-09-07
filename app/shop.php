<?php

declare(strict_types=1);

/**
 * Shop-page enhancements.
 *
 * - JSON-LD ItemList schema for the shop archive
 * - Products per page / column count adjustments
 * - Default sort order on the shop archive
 */

namespace App;

defined('ABSPATH') || exit;

/* ── JSON-LD: ItemList (shop archive only) ───────────────────────────────── */

add_action('wp_head', function (): void {
    if (! function_exists('is_shop') || ! is_shop()) {
        return;
    }

    $products = wc_get_products([
        'status'  => 'publish',
        'limit'   => 20,
        'orderby' => 'menu_order',
        'order'   => 'ASC',
        'return'  => 'objects',
    ]);

    if (empty($products)) {
        return;
    }

    $items = [];
    foreach ($products as $i => $product) {
        if (! $product instanceof \WC_Product) {
            continue;
        }

        $desc = wp_strip_all_tags($product->get_short_description());
        if ($desc === '') {
            $desc = wp_strip_all_tags($product->get_description());
        }

        $offer = [
            '@type'         => 'Offer',
            'priceCurrency' => get_woocommerce_currency(),
            'availability'  => $product->is_in_stock()
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'url'           => get_permalink($product->get_id()),
        ];

        $price = $product->get_price();
        if ($price !== '') {
            $offer['price'] = (float) $price;
        }

        $item = [
            '@type'       => 'Product',
            'name'        => $product->get_name(),
            'url'         => get_permalink($product->get_id()),
            'description' => $desc,
            'offers'      => $offer,
        ];

        $image_id = $product->get_image_id();
        if ($image_id) {
            $src = wp_get_attachment_image_url($image_id, 'medium_large');
            if ($src) {
                $item['image'] = $src;
            }
        }

        $items[] = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'item'     => $item,
        ];
    }

    if (empty($items)) {
        return;
    }

    $schema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => __('Gettysburg Battlefield Tours', 'walkridge'),
        'description'     => __('Licensed-guide Gettysburg battlefield tours — walking, bus, lantern, and private sunrise experiences. Small groups, expert guides, easy online booking.', 'walkridge'),
        'url'             => (string) wc_get_page_permalink('shop'),
        'numberOfItems'   => count($items),
        'itemListElement' => $items,
    ];

    echo '<script type="application/ld+json">'
        . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        . '</script>' . "\n";
}, 5);

/* ── JSON-LD: LocalBusiness on shop page ────────────────────────────────── */

add_action('wp_head', function (): void {
    if (! function_exists('is_shop') || ! is_shop()) {
        return;
    }

    $schema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'TouristInformationCenter',
        'name'            => get_bloginfo('name'),
        'description'     => __('Licensed battlefield guides offering walking, bus, lantern, and private sunrise tours of the Gettysburg battlefield.', 'walkridge'),
        'url'             => home_url('/'),
        'address'         => [
            '@type'           => 'PostalAddress',
            'addressLocality' => 'Gettysburg',
            'addressRegion'   => 'PA',
            'addressCountry'  => 'US',
        ],
        'geo' => [
            '@type'     => 'GeoCoordinates',
            'latitude'  => 39.8309,
            'longitude' => -77.2311,
        ],
        'hasOfferCatalog' => [
            '@type' => 'OfferCatalog',
            'name'  => __('Gettysburg Battlefield Tour Experiences', 'walkridge'),
            'url'   => (string) wc_get_page_permalink('shop'),
        ],
    ];

    echo '<script type="application/ld+json">'
        . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        . '</script>' . "\n";
}, 6);

/* ── Products per page ───────────────────────────────────────────────────── */

add_filter('loop_shop_per_page', fn(): int => 12);

/* ── Column count (CSS does the real grid; this keeps WC li class sane) ─── */

add_filter('loop_shop_columns', fn(): int => 3);

/* ── Default sort: price low→high on the main shop archive ──────────────── */

add_filter('woocommerce_default_catalog_orderby', function (string $sort): string {
    if (function_exists('is_shop') && is_shop()) {
        return 'price';
    }

    return $sort;
});

/* ── Remove the default WC page title on archive (Blade adds a richer one) ─ */

add_filter('woocommerce_show_page_title', function (bool $show): bool {
    if (function_exists('is_shop') && is_shop()) {
        return false;
    }

    return $show;
});
