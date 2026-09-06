<?php

namespace App\Support;

/**
 * Migrates legacy wr_page_* post meta into Walkridge Gutenberg blocks,
 * and seeds demo page layouts.
 */
class BlockMigration
{
    private const MIGRATED_KEY = 'wr_block_migration_v1';

    /**
     * @return array{migrated: int, skipped: int, errors: list<string>}
     */
    public static function migrateAll(): array
    {
        $done = (array) get_option(self::MIGRATED_KEY, []);
        $results = ['migrated' => 0, 'skipped' => 0, 'errors' => []];

        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ]);

        foreach ($pages as $postId) {
            if (in_array((int) $postId, $done, true)) {
                $results['skipped']++;

                continue;
            }

            if (self::migrate((int) $postId)) {
                $done[] = (int) $postId;
                $results['migrated']++;
            } else {
                $results['skipped']++;
            }
        }

        update_option(self::MIGRATED_KEY, $done, false);

        return $results;
    }

    public static function migrate(int $postId): bool
    {
        $post = get_post($postId);
        if (! $post || $post->post_type !== 'page') {
            return false;
        }

        if (str_contains((string) $post->post_content, '<!-- wp:walkridge/')) {
            return false;
        }

        $slug = (string) $post->post_name;
        $defaults = PageFields::defaultsForSlug($slug);
        $eyebrow = PageFields::meta($postId, PageFields::EYEBROW, $defaults['eyebrow']);
        $heading = PageFields::meta($postId, PageFields::HEADING, $defaults['heading']);
        $intro = PageFields::meta($postId, PageFields::INTRO, $defaults['intro']);

        if ($eyebrow === '' && $heading === '' && $intro === '' && $slug !== 'front-page') {
            // Still seed structured layout for known concept pages.
            if (! in_array($slug, ['tours', 'guides', 'area', 'contact', 'refund-policy'], true)) {
                return false;
            }
        }

        $content = self::buildContentForSlug($slug, [
            'eyebrow' => $eyebrow,
            'heading' => $heading,
            'intro' => $intro,
        ]);

        if ($content === '') {
            return false;
        }

        wp_update_post([
            'ID' => $postId,
            'post_content' => $content,
        ]);

        self::markMigrated($postId);

        return true;
    }

    public static function markMigrated(int $postId): void
    {
        $done = (array) get_option(self::MIGRATED_KEY, []);
        $done[] = $postId;
        update_option(self::MIGRATED_KEY, array_values(array_unique($done)), false);
    }

    public static function resetMigrationRecord(): void
    {
        delete_option(self::MIGRATED_KEY);
    }

    /**
     * @return array{updated: int}
     */
    public static function seedDemoPages(): array
    {
        $map = [
            'tours' => 'tours',
            'guides' => 'guides',
            'area' => 'area',
            'contact' => 'contact',
            'refund-policy' => 'refund-policy',
        ];
        $updated = 0;

        $frontId = (int) get_option('page_on_front');
        if ($frontId > 0) {
            $defaults = PageFields::defaultsForSlug('home');
            wp_update_post([
                'ID' => $frontId,
                'post_content' => self::buildContentForSlug('home', $defaults),
            ]);
            self::markMigrated($frontId);
            $updated++;
        }

        foreach ($map as $slug => $key) {
            $page = get_page_by_path($slug);
            if (! $page) {
                continue;
            }
            $defaults = PageFields::defaultsForSlug($key);
            wp_update_post([
                'ID' => $page->ID,
                'post_content' => self::buildContentForSlug($key, $defaults),
            ]);
            self::markMigrated((int) $page->ID);
            $updated++;
        }

        return ['updated' => $updated];
    }

    /**
     * @param  array{eyebrow?: string, heading?: string, intro?: string}  $intro
     */
    public static function buildContentForSlug(string $slug, array $intro): string
    {
        $attrs = [
            'eyebrow' => $intro['eyebrow'] ?? '',
            'heading' => $intro['heading'] ?? '',
            'intro' => $intro['intro'] ?? '',
        ];

        return match ($slug) {
            'home', 'front-page' => implode("\n\n", [
                '<!-- wp:walkridge/home-hero /-->',
                '<!-- wp:walkridge/info-strip /-->',
                '<!-- wp:walkridge/about-split /-->',
                '<!-- wp:walkridge/pathway-cards /-->',
                '<!-- wp:walkridge/tour-grid {"limit":3,"showFilters":false,"showCompare":false,"eyebrow":"Featured Tours","heading":"Start with these three.","text":"Bookable tour products from WooCommerce — edit copy in the block sidebar."} /-->',
                '<!-- wp:walkridge/book-band /-->',
            ]),
            'tours' => implode("\n\n", [
                self::blockComment('walkridge/page-intro', $attrs),
                '<!-- wp:walkridge/info-strip /-->',
                '<!-- wp:walkridge/tour-grid /-->',
                '<!-- wp:walkridge/book-band /-->',
            ]),
            'guides' => implode("\n\n", [
                self::blockComment('walkridge/page-intro', $attrs),
                '<!-- wp:walkridge/info-strip /-->',
                '<!-- wp:walkridge/about-split {"eyebrow":"Licensed Guides","heading":"The same exam the park uses.","primaryLabel":"Book a Tour","primaryUrl":"/shop/","secondaryLabel":"See Tours","secondaryUrl":"/tours/"} /-->',
                '<!-- wp:walkridge/book-band /-->',
            ]),
            'area' => implode("\n\n", [
                self::blockComment('walkridge/page-intro', $attrs),
                '<!-- wp:walkridge/info-strip /-->',
                '<!-- wp:walkridge/pathway-cards {"eyebrow":"On the Ground","heading":"Park, town, and meeting point.","text":"Use this page for area context — the battlefield, downtown, and how guests find the sample office."} /-->',
                '<!-- wp:walkridge/cta-band {"eyebrow":"Plan the visit","heading":"Ready to walk the field?","text":"Pick a tour and a date. Sample checkout only.","buttonLabel":"Browse Tours"} /-->',
            ]),
            'contact' => implode("\n\n", [
                self::blockComment('walkridge/page-intro', $attrs),
                '<!-- wp:walkridge/info-strip /-->',
                '<!-- wp:walkridge/cta-band {"eyebrow":"Call or write","heading":"We answer during sample office hours.","text":"Demo phones are 555 numbers. Demo email does not reach a real inbox."} /-->',
            ]),
            'refund-policy' => implode("\n\n", [
                self::blockComment('walkridge/page-intro', $attrs ?: [
                    'eyebrow' => 'Store Policy',
                    'heading' => 'Refund Policy',
                    'intro' => '',
                ]),
                '<!-- wp:paragraph --><p>Sample refund policy for the concept demo. Replace this block content with your real store policy before launch.</p><!-- /wp:paragraph -->',
            ]),
            default => ($attrs['heading'] !== '' || $attrs['intro'] !== '')
                ? self::blockComment('walkridge/page-intro', $attrs)."\n\n<!-- wp:walkridge/info-strip /-->"
                : '',
        };
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    private static function blockComment(string $name, array $attrs): string
    {
        $clean = array_filter(
            $attrs,
            static fn ($v) => $v !== '' && $v !== null
        );
        if ($clean === []) {
            return "<!-- wp:{$name} /-->";
        }

        $json = wp_json_encode($clean, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return "<!-- wp:{$name} {$json} /-->";
    }
}
