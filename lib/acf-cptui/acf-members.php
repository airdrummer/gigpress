add_action( 'acf/include_fields', function() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
	'key' => 'group_68c05dec46523',
	'title' => 'members',
	'fields' => array(
		array(
			'key' => 'field_68c05ded78b00',
			'label' => 'sort-order',
			'name' => 'sort-order',
			'aria-label' => '',
			'type' => 'number',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => array(
				array(
					array(
						'operator' => '',
						'value' => '',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => 0,
			'min' => '',
			'max' => '',
			'allow_in_bindings' => 1,
			'placeholder' => 'sort order',
			'step' => 1,
			'prepend' => '',
			'append' => '',
		),
		array(
			'key' => 'field_68c6cc86b6ad9',
			'label' => 'active',
			'name' => 'active',
			'aria-label' => '',
			'type' => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => 'active in current season',
			'default_value' => 0,
			'allow_in_bindings' => 0,
			'ui' => 0,
			'ui_on_text' => '',
			'ui_off_text' => '',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'user_form',
				'operator' => '==',
				'value' => 'add',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'side',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_rest' => 0,
	'display_title' => '',
	'allow_ai_access' => false,
	'ai_description' => '',
) );
} );

