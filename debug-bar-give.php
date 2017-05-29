<?php
/**
 * Plugin Name: Debug Bar Give
 * Version: 0.5
 * Description: Adds information about the WordPress Transient API to Debug Bar.
 * Author: Dominik Schilling
 * Author URI: https://wphelper.de/
 * Plugin URI: https://dominikschilling.de/wp-plugins/debug-bar-transients/en/
 * Text Domain: debug-bar-transients
 * License: GPLv2 or later
 *    Copyright (C) 2011-2016 Dominik Schilling
 *    This program is free software; you can redistribute it and/or
 *    modify it under the terms of the GNU General Public License
 *    as published by the Free Software Foundation; either version 2
 *    of the License, or (at your option) any later version.
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *    You should have received a copy of the GNU General Public License
 *    along with this program; if not, write to the Free Software
 *    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * Don't call this file directly.
 */
if ( ! class_exists( 'WP' ) ) {
	die();
}

/**
 * Adds panel, as defined in the included class, to Debug Bar.
 *
 * @param $panels array
 *
 * @return array
 */
function debug_bar_give_add__panel( $panels ) {
	if ( ! class_exists( 'Debug_Bar_Give' ) ) {
		include( 'class-debug-bar-give.php' );
		$panels[] = new Debug_Bar_Give();
	}

	return $panels;
}
add_filter( 'debug_bar_panels', 'debug_bar_give_add__panel' );
