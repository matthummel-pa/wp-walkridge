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

        $content = DemoLayouts::forSlug($slug, [
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
        if ($frontId <= 0) {
            $home = get_page_by_path('home');
            if ($home instanceof \WP_Post) {
                $frontId = (int) $home->ID;
            }
        }
        if ($frontId > 0) {
            wp_update_post([
                'ID' => $frontId,
                'post_content' => DemoLayouts::forSlug('home'),
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
                'post_content' => DemoLayouts::forSlug($key, $defaults),
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
        return DemoLayouts::forSlug($slug, $intro);
    }
}
