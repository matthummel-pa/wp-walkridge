<?php

declare(strict_types=1);

/**
 * Shop-page layout helpers.
 *
 * Keeps 3-column grid and 12 products per page.
 * Schema and heavy SEO are not needed for walkridge at this stage.
 */

namespace App;

defined('ABSPATH') || exit;

/* Products per page */
add_filter('loop_shop_per_page', fn(): int => 12);

/* Column count — CSS Grid does the real work; this keeps WC li classes sane */
add_filter('loop_shop_columns', fn(): int => 3);
