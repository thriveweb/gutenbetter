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
	 * @modified 1.0.1
	 * @access   private
	 */
	private function define_admin_hooks() {

		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_scripts_styles' );

		$this->loader->add_action( 'admin_menu', $this, 'gutenbetter_add_menu_page' );

		$this->loader->add_action( 'admin_init', $this, 'gutenbetter_register_settings' );

		$this->loader->add_action( 'init', $this, 'gutenbetter_remove_block_directory_assets' );

		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'gutenbetter_force_preview_mode_assets' );

		$this->loader->add_action( 'enqueue_block_editor_assets', $this, 'gutenbetter_acf_sidebar_fields_css' );

		$this->loader->add_filter( 'use_block_editor_for_post_type', $this, 'gutenbetter_post_type_support', 10, 2 );

		$this->loader->add_filter( 'render_block', $this, 'gutenbetter_block_visibility', 10, 3 );

		$this->loader->add_filter( 'plugin_action_links_gutenbetter/gutenbetter.php', $this, 'gutenbetter_plugin_settings_link' );

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
	 * @modified 1.0.1
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
				do_settings_sections( 'gutenbetter-settings' ); ?>

				<?php
				$post_types = get_post_types( array( 'public' => true ), 'objects' );
				$disabled_post_types = array_map( 'sanitize_key', (array) get_option( 'post_type_support', array() ) ); ?>
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
				$remove_block_directory = boolval( get_option( 'remove_block_directory', 1 ) ); ?>
				<div class="postbox" style="padding: 20px; margin-bottom: 20px;">
					<h3 style="margin-top: 0;"><?php echo esc_html__( 'Block Directory Visibility', 'gutenbetter' ); ?></h3>
					<p><?php echo esc_html__( 'Show or hide the block directory in the block editor sidebar which promotes additional blocks available for installation:', 'gutenbetter' ); ?></p>
					<label for="remove_block_directory">
						<input type="checkbox" id="remove_block_directory" name="remove_block_directory" value="1" <?php checked( $remove_block_directory, 1 ); ?> />
						<?php echo esc_html__( 'Hide the block directory in the block editor?', 'gutenbetter' ); ?>
					</label>
				</div>

				<?php 
				$force_preview_mode = boolval( get_option( 'force_preview_mode', 1 ) ); ?>
				<div class="postbox" style="padding: 20px; margin-bottom: 20px;">
					<h3 style="margin-top: 0;"><?php echo esc_html__( 'ACF Block Preview Mode', 'gutenbetter' ); ?></h3>
					<p><?php echo esc_html__( 'Force ACF (Advanced Custom Fields) blocks to load in preview mode by default, making it easier to see actual content layouts in the editor:', 'gutenbetter' ); ?></p>

					<label for="force_preview_mode">
						<input type="checkbox" id="force_preview_mode" name="force_preview_mode" value="1" <?php checked( $force_preview_mode, 1 ); ?> />
						<?php echo esc_html__( 'Force preview mode for ACF blocks?', 'gutenbetter' ); ?>
					</label>
				</div>

				<?php 
				$acf_sidebar_fields = boolval( get_option( 'acf_sidebar_fields', 1 ) ); ?>
				<div class="postbox" style="padding: 20px; margin-bottom: 20px;">
					<h3 style="margin-top: 0;"><?php echo esc_html__( 'ACF Fields in Sidebar', 'gutenbetter' ); ?></h3>
					<p><?php echo esc_html__( 'Allow ACF (Advanced Custom Fields) fields to appear in the sidebar when an ACF block is selected:', 'gutenbetter' ); ?></p>

					<label for="acf_sidebar_fields">
						<input type="checkbox" id="acf_sidebar_fields" name="acf_sidebar_fields" value="1" <?php checked( $acf_sidebar_fields, 1 ); ?> />
						<?php echo esc_html__( 'Show ACF fields in the sidebar?', 'gutenbetter' ); ?>
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
	 * @modified 1.0.1
	 */
	public function gutenbetter_register_settings() {

		register_setting( 'gutenbetter_settings_group', 'post_type_support', 'sanitize_post_type_support' );
    register_setting( 'gutenbetter_settings_group', 'remove_block_directory', 'absint' );
    register_setting( 'gutenbetter_settings_group', 'force_preview_mode', 'absint' );
    register_setting( 'gutenbetter_settings_group', 'acf_sidebar_fields', 'absint' );

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

		if ( get_option( 'remove_block_directory', 0 ) ) {
			remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );
		}

	}

	/**
	 * Callback function for force_preview_mode plugin setting.
	 * 
	 * @since    1.0.0
	 */
	public function gutenbetter_force_preview_mode_assets() {

		if ( !get_option( 'force_preview_mode', 0 ) ) {
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
	 * Callback function for acf_sidebar_fields plugin setting.
	 * 
	 * @since    1.0.1
	 */
	public function gutenbetter_acf_sidebar_fields_css() {

		if ( !get_option( 'acf_sidebar_fields', 0 ) ) {
			$custom_css = ".block-editor .acf-block-panel { display: none !important; }";
			wp_add_inline_style( 'wp-block-editor', $custom_css );
		}

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
		
		return in_array( $post_type, $disabled_post_types ) ? 0 : $current_status;;

	}

	/**
	 * Conditionally return block content based on visibility status.
	 * 
	 * @since    1.0.0
	 */
	public function gutenbetter_block_visibility( $block_content, $block ) {

		return !empty( $block['attrs']['disable_frontend_block'] ) ? '' : $block_content;

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
