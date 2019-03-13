<?php
if( function_exists('acf_add_local_field_group') ):

	acf_add_local_field_group(array(
		'key' => 'group_5c59f4e879ff2',
		'title' => 'Donation',
		'fields' => array(
			array(
				'key' => 'field_5c816a08addd6',
				'label' => 'Student',
				'name' => 'student',
				'type' => 'user',
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
				'role' => '',
				'allow_null' => 1,
				'multiple' => 0,
				'return_format' => 'id',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'give_forms',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'acf_after_title',
		'style' => 'seamless',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
	));

endif;