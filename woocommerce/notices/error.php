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
?>
<div class="wr-wc-notices" role="alert">
  <?php foreach ($notices as $notice) { ?>
    <?php $data_attr = function_exists('wc_get_notice_data_attr') ? wc_get_notice_data_attr($notice) : ''; ?>
    <div class="woocommerce-error wr-wc-notice wr-wc-notice--error"<?php echo $data_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Woo helper ?>>
      <span class="wr-wc-notice__mark" aria-hidden="true">!</span>
      <?php echo wc_kses_notice($notice['notice']); ?>
    </div>
  <?php } ?>
</div>
