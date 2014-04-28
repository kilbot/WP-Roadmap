<?php
/**
 *
 * @package   WP Projects
 * @author    Paul Kilmurray <paul@kilbot.com.au>
 * @license   GPL-2.0+
 * @link      http://www.kilbot.com.au
 * @copyright 2014 Paul Kilmurray
 *
 * @wordpress-plugin
 * Plugin Name:       WP Projects
 * Plugin URI:        https://github.com/kilbot/wp-projects
 * Description:       Custom Post Type for Issues assigned to Milestones and tagged with Labels
 * Version:           0.1
 * Author:            kilbot
 * Author URI:        http://www.kilbot.com.au
 * Text Domain:       wp-projects-locale
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/kilbot/wp-projects
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

// no public facing yet .. but the place holders remain

// require the initial plugin class
require_once( plugin_dir_path( __FILE__ ) . 'public/class-wp-projects.php' );

// Register hooks that are fired when the plugin is activated or deactivated.
// When the plugin is deleted, the uninstall.php file is loaded.
register_activation_hook( __FILE__, array( 'WP_Projects', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WP_Projects', 'deactivate' ) );


// instantiate the public facing class
add_action( 'plugins_loaded', array( 'WP_Projects', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-wp-projects-admin.php' );

	add_action( 'plugins_loaded', array( 'WP_Projects_Admin', 'get_instance' ) );

}
