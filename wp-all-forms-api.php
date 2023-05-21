<?php

require __DIR__ . '/vendor/autoload.php';

/**
 * Plugin Name:       WP All Forms API
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Este é um plugin que gera as rotas para obter os dados do site WordPress, permitindo requisições autencticadas usando jwt
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            claudionhangapc
 * Author URI:        https://claudionhangapc.com/
 * License:           GPL v2 or later
 * License URI:       https://claudionhangapc/gpl-2.0.html
 * Update URI:        https://claudionhangapc.com
 * Text Domain:       wp-all-forms-api
 * Domain Path:       /languages
 */

define( 'WP_ALL_FORMS_API_PLUGIN_FILE', __FILE__ );

use Includes\Plugins\JWT\JWTPlugin;
use Includes\Routes\Route;
use Includes\Database\DatabaseInstaller;
use Includes\Plugins\QRCode;
use Includes\Admin\AdminOptions;
use Includes\Plugins\Constant;

/**
 * Init api.
 */
function wp_all_forms_api_rest_init() {
	$namespace = Constant::API_NAMESPACE . '/' . Constant::API_VERSION;
	( new Route( $namespace ) )->init();

	add_filter( 'rest_pre_dispatch', array( new JWTPlugin(), 'validate_token_rest_pre_dispatch' ), 10, 3 );
}

/**
* Add actions
*/
add_action( 'rest_api_init', 'wp_all_forms_api_rest_init' );

/**
* Register hooks.
*/
register_activation_hook( WP_ALL_FORMS_API_PLUGIN_FILE, array( new DatabaseInstaller(), 'install' ) );


( new AdminOptions() )->init();


function wp_all_forms_api_rest_init_load_textdomain() {
	load_plugin_textdomain( 'wp-all-forms-api', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'wp_all_forms_api_rest_init_load_textdomain' );
