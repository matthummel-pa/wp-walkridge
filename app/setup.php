<?php

/**
 * Theme setup.
 */

namespace App;

use App\Support\Seo;
use Illuminate\Support\Facades\Vite;

Seo::boot();

/**
 * Preload the two critical WOFF2 fonts (display + body) from the Vite manifest
 * so the browser fetches them before the CSS @font-face swap fires.
 */
add_action('wp_head', static function (): void {
    if (Vite::isRunningHot()) {
        return;
    }

    $manifestPath = public_path('build/manifest.json');
    if (! file_exists($manifestPath)) {
        return;
    }

    $manifest = json_decode((string) file_get_contents($manifestPath), true);
    if (! is_array($manifest)) {
        return;
    }

    $preloadKeys = [
        'resources/fonts/archivo-black-latin.woff2',
        'resources/fonts/atkinson-hyperlegible-regular.woff2',
    ];

    foreach ($preloadKeys as $key) {
        if (isset($manifest[$key]['file'])) {
            $url = get_template_directory_uri().'/public/build/'.$manifest[$key]['file'];
            echo '<link rel="preload" href="'.esc_url($url).'" as="font" type="font/woff2" crossorigin>'."\n";
        }
    }
}, 2);

/**
 * Inject styles into the block editor and disable template-editing mode
 * (this is a classic non-FSE theme; see also remove_theme_support above).
 *
 * @param  array<string,mixed>  $settings
 * @return array<string,mixed>
 */
add_filter('block_editor_settings_all', function (array $settings): array {
    $style = Vite::asset('resources/css/editor.css');

    $settings['styles'][] = [
        'css' => "@import url('{$style}')",
    ];

    $settings['supportsTemplateMode'] = false;

    return $settings;
});

/**
 * Inject scripts into the block editor.
 *
 * @return void
 */
add_action('admin_head', function () {
    if (! get_current_screen()?->is_block_editor()) {
        return;
    }

    if (! Vite::isRunningHot()) {
        $dependencies = json_decode(Vite::content('editor.deps.json'));

        foreach ($dependencies as $dependency) {
            if (! wp_script_is($dependency)) {
                wp_enqueue_script($dependency);
            }
        }
    }
    echo Vite::withEntryPoints([
        'resources/js/editor.js',
    ])->toHtml();
});

/**
 * Use the generated theme.json file.
 *
 * @return string
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * Disable on-demand block asset loading.
 *
 * @link https://core.trac.wordpress.org/ticket/61965
 */
add_filter('should_load_separate_core_block_assets', '__return_false');

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    load_theme_textdomain('walkridge', get_template_directory().'/languages');

    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'walkridge'),
        'footer_navigation' => __('Footer Navigation', 'walkridge'),
    ]);

    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable custom logo support.
     */
    add_theme_support('custom-logo', [
        'height' => 80,
        'width' => 240,
        'flex-height' => true,
        'flex-width' => true,
    ]);

    /**
     * Enable responsive embed support.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    add_theme_support('align-wide');
    add_theme_support('wp-block-styles');
    add_theme_support('editor-styles');
    add_theme_support('automatic-feed-links');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');
}, 20);

/**
 * Declare WooCommerce theme support — only when WooCommerce is active.
 * When WC is absent the theme operates fully without shop/booking routes.
 *
 * @link https://woocommerce.com/document/third-party-custom-theme-compatibility/
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    if (! class_exists('WooCommerce')) {
        return;
    }

    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}, 20);

/**
 * Let the Blade `woocommerce.blade.php` template own the layout by removing
 * WooCommerce's default content wrappers and sidebar — only when WC is active.
 *
 * @return void
 */
add_action('init', function () {
    if (! class_exists('WooCommerce')) {
        return;
    }

    remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
    remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
    remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
});

/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    register_sidebar([
        'name' => __('Primary', 'walkridge'),
        'id' => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'walkridge'),
        'id' => 'sidebar-footer',
    ] + $config);
});

/**
 * Enqueue the native WordPress comment-reply script on singular posts/pages
 * when threaded comments are enabled — required for the threaded-comments tag.
 *
 * @return void
 */
add_action('wp_enqueue_scripts', function (): void {
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
});

/**
 * Keep the block editor on pages/posts for Walkridge blocks.
 */
add_filter('use_block_editor_for_post_type', function (bool $enabled, string $postType): bool {
    if (in_array($postType, ['page', 'post'], true)) {
        return true;
    }

    return $enabled;
}, 100, 2);
