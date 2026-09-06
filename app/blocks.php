<?php

/**
 * Walkridge Gutenberg blocks — registration, editor assets, patterns.
 *
 * Blocks are server-side rendered (save returns null).
 * Editor UI: resources/js/blocks/index.js (via editor.js).
 */

namespace App;

use App\Support\BlockMigration;
use App\Support\Identity;
use App\Support\PageFields;
use App\Support\Tours;

add_filter('block_categories_all', function (array $categories): array {
    array_unshift($categories, [
        'slug' => 'walkridge',
        'title' => __('Walkridge', 'walkridge'),
        'icon' => null,
    ]);

    return $categories;
});

add_action('init', function (): void {
    wr_register_blocks();
});

function wr_register_blocks(): void
{
    $blocks = [
        'walkridge/home-hero' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_home_hero',
            'attributes' => [
                'eyebrow' => ['type' => 'string', 'default' => ''],
                'title' => ['type' => 'string', 'default' => 'Walk the ground where <em>history turned.</em>'],
                'text' => ['type' => 'string', 'default' => 'Small-group walking, bus, and evening lantern tours of the battlefield — led by licensed guides who bring the history to life, right where it happened.'],
                'primaryLabel' => ['type' => 'string', 'default' => ''],
                'primaryUrl' => ['type' => 'string', 'default' => ''],
                'secondaryLabel' => ['type' => 'string', 'default' => 'See All Tours'],
                'secondaryUrl' => ['type' => 'string', 'default' => ''],
                'imageKey' => ['type' => 'string', 'default' => 'cannon'],
                'imageUrl' => ['type' => 'string', 'default' => ''],
            ],
        ],
        'walkridge/page-intro' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_page_intro',
            'attributes' => [
                'eyebrow' => ['type' => 'string', 'default' => ''],
                'heading' => ['type' => 'string', 'default' => ''],
                'intro' => ['type' => 'string', 'default' => ''],
            ],
        ],
        'walkridge/info-strip' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_info_strip',
            'attributes' => [
                'showPhone' => ['type' => 'boolean', 'default' => true],
                'showAddress' => ['type' => 'boolean', 'default' => true],
                'showHours' => ['type' => 'boolean', 'default' => true],
            ],
        ],
        'walkridge/section-heading' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_section_heading',
            'attributes' => [
                'eyebrow' => ['type' => 'string', 'default' => ''],
                'heading' => ['type' => 'string', 'default' => ''],
                'text' => ['type' => 'string', 'default' => ''],
                'anchor' => ['type' => 'string', 'default' => ''],
                'alt' => ['type' => 'boolean', 'default' => false],
                'headingLevel' => ['type' => 'number', 'default' => 2],
                'textAlign' => ['type' => 'string', 'default' => 'center'],
            ],
        ],
        'walkridge/tour-grid' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_tour_grid',
            'attributes' => [
                'limit' => ['type' => 'number', 'default' => 0],
                'showFilters' => ['type' => 'boolean', 'default' => true],
                'showCompare' => ['type' => 'boolean', 'default' => true],
                'eyebrow' => ['type' => 'string', 'default' => 'Choose Your Tour'],
                'heading' => ['type' => 'string', 'default' => 'Ways to walk the field.'],
                'text' => ['type' => 'string', 'default' => 'Every tour is led by an Association-licensed guide and capped at a small group size.'],
            ],
        ],
        'walkridge/pathway-cards' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_pathway_cards',
            'attributes' => [
                'eyebrow' => ['type' => 'string', 'default' => 'Two Ways In'],
                'heading' => ['type' => 'string', 'default' => 'The field by day. The town after dark.'],
                'text' => ['type' => 'string', 'default' => 'Choose the landscape of July 1863, or the civilian streets where the letters were written.'],
                'leftEyebrow' => ['type' => 'string', 'default' => 'Historical'],
                'leftTitle' => ['type' => 'string', 'default' => 'Battlefield walking, bus, hike & private sunrise'],
                'leftText' => ['type' => 'string', 'default' => 'The main ridges, the rocky high ground, and the farthest reach of the final assault.'],
                'leftUrl' => ['type' => 'string', 'default' => ''],
                'rightEyebrow' => ['type' => 'string', 'default' => 'After Dark'],
                'rightTitle' => ['type' => 'string', 'default' => 'Evening Lantern Walk'],
                'rightText' => ['type' => 'string', 'default' => 'The historic downtown and the town square. Candlelit storytelling from the record.'],
                'rightUrl' => ['type' => 'string', 'default' => ''],
            ],
        ],
        'walkridge/about-split' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_about_split',
            'attributes' => [
                'eyebrow' => ['type' => 'string', 'default' => 'About Us'],
                'heading' => ['type' => 'string', 'default' => 'Guided by licensed historians, not a script.'],
                'text' => ['type' => 'string', 'default' => ''],
                'primaryLabel' => ['type' => 'string', 'default' => 'Meet Your Guides'],
                'primaryUrl' => ['type' => 'string', 'default' => ''],
                'secondaryLabel' => ['type' => 'string', 'default' => 'About the Area'],
                'secondaryUrl' => ['type' => 'string', 'default' => ''],
                'imageKey' => ['type' => 'string', 'default' => 'wentz'],
                'imageUrl' => ['type' => 'string', 'default' => ''],
                'caption' => ['type' => 'string', 'default' => 'A farmstead along the tour route'],
                'flip' => ['type' => 'boolean', 'default' => false],
            ],
        ],
        'walkridge/book-band' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_book_band',
            'attributes' => [
                'heading' => ['type' => 'string', 'default' => ''],
                'text' => ['type' => 'string', 'default' => ''],
                'buttonLabel' => ['type' => 'string', 'default' => ''],
            ],
        ],
        'walkridge/cta-band' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_cta_band',
            'attributes' => [
                'eyebrow' => ['type' => 'string', 'default' => ''],
                'heading' => ['type' => 'string', 'default' => ''],
                'text' => ['type' => 'string', 'default' => ''],
                'buttonLabel' => ['type' => 'string', 'default' => ''],
                'buttonUrl' => ['type' => 'string', 'default' => ''],
            ],
        ],
        'walkridge/custom' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_custom_block',
            'attributes' => [
                'blockId' => ['type' => 'string', 'default' => ''],
                'values' => ['type' => 'object', 'default' => []],
            ],
        ],
    ];

    foreach ($blocks as $name => $args) {
        register_block_type($name, array_merge([
            'api_version' => 3,
        ], $args));
    }
}

