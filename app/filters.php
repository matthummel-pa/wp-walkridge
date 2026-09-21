<?php

/**
 * Theme filters.
 */

namespace App;

use App\Support\BlockMigration;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(
        ' &hellip; <a href="%s">%s</a>',
        esc_url(get_permalink()),
        __('Continued', 'walkridge')
    );
});

/**
 * Decode kses-escaped / en-dash Gutenberg comments before do_blocks (priority 9).
 *
 * @param  mixed  $content
 * @return mixed
 */
add_filter('the_content', function ($content) {
    if (! is_string($content) || ! BlockMigration::contentNeedsBlockRepair($content)) {
        return $content;
    }

    return BlockMigration::normalizeBlockMarkup($content);
}, 1);

/**
 * Hide any leftover encoded comments so they never render as body text.
 *
 * @param  mixed  $content
 * @return mixed
 */
add_filter('the_content', function ($content) {
    if (! is_string($content) || $content === '') {
        return $content;
    }

    $dash = '(?:--|&#8211;|&#x2013;|&ndash;|&#8212;|&#x2014;|&mdash;)';
    $pattern = '/&lt;!'.$dash.'\s*(?:\/)?wp:.*?\/?'.$dash.'&gt;/si';
    $stripped = preg_replace($pattern, '', $content);

    return is_string($stripped) ? $stripped : $content;
}, 8);
