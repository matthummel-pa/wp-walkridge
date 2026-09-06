<?php

/**
 * Walkridge — Appearance > Theme Status admin page.
 *
 * Shows version / build info and provides a dev-only GitHub updater.
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

    $theme_dir    = get_template_directory();
    $manifest     = $theme_dir . '/public/build/manifest.json';
    $package_json = $theme_dir . '/package.json';

    $theme_version = wp_get_theme()->get('Version') ?: '1.0.0';
    $node_pkg      = file_exists($package_json) ? (json_decode((string) file_get_contents($package_json), true) ?? []) : [];
    $manifest_data = file_exists($manifest)
        ? (json_decode((string) file_get_contents($manifest), true) ?? [])
        : null;
    $last_build    = get_option('wr_last_build');
    $last_build_ts = $last_build
        ? human_time_diff((int) $last_build) . ' ' . __('ago', 'walkridge')
        : __('Never recorded', 'walkridge');

    $git_hash = '';
    if (is_readable($theme_dir . '/.git/HEAD')) {
        $head = trim((string) file_get_contents($theme_dir . '/.git/HEAD'));
        if (str_starts_with($head, 'ref:')) {
            $ref     = trim(substr($head, 4));
            $refFile = $theme_dir . '/.git/' . $ref;
            if (is_readable($refFile)) {
                $git_hash = substr(trim((string) file_get_contents($refFile)), 0, 7);
            }
        } elseif ($head !== '') {
            $git_hash = substr($head, 0, 7);
        }
    }

    $gh_token      = (string) get_option('wr_github_token', '');
    $gh_repo       = (string) get_option('wr_github_repo', '');
    $gh_branch     = (string) get_option('wr_github_branch', 'main');
    $gh_configured = $gh_token !== '' && $gh_repo !== '';
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

      <?php /* ── GitHub Dev Updater ────────────────────────────────────── */ ?>
      <hr>
      <h2 style="margin-top:1.5rem;">
        <?php esc_html_e('GitHub Dev Updater', 'walkridge'); ?>
        <span style="font-size:12px;font-weight:400;color:#888;margin-left:8px;"><?php esc_html_e('(dev only)', 'walkridge'); ?></span>
      </h2>

      <?php if (isset($_GET['gh_updated'])) { ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('GitHub settings saved.', 'walkridge'); ?></p></div>
      <?php } ?>

      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="max-width:640px;">
        <?php wp_nonce_field('wr_github_settings', 'wr_github_nonce'); ?>
        <input type="hidden" name="action" value="wr_save_github_settings">
        <table class="form-table" role="presentation">
          <tr>
            <th scope="row"><label for="wr_gh_token"><?php esc_html_e('GitHub Token', 'walkridge'); ?></label></th>
            <td>
              <input type="password" id="wr_gh_token" name="wr_github_token"
                     value="<?php echo esc_attr($gh_token); ?>" class="regular-text" autocomplete="off"
                     placeholder="ghp_xxxxxxxxxxxxxxxxxxxx">
              <p class="description"><?php esc_html_e('Personal access token — Contents: read-only scope. Stored in wp_options (plaintext — dev use only).', 'walkridge'); ?></p>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="wr_gh_repo"><?php esc_html_e('Repository', 'walkridge'); ?></label></th>
            <td>
              <input type="text" id="wr_gh_repo" name="wr_github_repo"
                     value="<?php echo esc_attr($gh_repo); ?>" class="regular-text"
                     placeholder="owner/repo-name">
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="wr_gh_branch"><?php esc_html_e('Branch', 'walkridge'); ?></label></th>
            <td>
              <input type="text" id="wr_gh_branch" name="wr_github_branch"
                     value="<?php echo esc_attr($gh_branch ?: 'main'); ?>" class="small-text">
            </td>
          </tr>
        </table>
        <?php submit_button(__('Save GitHub Settings', 'walkridge'), 'secondary', 'save_gh', false); ?>
      </form>

      <?php if ($gh_configured) { ?>
      <div style="margin-top:1.5rem;display:flex;gap:12px;flex-wrap:wrap;align-items:center;max-width:640px;">
        <button type="button" id="wr-btn-update-repo" class="button button-primary"
                style="display:inline-flex;align-items:center;gap:6px;">
          <span class="dashicons dashicons-update" style="margin-top:2px;"></span>
          <?php esc_html_e('Update from Repo', 'walkridge'); ?>
        </button>
        <label class="button button-secondary" for="wr-zip-upload"
               style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
          <span class="dashicons dashicons-upload" style="margin-top:2px;"></span>
          <?php esc_html_e('Rebuild &amp; Install from ZIP', 'walkridge'); ?>
        </label>
        <input type="file" id="wr-zip-upload" accept=".zip" style="display:none;">
        <span id="wr-spinner" class="spinner" style="display:none;float:none;margin:0;visibility:visible;"></span>
      </div>
      <p class="description" style="margin-top:6px;">
        <?php printf(
            esc_html__('Repo: %s — Branch: %s', 'walkridge'),
            '<code>' . esc_html($gh_repo) . '</code>',
            '<code>' . esc_html($gh_branch) . '</code>'
        ); ?>
      </p>
      <div id="wr-update-status" style="margin-top:12px;max-width:640px;display:none;">
        <div id="wr-update-log"
             style="background:#1d2327;color:#f0f0f0;padding:10px 14px;border-radius:4px;
                    font-family:monospace;font-size:12px;line-height:1.7;white-space:pre-wrap;"></div>
      </div>
      <?php } else { ?>
      <p class="description" style="margin-top:8px;">
        <?php esc_html_e('Save your GitHub token and repo above to unlock the update buttons.', 'walkridge'); ?>
      </p>
      <?php } ?>

      <script>
      (function () {
        var nonce    = <?php echo wp_json_encode(wp_create_nonce('wr_github_update')); ?>;
        var ajaxUrl  = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
        var logEl    = document.getElementById('wr-update-log');
        var statusEl = document.getElementById('wr-update-status');
        var spinner  = document.getElementById('wr-spinner');

        function log(msg) {
          if (!logEl || !statusEl) return;
          statusEl.style.display = 'block';
          logEl.textContent += msg + '\n';
          logEl.scrollTop = logEl.scrollHeight;
        }
        function setBusy(on) {
          var repoBtn = document.getElementById('wr-btn-update-repo');
          if (repoBtn) repoBtn.disabled = on;
          if (spinner) spinner.style.display = on ? 'inline-block' : 'none';
        }

        var repoBtn = document.getElementById('wr-btn-update-repo');
        if (repoBtn) {
          repoBtn.addEventListener('click', function () {
            if (!confirm('Download and install the latest ZIP from GitHub?\nThe active theme will be overwritten in place.')) return;
            if (logEl) logEl.textContent = '';
            setBusy(true);
            log('\u23f3 Fetching ZIP from GitHub\u2026');
            fetch(ajaxUrl, {
              method: 'POST',
              credentials: 'same-origin',
              body: new URLSearchParams({ action: 'wr_update_from_github', nonce: nonce }),
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
              if (data.success) {
                log('\u2705 ' + (data.data || 'Updated successfully.'));
                log('\u21ba Reloading\u2026');
                setTimeout(function () { location.reload(); }, 1500);
              } else {
                log('\u274c ' + (data.data || 'Update failed.'));
              }
            })
            .catch(function (err) { log('\u274c Network error: ' + err.message); })
            .finally(function () { setBusy(false); });
          });
        }

        var zipInput = document.getElementById('wr-zip-upload');
        if (zipInput) {
          zipInput.addEventListener('change', function () {
            var file = zipInput.files[0];
            if (!file) return;
            if (!confirm('Upload \u201c' + file.name + '\u201d and install as the active theme?')) {
              zipInput.value = '';
              return;
            }
            if (logEl) logEl.textContent = '';
            setBusy(true);
            log('\u23f3 Uploading ' + file.name + '\u2026');
            var fd = new FormData();
            fd.append('action', 'wr_install_zip');
            fd.append('nonce', nonce);
            fd.append('zip_file', file, file.name);
            fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
              if (data.success) {
                log('\u2705 ' + (data.data || 'Installed.'));
                log('\u21ba Reloading\u2026');
                setTimeout(function () { location.reload(); }, 1500);
              } else {
                log('\u274c ' + (data.data || 'Install failed.'));
              }
            })
            .catch(function (err) { log('\u274c Network error: ' + err.message); })
            .finally(function () { setBusy(false); zipInput.value = ''; });
          });
        }
      }());
      </script>
      <style>.spinner{background-size:20px 20px;width:20px;height:20px;}</style>
    </div>
    <?php
}

