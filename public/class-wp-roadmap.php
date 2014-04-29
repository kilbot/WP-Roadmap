<?php
/**
 * WP Roadmaps
 *
 * @package   WP Roadmap
 * @author    Paul Kilmurray <paul@kilbot.com.au>
 * @license   GPL-2.0+
 * @link      http://www.kilbot.com.au
 * @copyright 2014 Paul Kilmurray
 */

class WP_Roadmap {
				
	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '0.1';

	/**
	 * @TODO - Rename "plugin-name" to the name your your plugin
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'wp-roadmap';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		// add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Load public-facing style sheet and JavaScript.
		// add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		// add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/* Define custom functionality.
		 * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( 'init', array( $this, 'create_cpt' ) );
		add_action( 'init', array( $this, 'create_cpt_taxonomies' ) );
		add_action( 'init', array( $this, 'create_rewrite_rules' ) );

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}


	/**
	 * Fired when the plugin is activated.
	 */
	static function activate( ) {
		// set up the custom taxonomies
		self::create_cpt_taxonomies();

		// insert default status terms
		if(! term_exists( 'open', 'status') ) 
			wp_insert_term('Open', 'status', array( 'description'=> 'Shown on the Roadmap archive', 'slug' => 'open' ) );	
		if(! term_exists( 'closed', 'status') ) 
			wp_insert_term('Closed', 'status', array( 'description'=> 'Not shown on the Roadmap archive', 'slug' => 'closed' ) );	
		if(! term_exists( 'known-bugs', 'status') ) 
			wp_insert_term('Known Bugs', 'status', array( 'description'=> 'Stick to the top of the Roadmap archive', 'slug' => 'known-bugs' ) );	
		if(! term_exists( 'future-release', 'status') ) 
			wp_insert_term('Future Release', 'status', array( 'description'=> 'Stick to the bottom of the Roadmap archive', 'slug' => 'future-release' ) );	

		// insert default labels
		if(! term_exists( 'enhancement', 'label') ) 
			wp_insert_term('Enhancement', 'label', array( 'description'=> '', 'slug' => 'enhancement' ) );	
		if(! term_exists( 'bug', 'label') ) 
			wp_insert_term('Bug', 'label', array( 'description'=> '', 'slug' => 'bug' ) );

		// set up the custom rewrites
		self::create_rewrite_rules();
		
		// flush the rewrites
		flush_rewrite_rules();

	}

	/**
	 * Fired when the plugin is deactivated.
	 */
	static function deactivate( ) {

		// flush the rewrites
		flush_rewrite_rules();
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}

	/**
	 * Create Custom Post Types
	 * There are two CPT: Roadmaps and Issues
	 */
	static function create_cpt() {
		$labels = array(
			'name'               => _x( 'Milestones', 'post type general name', 'wp-projects-textdomain' ),
			'singular_name'      => _x( 'Milestone', 'post type singular name', 'wp-projects-textdomain' ),
			'menu_name'          => _x( 'Roadmap', 'admin menu', 'wp-projects-textdomain' ),
			'name_admin_bar'     => _x( 'Milestone', 'add new on admin bar', 'wp-projects-textdomain' ),
			'add_new'            => _x( 'Add New Milestone', 'project', 'wp-projects-textdomain' ),
			'add_new_item'       => __( 'Add New Milestone', 'wp-projects-textdomain' ),
			'new_item'           => __( 'New Milestone', 'wp-projects-textdomain' ),
			'edit_item'          => __( 'Edit Milestone', 'wp-projects-textdomain' ),
			'view_item'          => __( 'View Milestone', 'wp-projects-textdomain' ),
			'all_items'          => __( 'Milestones', 'wp-projects-textdomain' ),
			'search_items'       => __( 'Search Milestones', 'wp-projects-textdomain' ),
			'parent_item_colon'  => __( 'Parent Milestone:', 'wp-projects-textdomain' ),
			'not_found'          => __( 'No Milestones found.', 'wp-projects-textdomain' ),
			'not_found_in_trash' => __( 'No Milestones found in Trash.', 'wp-projects-textdomain' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'roadmap' ),
			'capability_type'    => 'page',
			'has_archive'        => true,
			'hierarchical'       => true,
			'menu_position'      => 20,
			'menu_icon' 		 => 'dashicons-flag',
			'supports'           => array( 
										'title', 
										'editor', 
										'author', 
										'comments', 
										'custom-fields', 
										'sticky', 
										'page-attributes'
									),
			'taxonomies'		=> array( 'status' ),
		);
		register_post_type( 'roadmap', $args );

		$labels = array(
			'name'               => _x( 'Issues', 'post type general name', 'wp-projects-textdomain' ),
			'singular_name'      => _x( 'Issue', 'post type singular name', 'wp-projects-textdomain' ),
			'menu_name'          => _x( 'issue', 'admin menu', 'wp-projects-textdomain' ),
			'name_admin_bar'     => _x( 'Issue', 'add new on admin bar', 'wp-projects-textdomain' ),
			'add_new'            => _x( 'Add New Issue', 'issue', 'wp-projects-textdomain' ),
			'add_new_item'       => __( 'Add New Issue', 'wp-projects-textdomain' ),
			'new_item'           => __( 'New Issue', 'wp-projects-textdomain' ),
			'edit_item'          => __( 'Edit Issue', 'wp-projects-textdomain' ),
			'view_item'          => __( 'View Issue', 'wp-projects-textdomain' ),
			'all_items'          => __( 'Issues', 'wp-projects-textdomain' ),
			'search_items'       => __( 'Search Issues', 'wp-projects-textdomain' ),
			'parent_item_colon'  => __( 'Parent Issue:', 'wp-projects-textdomain' ),
			'not_found'          => __( 'No Issues found.', 'wp-projects-textdomain' ),
			'not_found_in_trash' => __( 'No Issues found in Trash.', 'wp-projects-textdomain' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'edit.php?post_type=roadmap',
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'roadmap/issue' ), // handled by wp_projects_permalinks
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'supports'           => array( 
										'title', 
										'editor', 
										'author', 
										'comments', 
										'custom-fields', 
										'sticky', 
										'page-attributes'
									),
			'taxonomies'		=> array( 'milestone' , 'label' ),
		);

		register_post_type( 'issue', $args );

	}

