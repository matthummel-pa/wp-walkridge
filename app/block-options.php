<?php

/**
 * Shared Gutenberg inspector attributes and CSS class helpers.
 */

namespace App;

use App\Support\Identity;

/**
 * Typography + color attributes for section blocks.
 *
 * @return array<string, array{type: string, default: mixed}>
 */
function wr_typo_attributes(): array
{
    return [
        'headingAlign' => ['type' => 'string', 'default' => 'left'],
        'headingSize' => ['type' => 'string', 'default' => 'default'],
        'headingWeight' => ['type' => 'string', 'default' => 'default'],
        'bodySize' => ['type' => 'string', 'default' => 'default'],
        'headingTone' => ['type' => 'string', 'default' => 'default'],
        'bodyTone' => ['type' => 'string', 'default' => 'default'],
    ];
}

/**
 * Background band + padding + heading tag.
 *
 * @return array<string, array{type: string, default: mixed}>
 */
function wr_band_attributes(): array
{
    return [
        'bandStyle' => ['type' => 'string', 'default' => 'default'],
        'headingTag' => ['type' => 'string', 'default' => 'h2'],
        'sectionPad' => ['type' => 'string', 'default' => 'default'],
    ];
}

/**
 * Hero image / overlay / height attributes.
 *
 * @return array<string, array{type: string, default: mixed}>
 */
function wr_hero_attributes(): array
{
    return [
        'imageId' => ['type' => 'integer', 'default' => 0],
        'heroHeight' => ['type' => 'string', 'default' => 'default'],
        'overlayOpacity' => ['type' => 'integer', 'default' => 100],
        'overlayPreset' => ['type' => 'string', 'default' => 'default'],
        'imagePosition' => ['type' => 'string', 'default' => 'center'],
        'textAlign' => ['type' => 'string', 'default' => 'left'],
    ];
}

/**
 * @param  array<string, mixed>  $blockAttrs
 * @param  array<string, mixed>  $shared
 * @return array<string, mixed>
 */
function wr_merge_block_attributes(array $blockAttrs, array $shared): array
{
    return array_merge($shared, $blockAttrs);
}

/**
 * Section-head (or container) classes from typography / color attributes.
 *
 * @param  array<string, mixed>  $attrs
 */
function wr_head_class(array $attrs, string $base = 'section-head reveal'): string
{
    $cls = $base;

    $align = sanitize_key((string) ($attrs['headingAlign'] ?? $attrs['textAlign'] ?? 'left'));
    if (in_array($align, ['center', 'right'], true)) {
        $cls .= ' section-head--'.$align.' wr-head--'.$align;
    } elseif ($align === 'left') {
        $cls .= ' wr-head--left';
    }

    $size = sanitize_key((string) ($attrs['headingSize'] ?? 'default'));
    if (in_array($size, ['sm', 'lg', 'xl'], true)) {
        $cls .= ' wr-head--size-'.$size;
    }

    $weight = sanitize_key((string) ($attrs['headingWeight'] ?? 'default'));
    if (in_array($weight, ['medium', 'semibold', 'bold'], true)) {
        $cls .= ' wr-head--weight-'.$weight;
    }

    $bodySize = sanitize_key((string) ($attrs['bodySize'] ?? 'default'));
    if (in_array($bodySize, ['sm', 'lg'], true)) {
        $cls .= ' wr-body--'.$bodySize;
    }

    $headingTone = sanitize_key((string) ($attrs['headingTone'] ?? 'default'));
    if (in_array($headingTone, ['gold', 'parchment', 'muted', 'brick'], true)) {
        $cls .= ' wr-tone-heading-'.$headingTone;
    }

    $bodyTone = sanitize_key((string) ($attrs['bodyTone'] ?? 'default'));
    if (in_array($bodyTone, ['gold', 'parchment', 'muted', 'brick'], true)) {
        $cls .= ' wr-tone-body-'.$bodyTone;
    }

    return trim($cls);
}

/**
 * Section background + vertical pad from bandStyle / sectionPad.
 *
 * @param  array<string, mixed>  $attrs
 */
