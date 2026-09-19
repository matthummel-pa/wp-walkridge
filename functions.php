<?php

use App\Providers\ThemeServiceProvider;
use Roots\Acorn\Application;

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
*/

if (! file_exists($composer = __DIR__.'/vendor/autoload.php')) {
    wp_die(
        __('Error locating autoloader. Please run <code>composer install</code>.', 'walkridge')
    );
}

require $composer;

/*
|--------------------------------------------------------------------------
| Register The Bootloader
|--------------------------------------------------------------------------
*/

Application::configure()
    ->withProviders([
        ThemeServiceProvider::class,
    ])
    ->boot();

/*
|--------------------------------------------------------------------------
| Register Sage Theme Files
|--------------------------------------------------------------------------
*/

collect(['setup', 'filters', 'admin', 'customizer', 'theme-settings', 'marketplace', 'forms', 'page-fields', 'blocks', 'blocks-content', 'block-generator', 'shop'])
    ->each(function ($file) {
        if (! locate_template($file = "app/{$file}.php", true, true)) {
            wp_die(
                /* translators: %s is replaced with the relative file path */
                sprintf(__('Error locating <code>%s</code> for inclusion.', 'walkridge'), $file)
            );
        }
    });
