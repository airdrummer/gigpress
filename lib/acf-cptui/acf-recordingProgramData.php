add_action( 'acf/include_fields', function() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
	'key' => 'group_68976ed6b2a63',
	'title' => 'recordingProgramData',
	'fields' => array(
		array(
			'key' => 'field_68995bf55a051',
			'label' => 'streamingURL',
			'name' => 'streaming_url',
			'aria-label' => '',
			'type' => 'url',
			'instructions' => 'url of online recording',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'allow_in_bindings' => 1,
			'placeholder' => '',
		),
		array(
			'key' => 'field_68977155cf6dd',
			'label' => 'Program Description page id',
			'name' => 'program_description_page_id',
			'aria-label' => '',
			'type' => 'number',
			'instructions' => 'enter asssociated program id as listed in https://bostoncamerata.org/programs-repertoire',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => 0,
			'min' => '',
			'max' => '',
			'allow_in_bindings' => 1,
			'placeholder' => '',
			'step' => '',
			'prepend' => '',
			'append' => '',
		),
		array(
			'key' => 'field_68b733691e4c9',
			'label' => 'program description url',
			'name' => 'program_description_url',
			'aria-label' => '',
			'type' => 'url',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'allow_in_bindings' => 1,
			'placeholder' => '',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'recordings',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'acf_after_title',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_rest' => 1,
	'display_title' => '',
	'allow_ai_access' => false,
	'ai_description' => '',
) );
} );

