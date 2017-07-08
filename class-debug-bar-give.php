<?php
/**
 * Debug Bar Give Panel
 *
 * @package DebugBarGive
 * @since   1.0.0
 */

/**
 * Add a new Debug Bar Panel.
 */
class Debug_Bar_Give extends Debug_Bar_Panel {

	/**
	 * Holds all of the GiveWP post meta.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $give_meta = array();

	/**
	 * Holds all of the GiveWP specific post meta.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $give_meta_filtered = array();

	/**
	 * Holds all of the GiveWP action hooks on a given page request.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $give_actions = array();

	/**
	 * Holds all of the GiveWP filter hooks on a given page request.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $give_filters = array();

	/**
	 * Give the panel a title and set the enqueues.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->title( __( 'GiveWP', 'debug-bar-give' ) );

		add_action( 'wp_print_styles', array( $this, 'print_styles' ) );
		add_action( 'admin_print_styles', array( $this, 'print_styles' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'print_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'print_scripts' ) );

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load the textdomain.
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'debug-bar-give' );
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 1.0.0
	 */
	public function print_styles() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style( 'debug-bar-give', plugins_url( "css/debug-bar-give{$suffix}.css", __FILE__ ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 */
	public function print_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	}

	/**
	 * Show the menu item in Debug Bar.
	 *
	 * Also sets up some of our objects.
	 *
	 * @since 1.0.0
	 */
	public function prerender() {
		$this->set_visible( true );
		$this->set_give_actions();
		$this->set_give_filters();
		$this->set_give_meta();
	}

	/**
	 * Show the contents of the page.
	 *
	 * @since 1.0.0
	 */
	public function render() {
		global $post;
		if ( ! $post instanceof WP_Post || 'give_forms' !== $post->post_type ) {
			echo '<h2>' . esc_html__( 'No GiveWP form found.', 'debug-bar-give' ) . '</h2>';
			return;
		}

		printf(
			'<h2><span>%s</span>%s</h2>',
			esc_html__( 'Current GiveWP form ID:', 'debug-bar-give' ),
			number_format( $post->ID )
		);

		printf(
			'<h2><span>%s</span>%s</h2>',
			esc_html__( 'Total meta fields found:', 'debug-bar-give' ),
			number_format( count( $this->give_meta_filtered ) )
		);

		printf(
			'<h2><span>%s</span>%s</h2>',
			esc_html__( 'Total action hooks:', 'debug-bar-give' ),
			number_format( count( $this->give_actions ) )
		);

		printf(
			'<h2><span>%s</span>%s</h2>',
			esc_html__( 'Total filter hooks:', 'debug-bar-give' ),
			number_format( count( $this->give_filters ) )
		);

		echo '<div class="debug-bar-give-wrapper"><h3 id="meta_data">' . esc_html__( 'GiveWP metadata', 'debug-bar-give' ) . '</h3>';
		if ( empty( $this->give_meta_filtered ) ) {
			esc_html_e( 'No meta found.', 'debug-bar-give' );
		} else {
			$this->display_meta_fields( $this->give_meta_filtered );
		}
		echo '</div>';

		echo '<div class="debug-bar-give-wrapper"><h3 id="action_hooks">' . esc_html__( 'GiveWP action hooks', 'debug-bar-give' ) . '</h3>';
		if ( empty( $this->give_actions ) ) {
			esc_html_e( 'No actions for this request', 'debug-bar-give' );
		} else {
			$this->display_action_hooks( $this->give_actions );
		}
		echo '</div>';

		echo '<div class="debug-bar-give-wrapper"><h3 id="filter_hooks">' . esc_html__( 'GiveWP filter hooks', 'debug-bar-give' ) . '</h3>';
		if ( empty( $this->give_filters ) ) {
			esc_html_e( 'No filters for this request', 'debug-bar-give' );
		} else {
			$this->display_filter_hooks( $this->give_filters );
		}
		echo '</div>';
	}

	/**
	 * Sets our run actions for the current request.
	 *
	 * @since 1.0.0
	 */
	private function set_give_actions() {
		$wp_action = $GLOBALS['wp_actions'];
		ksort( $wp_action );

		foreach ( $wp_action as $key => $action ) {
			if ( 'give' !== substr( $key, 0, 4 ) ) {
				continue;
			}
			$this->give_actions[ $key ] = $action;
		}
	}

	/**
	 * Sets our run filters for the current request.
	 *
	 * @since 1.0.0
	 */
	private function set_give_filters() {
		$wp_filter = $GLOBALS['wp_filter'];
		ksort( $wp_filter );

		foreach ( $wp_filter as $key => $filter ) {
			if ( '_give' !== substr( $key, 0, 5 ) && 'give' !== substr( $key, 0, 4 ) ) {
				continue;
			}
			$this->give_filters[ $key ] = $filter;
		}
	}

	/**
	 * Sets our meta properties for GiveWP metadata on a form.
	 *
	 * @since 1.0.0
	 */
	private function set_give_meta() {
		global $post;

		if ( 'give_forms' === $post->post_type ) {
			$this->give_meta = get_post_meta( $post->ID );

			foreach ( $this->give_meta as $key => $value ) {
				if ( '_give' !== substr( $key, 0, 5 ) ) {
					continue;
				}
				$this->give_meta_filtered[ $key ] = $value[0];
			}
		}
	}

	/**
	 * Display the meta fields in a table.
	 *
	 * @since 1.0.0
	 *
	 * @param array $meta_fields The transients in an array.
	 */
	private function display_meta_fields( $meta_fields = array() ) {
		?>
		<table class="debug-bar-give-table debug-bar-give-meta" cellspacing="0">
			<thead>
				<tr>
					<th class="meta-name"><?php esc_html_e( 'Meta key', 'debug-bar-give' ); ?></th>
					<th class="meta-value"><?php esc_html_e( 'Meta value', 'debug-bar-give' ); ?></th>
				</tr>
			</thead>
			<?php
			foreach ( $meta_fields as $field => $data ) {
				?>
				<tr>
					<td><?php echo esc_html( $field ); ?></td>
					<td><?php echo esc_html( $data ); ?></td>
				</tr>
				<?php
			} ?>
		</table>
		<?php
	}

	/**
	 * Output our action hooks executed.
	 *
	 * @since 1.0.0
	 *
	 * @param array $action_hooks Actions to display.
	 */
	private function display_action_hooks( $action_hooks = array() ) {

		$count = ceil( count( $action_hooks ) / 2 );
		$action_hook_chunks = array_chunk( $action_hooks, $count, true );
		foreach ( $action_hook_chunks as $chunk ) {
			echo '<div class="debug-bar-give-actions-list"><ul>';
			foreach ( $chunk as $hook => $value ) {
				?>
					<li><?php echo esc_html( $hook ); ?></li>
				<?php
			}
			echo '</ul></div>';
		}
	}

	/**
	 * Output our filters, priorities, and callbacks.
	 *
	 * @since 1.0.0
	 *
	 * @param array $filter_hooks Filters to display.
	 */
	private function display_filter_hooks( $filter_hooks = array() ) {
		$hook_in_count = 0;
		?>
		<table class="debug-bar-give-table debug-bar-give-filters" cellspacing="0">
			<thead><tr>
				<th class="filter-name"><?php esc_html_e( 'Filter', 'debug-bar-give' ); ?></th>
				<th class="filter-priority"><?php esc_html_e( 'Priority', 'debug-bar-give' ); ?></th>
				<th class="filter-callbacks"><?php esc_html_e( 'Registered Callbacks', 'debug-bar-give' ); ?></th>
			</tr></thead>
			<tfoot><tr>
				<th class="filter-name"><?php esc_html_e( 'Filter', 'debug-bar-give' ); ?></th>
				<th class="filter-priority"><?php esc_html_e( 'Priority', 'debug-bar-give' ); ?></th>
				<th class="filter-callbacks"><?php esc_html_e( 'Registered Callbacks', 'debug-bar-give' ); ?></th>
			</tr></tfoot>
			<tbody>
			<?php
			foreach ( $filter_hooks as $hook => $value ) {
				$filter_val = array();
				if ( $value instanceof WP_Hook ) {
					$filter_val = $value->callbacks;
				}

				$filter_count = count( $filter_val );

				$rowspan = '';
				if ( $filter_count > 1 ) {
					$rowspan = 'rowspan="' . $filter_count . '"';
				}

				echo '<tr>';
				echo '<th ' . esc_attr( $rowspan ) . '>' . esc_html( $hook ) . '</th>';

				if ( $filter_count > 0 ) {
					$first = true;
					foreach ( $value->callbacks as $priority => $functions ) {
						if ( true !== $first ) {
							echo '<tr>';
						} else {
							$first = false;
						}
						echo '<td class="prio">' . esc_html( $priority ) . '</td>';
						echo '<td><ul>';
						foreach ( $functions as $single_function ) {
							$signature = $single_function['function'];
							if (
								(
									! is_string( $single_function['function'] ) &&
									! is_object( $single_function['function'] )
								) &&
								(
									! is_array( $single_function['function'] ) ||
									(
										is_array( $single_function['function'] ) &&
										(
											! is_string( $single_function['function'][0] ) &&
											! is_object( $single_function['function'][0] )
										)
									)
								)
							) {
								// Type 1 - not a callback.
								continue;
							} elseif (
							dbg_is_closure( $single_function['function'] )
							) {
								// Type 2 - closure.
								echo '<li>[<em>' . esc_html_e( 'closure', 'debug-bar-give' ) . '</em>]</li>';
								$signature = get_class( $single_function['function'] ) . $hook_in_count;
							} elseif (
								(
									is_array( $single_function['function'] ) ||
									is_object( $single_function['function'] )
								) &&
								dbg_is_closure( $single_function['function'][0] )
							) {
								// Type 3 - closure within an array.
								echo '<li>[<em>' . esc_html_e( 'closure', 'debug-bar-give' ) . '</em>]</li>';
								$signature = get_class( $single_function['function'] ) . $hook_in_count;
							} elseif (
								is_string( $single_function['function'] ) &&
								strpos( $single_function['function'], '::' ) === false
							) {
								// Type 4 - simple string function (includes lambda's).
								$signature = sanitize_text_field( $single_function['function'] );
								echo '<li>' . esc_html( $signature ) . '</li>';
							} elseif (
								is_string( $single_function['function'] ) &&
								strpos( $single_function['function'], '::' ) !== false
							) {
								// Type 5 - static class method calls - string.
								$signature = str_replace( '::', ' :: ', sanitize_text_field( $single_function['function'] ) );
								echo '<li>[<em>' . esc_html__( 'class', 'debug-bar-give' ) . '</em>] ' . esc_html( $signature ) . '</li>';
							} elseif (
								is_array( $single_function['function'] ) &&
								(
									is_string( $single_function['function'][0] ) &&
									is_string( $single_function['function'][1] )
								)
							) {
								// Type 6 - static class method calls - array.
								$signature = sanitize_text_field( $single_function['function'][0] ) . ' :: ' . sanitize_text_field( $single_function['function'][1] );
								echo '<li>[<em>' . esc_html__( 'class', 'debug-bar-give' ) . '</em>] ' . esc_html( $signature ) . '</li>';
							} elseif (
								is_array( $single_function['function'] ) &&
								(
									is_object( $single_function['function'][0] ) &&
									is_string( $single_function['function'][1] )
								)
							) {
								// Type 7 - object method calls.
								$signature = esc_html( get_class( $single_function['function'][0] ) ) . ' -> ' . sanitize_text_field( $single_function['function'][1] );
								echo '<li>[<em>' . esc_html__( 'object', 'debug-bar-give' ) . '</em>] ' . esc_html( $signature ) . '</li>';
							} else {
								// Type 8 - undetermined.
								esc_html_e( 'Undetermined callback', 'debug-bar-give' );
							} // End if().
						} // End foreach().
						echo '</ul></td>';
					} // End foreach().
					echo '</tr>';
				} else {
					?>
					<td>&nbsp;</td><td>&nbsp;</td></tr>
					<?php
				} // End if().
			} // End foreach().
			?>
			</tbody>
		</table>
		<?php
	}
}