function wr_band_section_class(array $attrs, string $base = 'section'): string
{
    $cls = $base;
    $style = sanitize_key((string) ($attrs['bandStyle'] ?? 'default'));

    if (in_array($style, ['alt', 'parchment'], true)) {
        if (! str_contains($cls, 'section-alt')) {
            $cls .= ' section-alt';
        }
    } elseif (in_array($style, ['ink', 'dark'], true)) {
        $cls = str_replace([' section-alt', ' section-lantern'], '', $cls);
        $cls .= ' section-dark';
    } elseif ($style === 'gold') {
        $cls .= ' wr-section--gold';
    } elseif ($style === 'lantern') {
        $cls = str_replace(' section-alt', '', $cls);
        if (! str_contains($cls, 'section-lantern')) {
            $cls .= ' section-lantern';
        }
    }

    $pad = sanitize_key((string) ($attrs['sectionPad'] ?? 'default'));
    if (in_array($pad, ['compact', 'tall'], true)) {
        $cls .= ' wr-pad--'.$pad;
    }

    $extra = trim((string) ($attrs['className'] ?? ''));
    if ($extra !== '') {
        $safeExtra = implode(' ', array_filter(array_map('sanitize_html_class', preg_split('/\s+/', $extra) ?: [])));
        if ($safeExtra !== '') {
            $cls .= ' '.$safeExtra;
        }
    }

    return trim($cls);
}

/**
 * @param  array<string, mixed>  $attrs
 */
function wr_section_id_attr(array $attrs, string $fallback = ''): string
{
    $id = sanitize_title((string) ($attrs['anchor'] ?? ''));
    if ($id === '') {
        $id = sanitize_title($fallback);
    }

    return $id !== '' ? ' id="'.esc_attr($id).'"' : '';
}

/**
 * Heading tag from headingTag (h2/h3/h4) or numeric headingLevel.
 *
 * @param  array<string, mixed>  $attrs
 */
function wr_heading_tag(array $attrs, string $default = 'h2'): string
{
    if (isset($attrs['headingLevel']) && is_numeric($attrs['headingLevel'])) {
        $level = max(2, min(6, (int) $attrs['headingLevel']));

        return 'h'.$level;
    }

    $tag = strtolower((string) ($attrs['headingTag'] ?? $default));

    return in_array($tag, ['h2', 'h3', 'h4'], true) ? $tag : $default;
}

/**
 * @param  array<string, mixed>  $attrs
 */
function wr_hero_class(array $attrs, string $base = 'hero'): string
{
    $cls = wr_head_class($attrs, $base);

    $height = sanitize_key((string) ($attrs['heroHeight'] ?? 'default'));
    if (in_array($height, ['compact', 'tall'], true)) {
        $cls .= ' wr-hero--'.$height;
    }

    $align = sanitize_key((string) ($attrs['textAlign'] ?? $attrs['headingAlign'] ?? 'left'));
    if ($align === 'center') {
        $cls .= ' wr-hero--center';
    } elseif ($align === 'right') {
        $cls .= ' wr-hero--right';
    }

    $overlay = sanitize_key((string) ($attrs['overlayPreset'] ?? 'default'));
    if (in_array($overlay, ['light', 'dark'], true)) {
        $cls .= ' wr-overlay--'.$overlay;
    }

    $imgPos = sanitize_key((string) ($attrs['imagePosition'] ?? 'center'));
    if (in_array($imgPos, ['top', 'bottom', 'left', 'right'], true)) {
        $cls .= ' wr-img-pos--'.$imgPos;
    }

    return trim($cls);
}

/**
 * Resolve an image URL from a custom URL, attachment ID, then a theme preset key.
 *
 * @param  array<string, mixed>  $attrs
 */
function wr_block_image_url(array $attrs, string $fallbackKey = '', string $urlKey = 'imageUrl', string $idKey = 'imageId'): string
{
    $url = trim((string) ($attrs[$urlKey] ?? ''));
    if ($url !== '') {
        return esc_url($url);
    }

    $id = (int) ($attrs[$idKey] ?? 0);
    if ($id > 0) {
        $fromId = wp_get_attachment_image_url($id, 'full');
        if (is_string($fromId) && $fromId !== '') {
            return esc_url($fromId);
        }
    }

    if ($fallbackKey !== '') {
        return esc_url(Identity::image($fallbackKey));
    }

    return '';
}

/**
 * Overlay opacity as a 0–1 CSS custom property.
 *
 * @param  array<string, mixed>  $attrs
 */
function wr_overlay_style(array $attrs, int $default = 100): string
{
    $opacity = max(0, min(100, (int) ($attrs['overlayOpacity'] ?? $default))) / 100;

    return '--wr-overlay-opacity: '.esc_attr(sprintf('%.2f', $opacity));
}
