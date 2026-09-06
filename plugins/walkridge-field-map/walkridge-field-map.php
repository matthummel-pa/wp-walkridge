<?php

/**
 * Plugin Name:  Walkridge Field Map
 * Plugin URI:   https://github.com/matthummel-pa/wp-walkridge
 * Description:  Interactive OpenLayers battlefield map for the Gettysburg area page. Provides the [wr_field_map] shortcode — monument search (~800 OSM pins), tour-location pins, satellite/street basemap toggle, layer filters, click popups with public-domain photos, guest field-itinerary builder (Save as PDF, Print) and an owner admin panel. No external API keys required.
 * Version:      1.0.0
 * Author:       Ridges & Valleys Studio
 * License:      GPLv2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  wr-field-map
 * Requires PHP: 8.1
 * Requires at least: 6.4
 */
defined('ABSPATH') || exit;

define('WRFM_VERSION', '1.0.0');
define('WRFM_FILE', __FILE__);
define('WRFM_DIR', plugin_dir_path(__FILE__));
define('WRFM_URL', plugin_dir_url(__FILE__));

require_once WRFM_DIR.'includes/class-wrfm-plugin.php';
require_once WRFM_DIR.'includes/class-wrfm-rest.php';
require_once WRFM_DIR.'includes/class-wrfm-admin.php';

WRFM_Plugin::instance();
