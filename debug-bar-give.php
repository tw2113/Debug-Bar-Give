<?php
/**
 * Debug Bar Give Loader
 *
 * @package DebugBarGive
 * @since 1.0.0
 */

/**
 * Plugin Name: Debug Bar Give
 * Version: 1.0.0
 * Description: Adds information about GiveWP to Debug Bar.
 * Author: Michael Beckwith
 * Author URI: https://michaelbox.net
 * Plugin URI: https://michaelbox.net
 * Text Domain: debug-bar-give
 * License: MIT
 */

/**
 * Plenty of credit goes to Subharanjan and https://wordpress.org/plugins/debug-bar-actions-and-filters-addon/
 */

/**
 * Don't call this file directly.
 */
if ( ! class_exists( 'WP' ) ) {
	wp_die();
}

/**
 * Adds panel, as defined in the included class, to Debug Bar.
 *
 * @param array $panels Array of panels to render.
 *
 * @return array
 */
function debug_bar_give_add_panel( $panels ) {
	if ( ! class_exists( 'Debug_Bar_Give' ) ) {
		include( 'class-debug-bar-give.php' );
		$panels[] = new Debug_Bar_Give();
	}

	return $panels;
}
add_filter( 'debug_bar_panels', 'debug_bar_give_add_panel' );

if ( ! function_exists( 'dbg_is_closure' ) ) {
	/**
	 * Function to to check for closures
	 *
	 * @param   mixed $arg function name
	 *
	 * @return  boolean $closurecheck return whether or not a closure
	 */
	function dbg_is_closure( $arg ) {
		if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
			return false;
		}

		include_once( plugin_dir_path( __FILE__ ) . 'php5.3-closure-test.php' );

		return debug_bar_give_is_closure( $arg );
	}
}
