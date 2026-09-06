<?php

namespace App\Support;

/**
 * Editable page intro copy stored as post meta (native custom fields).
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
            'tours' => [
                'eyebrow' => __('Tours & Tickets', 'walkridge'),
                'heading' => __('Battlefield tours & tickets', 'walkridge'),
                'intro' => __('Choose from licensed-guide experiences across the battlefield — a walking tour of the main ridges, a hike to the rocky high ground, an ADA-accessible bus loop, an evening lantern walk downtown, and a private sunrise tour.', 'walkridge'),
            ],
            'guides' => [
                'eyebrow' => __('Our Guides', 'walkridge'),
                'heading' => __('Meet your licensed battlefield guides', 'walkridge'),
                'intro' => __('Every walking and bus tour is led by a licensed battlefield guide who passed the same demanding certification the park uses. That means accurate history, no filler, and a guide who can answer the hard questions.', 'walkridge'),
            ],
            'area' => [
                'eyebrow' => __('About the Area', 'walkridge'),
                'heading' => __('The battlefield & how to find us', 'walkridge'),
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

    public static function meta(int $postId, string $key, string $default = ''): string
    {
        if ($postId <= 0) {
            return $default;
        }

        $value = get_post_meta($postId, $key, true);

        return (is_string($value) && $value !== '') ? $value : $default;
    }
}
