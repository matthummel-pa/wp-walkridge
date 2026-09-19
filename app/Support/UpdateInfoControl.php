<?php

namespace App\Support;

use WP_Customize_Control;

/**
 * Read-only Customizer control: theme version and a link to Update Theme.
 * Loaded only from customize_register so WP_Customize_Control exists.
 */
class UpdateInfoControl extends WP_Customize_Control
{
    public $type = 'wr_update_info';

    public function render_content(): void
    {
        $theme = wp_get_theme();
        $version = esc_html($theme->get('Version') ?: '1.0.0');
        $name = esc_html($theme->get('Name'));
        $status_url = esc_url(admin_url('themes.php?page=wr-update-theme'));

        echo '<style>
            .wr-update-info { font-size: 13px; line-height: 1.6; }
            .wr-update-info .wr-version-badge {
                display: inline-block;
                background: #1d2327;
                color: #f0f0f0;
                font-family: monospace;
                padding: 2px 8px;
                border-radius: 3px;
                font-size: 12px;
                margin-bottom: 10px;
            }
            .wr-update-info .wr-status-link {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                margin-top: 8px;
                text-decoration: none;
                font-weight: 600;
            }
        </style>';

        echo '<div class="wr-update-info">';
        echo '<strong>'.$name.'</strong><br>';
        echo '<span class="wr-version-badge">v'.$version.'</span><br>';
        echo '<span>'.esc_html__('Check build status, install a zip, or pull from GitHub on Update Theme.', 'walkridge').'</span><br>';
        echo '<a href="'.$status_url.'" class="wr-status-link" target="_blank">';
        echo esc_html__('Open Update Theme', 'walkridge').' &#8599;';
        echo '</a>';
        echo '</div>';
    }
}
