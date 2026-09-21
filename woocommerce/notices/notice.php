<?php
/**
 * Info notices — Walkridge parchment / gold, not Woo blue.
 *
 * @package Walkridge
 */

defined('ABSPATH') || exit;

if (! $notices) {
    return;
}
?>
<div class="wr-wc-notices" role="status">
  <?php foreach ($notices as $notice) { ?>
    <?php $data_attr = function_exists('wc_get_notice_data_attr') ? wc_get_notice_data_attr($notice) : ''; ?>
    <div class="woocommerce-info wr-wc-notice wr-wc-notice--info"<?php echo $data_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Woo helper ?>>
      <?php echo wc_kses_notice($notice['notice']); ?>
    </div>
  <?php } ?>
</div>
