<?php
/**
 * Error notices — colour plus text, not colour alone.
 *
 * @package Walkridge
 */

defined('ABSPATH') || exit;

if (! $notices) {
    return;
}

echo '<div class="wr-wc-notices" role="alert">';
foreach ($notices as $notice) {
    $data_attr = function_exists('wc_get_notice_data_attr') ? wc_get_notice_data_attr($notice) : '';
    $text = is_array($notice) ? (string) ($notice['notice'] ?? '') : (string) $notice;
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Woo helper for data attrs; notice body is kses'd
    echo '<div class="woocommerce-error wr-wc-notice wr-wc-notice--error"'.$data_attr.'>';
    echo '<span class="wr-wc-notice__mark" aria-hidden="true">!</span>';
    echo wc_kses_notice($text);
    echo '</div>';
}
echo '</div>';
