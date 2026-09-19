<?php

namespace App\Support;

/**
 * WooCommerce tour catalog for homepage / Tours page grids.
 */
class Tours
{
    /**
     * Concept defaults keyed by product slug (used when WC meta is empty).
     *
     * @return array<string, array<string, mixed>>
     */
    public static function defaults(): array
    {
        return [
            'battlefield-highlights-walking-tour' => [
                'title' => __('Battlefield Highlights Walking Tour', 'walkridge'),
                'sku' => 'WR-WALK-HIGHLIGHTS',
                'price' => '38',
                'category' => 'historical',
                'banner' => 'tb-1',
                'kicker' => __('Historical', 'walkridge'),
                'duration' => __('2 hours', 'walkridge'),
                'capacity' => __('Up to 15 guests', 'walkridge'),
                'difficulty' => __('Moderate · 2 mi walking', 'walkridge'),
                'difficulty_class' => 'chip-moderate',
                'lantern' => false,
                'excerpt' => __('A guided walk across the main ridges covering the turning points of all three days of the battle.', 'walkridge'),
                'description' => __('<p>A guided walk across Cemetery Ridge and McPherson Ridge covering the turning points of all three days of July 1863. Led by a licensed battlefield guide. Sample desk only — tours@walkridge.test or (717) 555-0100.</p>', 'walkridge'),
            ],
            'picketts-charge-deluxe-bus-tour' => [
                'title' => __('Deluxe Battlefield Bus Tour', 'walkridge'),
                'sku' => 'WR-BUS-DELUXE',
                'price' => '65',
                'category' => 'historical',
                'banner' => 'tb-2',
                'kicker' => __('Historical · ADA', 'walkridge'),
                'duration' => __('3.5 hours', 'walkridge'),
                'capacity' => __('Up to 24 guests', 'walkridge'),
                'difficulty' => __('Easy · Seated, ADA accessible', 'walkridge'),
                'difficulty_class' => 'chip-easy',
                'lantern' => false,
                'excerpt' => __('A narrated motorcoach loop of the full battlefield with two guided stops, including the site of the final assault.', 'walkridge'),
                'description' => __('<p>A narrated motorcoach loop of the full battlefield with two guided stops, including the High Water Mark. Seated and ADA accessible. Sample desk only — tours@walkridge.test or (717) 555-0100.</p>', 'walkridge'),
            ],
            'little-round-top-devils-den-hike' => [
                'title' => __('Ridge & Ravine Battlefield Hike', 'walkridge'),
                'sku' => 'WR-HIKE-RIDGE',
                'price' => '44',
                'category' => 'historical',
                'banner' => 'tb-3',
                'kicker' => __('Historical', 'walkridge'),
                'duration' => __('2.5 hours', 'walkridge'),
                'capacity' => __('Up to 12 guests', 'walkridge'),
                'difficulty' => __('Strenuous · Rocky, hills', 'walkridge'),
                'difficulty_class' => 'chip-strenuous',
                'lantern' => false,
                'excerpt' => __('A rugged hike to the most fought-over high ground of the second day, with time among the boulders below the ridge.', 'walkridge'),
                'description' => __('<p>A rugged hike to Little Round Top and the boulders of Devil’s Den — the most fought-over high ground of the second day. Sample desk only — tours@walkridge.test or (717) 555-0100.</p>', 'walkridge'),
            ],
            'ghosts-of-gettysburg-lantern-walk' => [
                'title' => __('After-Dark Lantern Walk', 'walkridge'),
                'sku' => 'WR-LANTERN-WALK',
                'price' => '28',
                'category' => 'after-dark',
                'banner' => 'tb-4',
                'kicker' => __('After dark', 'walkridge'),
                'duration' => __('90 minutes', 'walkridge'),
                'capacity' => __('Up to 20 guests', 'walkridge'),
                'difficulty' => __('Easy · Evening, level ground', 'walkridge'),
                'difficulty_class' => 'chip-easy',
                'lantern' => true,
                'excerpt' => __('An after-dark walking tour through the historic downtown, pairing real wartime accounts with candlelit storytelling.', 'walkridge'),
                'description' => __('<p>An after-dark walking tour through historic downtown Gettysburg, pairing wartime letters with candlelit storytelling. Not a haunted house. Sample desk only — tours@walkridge.test or (717) 555-0100.</p>', 'walkridge'),
            ],
            'sunrise-private-battlefield-experience' => [
                'title' => __('Sunrise Private Battlefield Experience', 'walkridge'),
                'sku' => 'WR-PRIVATE-SUNRISE',
                'price' => '89',
                'category' => 'historical',
                'banner' => 'tb-5',
                'kicker' => __('Historical · Private', 'walkridge'),
                'duration' => __('3 hours', 'walkridge'),
                'capacity' => __('Up to 6 guests', 'walkridge'),
                'difficulty' => __('Moderate · Customized route', 'walkridge'),
                'difficulty_class' => 'chip-moderate',
                'lantern' => false,
                'excerpt' => __('A private dawn tour with a senior guide, customized to your interests — for up to six guests.', 'walkridge'),
                'description' => __('<p>A private dawn tour with a senior guide, customized to your interests, for up to six guests. Sample desk only — tours@walkridge.test or (717) 555-0100.</p>', 'walkridge'),
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function all(?int $limit = null): array
    {
        $defaults = self::defaults();
        $tours = [];

        if (function_exists('wc_get_products')) {
            $products = wc_get_products([
                'status' => 'publish',
                'limit' => -1,
                'orderby' => 'menu_order',
                'order' => 'ASC',
                'return' => 'objects',
            ]);

            foreach ($products as $product) {
                if (! $product instanceof \WC_Product) {
                    continue;
                }
                $slug = $product->get_slug();
                $meta = $defaults[$slug] ?? [
                    'category' => 'historical',
                    'banner' => 'tb-1',
                    'kicker' => __('Tour', 'walkridge'),
                    'duration' => '',
                    'capacity' => '',
                    'difficulty' => '',
                    'difficulty_class' => 'chip-moderate',
                    'lantern' => false,
                    'excerpt' => '',
                ];

                $duration = (string) $product->get_meta('_wr_duration');
                $capacity = (string) $product->get_meta('_wr_capacity');
                $difficulty = (string) $product->get_meta('_wr_difficulty');
                $category = (string) $product->get_meta('_wr_category');
                $kicker = (string) $product->get_meta('_wr_kicker');

                $tours[] = [
                    'id' => $product->get_id(),
                    'slug' => $slug,
                    'title' => $product->get_name(),
                    'url' => $product->get_permalink(),
                    'price_html' => $product->get_price_html(),
                    'price' => (float) $product->get_price(),
                    'excerpt' => $product->get_short_description() !== ''
                        ? wp_strip_all_tags($product->get_short_description())
                        : ($meta['excerpt'] ?? ''),
                    'category' => $category !== '' ? $category : ($meta['category'] ?? 'historical'),
                    'banner' => $meta['banner'] ?? 'tb-1',
                    'kicker' => $kicker !== '' ? $kicker : ($meta['kicker'] ?? __('Tour', 'walkridge')),
                    'duration' => $duration !== '' ? $duration : ($meta['duration'] ?? ''),
                    'capacity' => $capacity !== '' ? $capacity : ($meta['capacity'] ?? ''),
                    'difficulty' => $difficulty !== '' ? $difficulty : ($meta['difficulty'] ?? ''),
                    'difficulty_class' => $meta['difficulty_class'] ?? 'chip-moderate',
                    'lantern' => ! empty($meta['lantern']) || (($category !== '' ? $category : ($meta['category'] ?? '')) === 'after-dark'),
                ];
            }
        }

        if ($tours === []) {
            foreach ($defaults as $slug => $meta) {
                $tours[] = [
                    'id' => 0,
                    'slug' => $slug,
                    'title' => (string) ($meta['title'] ?? ucwords(str_replace('-', ' ', $slug))),
                    'url' => Identity::productUrl($slug),
                    'price_html' => '',
                    'price' => (float) ($meta['price'] ?? 0),
                    'excerpt' => $meta['excerpt'],
                    'category' => $meta['category'],
                    'banner' => $meta['banner'],
                    'kicker' => $meta['kicker'],
                    'duration' => $meta['duration'],
                    'capacity' => $meta['capacity'],
                    'difficulty' => $meta['difficulty'],
                    'difficulty_class' => $meta['difficulty_class'],
                    'lantern' => $meta['lantern'],
                ];
            }
        }

        if ($limit !== null) {
            return array_slice($tours, 0, $limit);
        }

        return $tours;
    }

    /**
     * Pretty price for compare tables when price_html is empty.
     */
    public static function formatPrice(array $tour): string
    {
        if (! empty($tour['price_html'])) {
            return wp_strip_all_tags((string) $tour['price_html']);
        }
        if (! empty($tour['price'])) {
            return '$'.number_format((float) $tour['price'], 0);
        }

        return '';
    }

    /**
     * Create published simple products for each concept tour when WooCommerce is active.
     * Idempotent: skips slugs that already exist.
     */
    public static function ensureProducts(): void
    {
        if (! class_exists('WC_Product_Simple') || ! function_exists('wc_get_product_id_by_sku')) {
            return;
        }

        $cat = term_exists('tours', 'product_cat');
        if (! $cat) {
            $cat = wp_insert_term(
                __('Tours', 'walkridge'),
                'product_cat',
                [
                    'slug' => 'tours',
                    'description' => __('Licensed-guide battlefield tour experiences.', 'walkridge'),
                ]
            );
        }
        $catId = is_array($cat) ? (int) ($cat['term_id'] ?? 0) : 0;

        $order = 1;
        foreach (self::defaults() as $slug => $spec) {
            $existing = get_page_by_path($slug, OBJECT, 'product');
            if ($existing instanceof \WP_Post) {
                $order++;

                continue;
            }

            $product = new \WC_Product_Simple();
            $product->set_name((string) ($spec['title'] ?? $slug));
            $product->set_slug($slug);
            $product->set_status('publish');
            $product->set_catalog_visibility('visible');
            $product->set_virtual(true);
            $product->set_sold_individually(false);
            $product->set_regular_price((string) ($spec['price'] ?? ''));
            $product->set_short_description((string) ($spec['excerpt'] ?? ''));
            $product->set_description((string) ($spec['description'] ?? ''));
            $product->set_sku((string) ($spec['sku'] ?? ''));
            $product->set_menu_order($order);
            $product->set_stock_status('instock');
            $product->update_meta_data('_wr_duration', (string) ($spec['duration'] ?? ''));
            $product->update_meta_data('_wr_capacity', (string) ($spec['capacity'] ?? ''));
            $product->update_meta_data('_wr_difficulty', (string) ($spec['difficulty'] ?? ''));
            $product->update_meta_data('_wr_category', (string) ($spec['category'] ?? 'historical'));
            $product->update_meta_data('_wr_kicker', (string) ($spec['kicker'] ?? ''));

            $id = (int) $product->save();
            if ($id > 0 && $catId > 0) {
                wp_set_object_terms($id, [$catId], 'product_cat');
            }

            $order++;
        }
    }
}
