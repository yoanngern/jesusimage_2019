<?php
/**
 * Created by PhpStorm.
 * User: yoanngern
 * Date: 2019-02-16
 * Time: 11:50
 */

if( function_exists('acf_add_local_field_group') ):

	acf_add_local_field_group(array(
		'key' => 'group_5c681ef1631f0',
		'title' => 'Profile',
		'fields' => array(
			array(
				'key' => 'field_5c681ef6ff63f',
				'label' => 'Application form',
				'name' => 'user_app_form',
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'user_roles' => array(
					0 => 'super_admin',
					1 => 'administrator',
					2 => 'editor',
				),
				'choices' => array(
					'not_received' => '...',
					'received' => 'Received',
				),
				'default_value' => array(
				),
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'array',
				'ajax' => 0,
				'placeholder' => '',
			),
			array(
				'key' => 'field_5c6828944d181',
				'label' => 'Application fee',
				'name' => 'user_app_fee',
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'user_roles' => array(
					0 => 'super_admin',
					1 => 'administrator',
					2 => 'editor',
				),
				'choices' => array(
					'not_paid' => '...',
					'paid' => 'Paid',
				),
				'default_value' => array(
					0 => 'to_pay',
				),
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'array',
				'ajax' => 0,
				'placeholder' => '',
			),
			array(
				'key' => 'field_5c6832c1fb914',
				'label' => 'Year',
				'name' => 'user_app_year',
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'user_roles' => array(
					0 => 'super_admin',
					1 => 'administrator',
					2 => 'editor',
				),
				'choices' => array(
					1 => 'First year',
					2 => 'Second year',
				),
				'default_value' => array(
				),
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'array',
				'ajax' => 0,
				'placeholder' => '',
			),
			array(
				'key' => 'field_5c68349fd6b89',
				'label' => 'Application status',
				'name' => 'user_app_status',
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'user_roles' => array(
					0 => 'super_admin',
					1 => 'administrator',
					2 => 'editor',
				),
				'choices' => array(
					'applying' => 'Applying',
					'ready_for_interview' => 'Ready for interview',
					'interview_scheduled' => 'Interview scheduled',
					'second_interview' => 'Second interview',
					'accepted' => 'Accepted',
					'declined' => 'Declined',
				),
				'default_value' => array(
				),
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'array',
				'ajax' => 0,
				'placeholder' => '',
			),
			array(
				'key' => 'field_5c683e17f5049',
				'label' => 'Student ID',
				'name' => 'user_student_id',
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'user_roles' => array(
					0 => 'super_admin',
					1 => 'administrator',
					2 => 'editor',
				),
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'maxlength' => '',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'user_form',
					'operator' => '==',
					'value' => 'all',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'seamless',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => 1,
		'description' => '',
	));

endif;