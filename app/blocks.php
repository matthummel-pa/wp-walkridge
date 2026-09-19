<?php

/**
 * Walkridge Gutenberg blocks — registration, editor assets, patterns.
 *
 * Blocks are server-side rendered (save returns null).
 * Editor UI: resources/js/blocks/index.js (via editor.js).
 */

namespace App;

use App\Support\BlockMigration;
use App\Support\DemoLayouts;
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
    $shared = array_merge(wr_typo_attributes(), wr_band_attributes());
    $heroShared = array_merge(wr_hero_attributes(), $shared);

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
                'stats' => ['type' => 'string', 'default' => "14 | Years Guiding\n4.9★ | Average Rating\n15 | Max Group Size\n5 | Tour Experiences"],
                'marquee' => ['type' => 'string', 'default' => 'Cemetery Ridge | McPherson Ridge | Little Round Top | Devil’s Den | High Water Mark | Lincoln Square | David Wills House | Baltimore Street | Seminary Ridge'],
                'leftPathTitle' => ['type' => 'string', 'default' => 'Walk the field'],
                'leftPathText' => ['type' => 'string', 'default' => 'Daylight tours on Cemetery Ridge, Little Round Top, and the High Water Mark.'],
                'rightPathTitle' => ['type' => 'string', 'default' => 'Walk after dark'],
                'rightPathText' => ['type' => 'string', 'default' => 'Lantern-lit downtown accounts. Real names, letters, and streets — not jump scares.'],
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
                'headingAlign' => ['type' => 'string', 'default' => 'center'],
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
                'leftImageUrl' => ['type' => 'string', 'default' => ''],
                'leftImageId' => ['type' => 'integer', 'default' => 0],
                'rightEyebrow' => ['type' => 'string', 'default' => 'After Dark'],
                'rightTitle' => ['type' => 'string', 'default' => 'Evening Lantern Walk'],
                'rightText' => ['type' => 'string', 'default' => 'The historic downtown and the town square. Candlelit storytelling from the record.'],
                'rightUrl' => ['type' => 'string', 'default' => ''],
                'rightImageUrl' => ['type' => 'string', 'default' => ''],
                'rightImageId' => ['type' => 'integer', 'default' => 0],
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
                'imageId' => ['type' => 'integer', 'default' => 0],
                'imagePosition' => ['type' => 'string', 'default' => 'center'],
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
        'walkridge/contact-desk' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_contact_desk',
            'attributes' => [
                'eyebrow' => ['type' => 'string', 'default' => 'Reach Us'],
                'heading' => ['type' => 'string', 'default' => 'Ticket office & guest services'],
                'formEyebrow' => ['type' => 'string', 'default' => 'Send a Message'],
                'formHeading' => ['type' => 'string', 'default' => 'Ask us anything'],
                'showNap' => ['type' => 'boolean', 'default' => true],
                'showForm' => ['type' => 'boolean', 'default' => true],
            ],
        ],
        'walkridge/faq-list' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_faq_list',
            'attributes' => [
                'eyebrow' => ['type' => 'string', 'default' => 'FAQ'],
                'heading' => ['type' => 'string', 'default' => 'Before you book'],
                'items' => ['type' => 'string', 'default' => "Where do tours meet? | Walking and bus tours meet at a sample ticket office at 100 Sample Street — not a live storefront. The lantern walk uses a sample downtown meet at the Lincoln Square flagpole.\nAre tours ADA-accessible? | The bus loop is the accessible option. Walking tours cover uneven ground.\nWhat is your cancellation policy? | Cancel up to 24 hours before departure for a full refund. See the Refund Policy page."],
            ],
        ],
        'walkridge/guide-roster' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_guide_roster',
            'attributes' => [
                'eyebrow' => ['type' => 'string', 'default' => 'The Desk'],
                'heading' => ['type' => 'string', 'default' => 'Licensed battlefield guides'],
                'items' => ['type' => 'string', 'default' => "Eleanor Voss | Lead walking guide | Twenty years on the field. Primary sources first, then the landscape.\nJames Whitaker | Bus & accessibility | Former interpreter. Keeps the ADA loop paced for questions.\nMaya Trent | Evening lanterns | Civilian streets after dark, letters and the town square."],
            ],
        ],
        'walkridge/area-facts' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_area_facts',
            'attributes' => [
                'eyebrow' => ['type' => 'string', 'default' => 'Find Us'],
                'heading' => ['type' => 'string', 'default' => 'Meeting points, parking, and office hours'],
                'parking' => ['type' => 'string', 'default' => 'Sample ticket office at 100 Sample Street, Gettysburg, PA 17325 — not a live storefront. Use downtown public lots near Lincoln Square.'],
                'meeting' => ['type' => 'string', 'default' => 'Walking and bus tours leave from the sample office. Evening lantern walks meet at the Lincoln Square flagpole — look for a lit lantern.'],
                'directions' => ['type' => 'string', 'default' => 'US-15 to the Gettysburg exits, then Baltimore Street toward downtown. US-30 leads into Lincoln Square. Fiction address only.'],
            ],
        ],
        'walkridge/timeline' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_timeline',
            'attributes' => [
                'eyebrow' => ['type' => 'string', 'default' => 'July 1–3, 1863'],
                'heading' => ['type' => 'string', 'default' => 'Three days, walked in order.'],
                'text' => ['type' => 'string', 'default' => 'Daytime tours stitch the battle to the ground — not a greatest-hits montage.'],
                'items' => ['type' => 'string', 'default' => "Day One | McPherson Ridge | The opening fight west of town.\nDay Two | Little Round Top | The southern flank and the rocks of Devil’s Den.\nDay Three | High Water Mark | Cemetery Ridge and the final assault."],
            ],
        ],
        'walkridge/card-grid' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_card_grid',
            'attributes' => [
                'variant' => ['type' => 'string', 'default' => 'expect'],
                'alt' => ['type' => 'boolean', 'default' => false],
                'eyebrow' => ['type' => 'string', 'default' => ''],
                'heading' => ['type' => 'string', 'default' => ''],
                'text' => ['type' => 'string', 'default' => ''],
                'items' => ['type' => 'string', 'default' => ''],
            ],
        ],
        'walkridge/reviews' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_reviews',
            'attributes' => [
                'eyebrow' => ['type' => 'string', 'default' => 'Guest Reviews'],
                'heading' => ['type' => 'string', 'default' => 'What guests say after the walk.'],
                'text' => ['type' => 'string', 'default' => 'Sample reviews shown for this concept design.'],
                'items' => ['type' => 'string', 'default' => "Our guide made three days of history feel like one afternoon. | Karen D. | Ridge hike\nThe lantern walk was genuinely moving, not gimmicky. | Ray P. | Lantern walk\nBooked the bus tour for my in-laws who can't walk far. | Sandra M. | Bus tour"],
            ],
        ],
        'walkridge/journal-cards' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_journal_cards',
            'attributes' => [
                'eyebrow' => ['type' => 'string', 'default' => 'Field Notes'],
                'heading' => ['type' => 'string', 'default' => 'Read before you walk.'],
                'text' => ['type' => 'string', 'default' => ''],
                'items' => ['type' => 'string', 'default' => ''],
            ],
        ],
        'walkridge/town-grid' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_town_grid',
            'attributes' => [
                'eyebrow' => ['type' => 'string', 'default' => 'Nearby Towns'],
                'heading' => ['type' => 'string', 'default' => 'Adams County & beyond'],
                'text' => ['type' => 'string', 'default' => ''],
                'items' => ['type' => 'string', 'default' => "Biglerville | ~9 mi N\nLittlestown | ~10 mi SE\nNew Oxford | ~9 mi E\nHanover | ~15 mi SE"],
            ],
        ],
        'walkridge/copy-section' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_copy_section',
            'attributes' => [
                'eyebrow' => ['type' => 'string', 'default' => ''],
                'heading' => ['type' => 'string', 'default' => ''],
                'text' => ['type' => 'string', 'default' => ''],
                'alt' => ['type' => 'boolean', 'default' => false],
            ],
        ],
        'walkridge/refund-policy' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_refund_policy',
            'attributes' => [
                'effectiveDate' => ['type' => 'string', 'default' => 'September 2, 2026'],
                'storeName' => ['type' => 'string', 'default' => ''],
                'storeUrl' => ['type' => 'string', 'default' => ''],
                'contactEmail' => ['type' => 'string', 'default' => ''],
                'refundWindowDays' => ['type' => 'number', 'default' => 30],
                'resolutionDays' => ['type' => 'number', 'default' => 7],
                'duplicateDays' => ['type' => 'number', 'default' => 7],
                'responseDays' => ['type' => 'number', 'default' => 2],
                'paymentDaysMin' => ['type' => 'number', 'default' => 5],
                'paymentDaysMax' => ['type' => 'number', 'default' => 10],
            ],
        ],
        'walkridge/area-map' => [
            'render_callback' => __NAMESPACE__.'\\wr_render_area_map',
            'attributes' => [
                'eyebrow' => ['type' => 'string', 'default' => 'Find Us'],
                'heading' => ['type' => 'string', 'default' => 'Meeting points & office hours.'],
                'text' => ['type' => 'string', 'default' => ''],
                'variant' => ['type' => 'string', 'default' => 'static'],
                'imageKey' => ['type' => 'string', 'default' => 'downtown'],
                'imageUrl' => ['type' => 'string', 'default' => ''],
                'imageId' => ['type' => 'integer', 'default' => 0],
                'imageAlt' => ['type' => 'string', 'default' => 'Downtown Gettysburg near Lincoln Square'],
                'showPin' => ['type' => 'boolean', 'default' => true],
                'pinX' => ['type' => 'number', 'default' => 50],
                'pinY' => ['type' => 'number', 'default' => 50],
                'locationMode' => ['type' => 'string', 'default' => 'coordinates'],
                'latitude' => ['type' => 'number', 'default' => 39.83092],
                'longitude' => ['type' => 'number', 'default' => -77.23114],
                'zipcode' => ['type' => 'string', 'default' => '17325'],
                'mapZoom' => ['type' => 'number', 'default' => 14],
                'mapEmbedUrl' => ['type' => 'string', 'default' => ''],
                'mapHeight' => ['type' => 'number', 'default' => 420],
                'layout' => ['type' => 'string', 'default' => 'split'],
                'iconTone' => ['type' => 'string', 'default' => 'gold'],
                'cards' => ['type' => 'string', 'default' => "Ticket Office & Day Tour Meeting Point | 100 Sample Street, Gettysburg, PA 17325. Concept placeholder — not a live ticket office. Downtown public lots and metered parking sit near Lincoln Square. | pin\nEvening Lantern Walk Meeting Point | Sample downtown meet. Lincoln Square is tour geography, not a live business address. Look for your guide holding a lit lantern. | lantern\nOffice Hours | Mon–Sun, 8:00 AM – 6:00 PM (April–November). Thu–Sun, 9:00 AM – 4:00 PM (December–March). | clock"],
            ],
        ],
    ];

    foreach ($blocks as $name => $args) {
        $slug = str_replace('walkridge/', '', $name);
        $extra = $name === 'walkridge/home-hero' ? $heroShared : $shared;
        $args['attributes'] = wr_merge_block_attributes($args['attributes'] ?? [], $extra);
        $dir = get_template_directory().'/blocks/'.$slug;
        $settings = array_merge([
            'api_version' => 3,
        ], $args);
        if (is_readable($dir.'/block.json')) {
            register_block_type($dir, $settings);
        } else {
            register_block_type($name, $settings);
        }
    }
}

