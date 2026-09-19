<?php

/**
 * Offer Walkridge updates from GitHub Releases (compiled walkridge.zip).
 *
 * Appearance → Themes “Update now” and Appearance → Update Theme both use the
 * latest public release asset. Source zipballs are not production packages.
 */
const WR_GITHUB_RELEASES_REPO = 'matthummel-pa/wp-walkridge';

function wr_github_release_headers(): array
{
    $headers = [
        'Accept'     => 'application/vnd.github+json',
        'User-Agent' => 'WordPress/walkridge-updater',
    ];
    $token = (string) get_option('wr_github_token', '');
    if ($token !== '') {
        $headers['Authorization'] = 'Bearer '.$token;
    }

    return $headers;
}

/**
 * @return array{version:string,package:string,url:string}|null
 */
function wr_github_latest_release(): ?array
{
    $cached = get_transient('wr_github_latest_release');
    if (is_array($cached) && isset($cached['version'], $cached['package'])) {
        return $cached;
    }

    $repo = (string) get_option('wr_github_repo', WR_GITHUB_RELEASES_REPO);
    if ($repo === '') {
        $repo = WR_GITHUB_RELEASES_REPO;
    }

    $response = wp_remote_get(
        'https://api.github.com/repos/'.$repo.'/releases/latest',
        [
            'headers' => wr_github_release_headers(),
            'timeout' => 20,
        ]
    );

    if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
        return null;
    }

    $body = json_decode((string) wp_remote_retrieve_body($response), true);
    if (! is_array($body)) {
        return null;
    }

    $version = ltrim((string) ($body['tag_name'] ?? ''), 'v');
    $package = '';
    foreach ((array) ($body['assets'] ?? []) as $asset) {
        if (! is_array($asset)) {
            continue;
        }
        $name = (string) ($asset['name'] ?? '');
        if ($name === 'walkridge.zip' || str_ends_with(strtolower($name), '/walkridge.zip')) {
            $package = (string) ($asset['browser_download_url'] ?? '');
            break;
        }
    }

    if ($version === '' || $package === '') {
        return null;
    }

    $data = [
        'version' => $version,
        'package' => $package,
        'url'     => (string) ($body['html_url'] ?? 'https://github.com/'.$repo.'/releases'),
    ];
    set_transient('wr_github_latest_release', $data, 6 * HOUR_IN_SECONDS);

    return $data;
}

add_filter('pre_set_site_transient_update_themes', function ($transient) {
    if (! is_object($transient)) {
        return $transient;
    }

    $release = wr_github_latest_release();
    if ($release === null) {
        return $transient;
    }

    $current = (string) wp_get_theme(get_template())->get('Version');
    if ($current === '' || ! version_compare($release['version'], $current, '>')) {
        return $transient;
    }

    $slug = get_template();
    $transient->response[$slug] = [
        'theme'       => $slug,
        'new_version' => $release['version'],
        'url'         => $release['url'],
        'package'     => $release['package'],
    ];

    return $transient;
});
