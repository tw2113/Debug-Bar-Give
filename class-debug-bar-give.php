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

		echo '<h3 id="meta_data">' . esc_html__( 'Give Meta Data', 'debug-bar-give' ) . '</h3>';
		if ( empty( $this->give_meta_filtered ) ) {
			esc_html_e( 'No meta found.', 'debug-bar-give' );
		} else {
			$this->display_meta_fields( $this->give_meta_filtered );
		}

		echo '<h3 id="action_hooks">' . esc_html__( 'Give action hooks', 'debug-bar-give' ) . '</h3>';
		if ( empty( $this->give_actions ) ) {
			esc_html_e( 'No actions for this request', 'debug-bar-give' );
		} else {
			$this->display_action_hooks( $this->give_actions );
		}

		echo '<h3 id="filter_hooks">' . esc_html__( 'Give filter hooks', 'debug-bar-give' ) . '</h3>';
		if ( empty( $this->give_filters ) ) {
			esc_html_e( 'No filters for this request', 'debug-bar-give' );
		} else {
			$this->display_filter_hooks( $this->give_filters );
		}
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

		echo '<table cellspacing="0">';
		echo '<thead><tr>';
		echo '<th class="meta-name">' . esc_html__( 'Meta key', 'debug-bar-give' ) . '</th>';
		echo '<th class="meta-value">' . esc_html__( 'Meta value', 'debug-bar-give' ) . '</th>';
		echo '</tr></thead>';

		foreach ( $meta_fields as $field => $data ) {
			echo '<tr>';
			echo "<td>{$field}</td>";
			echo "<td>{$data}</td>";
			echo '</tr>';
		}

		echo '</table>';
	}

	/**
	 * Output our action hooks executed.
	 *
	 * @since 1.0.0
	 *
	 * @param array $action_hooks Actions to display.
	 */
	private function display_action_hooks( $action_hooks = array() ) {

		echo '<table cellspacing="0">';
		echo '<thead><tr>';
		echo '<th class="action-name">' . esc_html__( 'Action', 'debug-bar-give' ) . '</th>';
		echo '</tr></thead>';

		foreach ( $action_hooks as $hook => $value ) {
			echo '<tr>';
			echo "<td>{$hook}</td>";
			echo '</tr>';
		}

		echo '</table>';
	}

	/**
	 * Output our filters, priorities, and callbacks.
	 *
	 * @since 1.0.0
	 *
	 * @param array $filter_hooks Filters to display.
	 */
	private function display_filter_hooks( $filter_hooks = array() ) {

		echo '<table class="debug-bar-table" cellspacing="0">';
		echo '<thead><tr>';
		echo '<th class="filter-name">' . esc_html__( 'Filter', 'debug-bar-give' ) . '</th>';
		echo '<th class="filter-priority">' . esc_html__( 'Priority', 'debug-bar-give' ) . '</th>';
		echo '</tr></thead>';
		echo '<tfoot><tr>';
		echo '<th class="filter-name">' . esc_html__( 'Filter', 'debug-bar-give' ) . '</th>';
		echo '<th class="filter-priority">' . esc_html__( 'Priority', 'debug-bar-give' ) . '</th>';
		echo '</tr></tfoot>';
		echo '<tbody>';
		foreach ( $filter_hooks as $hook => $value ) {
			echo '<tr>';
			echo "<td>{$hook}</td>";
			echo '<td>';
				echo '<table>';
				foreach ( $value->callbacks as $priority => $callbacks ) {
					echo '<tr><td>' . $priority . '</td><td>';
						foreach ( $callbacks as $callback ) {
							if ( is_string( $callback['function'] ) ) {
								echo $callback['function'];
							} else {
								var_dump( $callback['function']);
							}
						}
					echo '</td></tr>';
				}
				echo '</table>';
			echo '</td>';
			echo '</tr>';
		}
		echo '</tbody>';

		echo '</table>';
	}
}
