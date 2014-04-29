<?php
/**
 * WP Roadmap
 *
 * @package   WP Roadmap
 * @author    Paul Kilmurray <paul@kilbot.com.au>
 * @license   GPL-2.0+
 * @link      http://www.kilbot.com.au
 * @copyright 2014 Paul Kilmurray
 */

class WP_Roadmap_Admin {

	/**
	 * Instance of this class.
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 */
	private function __construct() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 * @TODO:
		 *
		 * - Rename "Plugin_Name" to the name of your initial plugin class
		 *
		 */
		// $plugin = Plugin_Name::get_instance();
		// $this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		// add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		// add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		// add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		// $plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		// add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		/*
		 * Define custom functionality.
		 *
		 * Read more about actions and filters:
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( 'admin_init', array( $this, 'sanity_check' ) );
		add_action( 'admin_menu', array( $this, 'custom_admin_submenu' ) );

		add_filter( 'default_hidden_meta_boxes', array( $this , 'change_default_hidden_meta' ), 10, 2 );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @TODO:
	 *
	 * - Rename "Plugin_Name" to the name your plugin
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Plugin_Name::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @TODO:
	 *
	 * - Rename "Plugin_Name" to the name your plugin
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), Plugin_Name::VERSION );
		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 * @TODO:
		 *
		 * - Change 'Page Title' to the title of your plugin admin page
		 * - Change 'Menu Text' to the text for menu item for the plugin settings page
		 * - Change 'manage_options' to the capability you see fit
		 *   For reference: http://codex.wordpress.org/Roles_and_Capabilities
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Page Title', $this->plugin_slug ),
			__( 'Menu Text', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

	/**
	 * [create_admin_menu description]
	 * @return [type] [description]
	 */
	public function custom_admin_submenu() {
		add_submenu_page('edit.php?post_type=roadmap', 'Labels', 'Labels', 'manage_options', 'edit-tags.php?post_type=issue&taxonomy=label'); 
		add_submenu_page('edit.php?post_type=roadmap', 'Milestones', 'Milestones', 'manage_options', 'edit-tags.php?post_type=issue&taxonomy=milestone'); 
		remove_submenu_page( 'edit.php?post_type=roadmap', 'post-new.php?post_type=roadmap' );

		// // tidy up the admin menu
		add_action('parent_file', array( $this, 'admin_submenu_correction' ) );
	}

	/**
	 * [admin_menu_correction description]
	 * @param  string $parent_file [description]
	 */
	function admin_submenu_correction($parent_file) {
		global $current_screen, $submenu_file;
		
		if ( $current_screen->post_type == 'roadmap' &&  $current_screen->action == 'add' ) {
			$submenu_file = 'edit.php?post_type=roadmap';
		}
		
		// add .current to the appropiate submenu item
		if ( $current_screen->post_type == 'issue' ) {
			switch ($current_screen->taxonomy) {
			    case "milestone":
			        $submenu_file = 'edit-tags.php?post_type=issue&taxonomy=milestone';
			        break;
			    case "label":
			        $submenu_file = 'edit-tags.php?post_type=issue&taxonomy=label';
			        break;
			    default:
			        $submenu_file = 'edit.php?post_type=issue';
			}
		}

		// add .wp-has-current-submenu to the Roadmap menu when custom taxonomies are being used
		if ( $current_screen->taxonomy == 'milestone' || $current_screen->taxonomy == 'label' )
			$parent_file = 'edit.php?post_type=roadmap';

		return $parent_file;
	}


	public function change_default_hidden_meta( $hidden, $screen) {
		if ( 'roadmap' == $screen->id ) {
        	$hidden = array( 'postcustom', 'commentstatusdiv', 'slugdiv', 'authordiv' );
    	}
    	if ( 'issue' == $screen->id ) {
        	$hidden = array( 'postcustom', 'commentstatusdiv', 'slugdiv', 'authordiv', 'pageparentdiv' );
    	}
    	return $hidden;
	}

	/**
	 * Check for dependancies
	 */
	function sanity_check() {
		// check for GitHub Updater
		if ( current_user_can( 'activate_plugins' ) && !is_plugin_active( 'github-updater/github-updater.php' ) ) {
			$this->msg_type = 'update-nag';
			$this->msg 		= '<strong>WooCommerce POS</strong> requires the <a href="https://github.com/afragen/github-updater">GitHub Updater</a> plugin for updates. You can download and install the GitHub Updater plugin from <a href="https://github.com/afragen/github-updater">https://github.com/afragen/github-updater</a>.';
			add_action( 'admin_notices', array( $this, 'admin_notices' ), 10, 2 );
		}

		// check if ACF is installed
		if ( !is_plugin_active( 'advanced-custom-fields/acf.php' ) ) {
			$this->msg_type = 'error';
			$this->msg 		= '<strong>WP Roadmap</strong> requires <a href="http://www.advancedcustomfields.com/">Advanced Custom Fields</a> to work correctly.';
			add_action( 'admin_notices', array( $this, 'admin_notices' ), 10, 2 );
		}
	}

	/**
	 * Display the admin warning about GitHub Updater Plugin
	 */
	function admin_notices() {
		echo '<div class="' . $this->msg_type . '">
			<p>' . $this->msg . '</p>
		</div>';
	}

}