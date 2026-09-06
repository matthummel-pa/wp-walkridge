<?php

/**
 * Native custom fields for concept marketing pages (no ACF required).
 */

namespace App;

use App\Support\PageFields;

/**
 * Ensure concept pages + nav menus exist (idempotent).
 */
function wr_ensure_concept_pages_and_menus(): void
{
    $pages = [
        'tours' => __('Tours', 'walkridge'),
        'guides' => __('Our Guides', 'walkridge'),
        'area' => __('The Area', 'walkridge'),
        'contact' => __('Contact', 'walkridge'),
        'refund-policy' => __('Refund Policy', 'walkridge'),
    ];

    $ids = [];
    foreach ($pages as $slug => $title) {
        $existing = get_page_by_path($slug);
        if ($existing instanceof \WP_Post) {
            $ids[$slug] = (int) $existing->ID;
            if ($existing->post_status !== 'publish') {
                wp_update_post([
                    'ID' => $existing->ID,
                    'post_status' => 'publish',
                ]);
            }

            continue;
        }

        $id = wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => $title,
            'post_name' => $slug,
        ], true);

        if (! is_wp_error($id) && $id) {
            $ids[$slug] = (int) $id;
        }
    }

    if (! empty($ids['refund-policy'])) {
        $refundId = $ids['refund-policy'];
        update_option('woocommerce_refunds_page_id', $refundId);
        update_option('wr_refund_policy_page_id', $refundId);

        $refundDefaults = [
            'rp_effective_date' => 'September 2, 2026',
            'rp_store_name' => 'matthummel.com',
            'rp_store_url' => 'https://matthummel.com',
            'rp_contact_email' => 'hello@matthummel.com',
            'rp_refund_window_days' => '30',
            'rp_resolution_days' => '7',
            'rp_duplicate_days' => '7',
            'rp_response_days' => '2',
            'rp_payment_days_min' => '5',
            'rp_payment_days_max' => '10',
        ];
        foreach ($refundDefaults as $key => $value) {
            if (get_post_meta($refundId, $key, true) === '') {
                update_post_meta($refundId, $key, $value);
            }
        }
    }

    foreach ($ids as $slug => $id) {
        $defaults = PageFields::defaultsForSlug($slug);
        foreach (
            [
                PageFields::EYEBROW => $defaults['eyebrow'],
                PageFields::HEADING => $defaults['heading'],
                PageFields::INTRO => $defaults['intro'],
            ] as $key => $value
        ) {
            if ($value === '') {
                continue;
            }
            if (get_post_meta($id, $key, true) === '') {
                update_post_meta($id, $key, $value);
            }
        }
    }

    wr_ensure_nav_menu(
        'Walkridge Primary',
        'primary_navigation',
        [
            ['slug' => 'tours', 'title' => __('Tours', 'walkridge')],
            ['slug' => 'guides', 'title' => __('Guides', 'walkridge')],
            ['slug' => 'area', 'title' => __('The Area', 'walkridge')],
            ['slug' => 'contact', 'title' => __('Contact', 'walkridge')],
        ],
        $ids
    );

    wr_ensure_nav_menu(
        'Walkridge Footer',
        'footer_navigation',
        [
            ['slug' => 'tours', 'title' => __('All Tours', 'walkridge')],
            ['slug' => 'guides', 'title' => __('Our Guides', 'walkridge')],
            ['slug' => 'area', 'title' => __('The Area', 'walkridge')],
            ['slug' => 'contact', 'title' => __('Contact & FAQ', 'walkridge')],
            ['slug' => 'refund-policy', 'title' => __('Refund Policy', 'walkridge')],
        ],
        $ids
    );
}

/**
 * @param  list<array{slug: string, title: string}>  $items
 * @param  array<string, int>  $ids
 */
function wr_ensure_nav_menu(string $menuName, string $location, array $items, array $ids): void
{
    $menu = wp_get_nav_menu_object($menuName);
    if (! $menu) {
        $menuId = wp_create_nav_menu($menuName);
        if (is_wp_error($menuId)) {
            return;
        }
    } else {
        $menuId = (int) $menu->term_id;
    }

    $existing = wp_get_nav_menu_items($menuId);
    if (empty($existing)) {
        $position = 1;
        foreach ($items as $item) {
            $pageId = $ids[$item['slug']] ?? 0;
            if ($pageId <= 0) {
                continue;
            }
            wp_update_nav_menu_item($menuId, 0, [
                'menu-item-title' => $item['title'],
                'menu-item-object' => 'page',
                'menu-item-object-id' => $pageId,
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish',
                'menu-item-position' => $position++,
            ]);
        }
    }

    $locations = get_theme_mod('nav_menu_locations', []);
    if (! is_array($locations)) {
        $locations = [];
    }
    if (($locations[$location] ?? 0) != $menuId) {
        $locations[$location] = $menuId;
        set_theme_mod('nav_menu_locations', $locations);
    }
}

add_action('after_switch_theme', 'App\\wr_ensure_concept_pages_and_menus');
add_action('admin_init', function (): void {
    if (get_option('wr_pages_menus_seeded') === '1') {
        return;
    }
    wr_ensure_concept_pages_and_menus();
    update_option('wr_pages_menus_seeded', '1', false);
});
