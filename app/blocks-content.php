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
    wr_block_section_open($attrs, $class);
    if ($text !== '') {
        echo '<div class="prose reveal">'.$text.'</div>';
    }
    wr_block_section_close();

    return (string) ob_get_clean();
}

/** @param array<string, mixed> $attrs */
function wr_render_refund_policy(array $attrs): string
{
    $store = trim((string) ($attrs['storeName'] ?? ''));
    if ($store === '') {
        $store = Identity::brandName();
    }
    $url = trim((string) ($attrs['storeUrl'] ?? ''));
    if ($url === '') {
        $url = home_url('/');
    }
    $email = sanitize_email((string) ($attrs['contactEmail'] ?? ''));
    if ($email === '') {
        $email = Identity::email();
    }
    $effective = trim((string) ($attrs['effectiveDate'] ?? '')) ?: 'September 2, 2026';
    $window = max(1, (int) ($attrs['refundWindowDays'] ?? 30));
    $resolution = max(1, (int) ($attrs['resolutionDays'] ?? 7));
    $duplicate = max(1, (int) ($attrs['duplicateDays'] ?? 7));
    $response = max(1, (int) ($attrs['responseDays'] ?? 2));
    $payMin = max(1, (int) ($attrs['paymentDaysMin'] ?? 5));
    $payMax = max($payMin, (int) ($attrs['paymentDaysMax'] ?? 10));

    ob_start();
    echo '<section class="'.esc_attr(wr_band_section_class($attrs, wr_head_class($attrs, 'section'))).'"><div class="wrap">';
    ?>
    <div class="wr-policy prose reveal">
      <p><?php echo esc_html(sprintf(
          /* translators: 1: store name, 2: effective date */
          __('This sample store policy applies to digital and ticketed purchases from %1$s, effective %2$s. Replace it with your live terms before launch.', 'walkridge'),
          $store,
          $effective
      )); ?></p>
      <h3><?php esc_html_e('Tour tickets', 'walkridge'); ?></h3>
      <p><?php esc_html_e('Cancel or reschedule up to 24 hours before departure for a full refund. Cancellations inside 24 hours receive a credit toward a future tour. No-shows are non-refundable.', 'walkridge'); ?></p>
      <h3><?php esc_html_e('Other purchases', 'walkridge'); ?></h3>
      <p><?php echo esc_html(sprintf(
          /* translators: 1: refund window days, 2: response days, 3: resolution days */
          __('Request a refund within %1$d days of purchase. We respond within %2$d business days and resolve qualifying issues within %3$d days.', 'walkridge'),
          $window,
          $response,
          $resolution
      )); ?></p>
      <h3><?php esc_html_e('Duplicates and processing', 'walkridge'); ?></h3>
      <p><?php echo esc_html(sprintf(
          /* translators: 1: duplicate window, 2: min days, 3: max days */
          __('Duplicate charges reported within %1$d days are reversed. Approved refunds return to the original payment method in %2$d–%3$d business days.', 'walkridge'),
          $duplicate,
          $payMin,
          $payMax
      )); ?></p>
      <p><?php echo wp_kses(
          sprintf(
              /* translators: 1: store url href, 2: store url label, 3: email href, 4: email label */
              __('Questions: <a href="%1$s">%2$s</a> or <a href="mailto:%3$s">%4$s</a>.', 'walkridge'),
              esc_url($url),
              esc_html($url),
              esc_attr($email),
              esc_html($email)
          ),
          ['a' => ['href' => true]]
      ); ?></p>
    </div>
    </div></section>
    <?php

    return (string) ob_get_clean();
}

/**
 * @param  array<string, mixed>  $attrs
 */
