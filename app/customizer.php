<?php

/**
 * Theme Customizer — identity buyers change first.
 */

namespace App;

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
