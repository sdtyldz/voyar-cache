<?php
/**
 * Plugin Name: Voyar Cache
 * Plugin URI: https://www.internetbilisim.net
 * Description: İnternet Bilişim Cache Eklentisi.
 * Author: Voyar Technologies
 * Version: 1.0
 * Text Domain: voyar-cache
 * Author URI: http://voyar.net
 */

defined( 'ABSPATH' ) || exit;

define( 'VC_VERSION', '1.0' );

require_once dirname( __FILE__ ) . '/inc/functions.php';
require_once dirname( __FILE__ ) . '/inc/class-vc-settings.php';
require_once dirname( __FILE__ ) . '/inc/class-vc-config.php';
require_once dirname( __FILE__ ) . '/inc/class-vc-advanced-cache.php';
require_once dirname( __FILE__ ) . '/inc/class-vc-object-cache.php';
require_once dirname( __FILE__ ) . '/inc/class-vc-cron.php';

VC_Settings::factory();
VC_Advanced_Cache::factory();
VC_Object_Cache::factory();
VC_Cron::factory();


/**
 * Load text domain
 *
 * @since 1.0
 */
function vc_load_textdomain() {

	load_plugin_textdomain( 'voyar-cache', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'vc_load_textdomain' );


/**
 * Add settings link to plugin actions
 *
 * @param  array  $plugin_actions
 * @param  string $plugin_file
 * @since  1.0
 * @return array
 */
function vc_filter_plugin_action_links( $plugin_actions, $plugin_file ) {

	$new_actions = array();

	if ( basename( dirname( __FILE__ ) ) . '/voyar-cache.php' === $plugin_file ) {
		$new_actions['vc_settings'] = sprintf( __( '<a href="%s">Ayarlar</a>', 'voyar-cache' ), esc_url( admin_url( 'options-general.php?page=voyar-cache' ) ) );
	}

	return array_merge( $new_actions, $plugin_actions );
}
add_filter( 'plugin_action_links', 'vc_filter_plugin_action_links', 10, 2 );

/**
 * Clean up necessary files
 *
 * @since 1.0
 */
function vc_clean_up() {

	WP_Filesystem();

	VC_Advanced_Cache::factory()->clean_up();
	VC_Advanced_Cache::factory()->toggle_caching( false );
	VC_Object_Cache::factory()->clean_up();
	VC_Config::factory()->clean_up();
}
register_deactivation_hook( __FILE__, 'vc_clean_up' );


