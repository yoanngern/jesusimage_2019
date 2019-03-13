<?php

/**
 * Checkboxes for sections of tables to update.
 *
 * @author Mat Lipe
 */
class Go_Live_Update_URLS_Pro_Checkboxes {
	const COMMENTS = 'comments';
	const CUSTOM   = 'custom';
	const NETWORK  = 'network';
	const OPTIONS  = 'options';
	const POSTS    = 'posts';
	const TERMS    = 'terms';
	const USER     = 'user';


	/**
	 * List of checkbox sections and their contained tables.
	 *
	 * @var array
	 */
	protected $checkboxes;

	/**
	 * WP Database Object.
	 *
	 * @var wpdb $wpdb ;
	 */
	protected $wpdb;

	/**
	 * If we are passed a list of tables we have a flag here.
	 *
	 * @since 2.4.0
	 *
	 * @var bool
	 */
	protected $is_full_tables = false;


	/**
	 * Go_Live_Update_URLS_Pro_Checkboxes constructor.
	 */
	public function __construct() {
		$this->wpdb = $GLOBALS['wpdb'];
		$this->create_checkbox_list();
	}


	/**
	 * Get an array of sections => label to available checkboxes.
	 *
	 * @return array|false
	 */
	public function get_available_sections() {
		return array_combine( array_keys( $this->checkboxes ), wp_list_pluck( $this->checkboxes, 'label' ) );
	}


	/**
	 * Retrieve the list of tables to update based on
	 * which types are checked.
	 *
	 * If we have no matching sections is it assumed we have an
	 * array of tables not sections and therefore return what we
	 * started with.
	 *
	 * @param array $sections - Sections of tables or a list of tables.
	 *
	 * @return array
	 */
	public function swap_tables( array $sections ) {
		$tables = array();

		foreach ( $sections as $_table ) {
			if ( isset( $this->checkboxes[ $_table ] ) ) {
				$tables[] = $this->checkboxes[ $_table ]->tables;
			}
		}
		// We were passed tables instead of sections.
		if ( empty( $tables ) && ! empty( $sections ) ) {
			return $sections;
		}

		return call_user_func_array( 'array_merge', $tables );
	}


	/**
	 * Were we passed a list of tables instead of sections?
	 *
	 * @since 2.4.0
	 *
	 * @return bool
	 */
	public function is_full_tables() {
		return $this->is_full_tables;
	}

	/**
	 * Generate the list of sections and their tables.
	 *
	 * @return void
	 */
	protected function create_checkbox_list() {
		$checkboxes = array(
			self::POSTS    => $this->posts(),
			self::COMMENTS => $this->comments(),
			self::TERMS    => $this->terms(),
			self::OPTIONS  => $this->options(),
			self::USER     => $this->users(),
			self::CUSTOM   => $this->custom(),
		);
		if ( is_multisite() ) {
			$checkboxes[ self::NETWORK ] = $this->network();
		}
		$this->checkboxes = apply_filters( 'go-live-update-urls-pro/checkboxes/list', $checkboxes, $this );
	}


	/**
	 * Render to placeholder where React will populate the checkboxes.
	 *
	 * @return void
	 */
	public function render() {
		?>
		<div id="go-live-update-urls-pro/checkboxes/wrap"></div>
		<?php
	}


	/**
	 * All tables related to comments and their meta.
	 *
	 * @return object
	 */
	protected function comments() {
		return (object) array(
			'label'  => __( 'Comments', 'go-live-update-urls' ),
			'tables' => array(
				$this->wpdb->commentmeta,
				$this->wpdb->comments,
			),
		);
	}


	/**
	 * All tables related to users and their meta.
	 *
	 * @return object
	 */
	protected function users() {
		return (object) array(
			'label'  => __( 'Users', 'go-live-update-urls' ),
			'tables' => array(
				$this->wpdb->users,
				$this->wpdb->usermeta,
			),
		);
	}


	/**
	 * All tables related to taxonomies and their meta.
	 *
	 * @return object
	 */
	protected function terms() {
		$data = (object) array(
			'label'  => __( 'Categories, Tags, Custom Taxonomies', 'go-live-update-urls' ),
			'tables' => array(
				$this->wpdb->terms,
				$this->wpdb->term_relationships,
				$this->wpdb->term_taxonomy,
				$this->wpdb->termmeta,
			),
		);

		return $data;

	}


	/**
	 * The options table.
	 *
	 * @return object
	 */
	protected function options() {
		return (object) array(
			'label'  => __( 'Site Options, Widgets', 'go-live-update-urls' ),
			'tables' => array(
				$this->wpdb->options,
			),
		);
	}


	/**
	 * All tables related to posts and their meta.
	 *
	 * @return object
	 */
	protected function posts() {
		return (object) array(
			'label'  => __( 'Posts, Pages, Custom Post Types', 'go-live-update-urls' ),
			'tables' => array(
				$this->wpdb->posts,
				$this->wpdb->postmeta,
				$this->wpdb->links,
			),
		);
	}


	/**
	 * All tables related to network settings.
	 *
	 * @return object
	 */
	protected function network() {
		$tables = array(
			'label'  => __( 'Network Settings', 'go-live-update-urls' ),
			'tables' => array(
				$this->wpdb->blogs,
				$this->wpdb->site,
				$this->wpdb->sitemeta,
			),
		);
		// WP 5.0.0+.
		if ( isset( $this->wpdb->blogmeta ) ) {
			$tables['tables'][] = $this->wpdb->blogmeta;
		}

		return (object) $tables;

	}


	/**
	 * All the databases tables created by plugins or themes.
	 *
	 * @return object
	 */
	public function custom() {
		$default_tables = $this->wpdb->tables();
		$db             = Go_Live_Update_Urls_Database::instance();
		$all_tables     = $db->get_all_table_names();
		$all_tables     = array_flip( $all_tables );
		foreach ( $default_tables as $table ) {
			unset( $all_tables[ $table ] );
		}

		$custom = (object) array(
			'label'  => __( 'Custom tables created by plugins', 'go-live-update-urls' ),
			'tables' => array_flip( $all_tables ),
		);

		return $custom;
	}


	/**
	 * Instance of this class for use as singleton
	 *
	 * @var self
	 */
	protected static $instance;


	/**
	 * Get (and instantiate, if necessary) the instance of the
	 * class
	 *
	 * @static
	 * @return self
	 */
	public static function instance() {
		if ( ! is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
