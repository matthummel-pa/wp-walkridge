<?php

namespace App\Support;

/**
 * Demo intro copy used when seeding Gutenberg page-intro blocks.
 * Not stored as post meta — edit live copy in the block sidebar.
 */
class PageFields
{
    public const EYEBROW = 'wr_page_eyebrow';

    public const HEADING = 'wr_page_heading';

    public const INTRO = 'wr_page_intro';

    /**
     * Default intro copy keyed by page slug.
     *
     * @return array{eyebrow: string, heading: string, intro: string}
     */
    public static function defaultsForSlug(string $slug): array
    {
        return match ($slug) {
            'home', 'front-page' => [
                'eyebrow' => '',
                'heading' => '',
                'intro' => '',
            ],
            'tours' => [
                'eyebrow' => __('Tours & Tickets', 'walkridge'),
                'heading' => __('Gettysburg battlefield tours & tickets', 'walkridge'),
                'intro' => __('Choose from five licensed-guide experiences across Gettysburg National Military Park — a walking tour of Cemetery Ridge, a hike to Little Round Top and Devil’s Den, an ADA-accessible bus loop, an evening lantern walk downtown, and a private sunrise tour.', 'walkridge'),
            ],
            'guides' => [
                'eyebrow' => __('Our Guides', 'walkridge'),
                'heading' => __('Meet your licensed Gettysburg battlefield guides', 'walkridge'),
                'intro' => __('Every walking and bus tour is led by a guide certified through the Association of Licensed Battlefield Guides — the same demanding exam used by Gettysburg National Military Park. That means accurate history, no filler, and a guide who can answer the hard questions.', 'walkridge'),
            ],
            'area' => [
                'eyebrow' => __('About Gettysburg & the Area', 'walkridge'),
                'heading' => __('Gettysburg, the battlefield & how to find us', 'walkridge'),
                'intro' => sprintf(
                    /* translators: %s: street address line */
                    __('Everything you need to plan a visit — the park, the ground our tours cover, where to meet us on %s, parking, driving directions, and the towns we serve.', 'walkridge'),
                    Identity::addressLine()
                ),
            ],
            'contact' => [
                'eyebrow' => __('Contact & FAQ', 'walkridge'),
                'heading' => sprintf(
                    /* translators: 1: brand name, 2: brand subtitle */
                    __('Contact %1$s %2$s', 'walkridge'),
                    Identity::brandName(),
                    Identity::brandSub()
                ),
                'intro' => __('Questions about a tour, accessibility, or a private group? Reach us by phone, email, or the form below — and check the frequently asked questions before you go.', 'walkridge'),
            ],
            'refund-policy' => [
                'eyebrow' => __('Store Policy', 'walkridge'),
                'heading' => __('Refund Policy', 'walkridge'),
                'intro' => '',
            ],
            default => [
                'eyebrow' => '',
                'heading' => '',
                'intro' => '',
            ],
        };
    }

    /**
     * @deprecated 1.3.0 Read Gutenberg block attributes instead of post meta.
     */
    public static function meta(int $postId, string $key, string $default = ''): string
    {
        if ($postId <= 0) {
            return $default;
        }

        $value = get_post_meta($postId, $key, true);

        return (is_string($value) && $value !== '') ? $value : $default;
    }
}
