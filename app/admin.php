<?php

/**
 * Walkridge — Appearance > Update Theme admin page.
 *
 * Version / build cards plus zip and GitHub installers.
 */
add_action('admin_menu', function () {
    add_theme_page(
        __('Update Theme', 'walkridge'),
        __('Update Theme', 'walkridge'),
        'manage_options',
        'wr-update-theme',
        'wr_theme_update_page'
    );
});

add_action('admin_init', function (): void {
    $page = isset($_GET['page']) ? sanitize_key(wp_unslash((string) $_GET['page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only redirect of a legacy menu slug
    if (($GLOBALS['pagenow'] ?? '') === 'themes.php' && $page === 'hg-update-theme') {
        wp_safe_redirect(admin_url('themes.php?page=wr-update-theme'));
        exit;
    }
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
    $gh_repo       = (string) get_option('wr_github_repo', WR_GITHUB_RELEASES_REPO);
    if ($gh_repo === '') {
        $gh_repo = WR_GITHUB_RELEASES_REPO;
    }
    $gh_branch     = (string) get_option('wr_github_branch', 'main');
    $gh_configured = $gh_token !== '' && $gh_repo !== '';
    $gh_release    = function_exists('wr_github_latest_release') ? wr_github_latest_release() : null;
    $settings_url = admin_url('themes.php?page=wr-theme-settings');
    ?>
    <div class="wrap wr-update">
      <h1><?php esc_html_e('Update Theme', 'walkridge'); ?></h1>
      <p class="description">
        <?php esc_html_e('Install a production zip over the active theme, or pull from GitHub in development. Marketplace zips already include vendor and compiled assets — do not run npm on shared hosting.', 'walkridge'); ?>
        <a href="<?php echo esc_url($settings_url); ?>"><?php esc_html_e('Theme Settings', 'walkridge'); ?></a>
      </p>

      <div class="wr-update__grid">
        <div class="wr-stat">
          <span><?php esc_html_e('Theme', 'walkridge'); ?></span>
          <strong><?php echo esc_html(wp_get_theme()->get('Name')); ?> v<?php echo esc_html($theme_version); ?></strong>
        </div>
        <div class="wr-stat <?php echo $manifest_data !== null ? 'wr-stat--ok' : 'wr-stat--warn'; ?>">
          <span><?php esc_html_e('Built assets', 'walkridge'); ?></span>
          <strong>
            <?php if ($manifest_data !== null) { ?>
              <?php esc_html_e('manifest.json present', 'walkridge'); ?>
              (<?php echo count($manifest_data); ?>)
            <?php } else { ?>
              <?php esc_html_e('Missing — run npm run build', 'walkridge'); ?>
            <?php } ?>
          </strong>
        </div>
        <div class="wr-stat">
          <span><?php esc_html_e('Last rebuild', 'walkridge'); ?></span>
          <strong><?php echo esc_html($last_build_ts); ?><?php echo $git_hash !== '' ? ' · '.esc_html($git_hash) : ''; ?></strong>
        </div>
      </div>
      <?php if (! empty($node_pkg['engines']['node'])) { ?>
        <p class="description"><?php esc_html_e('Node requirement (source builds only):', 'walkridge'); ?> <code><?php echo esc_html($node_pkg['engines']['node']); ?></code></p>
      <?php } ?>

      <hr>
      <h2 style="margin-top:1.5rem;"><?php esc_html_e('Rebuild from the CLI', 'walkridge'); ?></h2>
      <pre style="background:#1d2327;color:#f0f0f0;padding:12px;border-radius:4px;max-width:640px;overflow:auto;">cd <?php echo esc_html($theme_dir); ?>

npm run build
wp acorn optimize:clear</pre>
      <p class="description"><?php esc_html_e('Store / ThemeForest zips already ship public/build and vendor — buyers do not need Node on the host.', 'walkridge'); ?></p>

      <hr>
      <h2 style="margin-top:1.5rem;"><?php esc_html_e('Install a production zip', 'walkridge'); ?></h2>
      <p class="description"><?php esc_html_e('Use a compiled walkridge.zip (vendor + public/build). WordPress will also offer this package under Appearance → Themes when a newer GitHub Release exists.', 'walkridge'); ?></p>
      <div style="margin-top:1rem;display:flex;gap:12px;flex-wrap:wrap;align-items:center;max-width:640px;">
        <button type="button" id="wr-btn-update-release" class="button button-primary"
                style="display:inline-flex;align-items:center;gap:6px;">
          <span class="dashicons dashicons-update" style="margin-top:2px;"></span>
          <?php esc_html_e('Install latest GitHub Release', 'walkridge'); ?>
        </button>
        <label class="button button-secondary" for="wr-zip-upload"
               style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
          <span class="dashicons dashicons-upload" style="margin-top:2px;"></span>
          <?php esc_html_e('Upload ZIP', 'walkridge'); ?>
        </label>
        <input type="file" id="wr-zip-upload" accept=".zip" style="display:none;">
        <span id="wr-spinner" class="spinner" style="display:none;float:none;margin:0;visibility:visible;"></span>
      </div>
      <?php if (is_array($gh_release)) { ?>
        <p class="description" style="margin-top:8px;">
          <?php printf(
              esc_html__('Latest release: %s', 'walkridge'),
              '<code>v' . esc_html($gh_release['version']) . '</code>'
          ); ?>
        </p>
      <?php } ?>
      <div id="wr-update-status" style="margin-top:12px;max-width:640px;display:none;">
        <div id="wr-update-log"
             style="background:#1d2327;color:#f0f0f0;padding:10px 14px;border-radius:4px;
                    font-family:monospace;font-size:12px;line-height:1.7;white-space:pre-wrap;"></div>
      </div>

      <?php /* ── GitHub Dev Updater ────────────────────────────────────── */ ?>
      <hr>
      <h2 style="margin-top:1.5rem;">
        <?php esc_html_e('GitHub Dev Updater', 'walkridge'); ?>
        <span style="font-size:12px;font-weight:400;color:#888;margin-left:8px;"><?php esc_html_e('(source zipball — not a production zip)', 'walkridge'); ?></span>
      </h2>

      <?php if (isset($_GET['gh_updated']) && sanitize_text_field(wp_unslash((string) $_GET['gh_updated'])) !== '') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only saved-settings flag ?>
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
        <button type="button" id="wr-btn-update-repo" class="button"
                style="display:inline-flex;align-items:center;gap:6px;">
          <span class="dashicons dashicons-download" style="margin-top:2px;"></span>
          <?php esc_html_e('Install source zipball', 'walkridge'); ?>
        </button>
      </div>
      <p class="description" style="margin-top:6px;">
        <?php printf(
            esc_html__('Repo: %s — Branch: %s. Source zipballs do not include vendor or Vite build.', 'walkridge'),
            '<code>' . esc_html($gh_repo) . '</code>',
            '<code>' . esc_html($gh_branch) . '</code>'
        ); ?>
      </p>
      <?php } else { ?>
      <p class="description" style="margin-top:8px;">
        <?php esc_html_e('Optional: save a GitHub token to pull a source zipball. Production updates use the public GitHub Release zip above.', 'walkridge'); ?>
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
          ['wr-btn-update-repo', 'wr-btn-update-release'].forEach(function (id) {
            var btn = document.getElementById(id);
            if (btn) btn.disabled = on;
          });
          if (spinner) spinner.style.display = on ? 'inline-block' : 'none';
        }

        function postUpdate(action, busyMsg) {
          if (logEl) logEl.textContent = '';
          setBusy(true);
          log(busyMsg);
          fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: new URLSearchParams({ action: action, nonce: nonce }),
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
        }

        var releaseBtn = document.getElementById('wr-btn-update-release');
        if (releaseBtn) {
          releaseBtn.addEventListener('click', function () {
            if (!confirm('Install the latest GitHub Release zip?\nThe active theme will be overwritten in place.')) return;
            postUpdate('wr_update_from_github_release', '\u23f3 Fetching walkridge.zip from GitHub Releases\u2026');
          });
        }

        var repoBtn = document.getElementById('wr-btn-update-repo');
        if (repoBtn) {
          repoBtn.addEventListener('click', function () {
            if (!confirm('Download the GitHub source zipball?\nThis is not a production zip (no vendor/build).')) return;
            postUpdate('wr_update_from_github', '\u23f3 Fetching source zipball from GitHub\u2026');
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
        wp_die(esc_html__('You do not have permission to access this page.', 'walkridge'));
    }
    check_admin_referer('wr_github_settings', 'wr_github_nonce');

    update_option('wr_github_token',  sanitize_text_field(wp_unslash((string) ($_POST['wr_github_token'] ?? ''))));
    update_option('wr_github_repo',   sanitize_text_field(wp_unslash((string) ($_POST['wr_github_repo'] ?? ''))));
    update_option('wr_github_branch', sanitize_text_field(wp_unslash((string) ($_POST['wr_github_branch'] ?? 'main'))));

    wp_safe_redirect(admin_url('themes.php?page=wr-update-theme&gh_updated=1'));
    exit;
});

/* ── AJAX: Install compiled zip from GitHub Releases ─────────────────────── */

add_action('wp_ajax_wr_update_from_github_release', function (): void {
    check_ajax_referer('wr_github_update', 'nonce');
    if (! current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $release = wr_github_latest_release();
    if ($release === null) {
        wp_send_json_error('No GitHub Release with a walkridge.zip asset was found.');
    }

    $result = wr_install_theme_from_url($release['package']);
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    delete_transient('wr_github_latest_release');
    wp_send_json_success('Installed GitHub Release v'.$release['version'].'.');
});

/* ── AJAX: Download latest ZIP from GitHub and install ───────────────────── */

add_action('wp_ajax_wr_update_from_github', function (): void {
    check_ajax_referer('wr_github_update', 'nonce');
    if (! current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $token  = (string) get_option('wr_github_token');
    $repo   = (string) get_option('wr_github_repo', WR_GITHUB_RELEASES_REPO);
    $branch = (string) get_option('wr_github_branch', 'main');
    if ($repo === '') {
        $repo = WR_GITHUB_RELEASES_REPO;
    }

    if (! $token) {
        wp_send_json_error('GitHub token not configured (required for source zipballs).');
    }

    $zip_url = "https://api.github.com/repos/{$repo}/zipball/{$branch}";
    $result  = wr_install_theme_from_url($zip_url, [
        'Authorization' => 'Bearer ' . $token,
        'Accept'        => 'application/vnd.github+json',
        'User-Agent'    => 'WordPress/walkridge-updater',
    ]);

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

    $tmp = isset($_FILES['zip_file']['tmp_name']) ? sanitize_text_field(wp_unslash((string) $_FILES['zip_file']['tmp_name'])) : '';
    if ($tmp === '' || ! is_uploaded_file($tmp)) {
        wp_send_json_error('No file received.');
    }

    $result = wr_install_theme_from_file($tmp);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    wp_send_json_success('Theme installed from ZIP.');
});

/* ── Shared: Install / overwrite the active theme from a local ZIP path ──── */

function wr_install_theme_from_url(string $url, array $headers = []): bool|\WP_Error
{
    $tmp_file = wp_tempnam('wr_theme_');
    $args     = [
        'timeout'  => 120,
        'stream'   => true,
        'filename' => $tmp_file,
        'headers'  => array_merge(
            [
                'User-Agent' => 'WordPress/walkridge-updater',
            ],
            $headers
        ),
    ];

    $response = wp_remote_get($url, $args);
    if (is_wp_error($response)) {
        @unlink($tmp_file);

        return $response;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        @unlink($tmp_file);

        return new \WP_Error('http_error', "Download returned HTTP {$code}.");
    }

    $result = wr_install_theme_from_file($tmp_file);
    @unlink($tmp_file);

    return $result;
}

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
