<?php
/**
 * Custom Image Uploader
 */
class Meow_WR2X_Uploader {
	private $core;

	public function __construct( $core ) {
		$this->core = $core;
		add_action( 'post-plupload-upload-ui', array ( $this, 'render' ) );
	}

	/**
	 * Renders the uploader UI
	 */
	public function render() {
		$screen = get_current_screen();
		switch ( $screen->id ) {
		case 'media':
?>
<div id="wr2x_retina-uploader">
	<h1><?php _e( 'Upload New Retina Image', 'wp-retina-2x' ); ?></h1>
	<p><?php _e( 'The Retina Image you upload here will become your Retina Image for Full-Size. Automatically, WP Retina 2x will generate the normal Full-Size image which will be basically divided by two. The thumbnails and retina thumbnails will also be generated based on your options.', 'wp-retina-2x' ); ?></p>
	<div id="wr2x_drag-drop-area">
		<div class="drag-drop-inside">
			<p class="drag-drop-info"><?php _e('Drop files here'); ?></p>
			<p><?php _ex('or', 'Uploader: Drop files here - or - Select Files'); ?></p>
			<p class="drag-drop-buttons"><input id="wr2x_file-select-button" type="button" value="<?php esc_attr_e('Select Files'); ?>" class="button"></p>
			<input id="wr2x_file-selector" type="file" accept="image/*" multiple>
		</div>
	</div>
</div>
<?php
			break;
		default:
		}
	}
}