add_action('enqueue_block_editor_assets', function (): void {
    $payload = [
        'themeUri' => get_template_directory_uri(),
        'customBlocks' => wr_get_custom_block_definitions(),
        'shopUrl' => Identity::shopUrl(),
        'toursUrl' => home_url('/tours'),
        'guidesUrl' => home_url('/guides'),
        'areaUrl' => home_url('/area'),
    ];
    // editor.js (Vite) imports the block registrations; expose config first.
    wp_add_inline_script(
        'wp-blocks',
        'window.WALKRIDGE_BLOCKS = '.wp_json_encode($payload).';',
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
        'content' => DemoLayouts::forSlug('home'),
    ]);
    register_block_pattern('walkridge/contact-pattern', [
        'title' => __('Walkridge — Contact desk', 'walkridge'),
        'categories' => ['walkridge'],
        'content' => DemoLayouts::forSlug('contact'),
    ]);
    register_block_pattern('walkridge/guides-pattern', [
        'title' => __('Walkridge — Guides', 'walkridge'),
        'categories' => ['walkridge'],
        'content' => DemoLayouts::forSlug('guides'),
    ]);
    register_block_pattern('walkridge/area-pattern', [
        'title' => __('Walkridge — Area', 'walkridge'),
        'categories' => ['walkridge'],
        'content' => DemoLayouts::forSlug('area'),
    ]);
    register_block_pattern('walkridge/tours-pattern', [
        'title' => __('Walkridge — Tours', 'walkridge'),
        'categories' => ['walkridge'],
        'content' => DemoLayouts::forSlug('tours'),
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
    $method = isset($_SERVER['REQUEST_METHOD']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])) : '';
    if ($method === 'POST' && isset($_POST['wr_blocks_nonce'])) {
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
    echo '<p>'.esc_html__('Move leftover page meta into Walkridge Gutenberg blocks, or re-seed demo page layouts. Page copy is edited in the block editor — not custom fields.', 'walkridge').'</p>';
    echo '<form method="post" class="wr-tools-form">';
    wp_nonce_field('wr_blocks_tools', 'wr_blocks_nonce');
    echo '<button class="button button-primary" name="wr_blocks_action" value="migrate">'.esc_html__('Migrate page fields → blocks', 'walkridge').'</button>';
    echo '<button class="button" name="wr_blocks_action" value="seed">'.esc_html__('Seed demo block layouts', 'walkridge').'</button>';
    echo '</form></div>';
}

/** @param array<string, mixed> $attrs */
function wr_render_home_hero(array $attrs): string
{
    $title = (string) ($attrs['title'] ?? '');
    $text = (string) ($attrs['text'] ?? '');
    $eyebrow = (string) ($attrs['eyebrow'] ?? '');
    if ($eyebrow === '') {
        $eyebrow = Identity::brandName().' · '.Identity::brandSub();
    }
    $primary = (string) ($attrs['primaryLabel'] ?? '');
    if ($primary === '') {
        $primary = Identity::ctaLabel();
    }
    $secondary = (string) ($attrs['secondaryLabel'] ?? '');
    if ($secondary === '') {
        $secondary = __('See All Tours', 'walkridge');
    }
    $img = wr_block_image_url($attrs, (string) ($attrs['imageKey'] ?? 'cannon'));
    $shop = (string) ($attrs['primaryUrl'] ?? '') !== '' ? (string) $attrs['primaryUrl'] : Identity::shopUrl();
    $tours = (string) ($attrs['secondaryUrl'] ?? '') !== '' ? (string) $attrs['secondaryUrl'] : home_url('/tours');
    $stats = wr_parse_piped_items((string) ($attrs['stats'] ?? ''));
    $leftTitle = (string) ($attrs['leftPathTitle'] ?? '');
    $leftText = (string) ($attrs['leftPathText'] ?? '');
    $rightTitle = (string) ($attrs['rightPathTitle'] ?? '');
    $rightText = (string) ($attrs['rightPathText'] ?? '');
    $marqueeRaw = (string) ($attrs['marquee'] ?? '');
    $marqueeParts = array_values(array_filter(array_map('trim', preg_split('/\||\n/', $marqueeRaw) ?: [])));

    ob_start();
    ?>
    <section class="<?php echo esc_attr(wr_hero_class($attrs)); ?>" id="top" style="<?php echo esc_attr(wr_overlay_style($attrs)); ?>">
      <div class="hero-compass" aria-hidden="true">
        <svg viewBox="0 0 40 40" fill="none"><circle cx="20" cy="20" r="18.5" stroke="currentColor" stroke-width="1.1"/><path d="M20 4l3.4 16L20 36l-3.4-16z" fill="currentColor"/><path d="M4 20l16-3.4L36 20l-16 3.4z" fill="currentColor" opacity=".4"/><circle cx="20" cy="20" r="3.1" fill="currentColor"/></svg>
      </div>
      <div class="hero-media">
        <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr__('Historic Gettysburg battlefield monument', 'walkridge'); ?>" fetchpriority="high">
      </div>
      <div class="wrap hero-content">
        <p class="hero-badge"><?php echo esc_html($eyebrow); ?></p>
        <h1 class="hero-title"><?php echo wp_kses($title, ['em' => [], 'strong' => []]); ?></h1>
        <?php if ($text !== '') { ?>
          <p class="hero-lede"><?php echo wp_kses_post($text); ?></p>
        <?php } ?>
        <?php if ($leftTitle !== '' || $rightTitle !== '') { ?>
        <div class="hero-paths">
          <a class="path-card" href="<?php echo esc_url($tours); ?>#historical">
            <b><?php echo $leftTitle !== '' ? esc_html($leftTitle) : esc_html__('Walk the field', 'walkridge'); ?></b>
            <span><?php echo esc_html($leftText); ?></span>
          </a>
          <a class="path-card is-lantern" href="<?php echo esc_url($tours); ?>#after-dark">
            <b><span class="flame" aria-hidden="true"></span><?php echo $rightTitle !== '' ? esc_html($rightTitle) : esc_html__('Walk after dark', 'walkridge'); ?></b>
            <span><?php echo esc_html($rightText); ?></span>
          </a>
        </div>
        <?php } ?>
        <div class="hero-ctas">
          <a href="<?php echo esc_url($shop); ?>" class="btn btn-primary"><?php echo esc_html($primary); ?></a>
          <a href="<?php echo esc_url($tours); ?>" class="btn btn-outline"><?php echo esc_html($secondary); ?></a>
        </div>
        <?php if ($stats !== []) { ?>
        <div class="hero-stats">
          <?php foreach ($stats as $row) { ?>
            <div class="hero-stat"><b><?php echo esc_html($row[0]); ?></b><span><?php echo esc_html($row[1] ?? ''); ?></span></div>
          <?php } ?>
        </div>
        <?php } ?>
      </div>
    </section>
    <?php
    if ($marqueeParts !== []) {
        echo '<div class="marquee" aria-hidden="true"><div class="marquee-track">';
        foreach (array_merge($marqueeParts, $marqueeParts) as $part) {
            echo '<span>'.esc_html($part).'</span>';
        }
        echo '</div></div>';
    }

    return (string) ob_get_clean();
}

/** @param array<string, mixed> $attrs */
function wr_render_page_intro(array $attrs): string
{
    $slug = (string) get_post_field('post_name', get_the_ID());
    $defaults = PageFields::defaultsForSlug($slug);
    $eyebrow = (string) (($attrs['eyebrow'] ?? '') !== '' ? $attrs['eyebrow'] : $defaults['eyebrow']);
    $heading = (string) (($attrs['heading'] ?? '') !== '' ? $attrs['heading'] : $defaults['heading']);
    $intro = (string) (($attrs['intro'] ?? '') !== '' ? $attrs['intro'] : $defaults['intro']);
    if ($heading === '' && $intro === '') {
        return '';
    }
    ob_start();
    ?>
    <section class="<?php echo esc_attr(wr_band_section_class($attrs, wr_head_class($attrs, 'page-intro'))); ?>">
      <div class="wrap reveal">
        <?php if ($eyebrow !== '') { ?><span class="eyebrow"><?php echo esc_html($eyebrow); ?></span><?php } ?>
        <?php if ($heading !== '') { ?><h1><?php echo esc_html($heading); ?></h1><?php } ?>
        <?php if ($intro !== '') { ?><p><?php echo esc_html($intro); ?></p><?php } ?>
      </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

/** @param array<string, mixed> $attrs */
function wr_render_info_strip(array $attrs): string
{
    return view('partials.info-strip', ['attrs' => $attrs])->render();
}

/** @param array<string, mixed> $attrs */
function wr_render_section_heading(array $attrs): string
{
    $eyebrow = (string) ($attrs['eyebrow'] ?? '');
    $heading = (string) ($attrs['heading'] ?? '');
    $text = (string) ($attrs['text'] ?? '');
    $anchor = sanitize_title((string) ($attrs['anchor'] ?? ''));
    $alt = ! empty($attrs['alt']);
    $sectionClass = wr_band_section_class($attrs, $alt ? 'section section-alt' : 'section');
    $headTag = wr_heading_tag($attrs, 'h2');
    ob_start();
    ?>
    <section class="<?php echo esc_attr($sectionClass); ?>"<?php echo $anchor !== '' ? ' id="'.esc_attr($anchor).'"' : ''; ?>>
      <div class="wrap">
        <div class="<?php echo esc_attr(wr_head_class($attrs)); ?>">
          <?php if ($eyebrow !== '') { ?><span class="eyebrow"><?php echo esc_html($eyebrow); ?></span><?php } ?>
          <?php if ($heading !== '') { ?><<?php echo esc_attr($headTag); ?>><?php echo wp_kses($heading, ['em' => [], 'strong' => []]); ?></<?php echo esc_attr($headTag); ?>><?php } ?>
          <?php if ($text !== '') { ?><p><?php echo wp_kses_post($text); ?></p><?php } ?>
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
    $eyebrow = (string) ($attrs['eyebrow'] ?? '');
    $heading = (string) ($attrs['heading'] ?? '');
    $text = (string) ($attrs['text'] ?? '');

    $headTag = wr_heading_tag($attrs);
    $tourSectionId = sanitize_title((string) ($attrs['anchor'] ?? ''));
    if ($tourSectionId === '') {
        $tourSectionId = 'historical';
    }
    ob_start();
    ?>
    <section class="<?php echo esc_attr(wr_band_section_class($attrs)); ?>" id="<?php echo esc_attr($tourSectionId); ?>">
      <div class="wrap">
        <?php if ($eyebrow || $heading || $text) { ?>
        <div class="<?php echo esc_attr(wr_head_class($attrs)); ?>" id="historical">
          <?php if ($eyebrow !== '') { ?><span class="eyebrow"><?php echo esc_html($eyebrow); ?></span><?php } ?>
          <?php if ($heading !== '') { ?><<?php echo esc_attr($headTag); ?>><?php echo esc_html($heading); ?></<?php echo esc_attr($headTag); ?>><?php } ?>
          <?php if ($text !== '') { ?><p><?php echo esc_html($text); ?></p><?php } ?>
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
                  echo wp_kses_post(view('partials.tour-card', ['tour' => $tour])->render());
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
    $leftImg = wr_block_image_url($attrs, 'cannon', 'leftImageUrl', 'leftImageId');
    $rightImg = wr_block_image_url($attrs, 'downtown', 'rightImageUrl', 'rightImageId');
    $headTag = wr_heading_tag($attrs);
    ob_start();
    ?>
    <section class="<?php echo esc_attr(wr_band_section_class($attrs, 'section section-alt')); ?>" id="pathways">
      <div class="wrap">
        <div class="<?php echo esc_attr(wr_head_class($attrs)); ?>">
          <span class="eyebrow"><?php echo esc_html((string) ($attrs['eyebrow'] ?? '')); ?></span>
          <<?php echo esc_attr($headTag); ?>><?php echo esc_html((string) ($attrs['heading'] ?? '')); ?></<?php echo esc_attr($headTag); ?>>
          <p><?php echo esc_html((string) ($attrs['text'] ?? '')); ?></p>
        </div>
        <div class="split-grid reveal">
          <a class="pathway" href="<?php echo esc_url($leftUrl); ?>">
            <img src="<?php echo esc_url($leftImg); ?>" alt="">
            <div class="inner">
              <span class="eyebrow"><?php echo esc_html((string) ($attrs['leftEyebrow'] ?? '')); ?></span>
              <h3><?php echo esc_html((string) ($attrs['leftTitle'] ?? '')); ?></h3>
              <p><?php echo esc_html((string) ($attrs['leftText'] ?? '')); ?></p>
            </div>
          </a>
          <a class="pathway lantern" href="<?php echo esc_url($rightUrl); ?>">
            <img src="<?php echo esc_url($rightImg); ?>" alt="">
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
    $imgSrc = wr_block_image_url($attrs, (string) ($attrs['imageKey'] ?? 'wentz'));
    $flip = ! empty($attrs['flip']);
    $headTag = wr_heading_tag($attrs);
    $imgPos = sanitize_key((string) ($attrs['imagePosition'] ?? 'center'));
    $mediaClass = 'about-media';
    if (in_array($imgPos, ['top', 'bottom', 'left', 'right'], true)) {
        $mediaClass .= ' wr-img-pos--'.$imgPos;
    }
    ob_start();
    ?>
    <section class="<?php echo esc_attr(wr_band_section_class($attrs)); ?>">
      <div class="wrap">
        <div class="about-grid reveal<?php echo $flip ? ' is-flipped' : ''; ?>">
          <div class="<?php echo esc_attr(wr_head_class($attrs, 'about-copy-col')); ?>">
            <span class="eyebrow"><?php echo esc_html((string) ($attrs['eyebrow'] ?? '')); ?></span>
            <<?php echo esc_attr($headTag); ?>><?php echo esc_html((string) ($attrs['heading'] ?? '')); ?></<?php echo esc_attr($headTag); ?>>
            <div class="about-copy"><?php echo wp_kses_post($text); ?></div>
            <div class="about-ctas">
              <a href="<?php echo esc_url($primaryUrl); ?>" class="btn btn-outline-dark btn-sm"><?php echo esc_html((string) ($attrs['primaryLabel'] ?? '')); ?></a>
              <a href="<?php echo esc_url($secondaryUrl); ?>" class="btn btn-ghost btn-sm"><?php echo esc_html((string) ($attrs['secondaryLabel'] ?? '')); ?></a>
            </div>
          </div>
          <figure class="<?php echo esc_attr($mediaClass); ?>">
            <img src="<?php echo esc_url($imgSrc); ?>" alt="">
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
        'attrs' => $attrs,
    ])->render();
}

/** @param array<string, mixed> $attrs */
function wr_render_cta_band(array $attrs): string
{
    $heading = (string) ($attrs['heading'] ?? '');
    $text = (string) ($attrs['text'] ?? '');
    $eyebrow = (string) ($attrs['eyebrow'] ?? '');
    $label = (string) (($attrs['buttonLabel'] ?? '') !== '' ? $attrs['buttonLabel'] : Identity::ctaLabel());
    $url = (string) (($attrs['buttonUrl'] ?? '') !== '' ? $attrs['buttonUrl'] : Identity::shopUrl());
    $headTag = wr_heading_tag($attrs);
    ob_start();
    ?>
    <section class="<?php echo esc_attr(wr_band_section_class($attrs)); ?>">
      <div class="wrap">
        <div class="<?php echo esc_attr(wr_head_class($attrs)); ?>">
          <?php if ($eyebrow !== '') { ?><span class="eyebrow"><?php echo esc_html($eyebrow); ?></span><?php } ?>
          <?php if ($heading !== '') { ?><<?php echo esc_attr($headTag); ?>><?php echo esc_html($heading); ?></<?php echo esc_attr($headTag); ?>><?php } ?>
          <?php if ($text !== '') { ?><p><?php echo esc_html($text); ?></p><?php } ?>
          <p class="cta-band__action"><a class="btn btn-primary" href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a></p>
        </div>
      </div>
    </section>
    <?php
    return (string) ob_get_clean();
}

/**
 * @return list<array{0: string, 1: string, 2?: string}>
 */
function wr_parse_piped_items(string $raw): array
{
    $rows = [];
    foreach (preg_split('/\r\n|\r|\n/', $raw) ?: [] as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $parts = array_map('trim', explode('|', $line));
        if ($parts[0] === '') {
            continue;
        }
        $rows[] = $parts;
    }

    return $rows;
}

/** @param array<string, mixed> $attrs */
function wr_render_contact_desk(array $attrs): string
{
    $eyebrow = (string) ($attrs['eyebrow'] ?? '');
    $heading = (string) ($attrs['heading'] ?? '');
    $formEyebrow = (string) ($attrs['formEyebrow'] ?? '');
    $formHeading = (string) ($attrs['formHeading'] ?? '');
    $showNap = ! empty($attrs['showNap']);
    $showForm = ! empty($attrs['showForm']);
    $note = '';
    // phpcs:disable WordPress.Security.NonceVerification.Recommended -- public query-string form status, sanitized below
    $formStatus = isset($_GET['wr_form']) ? sanitize_key(wp_unslash((string) $_GET['wr_form'])) : '';
    if ($formStatus !== '' && isset($_GET['wr_msg'])) {
        $note = sanitize_text_field(wp_unslash((string) $_GET['wr_msg']));
    }
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    $headTag = wr_heading_tag($attrs);
    ob_start();
    ?>
    <section class="<?php echo esc_attr(wr_band_section_class($attrs)); ?>">
      <div class="wrap">
        <div class="contact-grid reveal">
          <?php if ($showNap) { ?>
          <div class="<?php echo esc_attr(wr_head_class($attrs, '')); ?>">
            <?php if ($eyebrow !== '') { ?><span class="eyebrow"><?php echo esc_html($eyebrow); ?></span><?php } ?>
            <?php if ($heading !== '') { ?><<?php echo esc_attr($headTag); ?> class="contact-section-heading"><?php echo esc_html($heading); ?></<?php echo esc_attr($headTag); ?>><?php } ?>
            <div class="meet-card">
              <p>
                <a href="<?php echo esc_url(Identity::phoneHref()); ?>" class="nap-block nap-block--strong"><?php echo esc_html(Identity::phone()); ?></a><br>
                <?php if (Identity::showDemoChrome()) { ?>
                  <span class="nap-note"><?php esc_html_e('Fiction-range sample number — not a live line', 'walkridge'); ?></span><br>
                <?php } ?>
                <a href="<?php echo esc_url('mailto:'.Identity::email()); ?>" class="contact-email-link"><?php echo esc_html(Identity::email()); ?></a>
              </p>
              <p><?php echo wp_kses(Identity::hoursHtml(), ['br' => []]); ?></p>
              <p><?php echo esc_html(Identity::addressLine()); ?></p>
            </div>
          </div>
          <?php } ?>
          <?php if ($showForm) { ?>
          <form class="contact-form" id="contactForm" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-wr-contact novalidate>
            <input type="hidden" name="action" value="wr_contact">
            <?php wp_nonce_field('wr_contact', 'wr_contact_nonce'); ?>
            <?php if ($formEyebrow !== '') { ?><span class="eyebrow"><?php echo esc_html($formEyebrow); ?></span><?php } ?>
            <?php if ($formHeading !== '') { ?><h2 class="contact-section-heading"><?php echo esc_html($formHeading); ?></h2><?php } ?>
            <div class="form-grid">
              <div class="field full">
                <label for="cName"><?php esc_html_e('Your name', 'walkridge'); ?></label>
                <input type="text" id="cName" name="cName" autocomplete="name" required>
              </div>
              <div class="field full">
                <label for="cPhone"><?php esc_html_e('Phone (optional)', 'walkridge'); ?></label>
                <input type="tel" id="cPhone" name="cPhone" autocomplete="tel" placeholder="<?php esc_attr_e('(717) 555-0100', 'walkridge'); ?>">
              </div>
              <div class="field full">
                <label for="cEmail"><?php esc_html_e('Email address', 'walkridge'); ?></label>
                <input type="email" id="cEmail" name="cEmail" autocomplete="email" required>
              </div>
              <div class="field full">
                <label for="cMsg"><?php esc_html_e('Message', 'walkridge'); ?></label>
                <textarea id="cMsg" name="cMsg" required></textarea>
              </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block"><?php esc_html_e('Send Message', 'walkridge'); ?></button>
            <p id="contactNote" role="status" aria-live="polite" class="contact-form__note"><?php echo esc_html($note); ?></p>
          </form>
          <?php } ?>
        </div>
      </div>
    </section>
    <?php

    return (string) ob_get_clean();
}

/** @param array<string, mixed> $attrs */
function wr_render_faq_list(array $attrs): string
{
    $rows = wr_parse_piped_items((string) ($attrs['items'] ?? ''));
    if ($rows === []) {
        return '';
    }
    $headTag = wr_heading_tag($attrs);
    ob_start();
    ?>
    <section class="<?php echo esc_attr(wr_band_section_class($attrs, 'section section-alt')); ?>">
      <div class="wrap">
        <div class="<?php echo esc_attr(wr_head_class($attrs)); ?>">
          <?php if (($attrs['eyebrow'] ?? '') !== '') { ?><span class="eyebrow"><?php echo esc_html((string) $attrs['eyebrow']); ?></span><?php } ?>
          <?php if (($attrs['heading'] ?? '') !== '') { ?><<?php echo esc_attr($headTag); ?>><?php echo esc_html((string) $attrs['heading']); ?></<?php echo esc_attr($headTag); ?>><?php } ?>
        </div>
        <dl class="wr-faq reveal">
          <?php foreach ($rows as $row) { ?>
            <dt><?php echo esc_html($row[0]); ?></dt>
            <dd><?php echo esc_html($row[1] ?? ''); ?></dd>
          <?php } ?>
        </dl>
      </div>
    </section>
    <?php

    return (string) ob_get_clean();
}

/** @param array<string, mixed> $attrs */
function wr_render_guide_roster(array $attrs): string
{
    $rows = wr_parse_piped_items((string) ($attrs['items'] ?? ''));
    if ($rows === []) {
        return '';
    }
    $headTag = wr_heading_tag($attrs);
    ob_start();
    ?>
    <section class="<?php echo esc_attr(wr_band_section_class($attrs)); ?>">
      <div class="wrap">
        <div class="<?php echo esc_attr(wr_head_class($attrs)); ?>">
          <?php if (($attrs['eyebrow'] ?? '') !== '') { ?><span class="eyebrow"><?php echo esc_html((string) $attrs['eyebrow']); ?></span><?php } ?>
          <?php if (($attrs['heading'] ?? '') !== '') { ?><<?php echo esc_attr($headTag); ?>><?php echo esc_html((string) $attrs['heading']); ?></<?php echo esc_attr($headTag); ?>><?php } ?>
        </div>
        <div class="wr-roster reveal">
          <?php foreach ($rows as $row) { ?>
            <article class="wr-roster__card">
              <h3><?php echo esc_html($row[0]); ?></h3>
              <?php if (! empty($row[1])) { ?><p class="wr-roster__role"><?php echo esc_html($row[1]); ?></p><?php } ?>
              <?php if (! empty($row[2])) { ?><p><?php echo esc_html($row[2]); ?></p><?php } ?>
            </article>
          <?php } ?>
        </div>
      </div>
    </section>
    <?php

    return (string) ob_get_clean();
}

/** @param array<string, mixed> $attrs */
function wr_render_area_facts(array $attrs): string
{
    $facts = [
        __('Parking', 'walkridge') => (string) ($attrs['parking'] ?? ''),
        __('Meeting point', 'walkridge') => (string) ($attrs['meeting'] ?? ''),
        __('Directions', 'walkridge') => (string) ($attrs['directions'] ?? ''),
    ];
    $headTag = wr_heading_tag($attrs);
    ob_start();
    ?>
    <section class="<?php echo esc_attr(wr_band_section_class($attrs)); ?>">
      <div class="wrap">
        <div class="<?php echo esc_attr(wr_head_class($attrs)); ?>">
          <?php if (($attrs['eyebrow'] ?? '') !== '') { ?><span class="eyebrow"><?php echo esc_html((string) $attrs['eyebrow']); ?></span><?php } ?>
          <?php if (($attrs['heading'] ?? '') !== '') { ?><<?php echo esc_attr($headTag); ?>><?php echo esc_html((string) $attrs['heading']); ?></<?php echo esc_attr($headTag); ?>><?php } ?>
        </div>
        <div class="wr-facts reveal">
          <?php foreach ($facts as $label => $text) {
              if ($text === '') {
                  continue;
              } ?>
            <article class="wr-facts__card">
              <h3><?php echo esc_html($label); ?></h3>
              <p><?php echo esc_html($text); ?></p>
            </article>
          <?php } ?>
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
    echo '<section class="'.esc_attr(wr_band_section_class($attrs, wr_head_class($attrs, 'section'))).'"><div class="wrap reveal">';
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
            echo '<p>'.wp_kses(nl2br(esc_html((string) $raw), false), ['br' => []]).'</p>';
        } else {
            echo '<p><strong>'.esc_html($label).':</strong> '.esc_html((string) $raw).'</p>';
        }
    }
    echo '</div></section>';

    return (string) ob_get_clean();
}
