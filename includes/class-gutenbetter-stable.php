<?php

/**
 * @link       https://thriveweb.com.au
 * @since      1.0.0
 *
 * @package    Gutenbetter
 * @subpackage Gutenbetter/includes
 */

/**
 *
 * @since      1.0.0
 * @package    Gutenbetter
 * @subpackage Gutenbetter/includes
 * @author     Dean Oakley <dean@thriveweb.com.au>
 */
class Gutenbetter {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Gutenbetter_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 * 
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'GUTENBETTER_VERSION' ) ) {
			$this->version = GUTENBETTER_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$this->plugin_name = 'gutenbetter';

		$this->load_dependencies();

		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Gutenbetter_Loader Orchestrates the hooks of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gutenbetter-loader.php';

		$this->loader = new Gutenbetter_Loader();

	}

	/**
	 * Register all of the hooks related to the plugin functionality.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Gutenbetter_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts_styles' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'gutenbetter_add_menu_page' );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'gutenbetter_register_settings' );

		$this->loader->add_action( 'init', $plugin_admin, 'gutenbetter_remove_block_directory_assets' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'gutenbetter_force_preview_mode_assets' );

		$this->loader->add_filter( 'use_block_editor_for_post_type', $plugin_admin, 'gutenbetter_post_type_support', 10, 2 );

		$this->loader->add_filter( 'render_block', $plugin_admin, 'gutenbetter_block_visibility', 10, 3 );

		$this->loader->add_filter( 'plugin_action_links_gutenbetter/gutenbetter.php', $plugin_admin, 'gutenbetter_plugin_settings_link' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within WordPress.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Gutenbetter_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}

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
	 * Register the stylesheets and JavaScript for the plugin.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts_styles() {

		wp_enqueue_script( 'jquery-ui-resizable' );

		wp_enqueue_script( 
			$this->plugin_name, 
			plugin_dir_url( __FILE__ ) . '../assets/js/gutenbetter-admin.js', 
			array( 'jquery', 'jquery-ui-resizable', 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-compose', 'wp-i18n', 'wp-data' ), 
			$this->version, 
			false 
		);

		wp_enqueue_style( 
			$this->plugin_name, 
			plugin_dir_url( __FILE__ ) . '../assets/css/gutenbetter-admin.css', 
			array(), 
			$this->version, 
			'all' 
		);

	}

	/**
	 * Link to settings page from plugin list.
	 * 
	 * @since    1.0.0
	 */
	public function gutenbetter_plugin_settings_link( $links ) {

		$settings_link = '<a href="admin.php?page=gutenbetter-settings">Settings</a>';
		array_unshift( $links, $settings_link );
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
			esc_html__( 'Gutenbetter Settings', 'gutenbetter' ),
			esc_html__( 'Gutenbetter', 'gutenbetter'),
			'manage_options',
			'gutenbetter-settings',
			array( $this, 'gutenbetter_settings_page' ),
		);

	}

	/**
	 * Callback function to render form for all plugin settings.
	 * 
	 * @since    1.0.0
	 */
	function gutenbetter_settings_page() {

		if ( !current_user_can( 'manage_options' ) ) {
			wp_die(esc_html__( 'You do not have sufficient permissions to access this page.', 'gutenbetter' ) );
		} 
		
		?>
		
		<div class="wrap">

			<h1><?php echo esc_html__( 'Gutenbetter Settings', 'gutenbetter' ); ?></h1><br>

			<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
				<?php 
				settings_fields( 'gutenbetter_settings_group' );
				do_settings_sections( 'gutenbetter-settings' );

				$post_types = get_post_types( array( 'public' => true ), 'objects' );
				$disabled_post_types = array_map( 'sanitize_key', (array) get_option( 'post_type_support', array() ) );
				?>

				<div class="postbox" style="padding: 20px; margin-bottom: 20px;">
					<h3 style="margin-top: 0;"><?php echo esc_html__( 'Post Type Compatibility', 'gutenbetter' ); ?></h3>
					<p><?php echo esc_html__( 'Disable the Gutenberg editor for specific post types. This is useful for content that may be better suited for the Classic Editor:', 'gutenbetter' ); ?></p>
					<?php
					foreach ( $post_types as $post_type ) {
						if ( $post_type->name !== 'attachment' ) { // Exclude 'attachment' (Media)
							?>
							<label style="display: block; margin-bottom: 10px;">
								<input type="checkbox" name="post_type_support[]" value="<?php echo esc_attr( $post_type->name ); ?>" <?php checked( in_array( $post_type->name, $disabled_post_types ) ); ?> />
								<?php echo esc_html( $post_type->label ); ?>
							</label>
							<?php
						}
					} 
					?>
				</div>

				<?php 
				$remove_block_directory = boolval( get_option( 'remove_block_directory', true ) ); ?>

				<div class="postbox" style="padding: 20px; margin-bottom: 20px;">
					<h3 style="margin-top: 0;"><?php echo esc_html__( 'Block Directory Visibility', 'gutenbetter' ); ?></h3>
					<p><?php echo esc_html__( 'Show or hide the block directory in the block editor sidebar which promotes additional blocks available for installation:', 'gutenbetter' ); ?></p>
					<label for="remove_block_directory">
						<input type="checkbox" id="remove_block_directory" name="remove_block_directory" value="1" <?php checked( $remove_block_directory, true ); ?> />
						<?php echo esc_html__( 'Hide the block directory in the block editor?', 'gutenbetter' ); ?>
					</label>
				</div>

				<?php 
				$force_preview_mode = boolval( get_option( 'force_preview_mode', true ) ); ?>

				<div class="postbox" style="padding: 20px; margin-bottom: 20px;">
					<h3 style="margin-top: 0;"><?php echo esc_html__( 'ACF Block Preview Mode', 'gutenbetter' ); ?></h3>
					<p><?php echo esc_html__( 'Force ACF (Advanced Custom Fields) blocks to load in preview mode by default, making it easier to see actual content layouts in the editor:', 'gutenbetter' ); ?></p>

					<label for="force_preview_mode">
						<input type="checkbox" id="force_preview_mode" name="force_preview_mode" value="1" <?php checked( $force_preview_mode, true ); ?> />
						<?php echo esc_html__( 'Force preview mode for ACF blocks?', 'gutenbetter' ); ?>
					</label>
				</div>

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

		register_setting( 'gutenbetter_settings_group', 'post_type_support', array( 'sanitize_callback' => array( $this, 'sanitize_post_type_support' ) ) );
    register_setting( 'gutenbetter_settings_group', 'remove_block_directory', array( 'sanitize_callback' => 'absint' ) );
    register_setting( 'gutenbetter_settings_group', 'force_preview_mode', array( 'sanitize_callback' => 'absint' ) );

	}

	/**
	 * Sanitize callback for post_type_support setting.
	 * 
	 * @since    1.0.0
	 */

	public function sanitize_post_type_support( $input ) {

    if ( !is_array( $input ) ) {
			return array();
    }
    return array_map( 'sanitize_key', $input );
		
	}

	/**
	 * Callback function for remove_block_directory plugin setting.
	 * 
	 * @since    1.0.0
	 */
	public function gutenbetter_remove_block_directory_assets() {

		if ( get_option( 'remove_block_directory', false ) ) {
			remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );
		}

	}

	/**
	 * Callback function for force_preview_mode plugin setting.
	 * 
	 * @since    1.0.0
	 */
	public function gutenbetter_force_preview_mode_assets() {

		if ( !get_option( 'force_preview_mode', false ) ) {
			return;
		}

		wp_enqueue_script(
			'gutenbetter-force-preview',
			plugin_dir_url(__FILE__) . '../assets/js/gutenbetter-force-preview.js',
			array( 'jquery', 'wp-dom-ready', 'wp-data', 'wp-blocks', 'wp-element' ),
			$this->version,
			true
		);

	}

	/**
	 * Callback function for post_type_support plugin setting.
	 * 
	 * @since    1.0.0
	 */
	public function gutenbetter_post_type_support( $current_status, $post_type ) {

		$disabled_post_types = get_option( 'post_type_support', array() );

		if ( !is_array( get_option( 'post_type_support', array() ) ) ) {
			$disabled_post_types = array();
		}
		
		return in_array( $post_type, $disabled_post_types ) ? false : $current_status;;

	}

	/**
	 * Conditionally return block content based on visibility status.
	 * 
	 * @since    1.0.0
	 */
	public function gutenbetter_block_visibility( $block_content, $block ) {

		return !empty( $block['attrs']['disable_frontend_block'] ) ? '' : $block_content;

	}
	
}
