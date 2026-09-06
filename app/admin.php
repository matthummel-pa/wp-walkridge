<?php

/**
 * Walkridge — Appearance > Theme Status admin page.
 *
 * Shows version / build info. Asset rebuilds are CLI-only (npm run build)
 * so shared hosts never run exec/shell_exec from wp-admin.
 *
 * Refund Policy custom fields remain here.
 */
add_action('admin_menu', function () {
    add_theme_page(
        __('Theme Status', 'walkridge'),
        __('Theme Status', 'walkridge'),
        'manage_options',
        'hg-update-theme',
        'wr_theme_update_page'
    );
});

/* ── Admin page HTML ─────────────────────────────────────────────────────── */

function wr_theme_update_page(): void
{
    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'walkridge'));
    }

    $theme_dir = get_template_directory();
    $manifest = $theme_dir.'/public/build/manifest.json';
    $package_json = $theme_dir.'/package.json';

    $theme_version = wp_get_theme()->get('Version') ?: '1.0.0';
    $node_pkg = file_exists($package_json) ? (json_decode((string) file_get_contents($package_json), true) ?? []) : [];
    $manifest_data = file_exists($manifest)
        ? (json_decode((string) file_get_contents($manifest), true) ?? [])
        : null;
    $last_build = get_option('wr_last_build');
    $last_build_ts = $last_build
        ? human_time_diff((int) $last_build).' '.__('ago', 'walkridge')
        : __('Never recorded', 'walkridge');

    $git_hash = '';
    if (is_readable($theme_dir.'/.git/HEAD')) {
        $head = trim((string) file_get_contents($theme_dir.'/.git/HEAD'));
        if (str_starts_with($head, 'ref:')) {
            $ref = trim(substr($head, 4));
            $refFile = $theme_dir.'/.git/'.$ref;
            if (is_readable($refFile)) {
                $git_hash = substr(trim((string) file_get_contents($refFile)), 0, 7);
            }
        } elseif ($head !== '') {
            $git_hash = substr($head, 0, 7);
        }
    }
    ?>
    <div class="wrap">
      <h1><?php esc_html_e('Theme Status', 'walkridge'); ?></h1>
      <p class="description">
        <?php esc_html_e('Production zips already include compiled assets. Rebuild on the command line after a git pull — never from the browser on shared hosting.', 'walkridge'); ?>
      </p>

      <table class="form-table" role="presentation" style="max-width:640px;">
        <tr>
          <th scope="row"><?php esc_html_e('Theme', 'walkridge'); ?></th>
          <td><strong><?php echo esc_html(wp_get_theme()->get('Name')); ?></strong>
              v<?php echo esc_html($theme_version); ?></td>
        </tr>
        <?php if (! empty($node_pkg['engines']['node'])) { ?>
        <tr>
          <th scope="row"><?php esc_html_e('Node requirement', 'walkridge'); ?></th>
          <td><code><?php echo esc_html($node_pkg['engines']['node']); ?></code></td>
        </tr>
        <?php } ?>
        <tr>
          <th scope="row"><?php esc_html_e('Built assets', 'walkridge'); ?></th>
          <td>
            <?php if ($manifest_data !== null) { ?>
              <span style="color:#46b450;">&#10003; <?php esc_html_e('manifest.json present', 'walkridge'); ?></span>
              (<?php echo count($manifest_data); ?> <?php esc_html_e('entries', 'walkridge'); ?>)
            <?php } else { ?>
              <span style="color:#dc3232;">&#10007; <?php esc_html_e('manifest.json missing — run npm run build', 'walkridge'); ?></span>
            <?php } ?>
          </td>
        </tr>
        <tr>
          <th scope="row"><?php esc_html_e('Last rebuild recorded', 'walkridge'); ?></th>
          <td><?php echo esc_html($last_build_ts); ?></td>
        </tr>
        <?php if ($git_hash) { ?>
        <tr>
          <th scope="row"><?php esc_html_e('Git commit', 'walkridge'); ?></th>
          <td><code><?php echo esc_html($git_hash); ?></code></td>
        </tr>
        <?php } ?>
      </table>

      <hr>
      <h2 style="margin-top:1.5rem;"><?php esc_html_e('Rebuild from the CLI', 'walkridge'); ?></h2>
      <pre style="background:#1d2327;color:#f0f0f0;padding:12px;border-radius:4px;max-width:640px;overflow:auto;">cd <?php echo esc_html($theme_dir); ?>