/* ── Save GitHub Settings ─────────────────────────────────────────────────── */

add_action('admin_post_wr_save_github_settings', function (): void {
    if (! current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    check_admin_referer('wr_github_settings', 'wr_github_nonce');

    update_option('wr_github_token',  sanitize_text_field(wp_unslash((string) ($_POST['wr_github_token'] ?? ''))));
    update_option('wr_github_repo',   sanitize_text_field(wp_unslash((string) ($_POST['wr_github_repo'] ?? ''))));
    update_option('wr_github_branch', sanitize_text_field(wp_unslash((string) ($_POST['wr_github_branch'] ?? 'main'))));

    wp_safe_redirect(admin_url('themes.php?page=hg-update-theme&gh_updated=1'));
    exit;
});

/* ── AJAX: Download latest ZIP from GitHub and install ───────────────────── */

add_action('wp_ajax_wr_update_from_github', function (): void {
    check_ajax_referer('wr_github_update', 'nonce');
    if (! current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $token  = (string) get_option('wr_github_token');
    $repo   = (string) get_option('wr_github_repo');
    $branch = (string) get_option('wr_github_branch', 'main');

    if (! $token || ! $repo) {
        wp_send_json_error('GitHub token or repo not configured.');
    }

    $zip_url  = "https://api.github.com/repos/{$repo}/zipball/{$branch}";
    $tmp_file = wp_tempnam('wr_gh_');

    $response = wp_remote_get($zip_url, [
        'headers'  => [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/vnd.github+json',
            'User-Agent'    => 'WordPress/walkridge-updater',
        ],
        'timeout'  => 90,
        'stream'   => true,
        'filename' => $tmp_file,
    ]);

    if (is_wp_error($response)) {
        @unlink($tmp_file);
        wp_send_json_error($response->get_error_message());
    }

    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        @unlink($tmp_file);
        wp_send_json_error("GitHub returned HTTP {$code} — verify your token and repo slug.");
    }

    $result = wr_install_theme_from_file($tmp_file);
    @unlink($tmp_file);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    wp_send_json_success("Updated from {$repo} @ {$branch}.");
});

/* ── AJAX: Install an uploaded ZIP as the active theme ───────────────────── */

add_action('wp_ajax_wr_install_zip', function (): void {
    check_ajax_referer('wr_github_update', 'nonce');
    if (! current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    if (empty($_FILES['zip_file']['tmp_name'])) {
        wp_send_json_error('No file received.');
    }

    $result = wr_install_theme_from_file((string) $_FILES['zip_file']['tmp_name']);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    wp_send_json_success('Theme installed from ZIP.');
});

/* ── Shared: Install / overwrite the active theme from a local ZIP path ──── */

function wr_install_theme_from_file(string $zip_path): bool|\WP_Error
{
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/misc.php';

    WP_Filesystem();

    $upgrader = new Theme_Upgrader(new WP_Ajax_Upgrader_Skin());
    $result   = $upgrader->install($zip_path, ['overwrite_package' => true]);

    if (is_wp_error($result)) {
        return $result;
    }
    if ($result === null || $result === false) {
        return new \WP_Error('install_failed', 'Installer returned no result — ZIP may be malformed or missing style.css.');
    }

    wp_clean_themes_cache();

    return true;
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
