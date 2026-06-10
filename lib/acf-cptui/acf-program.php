add_action( 'acf/include_fields', function() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
	'key' => 'group_616e0be38905a',
	'title' => 'program',
	'fields' => array(
		array(
			'key' => 'field_616e0bf6aa1b4',
			'label' => 'description',
			'name' => 'description',
			'aria-label' => '',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'maxlength' => '',
			'rows' => '',
			'new_lines' => '',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'program',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => array(
		0 => 'discussion',
		1 => 'comments',
		2 => 'slug',
		3 => 'author',
		4 => 'send-trackbacks',
	),
	'active' => true,
	'description' => '',
	'show_in_rest' => false,
	'display_title' => '',
	'allow_ai_access' => false,
	'ai_description' => '',
) );
} );

