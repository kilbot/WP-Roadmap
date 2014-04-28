<?php
/**
 * WP Projects
 *
 * @package   WP Projects
 * @author    Paul Kilmurray <paul@kilbot.com.au>
 * @license   GPL-2.0+
 * @link      http://www.kilbot.com.au
 * @copyright 2014 Paul Kilmurray
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-plugin-name-admin.php`
 *
 * @package WP_Projects_Admin
 * @author  Paul Kilmurray <paul@kilbot.com.au>
 */

class WP_Projects {
				
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
	protected $plugin_slug = 'wp-projects';

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

		add_filter( 'post_type_link', array( $this, 'wp_projects_permalinks' ), 10, 2 );

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
	public function activate( ) {
	    // First, we "add" the custom post type via the above written function.
	    // Note: "add" is written with quotes, as CPTs don't get added to the DB,
	    // They are only referenced in the post_type column with a post entry, 
	    // when you add a post of this CPT.
	    $this->create_cpt();

	    // ATTENTION: This is *only* done during plugin activation hook in this example!
	    // You should *NEVER EVER* do this on every page load!!
	    flush_rewrite_rules();
	}

	/**
	 * Fired when the plugin is deactivated.
	 */
	public static function deactivate( ) {

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
	 * There are two CPT: Projects and Issues
	 * Projects are pages, stand alone silos to hold project data
	 * Issues are posts with a relationship to a Project page
	 */
	public function create_cpt() {
		$labels = array(
			'name'               => _x( 'Projects', 'post type general name', 'wp-projects-textdomain' ),
			'singular_name'      => _x( 'Project', 'post type singular name', 'wp-projects-textdomain' ),
			'menu_name'          => _x( 'Projects', 'admin menu', 'wp-projects-textdomain' ),
			'name_admin_bar'     => _x( 'Project', 'add new on admin bar', 'wp-projects-textdomain' ),
			'add_new'            => _x( 'Add New Project', 'project', 'wp-projects-textdomain' ),
			'add_new_item'       => __( 'Add New Project', 'wp-projects-textdomain' ),
			'new_item'           => __( 'New Project', 'wp-projects-textdomain' ),
			'edit_item'          => __( 'Edit Project', 'wp-projects-textdomain' ),
			'view_item'          => __( 'View Project', 'wp-projects-textdomain' ),
			'all_items'          => __( 'Projects', 'wp-projects-textdomain' ),
			'search_items'       => __( 'Search Projects', 'wp-projects-textdomain' ),
			'parent_item_colon'  => __( 'Parent Project:', 'wp-projects-textdomain' ),
			'not_found'          => __( 'No Projects found.', 'wp-projects-textdomain' ),
			'not_found_in_trash' => __( 'No Projects found in Trash.', 'wp-projects-textdomain' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => false,  // handled by wp_projects_permalinks
			'capability_type'    => 'page',
			'has_archive'        => true,
			'hierarchical'       => true,
			'menu_position'      => 20,
			'menu_icon' 		 => 'dashicons-hammer',
			'supports'           => array( 
										'title', 
										'editor', 
										'author', 
										'comments', 
										'custom-fields', 
										'sticky', 
										'page-attributes'
									),
		);

		register_post_type( 'project', $args );

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
			'show_in_menu'       => 'edit.php?post_type=project',
			'query_var'          => true,
			'rewrite'            => false, // handled by wp_projects_permalinks
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


	public function create_cpt_taxonomies() {
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
			'rewrite'           => false,  // handled by wp_projects_permalinks
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
			'rewrite'               => false,  // handled by wp_projects_permalinks
		);

		register_taxonomy( 'label', 'issue', $args );

	}

	public function create_rewrite_rules() {
		global $wp_rewrite;

		// add rewrite tag for project
		$wp_rewrite->add_rewrite_tag('%project%', '([^/]+)', 'project=');
		$wp_rewrite->add_rewrite_tag('%issue%', '([^/]+)', 'issue=');
		// $wp_rewrite->add_rewrite_tag('%milestone%', '([^/]+)', 'milestone=');
		// $wp_rewrite->add_rewrite_tag('%label%', '([^/]+)', 'label=');

		$wp_rewrite->add_permastruct('issue', '/%project%/issue/%issue%', false);


	}

	public function wp_projects_permalinks( $permalink, $post ) {
		if( 'issue' == get_post_type( $post ) ) {
			$project = get_field( 'project', $post->ID );
			if( $project && in_array( $project->post_status, array('publish', 'private') ) ) {
				$permalink = str_replace( '%project%', $project->post_name , $permalink );
			}
		}
		return $permalink;
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
