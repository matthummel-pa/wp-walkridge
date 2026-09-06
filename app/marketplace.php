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

add_action('admin_menu', function (): void {
    add_theme_page(
        __('Walkridge Setup', 'walkridge'),
        __('Theme Setup', 'walkridge'),
        'edit_theme_options',
        'hg-setup',
        __NAMESPACE__.'\\wr_render_setup_page'
    );
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
    $identityUrl = add_query_arg('autofocus[section]', 'wr_identity', $customizer);
    if (! function_exists('is_plugin_active')) {
        require_once ABSPATH.'wp-admin/includes/plugin.php';
    }
    $bookingsActive = is_plugin_active('walkridge-bookings/walkridge-bookings.php');

    echo '<div class="wrap">';
    echo '<h1>'.esc_html__('Walkridge Setup', 'walkridge').'</h1>';
    echo '<p style="max-width:70ch">'.esc_html__('Buyer checklist — walk these once after you activate the theme. No upsells.', 'walkridge').'</p>';
    echo '<ol style="max-width:70ch;line-height:1.7">';
    echo '<li><a href="'.esc_url($identityUrl).'">'.esc_html__('Identity', 'walkridge').'</a> — '.esc_html__('Brand name, phone, email, address, hours, and header button.', 'walkridge').'</li>';
    echo '<li><a href="'.esc_url($customizer).'">'.esc_html__('Site Identity / Logo', 'walkridge').'</a> — '.esc_html__('Upload a logo under Site Identity. Header falls back to the compass mark.', 'walkridge').'</li>';
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
    echo '<li>'.esc_html__('Pages', 'walkridge').' — '.esc_html__('Publish Pages with slugs tours, guides, area, contact, and refund-policy so the Blade templates attach.', 'walkridge').'</li>';
    echo '</ol>';
    echo '<p class="description" style="max-width:70ch">'.esc_html__('Turn off the concept demo badge and author credit under Customize → Identity before you show this to a client.', 'walkridge').' ';
    echo esc_html__('Current brand:', 'walkridge').' <strong>'.esc_html(Identity::brandName()).'</strong></p>';
    echo '</div>';
}
