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

        $slug = (string) $post->post_name;
        $content = (string) $post->post_content;
        $defaults = PageFields::defaultsForSlug($slug);
        $eyebrow = PageFields::meta($postId, PageFields::EYEBROW, $defaults['eyebrow']);
        $heading = PageFields::meta($postId, PageFields::HEADING, $defaults['heading']);
        $intro = PageFields::meta($postId, PageFields::INTRO, $defaults['intro']);
        $changed = false;

        if (! str_contains($content, '<!-- wp:walkridge/')) {
            if ($eyebrow === '' && $heading === '' && $intro === '' && $slug !== 'front-page' && $slug !== 'home') {
                if (! in_array($slug, ['tours', 'guides', 'area', 'contact', 'refund-policy'], true)) {
                    self::deleteLegacyPageMeta($postId);

                    return false;
                }
            }
            $content = DemoLayouts::forSlug($slug, [
                'eyebrow' => $eyebrow,
                'heading' => $heading,
                'intro' => $intro,
            ]);
            $changed = $content !== '';
        } elseif ($slug === 'refund-policy' && ! str_contains($content, 'walkridge/refund-policy')) {
            $content = DemoLayouts::forSlug('refund-policy', [
                'eyebrow' => $eyebrow,
                'heading' => $heading,
                'intro' => $intro,
            ]);
            $changed = true;
        }

        if ($changed) {
            self::updatePostContent($postId, $content);
            self::markMigrated($postId);
        }

        self::deleteLegacyPageMeta($postId);

        return $changed;
    }

    public static function deleteLegacyPageMeta(int $postId): void
    {
        foreach ([
            PageFields::EYEBROW,
            PageFields::HEADING,
            PageFields::INTRO,
            'rp_effective_date',
            'rp_store_name',
            'rp_store_url',
            'rp_contact_email',
            'rp_refund_window_days',
            'rp_resolution_days',
            'rp_duplicate_days',
            'rp_response_days',
            'rp_payment_days_min',
            'rp_payment_days_max',
        ] as $key) {
            delete_post_meta($postId, $key);
        }
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
        if ($frontId <= 0) {
            $home = get_page_by_path('home');
            if ($home instanceof \WP_Post) {
                $frontId = (int) $home->ID;
            }
        }
        if ($frontId > 0) {
            self::updatePostContent($frontId, DemoLayouts::forSlug('home'));
            self::markMigrated($frontId);
            self::deleteLegacyPageMeta($frontId);
            $updated++;
        }

        foreach ($map as $slug => $key) {
            $page = get_page_by_path($slug);
            if (! $page) {
                continue;
            }
            $defaults = PageFields::defaultsForSlug($key);
            self::updatePostContent((int) $page->ID, DemoLayouts::forSlug($key, $defaults));
            self::markMigrated((int) $page->ID);
            self::deleteLegacyPageMeta((int) $page->ID);
            $updated++;
        }

        return ['updated' => $updated];
    }

    /**
     * Restore Gutenberg comments that wp_kses encoded as visible `&lt;!-- wp:` text,
     * and re-serialize so HTML inside JSON is `\u003c` (kses-safe).
     *
     * @return array{repaired: int, reseeded: int}
     */
    public static function repairEscapedBlockComments(): array
    {
        $pages = get_posts([
            'post_type' => ['page', 'post', 'product'],
            'post_status' => 'any',
            'posts_per_page' => -1,
        ]);
        $repaired = 0;
        $reseeded = 0;
        $concept = ['home', 'tours', 'guides', 'area', 'contact', 'refund-policy'];

        foreach ($pages as $post) {
            if (! $post instanceof \WP_Post) {
                continue;
            }
            $content = (string) $post->post_content;
            if (! self::contentNeedsBlockRepair($content)) {
                continue;
            }

            $slug = (string) $post->post_name;
            $frontId = (int) get_option('page_on_front');
            $isHome = ((int) $post->ID === $frontId) || $slug === 'home';
            $normalized = self::normalizeBlockMarkup($content);
            $stillBroken = self::contentNeedsBlockRepair($normalized)
                || ! str_contains($normalized, '<!-- wp:');

            if ($stillBroken && ($isHome || in_array($slug, $concept, true))) {
                $layout = $isHome ? 'home' : $slug;
                $defaults = PageFields::defaultsForSlug($layout);
                self::updatePostContent((int) $post->ID, DemoLayouts::forSlug($layout, $defaults));
                self::markMigrated((int) $post->ID);
                $reseeded++;

                continue;
            }

            if ($normalized !== $content) {
                self::updatePostContent((int) $post->ID, $normalized);
                $repaired++;
            }
        }

        return ['repaired' => $repaired, 'reseeded' => $reseeded];
    }

    public static function contentNeedsBlockRepair(string $content): bool
    {
        if ($content === '') {
            return false;
        }

        $enDash = "\u{2013}";
        $emDash = "\u{2014}";

        return str_contains($content, '&lt;!-- wp:')
            || str_contains($content, '&lt;!-- /wp:')
            || str_contains($content, '<!'.$enDash)
            || str_contains($content, $enDash.'>')
            || str_contains($content, '<!'.$emDash)
            || str_contains($content, $emDash.'>')
            || (bool) preg_match('/<!-- wp:[^>]*<(?:p|em|strong|br|span|div)/i', $content);
    }

    public static function unescapeBlockComments(string $content): string
    {
        return self::normalizeBlockMarkup($content);
    }

    /**
     * ASCII comment delimiters + HEX-encoded tags inside block JSON.
     */
    public static function normalizeBlockMarkup(string $content): string
    {
        $enDash = "\u{2013}";
        $emDash = "\u{2014}";
        $content = str_replace(
            ['<!'.$enDash, $enDash.'>', '<!'.$emDash, $emDash.'>'],
            ['<!--', '-->', '<!--', '-->'],
            $content
        );

        if (
            str_contains($content, '&lt;!-- wp:')
            || str_contains($content, '&lt;!-- /wp:')
            || str_contains($content, '/--&gt;')
        ) {
            $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (! function_exists('parse_blocks') || ! function_exists('serialize_blocks')) {
            return $content;
        }

        if (! str_contains($content, '<!-- wp:')) {
            return $content;
        }

        return serialize_blocks(parse_blocks($content));
    }

    /**
     * Persist Gutenberg markup without kses encoding `<!-- wp:` comments,
     * and wp_slash so JSON `\n` / `\u003c` survive wp_unslash on save.
     */
    public static function updatePostContent(int $postId, string $content): void
    {
        $removed = false;
        if (function_exists('kses_remove_filters')) {
            kses_remove_filters();
            $removed = true;
        }

        wp_update_post([
            'ID' => $postId,
            'post_content' => wp_slash($content),
        ]);

        if ($removed && function_exists('kses_init_filters')) {
            kses_init_filters();
        }
    }

    /**
     * @param  array{eyebrow?: string, heading?: string, intro?: string}  $intro
     */
    public static function buildContentForSlug(string $slug, array $intro): string
    {
        return DemoLayouts::forSlug($slug, $intro);
    }
}
