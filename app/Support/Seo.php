<?php

namespace App\Support;

/**
 * Native theme SEO: title parts, meta description, canonical, Open Graph, Twitter.
 * Yields entirely when a major SEO plugin is active so tags are not duplicated.
 */
class Seo
{
    /**
     * Plugins that own document SEO when active.
     *
     * @var list<string>
     */
    private const SEO_PLUGINS = [
        'wordpress-seo/wp-seo.php',
        'seo-by-rank-math/rank-math.php',
        'wp-seopress/seopress.php',
        'all-in-one-seo-pack/all_in_one_seo_pack.php',
        'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
    ];

    public static function boot(): void
    {
        add_filter('document_title_parts', [self::class, 'titleParts']);
        add_action('wp_head', [self::class, 'render'], 1);

        // Avoid duplicate <link rel="canonical"> with core when we own head tags.
        add_action('wp_head', static function (): void {
            if (! self::shouldSkip() && ! self::pluginActive()) {
                remove_action('wp_head', 'rel_canonical');
            }
        }, 0);
    }

    public static function pluginActive(): bool
    {
        if (! function_exists('is_plugin_active')) {
            require_once ABSPATH.'wp-admin/includes/plugin.php';
        }

        foreach (self::SEO_PLUGINS as $plugin) {
            if (is_plugin_active($plugin)) {
                return true;
            }
        }

        if (defined('WPSEO_VERSION')
            || defined('RANK_MATH_VERSION')
            || defined('SEOPRESS_VERSION')
            || defined('AIOSEO_VERSION')
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param  array<string, string>  $parts
     * @return array<string, string>
     */
    public static function titleParts(array $parts): array
    {
        if (self::shouldSkip()) {
            return $parts;
        }

        $brand = Identity::brandName();

        if (is_front_page()) {
            $parts['title'] = self::clip(
                sprintf(
                    /* translators: 1: brand name, 2: brand subtitle */
                    __('%1$s %2$s — Licensed Gettysburg Tours', 'walkridge'),
                    $brand,
                    Identity::brandSub()
                ),
                60
            );
            unset($parts['tagline'], $parts['site']);

            return $parts;
        }

        if (is_singular('product') && ! empty($parts['title'])) {
            $parts['site'] = $brand;

            return $parts;
        }

        if (! empty($parts['title'])) {
            $parts['site'] = $brand;
        }

        return $parts;
    }

    public static function render(): void
    {
        if (self::shouldSkip() || self::pluginActive()) {
            return;
        }

        $title = self::pageTitle();
        $description = self::description();
        $url = self::canonicalUrl();
        $image = self::imageUrl();
        $type = is_singular() && ! is_front_page() ? 'article' : 'website';

        if (is_404() || is_search()) {
            echo '<meta name="robots" content="noindex,follow" />'."\n";
        }

        echo '<meta name="description" content="'.esc_attr($description).'" />'."\n";
        echo '<link rel="canonical" href="'.esc_url($url).'" />'."\n";

        echo '<meta property="og:locale" content="'.esc_attr(str_replace('-', '_', get_bloginfo('language') ?: 'en_US')).'" />'."\n";
        echo '<meta property="og:type" content="'.esc_attr($type).'" />'."\n";
        echo '<meta property="og:title" content="'.esc_attr($title).'" />'."\n";
        echo '<meta property="og:description" content="'.esc_attr($description).'" />'."\n";
        echo '<meta property="og:url" content="'.esc_url($url).'" />'."\n";
        echo '<meta property="og:site_name" content="'.esc_attr(Identity::brandName()).'" />'."\n";
        echo '<meta property="og:image" content="'.esc_url($image).'" />'."\n";
        echo '<meta property="og:image:width" content="1200" />'."\n";
        echo '<meta property="og:image:height" content="630" />'."\n";
        echo '<meta property="og:image:alt" content="'.esc_attr(Identity::brandName().' '.Identity::brandSub()).'" />'."\n";

        $twitter = Identity::socialTwitter();
        echo '<meta name="twitter:card" content="summary_large_image" />'."\n";
        if ($twitter !== '') {
            $handle = ltrim($twitter, '@');
            echo '<meta name="twitter:site" content="@'.esc_attr($handle).'" />'."\n";
        }
        echo '<meta name="twitter:title" content="'.esc_attr($title).'" />'."\n";
        echo '<meta name="twitter:description" content="'.esc_attr($description).'" />'."\n";
        echo '<meta name="twitter:image" content="'.esc_url($image).'" />'."\n";
    }

    private static function shouldSkip(): bool
    {
        return is_admin()
            || wp_doing_ajax()
            || (defined('REST_REQUEST') && REST_REQUEST)
            || is_feed();
    }

    private static function pageTitle(): string
    {
        $title = wp_get_document_title();

        return $title !== '' ? $title : Identity::brandName();
    }

    private static function description(): string
    {
        if (is_singular()) {
            $post = get_queried_object();
            if ($post instanceof \WP_Post) {
                // Product "excerpts" are often duration chips — handle products first.
                if ($post->post_type === 'product' && function_exists('wc_get_product')) {
                    $product = wc_get_product($post->ID);
                    if ($product) {
                        $short = wp_strip_all_tags((string) $product->get_short_description());
                        $long = wp_strip_all_tags((string) $product->get_description());
                        if ($long === '') {
                            $long = wp_strip_all_tags((string) $post->post_content);
                        }
                        // Prefer a sentence-length blurb; meta chips like "90 min · Max 6" are too thin alone.
                        if ($long !== '' && (mb_strlen($short) < 80 || $short === '')) {
                            $combined = $short !== '' ? $short.'. '.$long : $long;

                            return self::clip($combined, 160);
                        }
                        if ($short !== '') {
                            return self::clip($short, 160);
                        }
                        if ($long !== '') {
                            return self::clip($long, 160);
                        }
                    }
                }

                if (has_excerpt($post)) {
                    return self::clip(wp_strip_all_tags(get_the_excerpt($post)), 160);
                }

                $content = wp_strip_all_tags((string) $post->post_content);
                if ($content !== '') {
                    return self::clip($content, 160);
                }

                $bySlug = self::descriptionForSlug((string) $post->post_name);
                if ($bySlug !== '') {
                    return self::clip($bySlug, 160);
                }
            }
        }

        if (is_home() && ! is_front_page()) {
            return self::clip(
                __('News and notes from licensed-guide battlefield tours in Gettysburg, PA.', 'walkridge'),
                160
            );
        }

        if (is_post_type_archive('product') || (function_exists('is_shop') && is_shop())) {
            return self::clip(
                __('Book licensed-guide Gettysburg battlefield tours — walking, bus, lantern, and private sunrise experiences.', 'walkridge'),
                160
            );
        }

        if (is_front_page()) {
            return self::clip(Identity::footerBlurb(), 160);
        }

        $tagline = Identity::tagline();
        if ($tagline !== '') {
            return self::clip($tagline, 160);
        }

        return self::clip(Identity::footerBlurb(), 160);
    }

    /**
     * Unique descriptions for slug-based Blade marketing pages when excerpt/content is empty.
     */
    private static function descriptionForSlug(string $slug): string
    {
        return match ($slug) {
            'tours' => __('Choose licensed-guide Gettysburg battlefield tours — walking, bus, lantern walk, and private sunrise experiences. Small groups, Association-licensed guides.', 'walkridge'),
            'guides' => __('Meet Association-licensed Gettysburg battlefield guides. Walking, bus, lantern, and private sunrise tours led by historians who know the ground.', 'walkridge'),
            'area' => __('Plan your Gettysburg visit — battlefield ridges, downtown Lincoln Square, lodging nearby, and how our licensed-guide tours fit the day.', 'walkridge'),
            'contact' => sprintf(
                /* translators: %s: brand name */
                __('Contact %s Battlefield Tours in Gettysburg, PA — phone, email, ticket-office hours, gift certificates, groups, and FAQ.', 'walkridge'),
                Identity::brandName()
            ),
            default => '',
        };
    }

    private static function canonicalUrl(): string
    {
        if (is_front_page()) {
            return home_url('/');
        }

        if (is_singular()) {
            $permalink = get_permalink();
            if (is_string($permalink) && $permalink !== '') {
                return $permalink;
            }
        }

        if (function_exists('is_shop') && is_shop() && function_exists('wc_get_page_id')) {
            $shopId = (int) wc_get_page_id('shop');
            if ($shopId > 0) {
                $permalink = get_permalink($shopId);
                if (is_string($permalink) && $permalink !== '') {
                    return $permalink;
                }
            }
        }

        if (is_home() && ! is_front_page()) {
            $postsPage = (int) get_option('page_for_posts');
            if ($postsPage > 0) {
                $permalink = get_permalink($postsPage);
                if (is_string($permalink) && $permalink !== '') {
                    return $permalink;
                }
            }
        }

        global $wp;
        $path = isset($wp->request) ? (string) $wp->request : '';

        return home_url('/'.ltrim($path, '/'));
    }

    private static function imageUrl(): string
    {
        if (is_singular() && has_post_thumbnail()) {
            $url = get_the_post_thumbnail_url(null, 'full');
            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        return Identity::image('cannon');
    }

    private static function clip(string $text, int $max): string
    {
        $text = preg_replace('/\s+/u', ' ', trim($text)) ?? '';
        if ($text === '') {
            return '';
        }

        if (mb_strlen($text) <= $max) {
            return $text;
        }

        $cut = mb_substr($text, 0, $max - 1);
        $space = mb_strrpos($cut, ' ');
        if ($space !== false && $space > (int) ($max * 0.6)) {
            $cut = mb_substr($cut, 0, $space);
        }

        return rtrim($cut, '.,;:—–- ').'…';
    }
}
