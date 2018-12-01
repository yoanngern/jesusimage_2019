<?php

class MeowAppsPro_WR2X_Core {

	private $prefix = 'wr2x';
	private $item = 'WP Retina 2x Pro';
	private $admin = null;
	private $core = null;

	public function __construct( $prefix, $mainfile, $domain, $version, $core, $admin  ) {
		// Pro Admin (license, update system, etc...)
		$this->prefix = $prefix;
		$this->mainfile = $mainfile;
		$this->domain = $domain;
		$this->core = $core;
		$this->admin = $admin;
		new MeowApps_Admin_Pro( $prefix, $mainfile, $domain, $this->item, $version );

		// Overrides for the Pro
		add_filter( 'wr2x_plugin_title', array( $this, 'plugin_title' ), 10, 1 );
		if ( is_admin() ) {
			include __DIR__ . '/uploader.php';
			new Meow_WR2X_Uploader( $this );
		}
	}

	function plugin_title( $string ) {
			return $string . " (Pro)";
	}

}
