<?php

/**
 * Appearance → Theme Settings — graphical front door for identity.
 *
 * Writes the same theme_mods as Customize → Identity. Advanced controls
 * stay collapsed; Customizer remains the live-preview engine.
 */

namespace App;

use App\Support\BlockMigration;
use App\Support\Identity;

add_action('admin_menu', function (): void {
    add_theme_page(
        __('Theme Settings', 'walkridge'),
        __('Theme Settings', 'walkridge'),
        'edit_theme_options',
        'wr-theme-settings',
        __NAMESPACE__.'\\wr_render_theme_settings_page'
    );
});

add_action('admin_enqueue_scripts', function (string $hook): void {
    if ($hook !== 'appearance_page_wr-theme-settings' && $hook !== 'appearance_page_wr-update-theme' && $hook !== 'appearance_page_hg-update-theme') {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_style(
        'walkridge-admin-settings',
        get_theme_file_uri('resources/css/admin-settings.css'),
        [],
        wp_get_theme()->get('Version') ?: '1.2.0'
    );
    wp_enqueue_script(
        'walkridge-admin-settings',
        get_theme_file_uri('resources/js/admin-settings.js'),
        ['jquery'],
        wp_get_theme()->get('Version') ?: '1.2.0',
        true
    );
});

add_action('admin_post_wr_save_theme_settings', function (): void {
    if (! current_user_can('edit_theme_options')) {
        wp_die(esc_html__('You do not have permission to edit theme options.', 'walkridge'));
    }
    check_admin_referer('wr_save_theme_settings', 'wr_theme_settings_nonce');

    $text = [
        'wr_brand_name' => 'sanitize_text_field',
        'wr_brand_sub' => 'sanitize_text_field',
        'wr_tagline' => 'sanitize_text_field',
        'wr_phone' => 'sanitize_text_field',
        'wr_email' => 'sanitize_email',
        'wr_cta_label' => 'sanitize_text_field',
        'wr_cta_url' => 'esc_url_raw',
        'wr_rail_left' => 'sanitize_text_field',
        'wr_rail_right' => 'sanitize_text_field',
        'wr_credit_text' => 'sanitize_text_field',
        'wr_credit_url' => 'esc_url_raw',
        'wr_social_facebook' => 'esc_url_raw',
        'wr_social_instagram' => 'esc_url_raw',
        'wr_social_tripadvisor' => 'esc_url_raw',
        'wr_social_twitter' => 'sanitize_text_field',
        'wr_accent_color' => 'sanitize_hex_color',
    ];

    foreach ($text as $key => $callback) {
        if (! isset($_POST[$key])) {
            continue;
        }
        $raw = wp_unslash((string) $_POST[$key]);
        set_theme_mod($key, $callback($raw));
    }

    foreach (['wr_address', 'wr_hours', 'wr_footer_blurb'] as $key) {
        if (isset($_POST[$key])) {
            set_theme_mod($key, sanitize_textarea_field(wp_unslash((string) $_POST[$key])));
        }
    }

    set_theme_mod('wr_show_demo_chrome', ! empty($_POST['wr_show_demo_chrome']));
    set_theme_mod('wr_show_credit', ! empty($_POST['wr_show_credit']));

    if (isset($_POST['custom_logo'])) {
        $logoId = absint($_POST['custom_logo']);
        if ($logoId > 0) {
            set_theme_mod('custom_logo', $logoId);
        } elseif (isset($_POST['wr_clear_logo'])) {
            remove_theme_mod('custom_logo');
        }
    }

    if (! empty($_POST['blogname'])) {
        update_option('blogname', sanitize_text_field(wp_unslash((string) $_POST['blogname'])));
    }
    if (isset($_POST['blogdescription'])) {
        update_option('blogdescription', sanitize_text_field(wp_unslash((string) $_POST['blogdescription'])));
    }

    $redirect = ['page' => 'wr-theme-settings', 'wr_saved' => '1'];
    $seed = sanitize_key((string) ($_POST['wr_after_save'] ?? ''));
    if ($seed === 'seed_blocks' && current_user_can('manage_options')) {
        wr_ensure_concept_pages_and_menus();
        $result = BlockMigration::seedDemoPages();
        $redirect['wr_seeded'] = (string) (int) ($result['updated'] ?? 0);
    }

    wp_safe_redirect(add_query_arg($redirect, admin_url('themes.php')));
    exit;
});

add_action('wp_head', function (): void {
    $accent = sanitize_hex_color((string) get_theme_mod('wr_accent_color', ''));
    if (! $accent) {
        return;
    }
    echo '<style id="wr-accent-override">:root{--gold-600:'.esc_html($accent).';--gold-500:'.esc_html($accent).';}</style>'."\n";
}, 20);

function wr_render_theme_settings_page(): void
{
    if (! current_user_can('edit_theme_options')) {
        wp_die(esc_html__('You do not have permission to edit theme options.', 'walkridge'));
    }

    $theme = wp_get_theme();
    $logoId = (int) get_theme_mod('custom_logo');
    $logoSrc = $logoId ? wp_get_attachment_image_url($logoId, 'medium') : '';
    $accent = (string) get_theme_mod('wr_accent_color', '');
    $customizer = admin_url('customize.php');
    $identityUrl = add_query_arg('autofocus[section]', 'wr_identity', $customizer);
    $updateUrl = admin_url('themes.php?page=wr-update-theme');
    $blocksUrl = admin_url('tools.php?page=wr-blocks');
    $siteUrl = home_url('/');

    echo '<div class="wrap wr-settings">';
    if (isset($_GET['wr_saved'])) {
        echo '<div class="notice notice-success is-dismissible"><p>'.esc_html__('Theme settings saved. Identity, header, and footer now use these values.', 'walkridge').'</p></div>';
    }
    if (isset($_GET['wr_seeded'])) {
        echo '<div class="notice notice-success is-dismissible"><p>'.esc_html(sprintf(
            /* translators: %d: pages updated */
            __('Seeded Gutenberg layouts on %d page(s).', 'walkridge'),
            absint($_GET['wr_seeded'])
        )).'</p></div>';
    }

    echo '<header class="wr-settings__hero">';
    echo '<div class="wr-settings__hero-copy">';
    echo '<p class="wr-settings__kicker">'.esc_html__('Walkridge', 'walkridge').'</p>';
    echo '<h1>'.esc_html__('Theme Settings', 'walkridge').'</h1>';
    echo '<p>'.esc_html__('Graphical controls for the tour office. These write the same settings as Customize → Identity. Use Advanced only when you need Customizer live preview, block seeding, or a gold accent override.', 'walkridge').'</p>';
    echo '<p class="wr-settings__links">';
    echo '<a class="button button-secondary" href="'.esc_url($updateUrl).'">'.esc_html__('Update Theme', 'walkridge').'</a> ';
    echo '<a class="button button-secondary" href="'.esc_url($identityUrl).'">'.esc_html__('Open Customizer', 'walkridge').'</a> ';
    echo '<a class="button button-secondary" href="'.esc_url($siteUrl).'" target="_blank" rel="noopener noreferrer">'.esc_html__('View site', 'walkridge').'</a>';
    echo '</p>';
    echo '</div>';
    echo '<aside class="wr-settings__preview" aria-label="'.esc_attr__('Live identity preview', 'walkridge').'">';
    echo '<span class="wr-settings__preview-badge">v'.esc_html((string) ($theme->get('Version') ?: '1.2.0')).'</span>';
    if ($logoSrc) {
        echo '<img class="wr-settings__preview-logo" src="'.esc_url($logoSrc).'" alt="">';
    } else {
        echo '<span class="wr-settings__preview-mark" aria-hidden="true">+</span>';
    }
    echo '<strong>'.esc_html(Identity::brandName()).'</strong>';
    echo '<span>'.esc_html(Identity::brandSub()).'</span>';
    echo '<span>'.esc_html(Identity::phone()).'</span>';
    echo '</aside>';
    echo '</header>';

    echo '<form class="wr-settings__form" method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
    wp_nonce_field('wr_save_theme_settings', 'wr_theme_settings_nonce');
    echo '<input type="hidden" name="action" value="wr_save_theme_settings">';

    echo '<div class="wr-settings__grid">';

    wr_theme_settings_card(__('Brand', 'walkridge'), __('Name, subtitle, and the mark guests see in the header.', 'walkridge'), function () use ($logoId, $logoSrc): void {
        echo '<label class="wr-field"><span>'.esc_html__('WordPress site title', 'walkridge').'</span>';
        echo '<input type="text" name="blogname" value="'.esc_attr((string) get_option('blogname')).'"></label>';
        echo '<label class="wr-field"><span>'.esc_html__('WordPress tagline', 'walkridge').'</span>';
        echo '<input type="text" name="blogdescription" value="'.esc_attr((string) get_option('blogdescription')).'"></label>';
        wr_theme_settings_input('wr_brand_name', __('Office name', 'walkridge'), Identity::brandName());
        wr_theme_settings_input('wr_brand_sub', __('Subtitle', 'walkridge'), Identity::brandSub());
        wr_theme_settings_input('wr_tagline', __('Header tagline', 'walkridge'), Identity::tagline());
        echo '<div class="wr-logo-field">';
        echo '<span>'.esc_html__('Logo', 'walkridge').'</span>';
        echo '<input type="hidden" name="custom_logo" id="wr-logo-id" value="'.esc_attr((string) $logoId).'">';
        echo '<div class="wr-logo-field__preview">';
        if ($logoSrc) {
            echo '<img id="wr-logo-preview" src="'.esc_url($logoSrc).'" alt="">';
        } else {
            echo '<img id="wr-logo-preview" src="" alt="" hidden>';
        }
        echo '</div>';
        echo '<p class="wr-logo-field__actions">';
        echo '<button type="button" class="button" id="wr-logo-pick">'.esc_html__('Choose logo', 'walkridge').'</button> ';
        echo '<label class="wr-check"><input type="checkbox" name="wr_clear_logo" value="1"> '.esc_html__('Remove logo (compass mark returns)', 'walkridge').'</label>';
        echo '</p>';
        echo '</div>';
    });

    wr_theme_settings_card(__('Contact desk', 'walkridge'), __('Phone, email, address, and hours used by the info strip and contact block.', 'walkridge'), function (): void {
        wr_theme_settings_input('wr_phone', __('Phone', 'walkridge'), Identity::phone(), 'tel');
        wr_theme_settings_input('wr_email', __('Email', 'walkridge'), Identity::email(), 'email');
        echo '<label class="wr-field"><span>'.esc_html__('Address', 'walkridge').'</span>';
        echo '<textarea name="wr_address" rows="3">'.esc_textarea(Identity::address()).'</textarea></label>';
        echo '<label class="wr-field"><span>'.esc_html__('Hours', 'walkridge').'</span>';
        echo '<textarea name="wr_hours" rows="3">'.esc_textarea(Identity::hours()).'</textarea></label>';
    });

    wr_theme_settings_card(__('Header & booking', 'walkridge'), __('Primary CTA and the two-line rail above the navigation.', 'walkridge'), function (): void {
        wr_theme_settings_input('wr_cta_label', __('Header button label', 'walkridge'), Identity::ctaLabel());
        wr_theme_settings_input('wr_cta_url', __('Header button URL', 'walkridge'), (string) get_theme_mod('wr_cta_url', ''), 'url');
        wr_theme_settings_input('wr_rail_left', __('Header rail (left)', 'walkridge'), (string) get_theme_mod('wr_rail_left', ''));
        wr_theme_settings_input('wr_rail_right', __('Header rail (right)', 'walkridge'), (string) get_theme_mod('wr_rail_right', ''));
        echo '<p class="description">'.esc_html__('Leave the button URL empty to use the WooCommerce shop permalink.', 'walkridge').'</p>';
    });

    wr_theme_settings_card(__('Footer & social', 'walkridge'), __('Credit line, blurb, and profile URLs. Credit is removable for marketplace installs.', 'walkridge'), function (): void {
        echo '<label class="wr-field"><span>'.esc_html__('Footer blurb', 'walkridge').'</span>';
        echo '<textarea name="wr_footer_blurb" rows="3">'.esc_textarea((string) get_theme_mod('wr_footer_blurb', '')).'</textarea></label>';
        wr_theme_settings_input('wr_credit_text', __('Credit text', 'walkridge'), Identity::creditText());
        wr_theme_settings_input('wr_credit_url', __('Credit URL', 'walkridge'), Identity::creditUrl(), 'url');
        wr_theme_settings_input('wr_social_facebook', __('Facebook URL', 'walkridge'), Identity::socialFacebook(), 'url');
        wr_theme_settings_input('wr_social_instagram', __('Instagram URL', 'walkridge'), Identity::socialInstagram(), 'url');
        wr_theme_settings_input('wr_social_tripadvisor', __('TripAdvisor URL', 'walkridge'), Identity::socialTripadvisor(), 'url');
        wr_theme_settings_input('wr_social_twitter', __('X / Twitter handle', 'walkridge'), Identity::socialTwitter());
    });

    echo '</div>';

    echo '<details class="wr-settings__advanced">';
    echo '<summary>'.esc_html__('Advanced settings', 'walkridge').'</summary>';
    echo '<div class="wr-settings__advanced-grid">';

    echo '<section class="wr-card">';
    echo '<h2>'.esc_html__('Chrome & accent', 'walkridge').'</h2>';
    echo '<label class="wr-check"><input type="checkbox" name="wr_show_demo_chrome" value="1"'.checked(Identity::showDemoChrome(), true, false).'> '.esc_html__('Show concept demo badge', 'walkridge').'</label>';
    echo '<label class="wr-check"><input type="checkbox" name="wr_show_credit" value="1"'.checked(Identity::showCredit(), true, false).'> '.esc_html__('Show removable author credit', 'walkridge').'</label>';
    echo '<label class="wr-field"><span>'.esc_html__('Gold accent override', 'walkridge').'</span>';
    echo '<input type="text" name="wr_accent_color" value="'.esc_attr($accent).'" placeholder="#c4a35a" pattern="^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$">';
    echo '<span class="description">'.esc_html__('Optional hex. Empty keeps the bundled slate/gold tokens.', 'walkridge').'</span></label>';
    echo '</section>';

    echo '<section class="wr-card">';
    echo '<h2>'.esc_html__('Customizer live preview', 'walkridge').'</h2>';
    echo '<p>'.esc_html__('Theme Settings saves immediately. Use Customizer when you want a live canvas while you type.', 'walkridge').'</p>';
    echo '<ul class="wr-settings__deep">';
    echo '<li><a href="'.esc_url($identityUrl).'">'.esc_html__('Identity section', 'walkridge').'</a></li>';
    echo '<li><a href="'.esc_url(add_query_arg('autofocus[section]', 'title_tagline', $customizer)).'">'.esc_html__('Site Identity / logo', 'walkridge').'</a></li>';
    echo '<li><a href="'.esc_url(add_query_arg('autofocus[section]', 'wr_updates', $customizer)).'">'.esc_html__('Update Theme (Customizer card)', 'walkridge').'</a></li>';
    echo '<li><a href="'.esc_url(admin_url('nav-menus.php')).'">'.esc_html__('Menus', 'walkridge').'</a></li>';
    echo '</ul>';
    echo '</section>';

    echo '<section class="wr-card">';
    echo '<h2>'.esc_html__('Page blocks', 'walkridge').'</h2>';
    echo '<p>'.esc_html__('Home, Tours, Guides, Area, Contact, and Refund Policy are Gutenberg layouts made of Walkridge blocks. Seed overwrites those pages with the concept demo.', 'walkridge').'</p>';
    echo '<label class="wr-check"><input type="checkbox" name="wr_after_save" value="seed_blocks"> '.esc_html__('After save, seed demo block layouts on concept pages', 'walkridge').'</label>';
    echo '<p><a class="button" href="'.esc_url($blocksUrl).'">'.esc_html__('Open Walkridge Blocks tools', 'walkridge').'</a></p>';
    echo '</section>';

    echo '<section class="wr-card">';
    echo '<h2>'.esc_html__('Updates', 'walkridge').'</h2>';
    echo '<p>'.esc_html__('Install a production zip or pull from GitHub on the Update Theme screen. Marketplace zips already include vendor and compiled assets.', 'walkridge').'</p>';
    echo '<p><a class="button button-primary" href="'.esc_url($updateUrl).'">'.esc_html__('Open Update Theme', 'walkridge').'</a></p>';
    echo '</section>';

    echo '</div></details>';

    echo '<p class="wr-settings__submit">';
    submit_button(__('Save theme settings', 'walkridge'), 'primary', 'submit', false);
    echo '</p>';
    echo '</form></div>';
}

function wr_theme_settings_card(string $title, string $help, callable $fields): void
{
    echo '<section class="wr-card">';
    echo '<h2>'.esc_html($title).'</h2>';
    echo '<p class="wr-card__help">'.esc_html($help).'</p>';
    $fields();
    echo '</section>';
}

function wr_theme_settings_input(string $name, string $label, string $value, string $type = 'text'): void
{
    echo '<label class="wr-field"><span>'.esc_html($label).'</span>';
    printf(
        '<input type="%s" name="%s" value="%s">',
        esc_attr($type),
        esc_attr($name),
        esc_attr($value)
    );
    echo '</label>';
}
