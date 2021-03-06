<?php
if( function_exists('acf_add_local_field_group') ):

	acf_add_local_field_group(array(
		'key' => 'group_5c682afc15431',
		'title' => 'Dashboard',
		'fields' => array(
			array(
				'key' => 'field_5c682b06006f2',
				'label' => 'First year application form',
				'name' => 'first_app_form',
				'type' => 'page_link',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '51',
					'class' => '',
					'id' => '',
				),
				'user_roles' => array(
					0 => 'all',
				),
				'post_type' => array(
					0 => 'page',
				),
				'taxonomy' => '',
				'allow_null' => 0,
				'allow_archives' => 1,
				'multiple' => 0,
			),
			array(
				'key' => 'field_5c682b924687f',
				'label' => 'First year Application fee',
				'name' => 'first_app_fee',
				'type' => 'page_link',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '49',
					'class' => '',
					'id' => '',
				),
				'user_roles' => array(
					0 => 'all',
				),
				'post_type' => array(
					0 => 'page',
				),
				'taxonomy' => '',
				'allow_null' => 0,
				'allow_archives' => 1,
				'multiple' => 0,
			),
			array(
				'key' => 'field_5c682b79f0012',
				'label' => 'Second year application form',
				'name' => 'second_app_form',
				'type' => 'page_link',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '51',
					'class' => '',
					'id' => '',
				),
				'user_roles' => array(
					0 => 'all',
				),
				'post_type' => array(
					0 => 'page',
				),
				'taxonomy' => '',
				'allow_null' => 0,
				'allow_archives' => 1,
				'multiple' => 0,
			),
			array(
				'key' => 'field_5c6ad4739185b',
				'label' => 'Second year Application fee',
				'name' => 'second_app_fee',
				'type' => 'page_link',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '49',
					'class' => '',
					'id' => '',
				),
				'user_roles' => array(
					0 => 'all',
				),
				'post_type' => array(
					0 => 'page',
				),
				'taxonomy' => '',
				'allow_null' => 0,
				'allow_archives' => 1,
				'multiple' => 0,
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_template',
					'operator' => '==',
					'value' => 'page-dashboard.php',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'seamless',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
	));

endif;