<?php

$addFieldGroupFunc = quizmaster_get_fields_prefix() . '_add_local_field_group';
$addFieldGroupFunc(array (
	'key' => 'group_58c865196149b',
	'title' => 'QuizMaster Migration',
	'fields' => array (
		array (
			'key' => 'field_58c866cfcc428',
			'label' => 'Migration Type',
			'name' => 'qmmg_migration_type',
			'type' => 'radio',
			'instructions' => 'Choose the type of migration. If the source of your export is WP Pro Quiz choose the WP Pro Quiz Export option. For migration from one QuizMaster site to another choose QuizMaster export.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'layout' => 'vertical',
			'choices' => array (
				'wpproquiz' => 'WP Pro Quiz Export',
			),
			'default_value' => 'wpproquiz',
			'other_choice' => 0,
			'save_other_choice' => 0,
			'allow_null' => 0,
			'return_format' => 'value',
		),
		array (
			'key' => 'field_58c8658ddda3f',
			'label' => 'Data Source',
			'name' => 'qmmg_migration_source',
			'type' => 'radio',
			'instructions' => 'Choose the source of your quiz migration data. Choose Upload File to be able to Upload File, or choose Paste XML to directly enter XML markup into a textarea.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'layout' => 'vertical',
			'choices' => array (
				'file' => 'Upload File',
				'code' => 'Paste XML',
			),
			'default_value' => '',
			'other_choice' => 0,
			'save_other_choice' => 0,
			'allow_null' => 0,
			'return_format' => 'value',
		),
		array (
			'key' => 'field_58c86661d71b7',
			'label' => 'File Upload',
			'name' => 'qmmg_file_upload',
			'type' => 'file',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_58c8658ddda3f',
						'operator' => '==',
						'value' => 'file',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'return_format' => 'array',
			'library' => 'all',
			'min_size' => '',
			'max_size' => '',
			'mime_types' => '',
		),
		array (
			'key' => 'field_58c86693b24ce',
			'label' => 'Code Entry',
			'name' => 'qmmg_code_entry',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_58c8658ddda3f',
						'operator' => '==',
						'value' => 'code',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'new_lines' => '',
			'maxlength' => '',
			'placeholder' => '',
			'rows' => 20,
		),
	),
	'location' => array (
		array (
			array (
				'param' => 'options_page',
				'operator' => '==',
				'value' => 'quizmaster-migrate',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => 1,
	'description' => '',
));
