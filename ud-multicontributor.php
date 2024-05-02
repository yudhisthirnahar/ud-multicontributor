<?php
/**
 * Plugin Name: Ud MultiContributor
 * Description: Add meta box to add MultiContributor to the posts.
 * Plugin URI:  https://github.com/yudhisthirnahar/wordpress_post_multicontributers
 * Version:     1.0.0
 * Author:      Yudhisthir Nahar
 * Author URI:  https://github.com/yudhisthirnahar
 * Text Domain: ud-multicontributor
 *
 * @package     UDMC
 */

namespace UDMC;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'UDMC_PLUGIN_VERSION' ) ) {
	define( 'UDMC_PLUGIN_VERSION', '1.0.0' );
}

// Path and URL.
if ( ! defined( 'UDMC_PLUGIN_DIR' ) ) {
	define( 'UDMC_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

if ( ! defined( 'UDMC_PLUGIN_URL' ) ) {
	define( 'UDMC_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}

if ( ! defined( 'UDMC_PLUGIN_BASE_NAME' ) ) {
	define( 'UDMC_PLUGIN_BASE_NAME', plugin_basename( __FILE__ ) );
}

// Autoload Composer dependencies.
if ( is_readable( UDMC_PLUGIN_DIR . '/vendor/autoload.php' ) ) {
	require_once UDMC_PLUGIN_DIR . '/vendor/autoload.php';
}

class_exists( UdMultiContributor::class ) && UdMultiContributor::get_instance();
