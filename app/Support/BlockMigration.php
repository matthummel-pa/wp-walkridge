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
            wp_update_post([
                'ID' => $postId,
                'post_content' => $content,
            ]);
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
            wp_update_post([
                'ID' => $frontId,
                'post_content' => DemoLayouts::forSlug('home'),
            ]);
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
            wp_update_post([
                'ID' => $page->ID,
                'post_content' => DemoLayouts::forSlug($key, $defaults),
            ]);
            self::markMigrated((int) $page->ID);
            self::deleteLegacyPageMeta((int) $page->ID);
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
