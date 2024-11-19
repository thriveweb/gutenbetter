<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://thriveweb.com.au
 * @since      1.0.0
 *
 * @package    Gutenbetter
 * @subpackage Gutenbetter/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Gutenbetter
 * @subpackage Gutenbetter/admin
 * @author     Dean Oakley <dean@thriveweb.com.au>
 */
class Gutenbetter_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gutenbetter_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gutenbetter_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( 
			$this->plugin_name, 
			plugin_dir_url( __FILE__ ) . 'css/gutenbetter-admin.css', 
			array(), 
			$this->version, 
			'all' 
		);

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gutenbetter_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gutenbetter_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'jquery-ui-resizable' );

		wp_enqueue_script( 
			$this->plugin_name, 
			plugin_dir_url( __FILE__ ) . 'js/gutenbetter-admin.js', 
			array( 'jquery', 'jquery-ui-resizable', 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-compose', 'wp-i18n', 'wp-data' ), 
			$this->version, 
			false 
		);

	}

	/**
	 * Link to settings page from plugin list.
	 * 
	 * @since    1.0.0
	 */
	public function gutenbetter_plugin_settings_link($links) {

		$settings_link = '<a href="admin.php?page=gutenbetter-settings">Settings</a>';
		array_unshift($links, $settings_link);
		return $links;

	}

	/**
	 * Create admin page to handle other plugin settings.
	 * 
	 * @since    1.0.0
	 */
	public function gutenbetter_add_menu_page() {

		add_submenu_page(
			'tools.php',
			esc_html__('Gutenbetter Settings', 'gutenbetter'),
			esc_html__('Gutenbetter', 'gutenbetter'),
			'manage_options',
			'gutenbetter-settings',
			array($this, 'gutenbetter_settings_page'),
		);

	}

	/**
	 * Register and save form values within plugin settings page.
	 * 
	 * @since    1.0.0
	 */
	public function gutenbetter_register_settings() {
		register_setting(
			'gutenbetter_settings_group',
			'post_type_support',
			array(
				'sanitize_callback' => array($this, 'sanitize_post_type_support'),
				'default' => array()
			)
		);

		register_setting(
			'gutenbetter_settings_group',
			'remove_block_directory',
			array(
				'sanitize_callback' => array($this, 'sanitize_checkbox'),
				'default' => true
			)
		);

		register_setting(
			'gutenbetter_settings_group',
			'force_preview_mode',
			array(
				'sanitize_callback' => array($this, 'sanitize_checkbox'),
				'default' => true
			)
		);
	}

	/**
	 * Sanitize the post type support array.
	 *
	 * @param array $input The input array to sanitize.
	 * @return array Sanitized array.
	 */
	public function sanitize_post_type_support($input) {
		if (!is_array($input)) {
			return array();
		}

		$valid_post_types = array_keys(get_post_types(array('public' => true)));
		return array_filter(array_map('sanitize_key', $input), function($post_type) use ($valid_post_types) {
			return in_array($post_type, $valid_post_types, true);
		});
	}

	/**
	 * Sanitize checkbox inputs.
	 *
	 * @param mixed $input The input to sanitize.
	 * @return bool
	 */
	public function sanitize_checkbox($input) {
		return !empty($input);
	}

	/**
	 * Callback function to render form for all plugin settings.
	 * 
	 * @since    1.0.0
	 */
	function gutenbetter_settings_page() {

		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'gutenbetter'));
		}
		?>
		
		<div class="wrap">
			<h1><?php echo esc_html__('Gutenbetter Settings', 'gutenbetter'); ?></h1><br>
			<form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
				<?php 
				settings_fields('gutenbetter_settings_group');
				do_settings_sections('gutenbetter-settings');

				$post_types = get_post_types(array('public' => true), 'objects');
				$disabled_post_types = array_map('sanitize_key', (array) get_option('post_type_support', array()));
				?>

				<div class="postbox" style="padding: 20px; margin-bottom: 20px;">
					<h3 style="margin-top: 0;"><?php echo esc_html__('Post Type Compatibility', 'gutenbetter'); ?></h3>
					<p><?php echo esc_html__('Disable the Gutenberg editor for specific post types. This is useful for content that may be better suited for the Classic Editor:', 'gutenbetter'); ?></p>
					<?php
					foreach ($post_types as $post_type) {
						if ($post_type->name !== 'attachment') {
							$post_type_name = esc_attr($post_type->name);
							$is_checked = in_array($post_type_name, $disabled_post_types, true);
							?>
							<label style="display: block; margin-bottom: 10px;">
								<input type="checkbox" name="post_type_support[]" value="<?php echo $post_type_name; ?>" <?php checked($is_checked); ?> />
								<?php echo esc_html($post_type->label); ?>
							</label>
							<?php
						}
					} 
					?>
				</div>

				<?php 
				$remove_block_directory = (bool) get_option('remove_block_directory', true);
				$force_preview_mode = (bool) get_option('force_preview_mode', true);
				?>

				<div class="postbox" style="padding: 20px; margin-bottom: 20px;">
					<h3 style="margin-top: 0;"><?php echo esc_html__('Block Directory Visibility', 'gutenbetter'); ?></h3>
					<p><?php echo esc_html__('Show or hide the block directory in the block editor sidebar which promotes additional blocks available for installation:', 'gutenbetter'); ?></p>
					<label for="remove_block_directory">
						<input type="checkbox" id="remove_block_directory" name="remove_block_directory" value="1" <?php checked($remove_block_directory); ?> />
						<?php echo esc_html__('Hide the block directory in the block editor?', 'gutenbetter'); ?>
					</label>
				</div>

				<div class="postbox" style="padding: 20px; margin-bottom: 20px;">
					<h3 style="margin-top: 0;"><?php echo esc_html__('ACF Block Preview Mode', 'gutenbetter'); ?></h3>
					<p><?php echo esc_html__('Force ACF (Advanced Custom Fields) blocks to load in preview mode by default, making it easier to see actual content layouts in the editor:', 'gutenbetter'); ?></p>
					<label for="force_preview_mode">
						<input type="checkbox" id="force_preview_mode" name="force_preview_mode" value="1" <?php checked($force_preview_mode); ?> />
						<?php echo esc_html__('Force preview mode for ACF blocks?', 'gutenbetter'); ?>
					</label>
				</div>

				<?php wp_nonce_field('gutenbetter_settings_action', 'gutenbetter_settings_nonce'); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php

	}

	/**
	 * Register and save form values within plugin settings page.
	 * 
	 * @since    1.0.0
	 */
	public function gutenbetter_register_settings() {

		register_setting('gutenbetter_settings_group', 'post_type_support');
		register_setting('gutenbetter_settings_group', 'remove_block_directory');
		register_setting('gutenbetter_settings_group', 'force_preview_mode');

	}

	/**
	 * Callback function for remove_block_directory plugin setting.
	 * 
	 * @since    1.0.0
	 */
	public function gutenbetter_remove_block_directory_assets() {

		if (get_option('remove_block_directory', false)) {
			remove_action('enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets');
		}

	}

	/**
	 * Callback function for force_preview_mode plugin setting.
	 * 
	 * @since    1.0.0
	 */
	public function gutenbetter_force_preview_mode_assets() {

		if (!get_option('force_preview_mode', false)) {
			return;
		}

		wp_enqueue_script(
			'gutenbetter-force-preview',
			plugin_dir_url(__FILE__) . 'js/gutenbetter-force-preview.js',
			array('jquery', 'wp-dom-ready', 'wp-data', 'wp-blocks', 'wp-element'),
			$this->version,
			true
		);

	}

	/**
	 * Callback function for post_type_support plugin setting.
	 * 
	 * @since    1.0.0
	 */
	public function gutenbetter_post_type_support($current_status, $post_type) {

		$disabled_post_types = get_option('post_type_support', array());

		if (!is_array(get_option('post_type_support', array()))) {
			$disabled_post_types = array();
		}
		
		return in_array($post_type, $disabled_post_types) ? false : $current_status;;

	}

	/**
	 * Conditionally return block content based on visibility status.
	 * 
	 * @since    1.0.0
	 */
	public function gutenbetter_block_visibility($block_content, $block) {

		return !empty($block['attrs']['disable_frontend_block']) ? '' : $block_content;

	}
	
}