add_action('enqueue_block_editor_assets', function (): void {
    $data = wp_json_encode([
        'themeUri' => get_template_directory_uri(),
        'customBlocks' => wr_get_custom_block_definitions(),
        'shopUrl' => Identity::shopUrl(),
        'toursUrl' => home_url('/tours'),
        'guidesUrl' => home_url('/guides'),
        'areaUrl' => home_url('/area'),
    ]);
    // editor.js (Vite) imports the block registrations; expose config first.
    wp_add_inline_script(
        'wp-blocks',
        'window.WALKRIDGE_BLOCKS = '.$data.';',
        'before'
    );
});

add_action('init', function (): void {
    if (! function_exists('register_block_pattern_category')) {
        return;
    }
    register_block_pattern_category('walkridge', [
        'label' => __('Walkridge', 'walkridge'),
    ]);
});

add_action('init', function (): void {
    if (! function_exists('register_block_pattern')) {
        return;
    }
    register_block_pattern('walkridge/page-intro-pattern', [
        'title' => __('Walkridge — Page intro', 'walkridge'),
        'categories' => ['walkridge'],
        'content' => '<!-- wp:walkridge/page-intro /-->\n<!-- wp:walkridge/info-strip /-->',
    ]);
    register_block_pattern('walkridge/home-pattern', [
        'title' => __('Walkridge — Home starter', 'walkridge'),
        'categories' => ['walkridge'],
        'content' => implode("\n", [
            '<!-- wp:walkridge/home-hero /-->',
            '<!-- wp:walkridge/info-strip /-->',
            '<!-- wp:walkridge/about-split /-->',
            '<!-- wp:walkridge/pathway-cards /-->',
            '<!-- wp:walkridge/tour-grid {"limit":3,"showFilters":false,"showCompare":false} /-->',
            '<!-- wp:walkridge/book-band /-->',
        ]),
    ]);
});

