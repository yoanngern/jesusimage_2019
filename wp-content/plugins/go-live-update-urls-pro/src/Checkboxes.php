<?php


/**
 * Go_Live_Update_URLS_Pro_Checkboxes
 *
 * @author Mat Lipe
 *
 */
class Go_Live_Update_URLS_Pro_Checkboxes {
	const COMMENTS = 'comments';
	const CUSTOM = 'custom';
	const NETWORK = 'network';
	const OPTIONS = 'options';
	const POSTS = 'posts';
	const TERMS = 'terms';
	const USER = 'user';


	/**
	 * checkboxes
	 *
	 * @var array
	 */
	protected $checkboxes;

	/**
	 * wpdb
	 *
	 * @var wpdb $wpdb ;
	 */
	protected $wpdb;


	public function __construct() {
		$this->wpdb = $GLOBALS['wpdb'];
		$this->create_checkbox_list();

	}


	/**
	 * Retrieve the list of tables to update based on
	 * which types are checked.
	 * If we have no matching sections is it assumed we have an
	 * array of tables not sections and therefore return what we
	 * started with.
	 *
	 *
	 * @param array $sections
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
		//we were passed tables instead of sections (to be used with upcoming feature)
		if ( empty( $tables ) && ! empty( $sections ) ) {
			return $sections;
		}
		//see https://github.com/kalessil/phpinspectionsea/blob/master/docs/performance.md#slow-array-function-used-in-loop
		return call_user_func_array( 'array_merge', $tables );
	}


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


	public function render() {
		?>
		<ul id="gluu-checkboxes">
			<?php
			foreach ( $this->checkboxes as $checkbox => $data ) {
				?>
				<li>
					<?php
					printf( '<input name="%s[]" type="checkbox" value="%s" checked /> %s', esc_attr( Go_Live_Update_Urls_Admin_Page::TABLE_INPUT_NAME ), esc_attr( $checkbox ), esc_html( $data->label ) );
					?>
				</li>
				<?php

			}
			?>
		</ul>
		<?php
	}


	protected function comments() {
		return (object) array(
			'label'  => __( 'Comments', 'go-live-update-urls' ),
			'tables' => array(
				$this->wpdb->commentmeta,
				$this->wpdb->comments,
			),
		);
	}


	protected function users() {
		return (object) array(
			'label'  => __( 'Users', 'go-live-update-urls' ),
			'tables' => array(
				$this->wpdb->users,
				$this->wpdb->usermeta,
			),
		);
	}


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


	protected function options() {
		return (object) array(
			'label'  => __( 'Site Options, Widgets', 'go-live-update-urls' ),
			'tables' => array(
				$this->wpdb->options,
			),
		);
	}


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


	protected function network() {
		$tables = array(
			'label'  => __( 'Network Settings', 'go-live-update-urls' ),
			'tables' => array(
				$this->wpdb->blogs,
				$this->wpdb->site,
				$this->wpdb->sitemeta,
			),
		);
		//WP 5.0.0+
		if ( isset( $this->wpdb->blogmeta ) ) {
			$tables['tables'][] = $this->wpdb->blogmeta;
		}

		return (object) $tables;

	}


	public function custom() {
		$default_tables = $this->wpdb->tables();
		$db             = Go_Live_Update_Urls_Database::instance();

		//basic version 5.0.1+
		if ( method_exists( $db, 'get_all_table_names' ) ) {
			$all_tables = $db->get_all_table_names();
		} else {
			$all_tables = wp_list_pluck( $db->get_all_tables(), 'TABLE_NAME' );
		}

		$all_tables = array_flip( $all_tables );
		foreach ( $default_tables as $table ) {
			unset( $all_tables[ $table ] );
		}

		$custom = (object) array(
			'label'  => __( 'Custom tables created by plugins', 'go-live-update-urls' ),
			'tables' => array_flip( $all_tables ),
		);

		return $custom;
	}


}
