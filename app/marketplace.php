<?php

/**
 * Marketplace chrome: menus, setup checklist.
 */

namespace App;

use App\Support\Identity;

add_filter('nav_menu_css_class', function (array $classes, $item): array {
    if (in_array('current-menu-item', $classes, true) || in_array('current-menu-ancestor', $classes, true)) {
        $classes[] = 'is-active';
    }

    return $classes;
}, 10, 2);

add_filter('nav_menu_link_attributes', function (array $atts, $item): array {
    if (! empty($item->current) || ! empty($item->current_item_ancestor)) {
        $atts['aria-current'] = 'page';
        $atts['class'] = trim(($atts['class'] ?? '').' is-active');
    }

    return $atts;
}, 10, 2);

/**
 * Keep the sticky header labels short when WordPress filled them from long page titles.
 */
add_filter('nav_menu_item_title', function (string $title, $item, $args): string {
    if (($args->theme_location ?? '') !== 'primary_navigation') {
        return $title;
    }
    if (($item->object ?? '') !== 'page' || empty($item->object_id)) {
        return $title;
    }
    $slug = (string) get_post_field('post_name', (int) $item->object_id);
    $short = [
        'tours' => __('Tours', 'walkridge'),
        'guides' => __('Guides', 'walkridge'),
        'area' => __('The Area', 'walkridge'),
        'contact' => __('Contact', 'walkridge'),
        'shop' => __('Shop', 'walkridge'),
    ];
    if (! isset($short[$slug])) {
        return $title;
    }
    $pageTitle = get_the_title((int) $item->object_id);
    $decode = static fn (string $value): string => html_entity_decode(
        wp_strip_all_tags($value),
        ENT_QUOTES | ENT_HTML5,
        'UTF-8'
    );
    if ($decode($title) === $decode((string) $pageTitle)) {
        return $short[$slug];
    }

    return $title;
}, 10, 3);

add_action('admin_menu', function (): void {
    add_theme_page(
        __('Walkridge Setup', 'walkridge'),
        __('Theme Setup', 'walkridge'),
        'edit_theme_options',
        'wr-setup',
        __NAMESPACE__.'\\wr_render_setup_page'
    );
});

add_action('admin_init', function (): void {
    $page = isset($_GET['page']) ? sanitize_key(wp_unslash((string) $_GET['page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only redirect of a legacy menu slug
    if (($GLOBALS['pagenow'] ?? '') === 'themes.php' && $page === 'hg-setup') {
        wp_safe_redirect(admin_url('themes.php?page=wr-setup'));
        exit;
    }
});

function wr_render_setup_page(): void
{
    if (! current_user_can('edit_theme_options')) {
        wp_die(esc_html__('You do not have permission to edit theme options.', 'walkridge'));
    }

    $menus = get_nav_menu_locations();
    $hasPrimary = ! empty($menus['primary_navigation']);
    $hasFooter = ! empty($menus['footer_navigation']);
    $customizer = admin_url('customize.php');
    $menusUrl = admin_url('nav-menus.php');
    $identityUrl = admin_url('themes.php?page=wr-theme-settings');
    $updateUrl = admin_url('themes.php?page=wr-update-theme');
    $blocksUrl = admin_url('tools.php?page=wr-blocks');
    if (! function_exists('is_plugin_active')) {
        require_once ABSPATH.'wp-admin/includes/plugin.php';
    }
    $bookingsActive = is_plugin_active('walkridge-bookings/walkridge-bookings.php');

    echo '<div class="wrap">';
    echo '<h1>'.esc_html__('Walkridge Setup', 'walkridge').'</h1>';
    echo '<p style="max-width:70ch">'.esc_html__('Buyer checklist — walk these once after you activate the theme. No upsells.', 'walkridge').'</p>';
    echo '<ol style="max-width:70ch;line-height:1.7">';
    echo '<li><a href="'.esc_url($identityUrl).'">'.esc_html__('Theme Settings', 'walkridge').'</a> — '.esc_html__('Graphical identity, contact desk, header, footer, and Advanced controls.', 'walkridge').'</li>';
    echo '<li><a href="'.esc_url($customizer).'">'.esc_html__('Customizer (advanced)', 'walkridge').'</a> — '.esc_html__('Live preview for the same identity mods. Logo also lives under Site Identity.', 'walkridge').'</li>';
    echo '<li><a href="'.esc_url($updateUrl).'">'.esc_html__('Update Theme', 'walkridge').'</a> — '.esc_html__('Install a production zip or pull from GitHub.', 'walkridge').'</li>';
    echo '<li><a href="'.esc_url($blocksUrl).'">'.esc_html__('Walkridge Blocks', 'walkridge').'</a> — '.esc_html__('Seed Home, Tours, Guides, Area, Contact, and Refund Policy as Gutenberg layouts.', 'walkridge').'</li>';
    echo '<li><a href="'.esc_url($menusUrl).'">'.esc_html__('Menus', 'walkridge').'</a> — ';
    echo ($hasPrimary && $hasFooter)
        ? esc_html__('Primary and Footer menus are assigned.', 'walkridge')
        : esc_html__('Assign Primary and Footer menus (Appearance → Menus).', 'walkridge');
    echo '</li>';
    echo '<li>'.esc_html__('WooCommerce', 'walkridge').' — '.esc_html__('Install and activate WooCommerce, create tour products, and set the Shop page. Booking CTAs point at the shop.', 'walkridge').'</li>';
    echo '<li>'.esc_html__('Companion plugins', 'walkridge').' — ';
    echo $bookingsActive
        ? esc_html__('Walkridge Bookings is active.', 'walkridge')
        : esc_html__('Install Walkridge Bookings (and optional Field Map) from the marketplace pack for date/slot checkout and the interactive map.', 'walkridge');
    echo '</li>';
    echo '<li>'.esc_html__('Pages', 'walkridge').' — '.esc_html__('Publish Pages with slugs tours, guides, area, contact, and refund-policy, then seed Walkridge Gutenberg blocks.', 'walkridge').'</li>';
    echo '</ol>';
    echo '<p class="description" style="max-width:70ch">'.esc_html__('Turn off the concept demo badge and author credit under Customize → Identity before you show this to a client.', 'walkridge').' ';
    echo esc_html__('Current brand:', 'walkridge').' <strong>'.esc_html(Identity::brandName()).'</strong></p>';
    echo '</div>';
}