// Admin tools: migrate legacy page meta → blocks
add_action('admin_menu', function (): void {
    add_management_page(
        __('Walkridge Blocks', 'walkridge'),
        __('Walkridge Blocks', 'walkridge'),
        'manage_options',
        'wr-blocks',
        __NAMESPACE__.'\\wr_blocks_tools_page'
    );
});

function wr_blocks_tools_page(): void
{
    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'walkridge'));
    }
    $notice = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wr_blocks_nonce'])) {
        if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wr_blocks_nonce'])), 'wr_blocks_tools')) {
            wp_die(esc_html__('Security check failed.', 'walkridge'));
        }
        $action = sanitize_key((string) ($_POST['wr_blocks_action'] ?? ''));
        if ($action === 'migrate') {
            BlockMigration::resetMigrationRecord();
            $result = BlockMigration::migrateAll();
            $notice = sprintf(
                /* translators: 1: migrated count, 2: skipped count */
                __('Migration finished. Migrated: %1$d. Skipped: %2$d.', 'walkridge'),
                (int) $result['migrated'],
                (int) $result['skipped']
            );
        } elseif ($action === 'seed') {
            $result = BlockMigration::seedDemoPages();
            $notice = sprintf(
                /* translators: %d: pages updated */
                __('Demo block content seeded on %d page(s).', 'walkridge'),
                (int) $result['updated']
            );
        }
    }
    echo '<div class="wrap"><h1>'.esc_html__('Walkridge Blocks', 'walkridge').'</h1>';
    if ($notice !== '') {
        echo '<div class="notice notice-success"><p>'.esc_html($notice).'</p></div>';
    }
    echo '<p>'.esc_html__('Convert legacy page-intro custom fields into Walkridge Gutenberg blocks, or re-seed demo page layouts.', 'walkridge').'</p>';
    echo '<form method="post" class="wr-tools-form">';
    wp_nonce_field('wr_blocks_tools', 'wr_blocks_nonce');
    echo '<button class="button button-primary" name="wr_blocks_action" value="migrate">'.esc_html__('Migrate page fields → blocks', 'walkridge').'</button>';
    echo '<button class="button" name="wr_blocks_action" value="seed">'.esc_html__('Seed demo block layouts', 'walkridge').'</button>';
    echo '</form></div>';
}