function wr_block_section_open(array $attrs, string $sectionClass): void
{
    echo '<section class="'.esc_attr(wr_band_section_class($attrs, $sectionClass)).'"><div class="wrap">';
    $eyebrow = (string) ($attrs['eyebrow'] ?? '');
    $heading = (string) ($attrs['heading'] ?? '');
    $text = (string) ($attrs['text'] ?? '');
    if ($eyebrow === '' && $heading === '' && $text === '') {
        return;
    }
    $headTag = wr_heading_tag($attrs);
    echo '<div class="'.esc_attr(wr_head_class($attrs)).'">';
    if ($eyebrow !== '') {
        echo '<span class="eyebrow">'.esc_html($eyebrow).'</span>';
    }
    if ($heading !== '') {
        echo '<'.$headTag.'>'.esc_html($heading).'</'.$headTag.'>';
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

/**
 * Location map used on the Area page (Hallowed Ground location-grid).
 *
 * @param  array<string, mixed>  $attrs
 */
function wr_render_area_map(array $attrs): string
{
    $variant = sanitize_key((string) ($attrs['variant'] ?? 'static'));
    if (! in_array($variant, ['static', 'embed', 'field-map'], true)) {
        $variant = 'static';
    }
    $layout = sanitize_key((string) ($attrs['layout'] ?? 'split'));
    if (! in_array($layout, ['split', 'split-flip', 'stack'], true)) {
        $layout = 'split';
    }
    $tone = sanitize_key((string) ($attrs['iconTone'] ?? 'gold'));
    if (! in_array($tone, ['gold', 'lantern', 'parchment', 'brick'], true)) {
        $tone = 'gold';
    }
    $height = max(240, min(900, (int) ($attrs['mapHeight'] ?? 420)));
    $zoom = max(8, min(18, (int) ($attrs['mapZoom'] ?? 14)));
    $point = wr_area_map_point($attrs);
    $cards = wr_parse_piped_items((string) ($attrs['cards'] ?? ''));
    $alt = (string) ($attrs['imageAlt'] ?? '');
    if ($alt === '') {
        $alt = __('Downtown Gettysburg near Lincoln Square', 'walkridge');
    }
    $gridClass = 'location-grid wr-area-map reveal wr-area-map--'.$layout.' wr-area-map--tone-'.$tone;

    ob_start();
    wr_block_section_open($attrs, 'section');
    echo '<div class="'.esc_attr($gridClass).'">';

    echo '<div class="wr-area-map__stage">';
    if ($variant === 'field-map' && shortcode_exists('wr_field_map')) {
        $shortcode = sprintf(
            '[wr_field_map height="%spx" lat="%s" lng="%s" zoom="%s"]',
            esc_attr((string) $height),
            esc_attr((string) $point['lat']),
            esc_attr((string) $point['lng']),
            esc_attr((string) $zoom)
        );
        echo do_shortcode($shortcode);
    } elseif ($variant === 'embed') {
        $embed = wr_area_map_embed_url($attrs, $point, $zoom);
        echo '<div class="wr-area-map__frame">';
        echo '<iframe class="wr-area-map__embed" src="'.esc_url($embed).'" title="'.esc_attr($alt).'" height="'.esc_attr((string) $height).'" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
        echo '</div>';
        echo wr_area_map_open_link($point);
    } else {
        $img = wr_block_image_url($attrs, (string) ($attrs['imageKey'] ?? 'downtown'));
        $pinX = max(0, min(100, (int) ($attrs['pinX'] ?? 50)));
        $pinY = max(0, min(100, (int) ($attrs['pinY'] ?? 50)));
        echo '<figure class="map-card">';
        if ($img !== '') {
            echo '<img src="'.esc_url($img).'" alt="'.esc_attr($alt).'">';
        }
        if (! empty($attrs['showPin'])) {
            echo '<svg class="pin" width="40" height="52" viewBox="0 0 40 52" fill="none" aria-hidden="true" style="left:'.esc_attr((string) $pinX).'%;top:'.esc_attr((string) $pinY).'%"><path d="M20 0C9 0 0 9 0 20c0 15 20 32 20 32s20-17 20-32C40 9 31 0 20 0z" fill="currentColor"/><circle class="pin__core" cx="20" cy="19" r="7"/></svg>';
        }
        echo '</figure>';
        echo wr_area_map_open_link($point);
    }
    echo '</div>';

    if ($cards !== []) {
        echo '<div class="wr-area-map__cards">';
        foreach ($cards as $row) {
            $icon = sanitize_key((string) ($row[2] ?? 'pin'));
            echo '<div class="meet-card">';
            echo '<h3>'.wr_area_map_icon($icon).esc_html($row[0]).'</h3>';
            if (! empty($row[1])) {
                echo '<p>'.esc_html($row[1]).'</p>';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    echo '</div>';
    wr_block_section_close();

    return (string) ob_get_clean();
}

/**
 * @param  array<string, mixed>  $attrs
 * @return array{lat: float, lng: float, source: string, zip?: string}
 */
function wr_area_map_point(array $attrs): array
{
    $defaultLat = 39.83092;
    $defaultLng = -77.23114;
    $mode = sanitize_key((string) ($attrs['locationMode'] ?? 'coordinates'));

    if ($mode === 'zipcode') {
        $zip = trim((string) ($attrs['zipcode'] ?? ''));
        if ($zip === '') {
            $zip = '17325';
        }
        $geo = wr_geocode_zipcode($zip);
        if ($geo !== null) {
            $geo['source'] = 'zipcode';
            $geo['zip'] = $zip;

            return $geo;
        }
    }

    $lat = (float) ($attrs['latitude'] ?? $defaultLat);
    $lng = (float) ($attrs['longitude'] ?? $defaultLng);
    if ($lat < -90 || $lat > 90) {
        $lat = $defaultLat;
    }
    if ($lng < -180 || $lng > 180) {
        $lng = $defaultLng;
    }

    return [
        'lat' => $lat,
        'lng' => $lng,
        'source' => 'coordinates',
    ];
}

/**
 * @param  array<string, mixed>  $attrs
 * @param  array{lat: float, lng: float}  $point
 */
function wr_area_map_embed_url(array $attrs, array $point, int $zoom): string
{
    $custom = trim((string) ($attrs['mapEmbedUrl'] ?? ''));
    if ($custom !== '') {
        return $custom;
    }

    $span = 360 / (2 ** $zoom);
    $west = $point['lng'] - ($span * 0.55);
    $east = $point['lng'] + ($span * 0.55);
    $south = $point['lat'] - ($span * 0.32);
    $north = $point['lat'] + ($span * 0.32);

    return sprintf(
        'https://www.openstreetmap.org/export/embed.html?bbox=%.5f,%.5f,%.5f,%.5f&layer=mapnik&marker=%.5f,%.5f',
        $west,
        $south,
        $east,
        $north,
        $point['lat'],
        $point['lng']
    );
}

/**
 * @param  array{lat: float, lng: float}  $point
 */
function wr_area_map_open_link(array $point): string
{
    $href = sprintf(
        'https://www.openstreetmap.org/?mlat=%s&mlon=%s#map=15/%s/%s',
        rawurlencode(sprintf('%.5f', $point['lat'])),
        rawurlencode(sprintf('%.5f', $point['lng'])),
        rawurlencode(sprintf('%.5f', $point['lat'])),
        rawurlencode(sprintf('%.5f', $point['lng']))
    );

    return '<p class="wr-area-map__open"><a href="'.esc_url($href).'" rel="noopener noreferrer" target="_blank">'.esc_html__('Open this location on OpenStreetMap', 'walkridge').'</a></p>';
}

/**
 * @return array{lat: float, lng: float}|null
 */
function wr_geocode_zipcode(string $zip): ?array
{
    $zip = strtoupper(trim($zip));
    if ($zip === '' || strlen($zip) < 3 || strlen($zip) > 12) {
        return null;
    }
    if (! preg_match('/^[A-Z0-9][A-Z0-9\s-]{2,10}$/', $zip)) {
        return null;
    }

    $cacheKey = 'wr_geo_zip_'.md5($zip);
    $cached = get_transient($cacheKey);
    if (is_array($cached) && isset($cached['lat'], $cached['lng'])) {
        return [
            'lat' => (float) $cached['lat'],
            'lng' => (float) $cached['lng'],
        ];
    }

    $args = [
        'format' => 'jsonv2',
        'limit' => 1,
    ];
    if (preg_match('/^\d{5}(?:-\d{4})?$/', $zip)) {
        $args['postalcode'] = substr($zip, 0, 5);
        $args['countrycodes'] = 'us';
    } else {
        $args['q'] = $zip;
        $args['postalcode'] = $zip;
    }

    $response = wp_remote_get(add_query_arg($args, 'https://nominatim.openstreetmap.org/search'), [
        'timeout' => 8,
        'headers' => [
            'Accept' => 'application/json',
            'User-Agent' => 'WalkridgeTheme/1.4 (https://walkridge.matthummel.com; map block geocode)',
        ],
    ]);
    if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
        return null;
    }

    $data = json_decode((string) wp_remote_retrieve_body($response), true);
    if (! is_array($data) || $data === [] || ! isset($data[0]['lat'], $data[0]['lon'])) {
        return null;
    }

    $point = [
        'lat' => (float) $data[0]['lat'],
        'lng' => (float) $data[0]['lon'],
    ];
    set_transient($cacheKey, $point, WEEK_IN_SECONDS);

    return $point;
}

function wr_area_map_icon(string $icon): string
{
    $svgs = [
        'pin' => '<svg class="wr-area-map__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 2a7 7 0 00-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 00-7-7z"/><circle cx="12" cy="9.2" r="2.2"/></svg>',
        'lantern' => '<svg class="wr-area-map__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M9 5h6M10 5v2h4V5M8 7h8l-1 10H9L8 7z"/><path d="M10 11h4"/></svg>',
        'clock' => '<svg class="wr-area-map__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="8"/><path d="M12 8v4l2.5 1.5"/></svg>',
    ];

    return $svgs[$icon] ?? $svgs['pin'];
}