npm run build
wp acorn optimize:clear</pre>
      <p class="description"><?php esc_html_e('Store / ThemeForest zips already ship public/build and vendor — buyers do not need Node on the host.', 'walkridge'); ?></p>
    </div>
    <?php
}

/* ── Refund Policy custom-fields meta box ──────────────────────────────────── */

add_action('add_meta_boxes', function () {
    add_meta_box(
        'wr_refund_policy_fields',
        __('Refund Policy Settings', 'walkridge'),
        'wr_render_refund_policy_meta_box',
        'page',
        'normal',
        'high'
    );
});

function wr_render_refund_policy_meta_box(WP_Post $post): void
{
    if ($post->post_name !== 'refund-policy' && get_option('wr_refund_policy_page_id') != $post->ID) {
        echo '<p style="color:#666;font-size:13px;">'.esc_html__('This meta box is only active on the Refund Policy page.', 'walkridge').'</p>';

        return;
    }

    wp_nonce_field('wr_refund_policy_save', 'wr_refund_policy_nonce');

    $fields = [
        'rp_effective_date' => [__('Effective date', 'walkridge'), 'September 2, 2026', 'text'],
        'rp_store_name' => [__('Store name', 'walkridge'), 'matthummel.com', 'text'],
        'rp_store_url' => [__('Store URL', 'walkridge'), 'https://matthummel.com', 'url'],
        'rp_contact_email' => [__('Contact email', 'walkridge'), 'hello@matthummel.com', 'email'],
        'rp_refund_window_days' => [__('Refund window (days)', 'walkridge'), '30', 'number'],
        'rp_resolution_days' => [__('Bug resolution window (days)', 'walkridge'), '7', 'number'],
        'rp_duplicate_days' => [__('Duplicate-purchase window (days)', 'walkridge'), '7', 'number'],
        'rp_response_days' => [__('Response time (business days)', 'walkridge'), '2', 'number'],
        'rp_payment_days_min' => [__('Min refund processing days', 'walkridge'), '5', 'number'],
        'rp_payment_days_max' => [__('Max refund processing days', 'walkridge'), '10', 'number'],
    ];

    echo '<table class="form-table" role="presentation">';
    foreach ($fields as $key => [$label, $default, $type]) {
        $value = get_post_meta($post->ID, $key, true);
        $value = ($value !== '') ? esc_attr((string) $value) : esc_attr($default);
        $step = $type === 'number' ? ' step="1" min="1"' : '';
        printf(
            '<tr><th scope="row"><label for="%s">%s</label></th>
             <td><input type="%s" id="%s" name="%s" value="%s" class="regular-text"%s></td></tr>',
            esc_attr($key),
            esc_html($label),
            esc_attr($type),
            esc_attr($key),
            esc_attr($key),
            $value,
            $step
        );
    }
    echo '</table>';
    echo '<p class="description" style="margin-top:8px;">'.esc_html__('Changes here update the live Refund Policy page immediately on save.', 'walkridge').'</p>';
}

add_action('save_post', function (int $post_id): void {
    if (! isset($_POST['wr_refund_policy_nonce'])) {
        return;
    }
    if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wr_refund_policy_nonce'])), 'wr_refund_policy_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    $map = [
        'rp_effective_date' => 'sanitize_text_field',
        'rp_store_name' => 'sanitize_text_field',
        'rp_store_url' => 'esc_url_raw',
        'rp_contact_email' => 'sanitize_email',
        'rp_refund_window_days' => 'absint',
        'rp_resolution_days' => 'absint',
        'rp_duplicate_days' => 'absint',
        'rp_response_days' => 'absint',
        'rp_payment_days_min' => 'absint',
        'rp_payment_days_max' => 'absint',
    ];
    foreach ($map as $key => $callback) {
        if (isset($_POST[$key])) {
            $raw = wp_unslash($_POST[$key]);
            update_post_meta($post_id, $key, call_user_func($callback, $raw));
        }
    }
});
