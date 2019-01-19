<?php
namespace ElementorPro\Modules\DynamicTags\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use ElementorPro\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Shortcode extends Tag {
	public function get_name() {
		return 'shortcode';
	}

	public function get_title() {
		return __( 'Shortcode', 'elementor-pro' );
	}

	public function get_group() {
		return Module::MEDIA_GROUP;
	}

	public function get_categories() {
		return [
			Module::TEXT_CATEGORY,
			Module::URL_CATEGORY,
			Module::POST_META_CATEGORY,
			Module::GALLERY_CATEGORY,
			Module::IMAGE_CATEGORY,
			Module::MEDIA_CATEGORY,
		];
	}

	protected function _register_controls() {
		$this->add_control(
			'shortcode',
			[
				'label' => __( 'Shortcode', 'elementor-pro' ),
				'type'  => Controls_Manager::TEXTAREA,
			]
		);
	}

	public function render() {
		$settings = $this->get_settings();

		if ( empty( $settings['shortcode'] ) ) {
			return;
		}

		$shortcode_string = $settings['shortcode'];

		echo wp_kses_post( do_shortcode( $shortcode_string ) );
	}
}
