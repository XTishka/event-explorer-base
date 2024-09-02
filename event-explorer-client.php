<?php
/**
 *
 * @link              https://designrus.dk
 * @since             1.0.0
 * @package           Event_Explorer
 *
 * @wordpress-plugin
 * Plugin Name:       Event Explorer Base
 * Plugin URI:        https://https://github.com/XTishka/wp-event-explorer-client
 * Description:       A plugin to provide an API for events and a shortcode to display them.
 * Version:           1.0.0
 * Author:            <a href="https://designrus.dk">Design'R'us</ | Takhir Berdyiev
 * Author URI:        https://designrus.dk/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       event-explorer-base
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'EVENT_EXPLORER_VERSION', '1.0.0' );


function activate_event_explorer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-event-explorer-activator.php';
	Event_Explorer_Activator::activate();
}

function deactivate_event_explorer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-event-explorer-deactivator.php';
	Event_Explorer_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_event_explorer' );
register_deactivation_hook( __FILE__, 'deactivate_event_explorer' );

require plugin_dir_path( __FILE__ ) . 'includes/class-event-explorer.php';


function run_event_explorer() {

	$plugin = new Event_Explorer();
	$plugin->run();

}
run_event_explorer();
