<?php
/**
 *
 * @package   WP Roadmap
 * @author    Paul Kilmurray <paul@kilbot.com.au>
 * @license   GPL-2.0+
 * @link      http://www.kilbot.com.au
 * @copyright 2014 Paul Kilmurray
 *
 * @wordpress-plugin
 * Plugin Name:       WP Roadmap
 * Plugin URI:        https://github.com/kilbot/wp-roadmap
 * Description:       CPT for Milestones cludged together with CPT for Issues
 * Version:           0.1
 * Author:            kilbot
 * Author URI:        http://www.kilbot.com.au
 * Text Domain:       wp-roadmap-locale
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/kilbot/wp-roadmap
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
require_once( plugin_dir_path( __FILE__ ) . 'public/class-wp-roadmap.php' );

// Register hooks that are fired when the plugin is activated or deactivated.
// When the plugin is deleted, the uninstall.php file is loaded.
register_activation_hook( __FILE__, array( 'WP_Roadmap', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WP_Roadmap', 'deactivate' ) );


// instantiate the public facing class
add_action( 'plugins_loaded', array( 'WP_Roadmap', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-wp-roadmap-admin.php' );

	add_action( 'plugins_loaded', array( 'WP_Roadmap_Admin', 'get_instance' ) );

}
