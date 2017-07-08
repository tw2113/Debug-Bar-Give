<?php
/**
 * PHP 5.3+ functionality in a separate file
 *
 * Borrowed from https://wordpress.org/plugins/debug-bar-actions-and-filters-addon/
 *
 * @since 1.0.0
 * @author subharanjan
 */
if ( ! function_exists( 'debug_bar_give_is_closure' ) ) {
	/**
	 * Function to check for closures
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $arg function name.
	 * @return boolean $closurecheck Return whether or not a closure.
	 */
	function debug_bar_give_is_closure( $arg ) {
		$test         = function () {
		};
		$closurecheck = ( $arg instanceof $test );

		return $closurecheck;
	}
}
