<?php
defined('ABSPATH') || exit;

/**
 * WP Admin page — owner map editor.
 * Accessible at Tools → Field Map Admin.
 * The same OpenLayers map renders here with full admin controls
 * (add/edit/delete pins, save to WP Options via REST API).
 */
final class WRFM_Admin
{
    public static function register_menu(): void
    {
        add_management_page(
            __('Field Map Admin', 'wr-field-map'),
            __('Field Map', 'wr-field-map'),
            'manage_options',
            'wr-field-map',
            [self::class, 'render_page']
        );
    }

    public static function render_page(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions.', 'wr-field-map'));
        }

        $count = count((array) get_option('wrfm_places_custom', []));
        ?>
        <div class="wrap">
          <h1><?php esc_html_e('Gettysburg Field Map — Owner Admin', 'wr-field-map'); ?></h1>
          <p>
            <?php esc_html_e('Add, move, or remove tour pins on the battlefield map. Changes save to the WordPress database and appear live on the Area page.', 'wr-field-map'); ?>
            <?php if ($count) { ?>
              <strong>
                <?php printf(
                    /* translators: %d number of custom places */
                    esc_html(_n('%d custom place saved.', '%d custom places saved.', $count, 'wr-field-map')),
                    $count
                ); ?>
              </strong>
            <?php } ?>
          </p>

          <div id="hgfm-admin-app" data-hgfm-admin>
            <div class="hgfm-admin-layout">
              <div class="hgfm-admin-map-wrap">
                <div class="diorama-stage" data-diorama data-diorama-mode="admin"
                     style="min-height:520px;"
                     aria-label="<?php esc_attr_e('Interactive Gettysburg field map — admin', 'wr-field-map'); ?>">
                </div>
              </div>

              <div class="hgfm-admin-panel">
                <div class="hgfm-admin-toolbar">
                  <button type="button" id="addPlace" class="button button-secondary"><?php esc_html_e('+ Add pin', 'wr-field-map'); ?></button>
                  <button type="button" id="deletePlace" class="button"><?php esc_html_e('Delete selected', 'wr-field-map'); ?></button>
                  <button type="button" id="saveMap" class="button button-primary"><?php esc_html_e('Save', 'wr-field-map'); ?></button>
                  <button type="button" id="resetMap" class="button"><?php esc_html_e('Reset to defaults', 'wr-field-map'); ?></button>
                  <button type="button" id="exportMap" class="button"><?php esc_html_e('Export JSON', 'wr-field-map'); ?></button>
                </div>
                <p id="mapStatus" class="hgfm-admin-status" role="status" aria-live="polite"></p>

                <table class="wp-list-table widefat striped" style="margin-bottom:1rem;">
                  <thead>
                    <tr>
                      <th><?php esc_html_e('Name', 'wr-field-map'); ?></th>
                      <th><?php esc_html_e('Category', 'wr-field-map'); ?></th>
                      <th><?php esc_html_e('Lat / Lng', 'wr-field-map'); ?></th>
                      <th><?php esc_html_e('Visible', 'wr-field-map'); ?></th>
                    </tr>
                  </thead>
                  <tbody data-place-rows></tbody>
                </table>

                <form id="placeForm" class="hgfm-admin-form">
                  <h3><?php esc_html_e('Edit selected pin', 'wr-field-map'); ?></h3>
                  <p><label><?php esc_html_e('Title', 'wr-field-map'); ?><br>
                    <input name="title" type="text" class="widefat"></label></p>
                  <p><label><?php esc_html_e('Short description', 'wr-field-map'); ?><br>
                    <textarea name="blurb" class="widefat" rows="2"></textarea></label></p>
                  <p><label><?php esc_html_e('Category', 'wr-field-map'); ?><br>
                    <select name="category" class="widefat">
                      <?php
                      foreach (['ridge', 'hill', 'hike', 'downtown', 'meet', 'monument'] as $c) {
                          printf('<option value="%s">%s</option>', esc_attr($c), esc_html(ucfirst($c)));
                      }
        ?>
                    </select></label></p>
                  <p>
                    <label><?php esc_html_e('Tour link', 'wr-field-map'); ?><br>
                      <input name="tourHref" type="url" class="widefat" placeholder="<?php echo esc_url(home_url('/tours')); ?>"></label>
                    <label><?php esc_html_e('Link label', 'wr-field-map'); ?><br>
                      <input name="tourLabel" type="text" class="widefat"></label>
                  </p>
                  <p style="display:flex;gap:8px;flex-wrap:wrap;">
                    <label><?php esc_html_e('Lat', 'wr-field-map'); ?><br><input name="lat" type="number" step="0.00001" style="width:110px;"></label>
                    <label><?php esc_html_e('Lng', 'wr-field-map'); ?><br><input name="lng" type="number" step="0.00001" style="width:110px;"></label>
                  </p>
                  <p><label><input name="visible" type="checkbox" checked>
                    <?php esc_html_e('Visible on the public map', 'wr-field-map'); ?></label></p>
                </form>

                <hr>
                <h3><?php esc_html_e('Map view defaults', 'wr-field-map'); ?></h3>
                <form id="mapsConfigForm">
                  <p style="display:flex;gap:8px;flex-wrap:wrap;">
                    <label><?php esc_html_e('Zoom', 'wr-field-map'); ?><br>
                      <input name="zoom" type="number" step="0.1" value="13.4" style="width:80px;"></label>
                    <label><?php esc_html_e('Rotation (deg)', 'wr-field-map'); ?><br>
                      <input name="rotation" type="number" step="1" value="-10" style="width:80px;"></label>
                  </p>
                  <p class="description"><?php esc_html_e('Pan and zoom the map to the view you want, then click Save above.', 'wr-field-map'); ?></p>
                </form>
              </div>
            </div>
          </div>
        </div>
        <style>
          .hgfm-admin-layout{display:grid;gap:1.5rem;}
          @media(min-width:1280px){.hgfm-admin-layout{grid-template-columns:1.5fr 0.9fr;}}
          .hgfm-admin-map-wrap .diorama-stage{border-radius:4px;}
          .hgfm-admin-panel{overflow:auto;max-height:90vh;}
          .hgfm-admin-toolbar{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;}
          .hgfm-admin-status{min-height:1.4em;color:#46b450;font-size:13px;}
          .hgfm-admin-form p{margin:0 0 8px;}
        </style>
        <?php
    }
}
