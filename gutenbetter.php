<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://thriveweb.com.au
 * @since             1.0.0
 * @package           Gutenbetter
 *
 * @wordpress-plugin
 * Plugin Name:       Gutenbetter
 * Plugin URI:        https://thriveweb.com.au/the-lab/gutenbetter/
 * Description:       Handy improvements for the Gutenberg block editor interface such as post type support, hiding blocks, adjustable sidebar, and more.
 * Version:           1.0.0
 * Author:            Thrive Digital
 * Author URI:        https://thriveweb.com.au/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gutenbetter
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'GUTENBETTER_VERSION', '1.0.0' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-gutenbetter.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_gutenbetter() {

	$plugin = new Gutenbetter();
	$plugin->run();

}
run_gutenbetter();

