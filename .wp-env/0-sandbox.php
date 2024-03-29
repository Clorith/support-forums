<?php
// This file handles special loading of mu-plugins.

require_once __DIR__ . '/pub/locales.php';

if ( ! defined( 'FEATURE_2021_GLOBAL_HEADER_FOOTER' ) ) {
	define( 'FEATURE_2021_GLOBAL_HEADER_FOOTER', true );
}

if ( ! defined( 'GLOTPRESS_LOCALES_PATH' ) ) {
	define( 'GLOTPRESS_LOCALES_PATH', __DIR__ . '/pub/locales.php' );
}

require_once WPMU_PLUGIN_DIR . '/pub/wporg-seo.php';

require_once WPMU_PLUGIN_DIR . '/wporg-mu-plugins/mu-plugins/blocks/global-header-footer/blocks.php';
require_once WPMU_PLUGIN_DIR . '/wporg-mu-plugins/mu-plugins/skip-to/skip-to.php';