/** @param array<string, mixed> $attrs */
function wr_render_home_hero(array $attrs): string
{
    $title = wp_kses($attrs['title'] ?? '', ['em' => [], 'strong' => []]);
    $text = wp_kses_post((string) ($attrs['text'] ?? ''));
    $eyebrow = esc_html((string) ($attrs['eyebrow'] ?? ''));
    if ($eyebrow === '') {
        $eyebrow = esc_html(Identity::brandName().' · '.Identity::brandSub());
    }
    $primary = esc_html((string) ($attrs['primaryLabel'] ?? '')) ?: esc_html(Identity::ctaLabel());
    $secondary = esc_html((string) ($attrs['secondaryLabel'] ?? '')) ?: esc_html__('See All Tours', 'walkridge');
    $customUrl = (string) ($attrs['imageUrl'] ?? '');
    $img = $customUrl !== '' ? esc_url($customUrl) : esc_url(Identity::image((string) ($attrs['imageKey'] ?? 'cannon')));
    $shop = (string) ($attrs['primaryUrl'] ?? '') !== '' ? esc_url((string) $attrs['primaryUrl']) : esc_url(Identity::shopUrl());
    $tours = (string) ($attrs['secondaryUrl'] ?? '') !== '' ? esc_url((string) $attrs['secondaryUrl']) : esc_url(home_url('/tours'));

    ob_start();
    ?>
    <section class="hero" id="top">
      <div class="hero-compass" aria-hidden="true">
        <svg viewBox="0 0 40 40" fill="none"><circle cx="20" cy="20" r="18.5" stroke="currentColor" stroke-width="1.1"/><path d="M20 4l3.4 16L20 36l-3.4-16z" fill="currentColor"/><path d="M4 20l16-3.4L36 20l-16 3.4z" fill="currentColor" opacity=".4"/><circle cx="20" cy="20" r="3.1" fill="currentColor"/></svg>
      </div>
      <div class="hero-media">
        <img src="<?php echo $img; ?>" alt="" fetchpriority="high"> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
      </div>
      <div class="wrap hero-content">
        <p class="hero-brand"><?php echo $eyebrow; ?></p> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
        <h1 class="hero-title"><?php echo $title; ?></h1> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
        <?php if ($text !== '') { ?><p class="hero-lede"><?php echo $text; ?></p><?php } ?> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
        <div class="hero-ctas">
          <a href="<?php echo $shop; ?>" class="btn btn-primary"><?php echo $primary; ?></a> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
          <a href="<?php echo $tours; ?>" class="btn btn-outline"><?php echo $secondary; ?></a> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
        </div>
      </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

/** @param array<string, mixed> $attrs */
function wr_render_page_intro(array $attrs): string
{
    $slug = (string) get_post_field('post_name', get_the_ID());
    $defaults = PageFields::defaultsForSlug($slug);
    $eyebrow = esc_html((string) (($attrs['eyebrow'] ?? '') !== '' ? $attrs['eyebrow'] : $defaults['eyebrow']));
    $heading = esc_html((string) (($attrs['heading'] ?? '') !== '' ? $attrs['heading'] : $defaults['heading']));
    $intro = esc_html((string) (($attrs['intro'] ?? '') !== '' ? $attrs['intro'] : $defaults['intro']));
    if ($heading === '' && $intro === '') {
        return '';
    }
    ob_start();
    ?>
    <section class="page-intro">
      <div class="wrap reveal">
        <?php if ($eyebrow !== '') { ?><span class="eyebrow"><?php echo $eyebrow; ?></span><?php } ?> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
        <?php if ($heading !== '') { ?><h1><?php echo $heading; ?></h1><?php } ?> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
        <?php if ($intro !== '') { ?><p><?php echo $intro; ?></p><?php } ?> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
      </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

/** @param array<string, mixed> $attrs */
function wr_render_info_strip(array $attrs): string
{
    return view('partials.info-strip')->render();
}

/** @param array<string, mixed> $attrs */
function wr_render_section_heading(array $attrs): string
{
    $eyebrow = esc_html((string) ($attrs['eyebrow'] ?? ''));
    $heading = wp_kses((string) ($attrs['heading'] ?? ''), ['em' => [], 'strong' => []]);
    $text = wp_kses_post((string) ($attrs['text'] ?? ''));
    $anchor = sanitize_title((string) ($attrs['anchor'] ?? ''));
    $alt = ! empty($attrs['alt']);
    $level = max(2, min(6, (int) ($attrs['headingLevel'] ?? 2)));
    $textAlign = in_array($attrs['textAlign'] ?? 'center', ['left', 'center', 'right'], true)
        ? (string) $attrs['textAlign']
        : 'center';
    $idAttr = $anchor !== '' ? ' id="'.esc_attr($anchor).'"' : '';
    $sectionClass = $alt ? 'section section-alt' : 'section';
    $headTag = 'h'.$level;
    ob_start();
    ?>
    <section class="<?php echo esc_attr($sectionClass); ?>"<?php echo $idAttr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>>
      <div class="wrap">
        <div class="section-head reveal section-head--<?php echo esc_attr($textAlign); ?>">
          <?php if ($eyebrow !== '') { ?><span class="eyebrow"><?php echo $eyebrow; ?></span><?php } ?> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
          <?php if ($heading !== '') { ?><<?php echo esc_attr($headTag); ?>><?php echo $heading; ?></<?php echo esc_attr($headTag); ?>><?php } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?>
          <?php if ($text !== '') { ?><p><?php echo $text; ?></p><?php } ?> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
        </div>
      </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

/** @param array<string, mixed> $attrs */
function wr_render_tour_grid(array $attrs): string
{
    $limit = (int) ($attrs['limit'] ?? 0);
    $tours = $limit > 0 ? Tours::all($limit) : Tours::all();
    $showFilters = ! empty($attrs['showFilters']);
    $showCompare = ! empty($attrs['showCompare']);
    $eyebrow = esc_html((string) ($attrs['eyebrow'] ?? ''));
    $heading = esc_html((string) ($attrs['heading'] ?? ''));
    $text = esc_html((string) ($attrs['text'] ?? ''));

    ob_start();
    ?>
    <section class="section">
      <div class="wrap">
        <?php if ($eyebrow || $heading || $text) { ?>
        <div class="section-head reveal" id="historical">
          <?php if ($eyebrow !== '') { ?><span class="eyebrow"><?php echo $eyebrow; ?></span><?php } ?> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
          <?php if ($heading !== '') { ?><h2><?php echo $heading; ?></h2><?php } ?> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
          <?php if ($text !== '') { ?><p><?php echo $text; ?></p><?php } ?> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
        </div>
        <?php } ?>
        <?php if ($showFilters) { ?>
        <div class="filter-bar" data-tour-filter role="group" aria-label="<?php esc_attr_e('Filter tours', 'walkridge'); ?>">
          <button type="button" data-filter="all" aria-pressed="true"><?php esc_html_e('All tours', 'walkridge'); ?></button>
          <button type="button" data-filter="historical"><?php esc_html_e('Historical', 'walkridge'); ?></button>
          <button type="button" data-filter="after-dark"><?php esc_html_e('After dark', 'walkridge'); ?></button>
        </div>
        <?php } ?>
        <div class="tour-grid reveal products">
          <?php
          if ($tours === []) {
              echo '<p>'.esc_html__('No tours are published yet. Add WooCommerce products or run the setup seed.', 'walkridge').'</p>';
          } else {
              foreach ($tours as $tour) {
                  echo view('partials.tour-card', ['tour' => $tour])->render();
              }
          }
    ?>
        </div>
        <?php if ($showCompare && $tours !== []) { ?>
        <div class="reveal compare-wrapper">
          <span class="eyebrow"><?php esc_html_e('Compare', 'walkridge'); ?></span>
          <h3 class="compare-heading"><?php esc_html_e('Pick by pace, access, and light.', 'walkridge'); ?></h3>
          <table class="compare">
            <thead>
              <tr>
                <th><?php esc_html_e('Tour', 'walkridge'); ?></th>
                <th><?php esc_html_e('Type', 'walkridge'); ?></th>
                <th><?php esc_html_e('Duration', 'walkridge'); ?></th>
                <th><?php esc_html_e('Cap', 'walkridge'); ?></th>
                <th><?php esc_html_e('Adult', 'walkridge'); ?></th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tours as $tour) { ?>
                <tr<?php echo ($tour['category'] ?? '') === 'after-dark' ? ' id="after-dark"' : ''; ?>>
                  <td><?php echo esc_html($tour['title']); ?></td>
                  <td><?php echo esc_html($tour['kicker']); ?></td>
                  <td><?php echo esc_html($tour['duration']); ?></td>
                  <td><?php echo esc_html($tour['capacity']); ?></td>
                  <td><?php echo esc_html(Tours::formatPrice($tour)); ?></td>
                  <td><a href="<?php echo esc_url($tour['url']); ?>"><?php esc_html_e('Book This Tour', 'walkridge'); ?></a></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <?php } ?>
      </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

/** @param array<string, mixed> $attrs */
function wr_render_pathway_cards(array $attrs): string
{
    $leftUrl = esc_url((string) (($attrs['leftUrl'] ?? '') !== '' ? $attrs['leftUrl'] : home_url('/tours#historical')));
    $rightUrl = esc_url((string) (($attrs['rightUrl'] ?? '') !== '' ? $attrs['rightUrl'] : home_url('/tours#after-dark')));
    ob_start();
    ?>
    <section class="section section-alt" id="pathways">
      <div class="wrap">
        <div class="section-head reveal">
          <span class="eyebrow"><?php echo esc_html((string) ($attrs['eyebrow'] ?? '')); ?></span>
          <h2><?php echo esc_html((string) ($attrs['heading'] ?? '')); ?></h2>
          <p><?php echo esc_html((string) ($attrs['text'] ?? '')); ?></p>
        </div>
        <div class="split-grid reveal">
          <a class="pathway" href="<?php echo $leftUrl; ?>"> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
            <img src="<?php echo esc_url(Identity::image('cannon')); ?>" alt="">
            <div class="inner">
              <span class="eyebrow"><?php echo esc_html((string) ($attrs['leftEyebrow'] ?? '')); ?></span>
              <h3><?php echo esc_html((string) ($attrs['leftTitle'] ?? '')); ?></h3>
              <p><?php echo esc_html((string) ($attrs['leftText'] ?? '')); ?></p>
            </div>
          </a>
          <a class="pathway lantern" href="<?php echo $rightUrl; ?>"> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
            <img src="<?php echo esc_url(Identity::image('downtown')); ?>" alt="">
            <div class="inner">
              <span class="eyebrow"><?php echo esc_html((string) ($attrs['rightEyebrow'] ?? '')); ?></span>
              <h3><?php echo esc_html((string) ($attrs['rightTitle'] ?? '')); ?></h3>
              <p><?php echo esc_html((string) ($attrs['rightText'] ?? '')); ?></p>
            </div>
          </a>
        </div>
      </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

/** @param array<string, mixed> $attrs */
function wr_render_about_split(array $attrs): string
{
    $text = wp_kses_post((string) ($attrs['text'] ?? ''));
    if ($text === '') {
        $text = '<p>'.esc_html__('Walkridge Battlefield Tours was founded by a former park ranger and a local family who wanted visitors to experience the battlefield the way locals understand it — grounded in primary sources and walked at a pace that lets the landscape do the talking.', 'walkridge').'</p>'
            .'<p>'.esc_html__('Every walking and bus tour is led by a guide certified through the same licensing program the national park uses.', 'walkridge').'</p>';
    }
    $primaryUrl = esc_url((string) (($attrs['primaryUrl'] ?? '') !== '' ? $attrs['primaryUrl'] : home_url('/guides')));
    $secondaryUrl = esc_url((string) (($attrs['secondaryUrl'] ?? '') !== '' ? $attrs['secondaryUrl'] : home_url('/area')));
    $customImageUrl = (string) ($attrs['imageUrl'] ?? '');
    $imgSrc = $customImageUrl !== '' ? esc_url($customImageUrl) : esc_url(Identity::image((string) ($attrs['imageKey'] ?? 'wentz')));
    $flip = ! empty($attrs['flip']);
    ob_start();
    ?>
    <section class="section">
      <div class="wrap">
        <div class="about-grid reveal<?php echo $flip ? ' is-flipped' : ''; ?>">
          <div class="about-copy-col">
            <span class="eyebrow"><?php echo esc_html((string) ($attrs['eyebrow'] ?? '')); ?></span>
            <h2><?php echo esc_html((string) ($attrs['heading'] ?? '')); ?></h2>
            <div class="about-copy"><?php echo $text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped?></div>
            <div class="about-ctas">
              <a href="<?php echo $primaryUrl; ?>" class="btn btn-outline-dark btn-sm"><?php echo esc_html((string) ($attrs['primaryLabel'] ?? '')); ?></a> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
              <a href="<?php echo $secondaryUrl; ?>" class="btn btn-ghost btn-sm"><?php echo esc_html((string) ($attrs['secondaryLabel'] ?? '')); ?></a> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
            </div>
          </div>
          <figure class="about-media">
            <img src="<?php echo $imgSrc; ?>" alt=""> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
            <figcaption><?php echo esc_html((string) ($attrs['caption'] ?? '')); ?></figcaption>
          </figure>
        </div>
      </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

/** @param array<string, mixed> $attrs */
function wr_render_book_band(array $attrs): string
{
    return view('partials.book-band', [
        'heading' => (string) ($attrs['heading'] ?? ''),
        'text' => (string) ($attrs['text'] ?? ''),
        'buttonLabel' => (string) ($attrs['buttonLabel'] ?? ''),
    ])->render();
}

/** @param array<string, mixed> $attrs */
function wr_render_cta_band(array $attrs): string
{
    $heading = esc_html((string) ($attrs['heading'] ?? ''));
    $text = esc_html((string) ($attrs['text'] ?? ''));
    $eyebrow = esc_html((string) ($attrs['eyebrow'] ?? ''));
    $label = esc_html((string) (($attrs['buttonLabel'] ?? '') !== '' ? $attrs['buttonLabel'] : Identity::ctaLabel()));
    $url = esc_url((string) (($attrs['buttonUrl'] ?? '') !== '' ? $attrs['buttonUrl'] : Identity::shopUrl()));
    ob_start();
    ?>
    <section class="section">
      <div class="wrap">
        <div class="section-head reveal">
          <?php if ($eyebrow !== '') { ?><span class="eyebrow"><?php echo $eyebrow; ?></span><?php } ?> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
          <?php if ($heading !== '') { ?><h2><?php echo $heading; ?></h2><?php } ?> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
          <?php if ($text !== '') { ?><p><?php echo $text; ?></p><?php } ?> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
          <p class="cta-band__action"><a class="btn btn-primary" href="<?php echo $url; ?>"><?php echo $label; ?></a></p> // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped on assignment
        </div>
      </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

/** @return array<string, array<string, mixed>> */
function wr_get_custom_block_definitions(): array
{
    $defs = get_option('wr_custom_blocks', []);

    return is_array($defs) ? $defs : [];
}

/** @param array<string, mixed> $attrs */
function wr_render_custom_block(array $attrs): string
{
    $id = sanitize_key((string) ($attrs['blockId'] ?? ''));
    $defs = wr_get_custom_block_definitions();
    if ($id === '' || ! isset($defs[$id])) {
        return '';
    }
    $def = $defs[$id];
    $values = is_array($attrs['values'] ?? null) ? $attrs['values'] : [];
    ob_start();
    echo '<section class="section"><div class="wrap reveal">';
    echo '<span class="eyebrow">'.esc_html((string) ($def['title'] ?? $id)).'</span>';
    foreach ((array) ($def['fields'] ?? []) as $field) {
        $name = sanitize_key((string) ($field['name'] ?? ''));
        if ($name === '') {
            continue;
        }
        $label = esc_html((string) ($field['label'] ?? $name));
        $raw = $values[$name] ?? ($field['default'] ?? '');
        $type = sanitize_key((string) ($field['type'] ?? 'text'));
        if ($type === 'image' && is_string($raw) && $raw !== '') {
            echo '<p><img src="'.esc_url($raw).'" alt="'.esc_attr($label).'" class="wr-field-img"></p>';
        } elseif ($type === 'toggle') {
            if (! empty($raw)) {
                echo '<p><strong>'.esc_html($label).'</strong></p>';
            }
        } elseif ($type === 'url' && is_string($raw) && $raw !== '') {
            echo '<p><a href="'.esc_url($raw).'">'.esc_html($label).'</a></p>';
        } elseif ($type === 'textarea') {
            echo '<p>'.nl2br(esc_html((string) $raw)).'</p>';
        } else {
            echo '<p><strong>'.esc_html($label).':</strong> '.esc_html((string) $raw).'</p>';
        }
    }
    echo '</div></section>';

    return (string) ob_get_clean();
}
