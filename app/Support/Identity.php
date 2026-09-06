<?php

namespace App\Support;

/**
 * Buyer-facing tour-office identity. Customizer first, concept defaults second.
 */
class Identity
{
    public static function brandName(): string
    {
        $mod = trim((string) get_theme_mod('wr_brand_name', ''));
        if ($mod !== '') {
            return $mod;
        }

        $name = (string) get_bloginfo('name', 'display');

        return $name !== '' ? $name : 'Walkridge';
    }

    public static function brandSub(): string
    {
        $mod = trim((string) get_theme_mod('wr_brand_sub', ''));

        return $mod !== '' ? $mod : __('Battlefield Tours', 'walkridge');
    }

    public static function tagline(): string
    {
        $mod = trim((string) get_theme_mod('wr_tagline', ''));
        if ($mod !== '') {
            return $mod;
        }

        $desc = (string) get_bloginfo('description', 'display');

        return $desc !== '' ? $desc : __('Licensed-guide battlefield tours', 'walkridge');
    }

    public static function phone(): string
    {
        $phone = trim((string) get_theme_mod('wr_phone', '(717) 555-0100'));

        return $phone !== '' ? $phone : '(717) 555-0100';
    }

    public static function phoneHref(): string
    {
        $digits = preg_replace('/\D+/', '', self::phone()) ?? '';
        if ($digits === '') {
            return 'tel:+17175550100';
        }
        if (strlen($digits) === 10) {
            $digits = '1'.$digits;
        }

        return 'tel:+'.$digits;
    }

    public static function email(): string
    {
        $email = sanitize_email((string) get_theme_mod('wr_email', 'tours@walkridge.test'));

        return $email !== '' ? $email : 'tours@walkridge.test';
    }

    public static function addressLine(): string
    {
        $address = trim((string) get_theme_mod('wr_address', '100 Sample Street'));
        $first = strtok(str_replace(["\r\n", "\r"], "\n", $address), "\n");

        return $first !== false && $first !== '' ? $first : '100 Sample Street';
    }

    public static function address(): string
    {
        $address = trim((string) get_theme_mod('wr_address', '100 Sample Street'));

        return $address !== '' ? $address : '100 Sample Street';
    }

    public static function addressHtml(): string
    {
        return nl2br(esc_html(self::address()), false);
    }

    public static function hours(): string
    {
        $hours = trim((string) get_theme_mod('wr_hours', "Apr–Nov: Mon–Sun, 8:00 AM–6:00 PM\nDec–Mar: Thu–Sun, 9:00 AM–4:00 PM"));

        return $hours !== '' ? $hours : "Apr–Nov: Mon–Sun, 8:00 AM–6:00 PM\nDec–Mar: Thu–Sun, 9:00 AM–4:00 PM";
    }

    public static function hoursHtml(): string
    {
        return nl2br(esc_html(self::hours()), false);
    }

    public static function railLeft(): string
    {
        $mod = trim((string) get_theme_mod('wr_rail_left', ''));

        return $mod !== '' ? $mod : sprintf(
            /* translators: %s: short address line */
            __('Day tours · %s', 'walkridge'),
            self::addressLine()
        );
    }

    public static function railRight(): string
    {
        $mod = trim((string) get_theme_mod('wr_rail_right', ''));

        return $mod !== '' ? $mod : __('Evening lantern walks · downtown Gettysburg', 'walkridge');
    }

    public static function footerBlurb(): string
    {
        $blurb = trim((string) get_theme_mod('wr_footer_blurb', ''));
        if ($blurb !== '') {
            return $blurb;
        }

        return __('Licensed-guide battlefield tours. Walking, bus, and evening lantern experiences for individuals, families, and groups.', 'walkridge');
    }

    public static function ctaLabel(): string
    {
        $label = trim((string) get_theme_mod('wr_cta_label', ''));

        return $label !== '' ? $label : __('Book a Tour', 'walkridge');
    }

    public static function shopUrl(): string
    {
        $custom = trim((string) get_theme_mod('wr_cta_url', ''));
        if ($custom !== '') {
            return $custom;
        }

        if (function_exists('wc_get_page_permalink')) {
            $shop = wc_get_page_permalink('shop');
            if (is_string($shop) && $shop !== '') {
                return $shop;
            }
        }

        return home_url('/shop/');
    }

    public static function showDemoChrome(): bool
    {
        return (bool) get_theme_mod('wr_show_demo_chrome', true);
    }

    public static function showCredit(): bool
    {
        return (bool) get_theme_mod('wr_show_credit', true);
    }

    public static function creditText(): string
    {
        $text = trim((string) get_theme_mod('wr_credit_text', ''));

        return $text !== '' ? $text : __('Theme by Matt Hummel', 'walkridge');
    }

    public static function creditUrl(): string
    {
        $url = trim((string) get_theme_mod('wr_credit_url', 'https://matthummel.com/'));

        return $url !== '' ? $url : 'https://matthummel.com/';
    }

    public static function socialFacebook(): string
    {
        return trim((string) get_theme_mod('wr_social_facebook', ''));
    }

    public static function socialInstagram(): string
    {
        return trim((string) get_theme_mod('wr_social_instagram', ''));
    }

    public static function socialTripadvisor(): string
    {
        return trim((string) get_theme_mod('wr_social_tripadvisor', ''));
    }

    public static function socialTwitter(): string
    {
        return trim((string) get_theme_mod('wr_social_twitter', ''));
    }

    public static function image(string $slug): string
    {
        $map = [
            'cannon' => 'gettysburg-cannon.jpg',
            'wentz' => 'wentz-farm.jpg',
            'downtown' => 'downtown-gettysburg.jpg',
        ];
        $file = $map[$slug] ?? $map['cannon'];

        return get_theme_file_uri('public/images/'.$file);
    }

    /**
     * Resolve a WooCommerce product permalink by slug, falling back to the shop.
     */
    public static function productUrl(string $slug): string
    {
        if (function_exists('wc_get_product_id_by_sku') || post_type_exists('product')) {
            $post = get_page_by_path($slug, OBJECT, 'product');
            if ($post instanceof \WP_Post) {
                return (string) get_permalink($post);
            }
        }

        return self::shopUrl();
    }
}
