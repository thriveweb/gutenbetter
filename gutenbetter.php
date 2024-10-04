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
 * Description:       Handy improvements for the Gutenberg block editor such as post type support, hiding blocks, adjusting sidebar width, styling improvements, etc. 
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
 * The code that runs during plugin activation.
 * This action is documented in includes/class-gutenbetter-activator.php
 */
function activate_gutenbetter() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gutenbetter-activator.php';
	Gutenbetter_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-gutenbetter-deactivator.php
 */
function deactivate_gutenbetter() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gutenbetter-deactivator.php';
	Gutenbetter_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_gutenbetter' );
register_deactivation_hook( __FILE__, 'deactivate_gutenbetter' );

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

/**
 * Create admin page to handle other plugin settings.
 */
function gutenbetter_add_menu_page() {
	add_submenu_page(
		'tools.php',                	// Parent menu slug
		'Gutenbetter Settings',     	// Page title
		'Gutenbetter',              	// Menu title
		'manage_options',           	// Capability required to access the page
		'gutenbetter-settings',     	// Unique slug for the page
		'gutenbetter_settings_page', 	// Callback function to render the page
	);
}
add_action('admin_menu', 'gutenbetter_add_menu_page');

/**
 * Link to settings page from plugin list.
 */
function gutenbetter_plugin_settings_link($links) {
	$settings_link = '<a href="admin.php?page=gutenbetter-settings">Settings</a>';
	array_unshift($links, $settings_link);
	return $links;
}
add_filter('plugin_action_links_gutenbetter/gutenbetter.php', 'gutenbetter_plugin_settings_link');

/**
 * Callback function to render form for all plugin settings.
 */
function gutenbetter_settings_page() {
	?>
	<div class="wrap">
			<h1>Gutenbetter Settings</h1><br>
			<form method="post" action="options.php">
				<?php 
				settings_fields('gutenbetter_settings_group');
				do_settings_sections('gutenbetter-settings'); ?>

				<?php 
				$post_types = get_post_types(array('public' => true), 'objects');

				if (!empty(get_option('disabled_gutenberg_post_types', array()))) {
					$disabled_post_types = get_option('disabled_gutenberg_post_types', array());
				} else {
					$disabled_post_types = array();
				}
				?>

				<div class="postbox" style="padding: 20px; margin-bottom: 20px;">
					<h3 style="margin-top: 0;">Post Type Support</h3>
					<p>Disable the Gutenberg editor on the below post types:</p>
					<?php
					foreach ($post_types as $post_type) {
						if ($post_type->name !== 'attachment') { // Exclude 'attachment' (Media)
							?>
							<label style="display: block; margin-bottom: 10px;">
								<input type="checkbox" name="disabled_gutenberg_post_types[]" value="<?php echo $post_type->name; ?>" <?php checked(in_array($post_type->name, $disabled_post_types)); ?> />
								<?php echo $post_type->label; ?>
							</label>
							<?php
						}
					} ?>
				</div>

				<?php 
				$remove_block_directory = get_option('remove_block_directory', true); ?>

				<div class="postbox" style="padding: 20px; margin-bottom: 20px;">
					<h3 style="margin-top: 0;">Block Directory</h3>
					<label for="remove_block_directory">
						<input type="checkbox" id="remove_block_directory" name="remove_block_directory" value="1" <?php checked($remove_block_directory, true); ?> />
						Hide block directory from block sidebar?
					</label>
				</div>

				<?php 
				$force_preview_mode = get_option('force_preview_mode', true); ?>

				<div class="postbox" style="padding: 20px; margin-bottom: 20px;">
					<h3 style="margin-top: 0;">Preview Mode</h3>
					<label for="force_preview_mode">
						<input type="checkbox" id="force_preview_mode" name="force_preview_mode" value="1" <?php checked($force_preview_mode, true); ?> />
						Force preview mode for ACF blocks?
					</label>
				</div>

				<?php submit_button(); ?>
			</form>
	</div>
	<?php
}

/**
 * Register and save form values within plugin settings page.
 */
function gutenbetter_register_settings() {
	register_setting('gutenbetter_settings_group', 'disabled_gutenberg_post_types');
	register_setting('gutenbetter_settings_group', 'remove_block_directory');
	register_setting('gutenbetter_settings_group', 'force_preview_mode');
}
add_action('admin_init', 'gutenbetter_register_settings');

/**
 * Callback function for remove_block_directory plugin setting.
 */
function gutenbetter_remove_block_directory_assets() {
	$remove_block_directory = get_option('remove_block_directory', false);
	if ($remove_block_directory) {
		remove_action('enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets');
	}
}
add_action('init', 'gutenbetter_remove_block_directory_assets');

/**
 * Callback function for force_preview_mode plugin setting.
 */
function gutenbetter_force_preview_mode_assets() {
	$force_preview_mode = get_option('force_preview_mode', false);
	if ($force_preview_mode) {
		wp_enqueue_script('gutenbetter-force-preview', plugin_dir_url( __FILE__ ) . 'admin/js/gutenbetter-force-preview.js', array( 'jquery', 'jquery-ui-resizable', 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-compose', 'wp-i18n' ),array(), true);
	}
}
add_action('admin_enqueue_scripts', 'gutenbetter_force_preview_mode_assets');

/**
 * Callback function for disabled_gutenberg_post_types plugin setting.
 */
function gutenbetter_gutenburg_usage($current_status, $post_type) {
	$disabled_post_types = get_option('disabled_gutenberg_post_types', array());

	if (!is_array($disabled_post_types)) {
		$disabled_post_types = array();
	}

	if (in_array($post_type, $disabled_post_types)) {
		return false;
	}

	return $current_status;
}
add_filter('use_block_editor_for_post_type', 'gutenbetter_gutenburg_usage', 10, 2);

/**
 * Conditionally return block content based on visibility status.
 */
function hide_block_conditional_content($block_content, $block) {
	if (!empty($block['attrs']['disable_frontend_block'])) {
		return '';
	} else {
		return $block_content;
	}
}
add_filter('render_block', 'hide_block_conditional_content', 10, 3);

