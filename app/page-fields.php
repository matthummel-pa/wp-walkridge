<?php

/**
 * Native custom fields for concept marketing pages (no ACF required).
 * Page copy is seeded as Gutenberg blocks; this file only creates pages and menus.
 */

namespace App;

use App\Support\BlockMigration;
use App\Support\PageFields;

/**
 * Ensure concept pages + nav menus exist (idempotent).
 */
function wr_ensure_concept_pages_and_menus(): void
{
    $pages = [
        'home' => __('Home', 'walkridge'),
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
    }

    if (! empty($ids['home'])) {
        $frontId = (int) get_option('page_on_front');
        if ($frontId <= 0) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $ids['home']);
        }
    }

    foreach ($ids as $slug => $id) {
        $defaults = PageFields::defaultsForSlug($slug);
        $post = get_post($id);
        $content = is_string($post->post_content ?? null) ? trim((string) $post->post_content) : '';
        if ($content === '' || ! str_contains($content, '<!-- wp:walkridge/')) {
            $layoutSlug = $slug === 'home' ? 'home' : $slug;
            wp_update_post([
                'ID' => $id,
                'post_content' => BlockMigration::buildContentForSlug($layoutSlug, $defaults),
            ]);
            BlockMigration::markMigrated($id);
        }
        BlockMigration::deleteLegacyPageMeta($id);
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
    wr_ensure_concept_pages_and_menus();
    if (get_option('wr_demo_layouts_v4') === '1') {
        return;
    }
    BlockMigration::seedDemoPages();
    update_option('wr_demo_layouts_v4', '1', false);
    update_option('wr_demo_layouts_v3', '1', false);
    update_option('wr_demo_layouts_v2', '1', false);
    update_option('wr_pages_menus_seeded', '1', false);
});
