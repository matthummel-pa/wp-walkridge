<?php

/**
 * Theme Customizer — identity buyers change first.
 */

namespace App;

use WP_Customize_Control;
use WP_Customize_Manager;

add_action('customize_register', function (WP_Customize_Manager $wp_customize) {
    $wp_customize->add_section('wr_identity', [
        'title' => __('Identity', 'walkridge'),
        'description' => __('Tour office name, phone, and chrome buyers change first. Concept defaults stay until you overwrite them.', 'walkridge'),
        'priority' => 30,
    ]);

    $text = [
        'wr_brand_name' => [__('Brand name', 'walkridge'), 'Walkridge'],
        'wr_brand_sub' => [__('Brand subtitle', 'walkridge'), 'Battlefield Tours'],
        'wr_tagline' => [__('Header tagline', 'walkridge'), 'Licensed-guide tours in Gettysburg, PA'],
        'wr_phone' => [__('Phone', 'walkridge'), '(717) 555-0100'],
        'wr_email' => [__('Email', 'walkridge'), 'tours@walkridge.test'],
        'wr_cta_label' => [__('Header button label', 'walkridge'), 'Book a Tour'],
        'wr_cta_url' => [__('Header button URL', 'walkridge'), ''],
        'wr_rail_left' => [__('Header rail (left)', 'walkridge'), ''],
        'wr_rail_right' => [__('Header rail (right)', 'walkridge'), ''],
        'wr_credit_text' => [__('Footer credit text', 'walkridge'), 'Theme by Matt Hummel'],
        'wr_credit_url' => [__('Footer credit URL', 'walkridge'), 'https://matthummel.com/'],
        'wr_social_facebook' => [__('Facebook URL', 'walkridge'), ''],
        'wr_social_instagram' => [__('Instagram URL', 'walkridge'), ''],
        'wr_social_tripadvisor' => [__('TripAdvisor URL', 'walkridge'), ''],
        'wr_social_twitter' => [__('X / Twitter handle (e.g. @walkridge)', 'walkridge'), ''],
    ];

    foreach ($text as $id => [$label, $default]) {
        $urlFields = ['wr_social_facebook', 'wr_social_instagram', 'wr_social_tripadvisor'];
        $sanitize = str_contains($id, 'url') || in_array($id, $urlFields, true)
            ? 'esc_url_raw'
            : (str_contains($id, 'email') ? 'sanitize_email' : 'sanitize_text_field');
        $wp_customize->add_setting($id, [
            'default' => $default,
            'sanitize_callback' => $sanitize,
            'transport' => 'refresh',
        ]);
        $wp_customize->add_control($id, [
            'label' => $label,
            'section' => 'wr_identity',
            'type' => 'text',
        ]);
    }

    $wp_customize->add_setting('wr_address', [
        'default' => "100 Sample Street\nGettysburg, PA 17325",
        'sanitize_callback' => 'sanitize_textarea_field',
    ]);
    $wp_customize->add_control('wr_address', [
        'label' => __('Address', 'walkridge'),
        'section' => 'wr_identity',
        'type' => 'textarea',
    ]);

    $wp_customize->add_setting('wr_hours', [
        'default' => "Apr–Nov: Mon–Sun, 8:00 AM–6:00 PM\nDec–Mar: Thu–Sun, 9:00 AM–4:00 PM",
        'sanitize_callback' => 'sanitize_textarea_field',
    ]);
    $wp_customize->add_control('wr_hours', [
        'label' => __('Hours', 'walkridge'),
        'section' => 'wr_identity',
        'type' => 'textarea',
    ]);

    $wp_customize->add_setting('wr_footer_blurb', [
        'default' => '',
        'sanitize_callback' => 'sanitize_textarea_field',
    ]);
    $wp_customize->add_control('wr_footer_blurb', [
        'label' => __('Footer blurb', 'walkridge'),
        'description' => __('Leave empty for the concept sentence.', 'walkridge'),
        'section' => 'wr_identity',
        'type' => 'textarea',
    ]);

    $wp_customize->add_setting('wr_show_demo_chrome', [
        'default' => true,
        'sanitize_callback' => __NAMESPACE__.'\\wr_sanitize_checkbox',
    ]);
    $wp_customize->add_control('wr_show_demo_chrome', [
        'label' => __('Show concept demo badge', 'walkridge'),
        'section' => 'wr_identity',
        'type' => 'checkbox',
    ]);

    $wp_customize->add_setting('wr_show_credit', [
        'default' => true,
        'sanitize_callback' => __NAMESPACE__.'\\wr_sanitize_checkbox',
    ]);
    $wp_customize->add_control('wr_show_credit', [
        'label' => __('Show removable author credit in the footer', 'walkridge'),
        'section' => 'wr_identity',
        'type' => 'checkbox',
    ]);
});

/**
 * @param  mixed  $checked
 */
function wr_sanitize_checkbox($checked): bool
{
    return (bool) $checked;
}

/* ── Update Theme section ──────────────────────────────────────────────────── */

/**
 * Read-only Customizer control that renders theme version info and an
 * admin link. No setting needed — purely informational.
 */
class WR_Update_Info_Control extends WP_Customize_Control
{
    public $type = 'wr_update_info';

    public function render_content(): void
    {
        $theme   = wp_get_theme();
        $version = esc_html($theme->get('Version') ?: '1.0.0');
        $name    = esc_html($theme->get('Name'));
        $status_url = esc_url(admin_url('themes.php?page=hg-update-theme'));

        echo '<style>
            .wr-update-info { font-size: 13px; line-height: 1.6; }
            .wr-update-info .wr-version-badge {
                display: inline-block;
                background: #1d2327;
                color: #f0f0f0;
                font-family: monospace;
                padding: 2px 8px;
                border-radius: 3px;
                font-size: 12px;
                margin-bottom: 10px;
            }
            .wr-update-info .wr-status-link {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                margin-top: 8px;
                text-decoration: none;
                font-weight: 600;
            }
        </style>';

        echo '<div class="wr-update-info">';
        echo '<strong>' . $name . '</strong><br>';
        echo '<span class="wr-version-badge">v' . $version . '</span><br>';
        echo '<span>' . esc_html__('Check build status, git commit, and asset manifest on the Theme Status screen.', 'walkridge') . '</span><br>';
        echo '<a href="' . $status_url . '" class="wr-status-link" target="_blank">';
        echo esc_html__('Open Theme Status', 'walkridge') . ' &#8599;';
        echo '</a>';
        echo '</div>';
    }
}

add_action('customize_register', function (WP_Customize_Manager $wp_customize) {
    $wp_customize->add_section('wr_updates', [
        'title'       => __('Update Theme', 'walkridge'),
        'description' => __('Theme version and build info. Update by deploying a new zip or running git pull + npm run build on the server.', 'walkridge'),
        'priority'    => 200,
    ]);

    // Dummy setting — the control is read-only but WP requires a setting.
    $wp_customize->add_setting('wr_update_info_noop', [
        'sanitize_callback' => '__return_empty_string',
    ]);

    $wp_customize->add_control(new WR_Update_Info_Control($wp_customize, 'wr_update_info_noop', [
        'section' => 'wr_updates',
    ]));
});