	/**
	 * Create CPT taxonomies
	 * 
	 */
	static function create_cpt_taxonomies() {
		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => _x( 'Status', 'taxonomy general name' ),
			'singular_name'     => _x( 'Status', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Status' ),
			'all_items'         => __( 'All Status' ),
			'parent_item'       => __( 'Parent Status' ),
			'parent_item_colon' => __( 'Parent Status:' ),
			'edit_item'         => __( 'Edit Status' ),
			'update_item'       => __( 'Update Status' ),
			'add_new_item'      => __( 'Add New Status' ),
			'new_item_name'     => __( 'New Status Name' ),
			'menu_name'         => __( 'Status' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'roadmap/status' ),
		);
		register_taxonomy( 'status', array( 'roadmap' ), $args );

		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => _x( 'Milestones', 'taxonomy general name' ),
			'singular_name'     => _x( 'Milestone', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Milestones' ),
			'all_items'         => __( 'All Milestones' ),
			'parent_item'       => __( 'Parent Milestone' ),
			'parent_item_colon' => __( 'Parent Milestone:' ),
			'edit_item'         => __( 'Edit Milestone' ),
			'update_item'       => __( 'Update Milestone' ),
			'add_new_item'      => __( 'Add New Milestone' ),
			'new_item_name'     => __( 'New Milestone Name' ),
			'menu_name'         => __( 'Milestones' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'roadmap/issue/milestone' ),
		);
		register_taxonomy( 'milestone', array( 'issue' ), $args );
		
		// Add new taxonomy, NOT hierarchical (like tags)
		$labels = array(
			'name'                       => _x( 'Labels', 'taxonomy general name' ),
			'singular_name'              => _x( 'Label', 'taxonomy singular name' ),
			'search_items'               => __( 'Search Labels' ),
			'popular_items'              => __( 'Popular Labels' ),
			'all_items'                  => __( 'All Labels' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Label' ),
			'update_item'                => __( 'Update Label' ),
			'add_new_item'               => __( 'Add New Label' ),
			'new_item_name'              => __( 'New Label Name' ),
			'separate_items_with_commas' => __( 'Separate labels with commas' ),
			'add_or_remove_items'        => __( 'Add or remove labels' ),
			'choose_from_most_used'      => __( 'Choose from the most used labels' ),
			'not_found'                  => __( 'No labels found.' ),
			'menu_name'                  => __( 'Labels' ),
		);

		$args = array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'           	=> array( 'slug' => 'roadmap/issue/label' ),
		);
		register_taxonomy( 'label', 'issue', $args );

	}

	static function create_rewrite_rules() {

		// prefix with roadmap slug, must by in the right order
		add_rewrite_rule('^roadmap/status/([^/]*)/?','index.php?status=$matches[1]','top');
		add_rewrite_rule('^roadmap/issue/label/([^/]*)/?','index.php?label=$matches[1]','top');
		add_rewrite_rule('^roadmap/issue/milestone/([^/]*)/?','index.php?milestone=$matches[1]','top');
		add_rewrite_rule('^roadmap/issue/([^/]*)/?','index.php?post_type=issue&name=$matches[1]','top');

	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

}
