<?php

/**
 * Walkridge Child — enqueue parent + child styles.
 */
defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', function () {
    $parent = wp_get_theme(get_template());
    $version = $parent->get('Version') ?: '1.0.0';

    wp_enqueue_style(
        'walkridge-parent',
        get_template_directory_uri().'/style.css',
        [],
        $version
    );

    wp_enqueue_style(
        'walkridge-child',
        get_stylesheet_uri(),
        ['walkridge-parent'],
        wp_get_theme()->get('Version')
    );
}, 20);
