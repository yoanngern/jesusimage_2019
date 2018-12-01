<?php

if( function_exists('acf_add_local_field_group') ):

	acf_add_local_field_group(array(
		'key' => 'group_59f35cf1e5baa',
		'title' => 'Header',
		'fields' => array(
			array(
				'key' => 'field_59f35d08bff4d',
				'label' => 'Background',
				'name' => 'bg_image',
				'type' => 'image',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '51',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'array',
				'preview_size' => 'header',
				'library' => 'all',
				'min_width' => '',
				'min_height' => '',
				'min_size' => '',
				'max_width' => '',
				'max_height' => '',
				'max_size' => '',
				'mime_types' => '',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'page',
				),
				array(
					'param' => 'post_template',
					'operator' => '!=',
					'value' => 'page-home.php',
				),
				array(
					'param' => 'post_template',
					'operator' => '!=',
					'value' => 'page-tv.php',
				),
			),
		),
		'menu_order' => 1,
		'position' => 'normal',
		'style' => 'seamless',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => 1,
		'description' => '',
	));

endif;