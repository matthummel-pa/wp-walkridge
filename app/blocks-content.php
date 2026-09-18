<?php

/**
 * Extra Walkridge block renderers for restored Hallowed Ground page sections.
 */

namespace App;

use App\Support\Identity;

/** @param array<string, mixed> $attrs */
function wr_render_timeline(array $attrs): string
{
    $rows = wr_parse_piped_items((string) ($attrs['items'] ?? ''));
    if ($rows === []) {
        return '';
    }
    ob_start();
    wr_block_section_open($attrs, 'section');
    echo '<div class="timeline reveal">';
    foreach ($rows as $row) {
        echo '<article class="tl-item">';
        echo '<div class="tl-day">'.esc_html($row[0]).'</div>';
        if (! empty($row[1])) {
            echo '<h3>'.esc_html($row[1]).'</h3>';
        }
        if (! empty($row[2])) {
            echo '<p>'.esc_html($row[2]).'</p>';
        }
        echo '</article>';
    }
    echo '</div>';
    wr_block_section_close();

    return (string) ob_get_clean();
}

/** @param array<string, mixed> $attrs */
function wr_render_card_grid(array $attrs): string
{
    $rows = wr_parse_piped_items((string) ($attrs['items'] ?? ''));
    if ($rows === []) {
        return '';
    }
    $variant = (string) ($attrs['variant'] ?? 'expect');
    $alt = ! empty($attrs['alt']);
    $sectionClass = match ($variant) {
        'feature' => $alt ? 'section section-lantern' : 'section section-alt',
        default => $alt ? 'section section-alt' : 'section',
    };

    ob_start();
    wr_block_section_open($attrs, $sectionClass);
    if ($variant === 'feature') {
        echo '<div class="feature-row reveal">';
        foreach ($rows as $row) {
            $rawUrl = (string) ($row[3] ?? '');
            $url = wr_block_url($rawUrl);
            echo '<article class="feature-tile">';
            if (! empty($row[2])) {
                echo '<span class="eyebrow">'.esc_html($row[2]).'</span>';
            }
            echo '<h3>'.esc_html($row[0]).'</h3>';
            echo '<p>'.esc_html($row[1] ?? '').'</p>';
            if ($url !== '') {
                echo '<a href="'.$url.'">'.esc_html__('Learn more', 'walkridge').'</a>';
            }
            echo '</article>';
        }
        echo '</div>';
    } else {
        echo '<div class="expect-grid reveal">';
        foreach ($rows as $row) {
            echo '<div class="expect-card">';
            echo '<h3>'.esc_html($row[0]).'</h3>';
            echo '<p>'.esc_html($row[1] ?? '').'</p>';
            echo '</div>';
        }
        echo '</div>';
    }
    wr_block_section_close();

    return (string) ob_get_clean();
}

/** @param array<string, mixed> $attrs */
function wr_render_reviews(array $attrs): string
{
    $rows = wr_parse_piped_items((string) ($attrs['items'] ?? ''));
    if ($rows === []) {
        return '';
    }
    ob_start();
    wr_block_section_open($attrs, 'section');
    echo '<div class="reviews-grid reveal">';
    foreach ($rows as $row) {
        echo '<article class="review-card">';
        echo '<div class="stars" aria-hidden="true">★★★★★</div>';
        echo '<blockquote>'.esc_html($row[0]).'</blockquote>';
        echo '<p class="review-author">'.esc_html($row[1] ?? '');
        if (! empty($row[2])) {
            echo '<span>'.esc_html($row[2]).'</span>';
        }
        echo '</p></article>';
    }
    echo '</div>';
    wr_block_section_close();

    return (string) ob_get_clean();
}

/** @param array<string, mixed> $attrs */
function wr_render_journal_cards(array $attrs): string
{
    $rows = wr_parse_piped_items((string) ($attrs['items'] ?? ''));
    if ($rows === []) {
        return '';
    }
    ob_start();
    wr_block_section_open($attrs, 'section section-alt');
    echo '<div class="journal-grid reveal">';
    foreach ($rows as $row) {
        $url = wr_block_url((string) ($row[3] ?? ''));
        if ($url === '') {
            $url = esc_url(home_url('/'));
        }
        $imageKey = ! empty($row[4]) ? (string) $row[4] : 'cannon';
        echo '<a class="journal-card" href="'.$url.'">';
        echo '<img src="'.esc_url(Identity::image($imageKey)).'" alt="">';
        echo '<div class="pad">';
        if (! empty($row[0])) {
            echo '<span class="eyebrow">'.esc_html($row[0]).'</span>';
        }
        echo '<h3>'.esc_html($row[1] ?? '').'</h3>';
        if (! empty($row[2])) {
            echo '<p class="desc">'.esc_html($row[2]).'</p>';
        }
        echo '</div></a>';
    }
    echo '</div>';
    wr_block_section_close();

    return (string) ob_get_clean();
}

/** @param array<string, mixed> $attrs */
function wr_render_town_grid(array $attrs): string
{
    $rows = wr_parse_piped_items((string) ($attrs['items'] ?? ''));
    if ($rows === []) {
        return '';
    }
    ob_start();
    wr_block_section_open($attrs, 'section section-alt');
    echo '<div class="town-grid reveal">';
    foreach ($rows as $row) {
        echo '<div class="town-card"><b>'.esc_html($row[0]).'</b>';
        if (! empty($row[1])) {
            echo '<span>'.esc_html($row[1]).'</span>';
        }
        echo '</div>';
    }
    echo '</div>';
    wr_block_section_close();

    return (string) ob_get_clean();
}

/** @param array<string, mixed> $attrs */
function wr_render_copy_section(array $attrs): string
{
    $text = wp_kses_post((string) ($attrs['text'] ?? ''));
    if ($text === '' && ($attrs['heading'] ?? '') === '') {
        return '';
    }
    $class = ! empty($attrs['alt']) ? 'section section-alt' : 'section';
    ob_start();
    wr_block_section_open([
        'eyebrow' => $attrs['eyebrow'] ?? '',
        'heading' => $attrs['heading'] ?? '',
    ], $class);
    if ($text !== '') {
        echo '<div class="prose reveal">'.$text.'</div>';
    }
    wr_block_section_close();

    return (string) ob_get_clean();
}

/**
 * @param  array<string, mixed>  $attrs
 */
function wr_block_section_open(array $attrs, string $sectionClass): void
{
    echo '<section class="'.esc_attr($sectionClass).'"><div class="wrap">';
    $eyebrow = (string) ($attrs['eyebrow'] ?? '');
    $heading = (string) ($attrs['heading'] ?? '');
    $text = (string) ($attrs['text'] ?? '');
    if ($eyebrow === '' && $heading === '' && $text === '') {
        return;
    }
    echo '<div class="section-head reveal">';
    if ($eyebrow !== '') {
        echo '<span class="eyebrow">'.esc_html($eyebrow).'</span>';
    }
    if ($heading !== '') {
        echo '<h2>'.esc_html($heading).'</h2>';
    }
    if ($text !== '') {
        echo '<p>'.esc_html($text).'</p>';
    }
    echo '</div>';
}

function wr_block_section_close(): void
{
    echo '</div></section>';
}

function wr_block_url(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '' || $raw === '#') {
        return '';
    }
    if (! preg_match('#^https?://#i', $raw)) {
        $raw = home_url('/'.ltrim($raw, '/'));
    }

    return esc_url($raw);
}
