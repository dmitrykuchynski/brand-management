<?php

class Brand_Management_Acf_Loader {
	public function bootstrap(): void {

		if ( ! class_exists( 'acf' ) ) {
			return;
		}

		$this->add_custom_fields_to_brand();
		$this->add_custom_fields_to_offer();
		$this->add_custom_fields_to_campaigns();
		$this->add_custom_fields_to_comparison_tables();
		$this->add_custom_fields_to_recommended_offers_widget();
		$this->add_custom_fields_to_popup_offers_widget();
		$this->add_custom_fields_to_pages();
		$this->add_custom_fields_to_sidebar_tables();
		$this->add_custom_fields_to_payment_methods();

		$this->redefine_acf_ajax_add_term();
		$this->filter_offers_list_in_regional_campaigns();

	}

	private function add_custom_fields_to_brand(): void {

		acf_add_local_field_group( [
			'key'      => 'group_brand_details',
			'title'    => 'Brand Details',
			'fields'   => [
				[
					'key'           => 'field_brand_logo',
					'label'         => 'Brand Logo',
					'name'          => 'brand_logo',
					'type'          => 'image',
					'return_format' => 'url',
					'preview_size'  => 'thumbnail',
				],
				[
					'key'   => 'field_gallery_label',
					'label' => 'Gallery Label',
					'name'  => 'gallery_label',
					'type'  => 'text',
				],
				[
					'key'        => 'field_image_gallery',
					'label'      => 'Image Gallery',
					'name'       => 'image_gallery',
					'type'       => 'repeater',
					'layout'     => 'row',
					'sub_fields' => [
						[
							'key'           => 'field_image',
							'label'         => 'Gallery',
							'name'          => 'image',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'thumbnail',
						],
					],
				],
				[
					'key'          => 'field_default_unique_visit_link',
					'label'        => 'Default Unique Visit Link',
					'name'         => 'unique_visit_link',
					'type'         => 'text',
					'placeholder'  => 'Start typing to see the existing visit links.',
					'instructions' => 'This field can be redefined at the offer level.',
					'wrapper'      => [
						'width' => '60',
					],
				],
				[
					'key'           => 'field_open_visit_links_in_new_tab',
					'label'         => 'Open Bookmaker Visit links In A New Tab',
					'name'          => 'open_visit_links_in_new_tab',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
					'ui_on_text'    => 'New Tab',
					'ui_off_text'   => 'Same Tab',
					'wrapper'       => [
						'width' => '40',
					],
				],
				[
					'key'   => 'field_regulated_by',
					'label' => 'Regulated By',
					'name'  => 'regulated_by',
					'type'  => 'text',
				],
				[
					'key'   => 'field_license_no',
					'label' => 'License №',
					'name'  => 'license_no',
					'type'  => 'text',
				],
				[
					'key'   => 'field_license_link',
					'label' => 'License Link',
					'name'  => 'license_link',
					'type'  => 'text',
				],
				[
					'key'        => 'field_key_features',
					'label'      => 'Key Features',
					'name'       => 'key_features',
					'type'       => 'repeater',
					'layout'     => 'table',
					'sub_fields' => [
						[
							'key'   => 'field_point',
							'label' => 'Point',
							'name'  => 'point',
							'type'  => 'text',
						],
					],
				],
				[
					'key'   => 'field_google_conversion_url',
					'label' => 'Google Conversion URL',
					'name'  => 'google_conversion_url',
					'type'  => 'text',
				],
				[
					'key'   => 'field_disclaimer_text',
					'label' => 'Disclaimer Text',
					'name'  => 'disclaimer_text',
					'type'  => 'text',
				],
				[
					'key'   => 'field_payout_duration',
					'label' => 'Payout Duration',
					'name'  => 'payout_duration',
					'type'  => 'text',
				],
				[
					'key'          => 'field_star_rating',
					'label'        => 'Star Rating (1-5)',
					'name'         => 'star_rating',
					'type'         => 'number',
					'min'          => '0',
					'max'          => '5',
					'step'         => '.5',
					'instructions' => 'How many stars should be displayed?',
				],
				[
					'key'          => 'field_star_rating_text',
					'label'        => 'Star Rating Text (1-10)',
					'name'         => 'star_rating_text',
					'type'         => 'number',
					'min'          => '0',
					'max'          => '10',
					'instructions' => 'Text which will be displayed next to stars.',
				],
				[
					'key'          => 'field_default_highlighted_label',
					'label'        => 'Default Highlighted Label',
					'name'         => 'highlighted_label',
					'type'         => 'text',
					'instructions' => 'This field can be redefined at the offer level.',
				],
				[
					'key'     => 'field_read_review_url',
					'label'   => 'Read Review URL',
					'name'    => 'read_review_url',
					'type'    => 'text',
					'wrapper' => [
						'width' => '50',
					],
				],
				[
					'key'     => 'field_read_review_button_label',
					'label'   => 'Read Review Button Label',
					'name'    => 'read_review_button_label',
					'type'    => 'text',
					'wrapper' => [
						'width' => '50',
					],
				],
				[
					'key'   => 'field_website',
					'label' => 'Brand Website',
					'name'  => 'website',
					'type'  => 'text',
				],
				[
					'key'   => 'field_owner',
					'label' => 'Brand Owner',
					'name'  => 'owner',
					'type'  => 'text',
				],
				[
					'key'   => 'field_founded',
					'label' => 'Founded',
					'name'  => 'founded',
					'type'  => 'text',
				],
				[
					'key'   => 'field_headquarters',
					'label' => 'Headquarters',
					'name'  => 'headquarters',
					'type'  => 'text',
				],
				[
					'key'   => 'field_call',
					'label' => 'Call',
					'name'  => 'call',
					'type'  => 'text',
				],
				[
					'key'   => 'field_helpdesk',
					'label' => 'Helpdesk',
					'name'  => 'helpdesk',
					'type'  => 'text',
				],
				[
					'key'     => 'field_profile_url',
					'label'   => 'Profile URL',
					'name'    => 'profile_url',
					'type'    => 'text',
					'wrapper' => [
						'width' => '50',
					],
				],
				[
					'key'     => 'field_profile_label',
					'label'   => 'Profile Label',
					'name'    => 'profile_label',
					'type'    => 'text',
					'wrapper' => [
						'width' => '50',
					],
				],
				[
					'key'   => 'field_bonus_text_as_title',
					'label' => 'Bonus Text As Title',
					'name'  => 'bonus_text_as_title',
					'type'  => 'text',
				],
				[
					'key'           => 'field_hide_terms_and_conditions',
					'label'         => 'Hide Terms & Conditions',
					'name'          => 'hide_terms_and_conditions',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 0,
					'instructions'  => 'Hide terms&conditions in a header row in a comparison table',
				],
				[
					'key'          => 'field_default_terms_and_conditions',
					'label'        => 'Default Terms & Conditions',
					'name'         => 'terms_and_conditions',
					'type'         => 'text',
					'instructions' => 'Disclaimer displayed at the bottom of the brand. This field can be redefined at the offer level.',
				],
				[
					'key'          => 'field_default_coupon_code',
					'label'        => 'Default Coupon Code',
					'name'         => 'coupon_code',
					'type'         => 'text',
					'instructions' => 'This field can be redefined at the offer level.',
				],
				[
					'key'          => 'field_brand_description',
					'label'        => 'Brand Description',
					'name'         => 'brand_description',
					'type'         => 'wysiwyg',
					'media_upload' => 0,
				],
				[
					'key'          => 'field_default_offer_description',
					'label'        => 'Default Offer Description',
					'name'         => 'offer_description',
					'type'         => 'wysiwyg',
					'media_upload' => 0,
					'instructions' => 'This field can be redefined at the offer level.',
				],
				[
					'key'          => 'field_default_offer_conditions',
					'label'        => 'Default Offer Conditions',
					'name'         => 'offer_conditions',
					'type'         => 'wysiwyg',
					'media_upload' => 0,
					'instructions' => 'This field can be redefined at the offer level.',
				],
				[
					'key'   => 'field_slider_tiles_info_text',
					'label' => 'Slider & Tales Info Text',
					'name'  => 'slider_tiles_info_text',
					'type'  => 'text',
				],
				[
					'key'        => 'field_mini_review_ratings',
					'label'      => 'Mini-Review Ratings',
					'name'       => 'mini_review_ratings',
					'type'       => 'repeater',
					'layout'     => 'table',
					'sub_fields' => [
						[
							'key'   => 'field_mini_review_rating_label',
							'label' => 'Rating Label',
							'name'  => 'mini_review_rating_label',
							'type'  => 'text',
						],
						[
							'key'   => 'field_mini_review_rating_score',
							'label' => 'Rating Score',
							'name'  => 'mini_review_rating_score',
							'type'  => 'text',
						],
					],
				],
				[
					'key'           => 'field_global_activity',
					'label'         => 'Global Activity',
					'name'          => 'global_activity',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'brand',
					]
				],
			],
		] );

	}

	private function add_custom_fields_to_offer(): void {

		acf_add_local_field_group( [
			'key'      => 'group_offer_information',
			'title'    => 'Offer Information',
			'fields'   => [
				[
					'key'           => 'field_brand_id',
					'label'         => 'Brand',
					'name'          => 'brand_id',
					'type'          => 'post_object',
					'post_type'     => 'brand',
					'return_format' => 'id',
					'required'      => 1,
				],
				[
					'key'   => 'field_redefined_brand_name',
					'label' => 'Redefined Brand Name',
					'name'  => 'redefined_brand_name',
					'type'  => 'text',
				],
				[
					'key'   => 'field_minimum_deposit',
					'label' => 'Minimum Deposit',
					'name'  => 'minimum_deposit',
					'type'  => 'text',
				],
				[
					'key'          => 'field_terms_and_conditions',
					'label'        => 'Terms & Conditions',
					'name'         => 'terms_and_conditions',
					'type'         => 'text',
					'instructions' => 'Disclaimer displayed at the bottom of the offer. If left empty, the value from the brand level will be used.',
				],
				[
					'key'          => 'field_unique_visit_link',
					'label'        => 'Unique Visit Link',
					'name'         => 'unique_visit_link',
					'type'         => 'text',
					'placeholder'  => 'Start typing to see the existing visit links.',
					'instructions' => 'If left empty, the value from the brand level will be used.',
				],
				[
					'key'          => 'field_coupon_code',
					'label'        => 'Coupon Code',
					'name'         => 'coupon_code',
					'type'         => 'text',
					'instructions' => 'If left empty, the value from the brand level will be used.',
				],
				[
					'key'   => 'field_bonus_taken_count',
					'label' => 'Bonus Taken Count',
					'name'  => 'bonus_taken_count',
					'type'  => 'text',
				],
				[
					'key'          => 'field_highlighted_label',
					'label'        => 'Highlighted Label',
					'name'         => 'highlighted_label',
					'type'         => 'text',
					'instructions' => 'If left empty, the value from the brand level will be used.',
				],
				[
					'key'          => 'field_offer_description',
					'label'        => 'Offer Description',
					'name'         => 'offer_description',
					'type'         => 'wysiwyg',
					'media_upload' => 0,
					'instructions' => 'If left empty, the value from the brand level will be used.',
				],
				[
					'key'          => 'field_offer_conditions',
					'label'        => 'Offer Conditions',
					'name'         => 'offer_conditions',
					'type'         => 'wysiwyg',
					'media_upload' => 0,
					'instructions' => 'If left empty, the value from the brand level will be used.',
				],
				[
					'key'        => 'field_regulation',
					'label'      => 'Regulation',
					'name'       => 'regulation',
					'type'       => 'repeater',
					'sub_fields' => [
						[
							'key'           => 'field_regulation_image',
							'label'         => 'Regulation Image',
							'name'          => 'regulation_image',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'thumbnail',
						],
					],
				],
				[
					'key'        => 'field_regulation',
					'label'      => 'Regulation',
					'name'       => 'regulation',
					'type'       => 'repeater',
					'sub_fields' => [
						[
							'key'           => 'field_regulation_image',
							'label'         => 'Regulation Image',
							'name'          => 'regulation_image',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'thumbnail',
						],
					],
				],
				[
					'key'        => 'field_overall_ratings',
					'label'      => 'Overall Ratings',
					'name'       => 'overall_ratings',
					'type'       => 'repeater',
					'layout'     => 'table',
					'sub_fields' => [
						[
							'key'   => 'field_overall_rating_label',
							'label' => 'Rating Label',
							'name'  => 'overall_rating_label',
							'type'  => 'text',
						],
						[
							'key'   => 'field_overall_rating_score',
							'label' => 'Rating Score',
							'name'  => 'overall_rating_score',
							'type'  => 'text',
						],
					],
				],
				[
					'key'           => 'field_adding_deposit_methods_flow',
					'label'         => 'Adding Deposit Methods Flow',
					'name'          => 'adding_deposit_methods_flow',
					'type'          => 'true_false',
					'ui'            => 1,
					'ui_on_text'    => 'New Flow',
					'ui_off_text'   => 'Old Flow',
					'default_value' => 0,
					'instructions'  => 'Old Flow: Add directly here label and image. New Flow: Select from created payment methods',
				],
				[
					'key'               => 'field_deposit_method',
					'label'             => 'Deposit Methods',
					'name'              => 'deposit_method',
					'type'              => 'repeater',
					'layout'            => 'table',
					'sub_fields'        => [
						[
							'key'   => 'field_deposit_method_label',
							'label' => 'Label',
							'name'  => 'deposit_method_label',
							'type'  => 'text',
						],
						[
							'key'           => 'field_deposit_method_image',
							'label'         => 'Image',
							'name'          => 'deposit_method_image',
							'type'          => 'image',
							'return_format' => 'url',
							'preview_size'  => 'thumbnail',
						],
						[
							'key'   => 'field_deposit_method_charge',
							'label' => 'Charge',
							'name'  => 'deposit_method_charge',
							'type'  => 'text',
						],
						[
							'key'   => 'field_deposit_method_min_deposit',
							'label' => 'Min. Deposit',
							'name'  => 'deposit_method_min_deposit',
							'type'  => 'text',
						],
					],
					'conditional_logic' => [
						[
							[
								'field'    => 'field_adding_deposit_methods_flow',
								'operator' => '==',
								'value'    => 0,
							],
						],
					],
				],
				[
					'key'               => 'field_deposit_methods_new_flow',
					'label'             => 'Deposit Methods',
					'name'              => 'deposit_methods_new_flow',
					'type'              => 'repeater',
					'layout'            => 'table',
					'sub_fields'        => [
						[
							'key'           => 'field_payment_method_id',
							'label'         => 'Payment Method',
							'name'          => 'payment_method_id',
							'type'          => 'post_object',
							'post_type'     => 'payment_method',
							'return_format' => 'id',
							'required'      => 1,
						],
						[
							'key'   => 'field_deposit_method_charge',
							'label' => 'Charge',
							'name'  => 'deposit_method_charge',
							'type'  => 'text',
						],
						[
							'key'   => 'field_deposit_method_min_deposit',
							'label' => 'Min. Deposit',
							'name'  => 'deposit_method_min_deposit',
							'type'  => 'text',
						],
					],
					'conditional_logic' => [
						[
							[
								'field'    => 'field_adding_deposit_methods_flow',
								'operator' => '==',
								'value'    => 1,
							],
						],
					],
				],
				[
					'key'           => 'field_adding_withdrawal_methods_flow',
					'label'         => 'Adding Withdrawal Methods Flow',
					'name'          => 'adding_withdrawal_methods_flow',
					'type'          => 'true_false',
					'ui'            => 1,
					'ui_on_text'    => 'New Flow',
					'ui_off_text'   => 'Old Flow',
					'default_value' => 0,
					'instructions'  => 'Old Flow: Add directly here label and image. New Flow: Select from created payment methods',
				],
				[
					'key'               => 'field_withdrawal_method',
					'label'             => 'Withdrawal Methods',
					'name'              => 'withdrawal_method',
					'type'              => 'repeater',
					'layout'            => 'table',
					'sub_fields'        => [
						[
							'key'   => 'field_withdrawal_method_label',
							'label' => 'Label',
							'name'  => 'withdrawal_method_label',
							'type'  => 'text',
						],
						[
							'key'           => 'field_withdrawal_method_image',
							'label'         => 'Image',
							'name'          => 'withdrawal_method_image',
							'type'          => 'image',
							'return_format' => 'url',
							'preview_size'  => 'thumbnail',
						],
						[
							'key'   => 'field_withdrawal_method_min_withdrawal',
							'label' => 'Min. Withdrawal',
							'name'  => 'withdrawal_method_min_withdrawal',
							'type'  => 'text',
						],
						[
							'key'   => 'field_withdrawal_method_time',
							'label' => 'Time',
							'name'  => 'withdrawal_method_time',
							'type'  => 'text',
						],
					],
					'conditional_logic' => [
						[
							[
								'field'    => 'field_adding_withdrawal_methods_flow',
								'operator' => '==',
								'value'    => 0,
							],
						],
					],
				],
				[
					'key'               => 'field_withdrawal_method_new_flow',
					'label'             => 'Withdrawal Methods',
					'name'              => 'withdrawal_method_new_flow',
					'type'              => 'repeater',
					'layout'            => 'table',
					'sub_fields'        => [
						[
							'key'           => 'field_payment_method_id',
							'label'         => 'Payment Method',
							'name'          => 'payment_method_id',
							'type'          => 'post_object',
							'post_type'     => 'payment_method',
							'return_format' => 'id',
							'required'      => 1,
						],
						[
							'key'   => 'field_withdrawal_method_min_withdrawal',
							'label' => 'Min. Withdrawal',
							'name'  => 'withdrawal_method_min_withdrawal',
							'type'  => 'text',
						],
						[
							'key'   => 'field_withdrawal_method_time',
							'label' => 'Time',
							'name'  => 'withdrawal_method_time',
							'type'  => 'text',
						],
					],
					'conditional_logic' => [
						[
							[
								'field'    => 'field_adding_withdrawal_methods_flow',
								'operator' => '==',
								'value'    => 1,
							],
						],
					],
				],
				[
					'key'   => 'field_bonus_amount',
					'label' => 'Bonus Amount',
					'name'  => 'bonus_amount',
					'type'  => 'text',
				],
				[
					'key'          => 'field_rows_on_card_back_side',
					'label'        => 'Rows On Card Back Side',
					'name'         => 'rows_on_card_back_side',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Add Row',
					'max'          => '8',
					'instructions' => 'Add rows for showing on card back side in slider and tiles',
					'sub_fields'   => [
						[
							'key'      => 'field_card_back_side_card_row_name',
							'label'    => 'Row Name',
							'name'     => 'name',
							'type'     => 'text',
							'required' => 1,
						],
						[
							'key'          => 'field_card_back_side_card_row_value',
							'label'        => 'Row Value',
							'name'         => 'value',
							'type'         => 'text',
							'required'     => 1,
							'instructions' => 'If you chose row type "Icons Yes/No", type here "yes" or "no"',
						],
						[
							'key'      => 'field_card_back_side_card_row_type',
							'label'    => 'Row Type',
							'name'     => 'type',
							'type'     => 'select',
							'choices'  => [
								'text'         => 'Text',
								'icons_yes_no' => 'Icons Yes / No',
							],
							'required' => 1,
						],
					],

				],
				[
					'key'           => 'field_global_activity',
					'label'         => 'Global Activity',
					'name'          => 'global_activity',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 'true',
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'offer',
					],
				],
			],
		] );

		acf_add_local_field_group( [
			'key'      => 'group_offer_statistics',
			'title'    => 'Offer Statistics',
			'fields'   => [
				[
					'key'           => 'field_offer_likes',
					'label'         => 'Offer Likes',
					'name'          => 'offer_likes',
					'type'          => 'number',
					'min'           => 0,
					'step'          => 1,
					'wrapper'       => [
						'width' => '50',
					],
					'default_value' => 0,
				],
				[
					'key'           => 'field_offer_dislikes',
					'label'         => 'Offer Dislikes',
					'name'          => 'offer_dislikes',
					'type'          => 'number',
					'min'           => 0,
					'step'          => 1,
					'wrapper'       => [
						'width' => '50',
					],
					'default_value' => 0,
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'offer',
					],
				],
			],
		] );

		acf_add_local_field_group( [
			'key'      => 'group_offer_geo_filter',
			'title'    => 'GEO Filter',
			'fields'   => [
				[
					'key'           => 'field_show_in_countries',
					'label'         => 'Show In Countries',
					'name'          => 'show_in_countries',
					'type'          => 'checkbox',
					'allow_null'    => 0,
					'return_format' => 'value',
					'toggle'        => 1,
					'instructions'  => 'The list of countries is customized at the <a href="/wp-admin/edit.php?post_type=brand&page=acf-options-brand-management-options">Brand Management Options</a> level.<br><br> In which countries should the offer be displayed?',
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'offer',
					],
				],
			],
			'position' => 'side',
		] );

	}

	private function add_custom_fields_to_campaigns(): void {

		$global_campaign_fields = [
			[
				'key'           => 'field_offers_list',
				'label'         => 'Offers List',
				'name'          => 'offers_list',
				'type'          => 'relationship',
				'post_type'     => 'offer',
				'filters'       => [ 'search' ],
				'return_format' => 'id',
				'required'      => 1,
				'instructions'  => 'Select offers to display in the campaign.',
			],
			[
				'key'           => 'field_show_filter_tags',
				'label'         => 'Show Filter Tags',
				'name'          => 'show_filter_tags',
				'type'          => 'true_false',
				'ui'            => 1,
				'default_value' => 1,
			],
			[
				'key'               => 'field_campaign_all_filter_tag_order',
				'label'             => 'Filter tag order in auto mode',
				'name'              => 'campaign_all_filter_tag_order',
				'type'              => 'relationship',
				'return_format'     => 'id',
				'instructions'      => 'The order of tags that are reflected in the campaign by default. If you need to limit or customize the tags, please use the filter tag section above.',
				'conditional_logic' => [
					[
						[
							'field'    => 'field_campaign_filter_tags',
							'operator' => '==empty',
						],
					],
				],
			],
			[
				'key'           => 'field_campaign_filter_tags',
				'label'         => 'Filter Tags',
				'name'          => 'campaign_filter_tags',
				'type'          => 'taxonomy',
				'taxonomy'      => 'bm_filter_tags',
				'instructions'  => 'Use this section to limit (customize) the tags for your campaign to be reflected.',
				'field_type'    => 'multi_select',
				'return_format' => 'id',
				'add_term'      => 1,
			],
			[
				'key'               => 'field_offers_order_within_a_tag',
				'label'             => 'Filter Tags: Offers Order',
				'name'              => 'offers_order_within_a_tag',
				'type'              => 'repeater',
				'layout'            => 'row',
				'button_label'      => 'Set the order',
				'instructions'      => 'Change offers order within a particular filter tag or keep the default (no changes are needed)',
				'conditional_logic' => [
					[
						[
							'field'    => 'field_campaign_filter_tags',
							'operator' => '!=empty',
						],
					],
				],
				'sub_fields'        => [
					[
						'key'          => 'field_ordering_tag',
						'label'        => 'Select Tag',
						'name'         => 'ordering_tag',
						'type'         => 'select',
						'instructions' => 'Select a tag to change the order',
						'required'     => 1,
					],
					[
						'key'           => 'field_ordering_offers',
						'label'         => 'Offers List',
						'name'          => 'ordering_offers',
						'type'          => 'relationship',
						'post_type'     => 'offer',
						'return_format' => 'id',
						'instructions'  => 'Change the order for the offers from the above list and click update',
					],
				],
			],
			[
				'key'          => 'field_rewriting_offer_fields',
				'label'        => 'Rewriting Offer Fields',
				'name'         => 'rewriting_offer_fields',
				'type'         => 'repeater',
				'layout'       => 'row',
				'button_label' => 'Select Offer',
				'instructions' => 'Select the offers you want to change their details.',
				'sub_fields'   => [
					[
						'key'           => 'field_rewrite_offer_id',
						'label'         => 'Select Offer',
						'name'          => 'rewrite_offer_id',
						'type'          => 'post_object',
						'post_type'     => 'offer',
						'return_format' => 'id',
						'required'      => 1,
					],
					[
						'key'        => 'field_key_features',
						'label'      => 'Key Features',
						'name'       => 'key_features',
						'type'       => 'repeater',
						'layout'     => 'table',
						'sub_fields' => [
							[
								'key'   => 'field_point',
								'label' => 'Point',
								'name'  => 'point',
								'type'  => 'text',
							],
						],
					],
					[
						'key'   => 'field_star_rating',
						'label' => 'Star Rating (1-5)',
						'name'  => 'star_rating',
						'type'  => 'number',
						'min'   => '0',
						'max'   => '5',
						'step'  => '.5',
					],
					[
						'key'   => 'field_star_rating_text',
						'label' => 'Star Rating Text (1-10)',
						'name'  => 'star_rating_text',
						'type'  => 'number',
						'min'   => '0',
						'max'   => '10',
					],

					[
						'key'   => 'field_highlighted_label',
						'label' => 'Highlighted Label',
						'name'  => 'highlighted_label',
						'type'  => 'text',
					],
					[
						'key'   => 'field_unique_visit_link',
						'label' => 'Visit Link',
						'name'  => 'unique_visit_link',
						'type'  => 'text',
					],
					[
						'key'           => 'field_tag',
						'label'         => 'Tag',
						'name'          => 'tag',
						'type'          => 'taxonomy',
						'taxonomy'      => 'bm_filter_tags',
						'field_type'    => 'multi_select',
						'return_format' => 'id',
						'add_term'      => 1,
					],
				],
			],
		];

		$main_campaign_fields = [
			[
				'key'           => 'field_ip_geo_filters',
				'label'         => 'IP Geo Filters',
				'name'          => 'ip_geo_filters',
				'type'          => 'true_false',
				'ui'            => 1,
				'default_value' => 0,
				'ui_on_text'    => 'Enabled',
				'ui_off_text'   => 'Disabled',
				'instructions'  => 'Enables the functionality of displaying offers depending on the user\'s geolocation (by ip address) for this campaign. It also allows you to link the main campaign with the regional one.',
			],
			[
				'key'               => 'field_linked_regional_campaigns',
				'label'             => 'Linked Regional Campaigns',
				'name'              => 'linked_regional_campaigns',
				'type'              => 'repeater',
				'layout'            => 'table',
				'button_label'      => 'Link Regional Campaign',
				'conditional_logic' => [
					[
						[
							'field'    => 'field_ip_geo_filters',
							'operator' => '==',
							'value'    => 1,
						],
					],
				],
				'sub_fields'        => [
					[
						'key'      => 'field_regional_campaign_country',
						'label'    => 'Country',
						'name'     => 'regional_campaign_country',
						'type'     => 'select',
						'choices'  => [
							'' => 'Select',
						],
						'ui'       => 1,
						'required' => 1,
					],
					[
						'key'        => 'field_regional_campaign_id',
						'label'      => 'Regional Campaign',
						'name'       => 'regional_campaign_id',
						'type'       => 'taxonomy',
						'taxonomy'   => 'bm_regional_campaigns',
						'field_type' => 'select',
						'required'   => 1,
					],
				],
				'instructions'      => 'Allows you to link a regional campaign for a specific region. If the user enters from the selected region, he will receive a list of offers according to the settings of the regional campaign.',
			],
			[
				'key'           => 'field_show_table_title',
				'label'         => 'Show Table Title',
				'name'          => 'show_table_title',
				'type'          => 'true_false',
				'ui'            => 1,
				'default_value' => 1,
			],
			[
				'key'           => 'field_show_offers_counter',
				'label'         => 'Show Offers Counter',
				'name'          => 'show_offers_counter',
				'type'          => 'true_false',
				'ui'            => 1,
				'default_value' => 1,
			],
			[
				'key'           => 'field_show_coupon_codes',
				'label'         => 'Show Coupon Codes',
				'name'          => 'show_coupon_codes',
				'type'          => 'true_false',
				'ui'            => 1,
				'default_value' => 1,
				'instructions'  => 'Determines the display of the coupon code for all offers in the campaign.',
			],
			[
				'key'   => 'field_cta_button_label',
				'label' => 'CTA Button Label',
				'name'  => 'cta_button_label',
				'type'  => 'text',
			],
			[
				'key'           => 'field_campaign_show_disclaimer',
				'label'         => 'Show Disclaimer',
				'name'          => 'campaign_show_disclaimer',
				'type'          => 'true_false',
				'ui'            => 1,
				'default_value' => 0,
				'instructions'  => 'If you set the disclaimer at the campaign level (here), the default page disclaimer will be hidden.',
			],
			[
				'key'               => 'field_campaign_disclaimer_button_label',
				'label'             => 'Disclaimer Button Label',
				'name'              => 'campaign_disclaimer_button_label',
				'type'              => 'text',
				'required'          => 1,
				'conditional_logic' => [
					[
						[
							'field'    => 'field_campaign_show_disclaimer',
							'operator' => '==',
							'value'    => 1,
						],
					],
				],
			],
			[
				'key'               => 'field_campaign_disclaimer_text',
				'label'             => 'Disclaimer Text',
				'name'              => 'campaign_disclaimer_text',
				'type'              => 'textarea',
				'rows'              => 4,
				'required'          => 1,
				'conditional_logic' => [
					[
						[
							'field'    => 'field_campaign_show_disclaimer',
							'operator' => '==',
							'value'    => 1,
						],
					],
				],
			],
		];

		$extra_main_campaign_fields = [
			[
				'key'   => 'field_active_mobile_slider',
				'label' => 'Active Mobile Slider',
				'name'  => 'active_mobile_slider',
				'type'  => 'true_false',
				'ui'    => 1,
			],
			[
				'key'        => 'field_sidebar_section_appearance_settings',
				'label'      => 'Sidebar section appearance settings',
				'name'       => 'sidebar_section_appearance_settings',
				'type'       => 'group',
				'layout'     => 'block',
				'sub_fields' => [
					[
						'key'           => 'field_sidebar_section_appearance_choose_appearance',
						'label'         => 'Choose appearance',
						'name'          => 'sidebar_section_appearance_choose_appearance',
						'type'          => 'select',
						'choices'       => [
							'view_1' => 'CTA text + Review Link',
							'view_2' => 'CTA text + Review Link + CTA Button',
							'view_3' => 'Review Link + CTA Button',
						],
						'default_value' => 'default',
						'allow_null'    => 0,
						'multiple'      => 0,
						'ui'            => 1,
						'return_format' => 'value',
					],
					[
						'key'               => 'field_sidebar_section_appearance_cta_on_off',
						'label'             => 'CTA: on/off',
						'name'              => 'sidebar_section_appearance_cta_on_off',
						'type'              => 'true_false',
						'conditional_logic' => [
							[
								[
									'field'    => 'field_sidebar_section_appearance_choose_appearance',
									'operator' => '==',
									'value'    => 'view_2',
								],
							],
							[
								[
									'field'    => 'field_sidebar_section_appearance_choose_appearance',
									'operator' => '==',
									'value'    => 'view_3',
								],
							],
						],
						'default_value'     => 1,
						'ui'                => 1,
						'ui_on_text'        => 'On',
						'ui_off_text'       => 'Off',
					],
					[
						'key'               => 'field_sidebar_section_appearance_cta_text_or_button',
						'label'             => 'CTA: text or button',
						'name'              => 'sidebar_section_appearance_cta_text_or_button',
						'type'              => 'radio',
						'conditional_logic' => [
							[
								[
									'field'    => 'field_sidebar_section_appearance_choose_appearance',
									'operator' => '==',
									'value'    => 'view_2',
								],
							],
							[
								[
									'field'    => 'field_sidebar_section_appearance_choose_appearance',
									'operator' => '==',
									'value'    => 'view_3',
								],
							],
						],
						'choices'           => [
							'button' => 'Button',
							'text'   => 'Text',
						],
						'default_value'     => 'button',
						'layout'            => 'horizontal',
						'return_format'     => 'value',
					],
					[
						'key'               => 'field_sidebar_section_appearance_cta_button_text',
						'label'             => 'CTA: Text',
						'name'              => 'sidebar_section_appearance_cta_button_text',
						'type'              => 'text',
						'conditional_logic' => [
							[
								[
									'field'    => 'field_sidebar_section_appearance_choose_appearance',
									'operator' => '==',
									'value'    => 'view_2',
								],
							],
							[
								[
									'field'    => 'field_sidebar_section_appearance_choose_appearance',
									'operator' => '==',
									'value'    => 'view_3',
								],
							],
						],
					],
					[
						'key'               => 'field_sidebar_section_appearance_ordinal_numbers_on_off',
						'label'             => 'Ordinal numbers: on/off',
						'name'              => 'sidebar_section_appearance_ordinal_numbers_on_off',
						'type'              => 'true_false',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => [
							[
								[
									'field'    => 'field_sidebar_section_appearance_choose_appearance',
									'operator' => '==',
									'value'    => 'view_1',
								],
							],
						],
						'message'           => '',
						'default_value'     => 0,
						'ui'                => 1,
						'ui_on_text'        => 'On',
						'ui_off_text'       => 'Off',
					],
					[
						'key'               => 'field_sidebar_section_appearance_date_stamp_on_off',
						'label'             => 'Date stamp: on/off',
						'name'              => 'sidebar_section_appearance_date_stamp_on_off',
						'type'              => 'true_false',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => [
							[
								[
									'field'    => 'field_sidebar_section_appearance_choose_appearance',
									'operator' => '==',
									'value'    => 'view_1',
								],
							],
							[
								[
									'field'    => 'field_sidebar_section_appearance_choose_appearance',
									'operator' => '==',
									'value'    => 'view_3',
								],
							],
						],
						'default_value'     => 1,
						'ui'                => 1,
						'ui_on_text'        => 'On',
						'ui_off_text'       => 'Off',
					],
					[
						'key'           => 'field_sidebar_section_appearance_review_redirect_button_on_off',
						'label'         => 'Review redirect button: on/off',
						'name'          => 'sidebar_section_appearance_review_redirect_button_on_off',
						'type'          => 'true_false',
						'default_value' => 1,
						'ui'            => 1,
						'ui_on_text'    => 'On',
						'ui_off_text'   => 'Off',
					],
					[
						'key'               => 'field_sidebar_section_appearance_offer_text_on_off',
						'label'             => 'Offer text: on/off',
						'name'              => 'sidebar_section_appearance_offer_text_on_off',
						'type'              => 'true_false',
						'instructions'      => '',
						'required'          => 0,
						'conditional_logic' => [
							[
								[
									'field'    => 'field_sidebar_section_appearance_choose_appearance',
									'operator' => '==',
									'value'    => 'view_1',
								],
							],
							[
								[
									'field'    => 'field_sidebar_section_appearance_choose_appearance',
									'operator' => '==',
									'value'    => 'view_2',
								],
							],
							[
								[
									'field'    => 'field_sidebar_section_appearance_choose_appearance',
									'operator' => '==',
									'value'    => 'default',
								],
							],
						],
						'default_value'     => 1,
						'ui'                => 1,
						'ui_on_text'        => 'On',
						'ui_off_text'       => 'Off',
					],
				],
			],
		];

		$regional_campaign_fields = [
			[
				'key'          => 'field_campaign_region',
				'label'        => 'Campaign Region',
				'name'         => 'campaign_region',
				'type'         => 'select',
				'choices'      => [
					'' => 'Select',
				],
				'ui'           => 1,
				'required'     => 0,
				'allow_null'   => 1,
				'instructions' => 'When selecting a region, the search results in the Offers List below will be filtered based on the selected region.'
			],
		];

		acf_add_local_field_group( [
			'key'      => 'group_campaign_management',
			'title'    => 'Campaign Management',
			'fields'   => array_merge( $main_campaign_fields, $global_campaign_fields, $extra_main_campaign_fields ),
			'location' => [
				[
					[
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'bm_campaign_management',
					],
				],
			],
		] );

		acf_add_local_field_group( [
			'key'      => 'group_regional_campaign_fields',
			'title'    => 'Regional Campaign Fields',
			'fields'   => array_merge( $regional_campaign_fields, $global_campaign_fields ),
			'location' => [
				[
					[
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'bm_regional_campaigns',
					],
				],
			],
		] );

	}

	private function add_custom_fields_to_comparison_tables(): void {

		$comparison_table = [
			'account_details' => [
				'Min Deposit'    => 'text',
				'Regulated'      => 'icons_yes_no',
				'KYC Required'   => 'icons_yes_no',
				'Self Exclusion' => 'icons_yes_no',
			],
			'bonuses_offered' => [
				'Deposit Bonus'           => 'text',
				'Free Bet'                => 'icons_yes_no',
				'Wagering Requirements'   => 'text',
				'Max Free Bet Withdrawal' => 'text',
				'Accas'                   => 'icons_yes_no',
				'Acca Insurance'          => 'icons_yes_no',
				'Cashback'                => 'icons_yes_no',
				'VIP Loyalty Club'        => 'icons_yes_no',
				'No Deposit Bonus'        => 'text',
				'Enhanced Odds'           => 'icons_yes_no',
				'Free Spins'              => 'icons_yes_no',
			],
			'overall_ratings' => [
				'Betting Markets'    => 'line_rating',
				'Mobile App'         => 'line_rating',
				'Odds'               => 'line_rating',
				'Support'            => 'line_rating',
				'Payment Methods'    => 'line_rating',
				'Bonuses/Promotions' => 'line_rating',
				'Withdrawal Speed'   => 'line_rating',
				'Safety'             => 'line_rating',
			],
			'casino_features' => [
				'Live Betting'    => 'icons_yes_no',
				'Live Streaming'  => 'icons_yes_no',
				'Early Cashout'   => 'icons_yes_no',
				'Bet Builder'     => 'icons_yes_no',
				'eSports'         => 'icons_yes_no',
				'Political Bets'  => 'icons_yes_no',
				'Reality TV Bets' => 'icons_yes_no',
				'US Sports'       => 'icons_yes_no',
				'Poker'           => 'icons_yes_no',
				'Casino'          => 'icons_yes_no',
				'VIP Program'     => 'icons_yes_no',
				'Self Exclusion'  => 'icons_yes_no',
			],
			'deposit_methods' => [
				'Payout Times'        => 'text',
				'VISA'                => 'icons_yes_no',
				'Bank Wire'           => 'icons_yes_no',
				'Paypal'              => 'icons_yes_no',
				'Skrill'              => 'icons_yes_no',
				'Bitcoin'             => 'icons_yes_no',
				'Boku'                => 'icons_yes_no',
				'PaysafeCard'         => 'icons_yes_no',
				'Apple Pay'           => 'icons_yes_no',
				'Klarna'              => 'icons_yes_no',
				'Giropay'             => 'icons_yes_no',
				'Trustly'             => 'icons_yes_no',
				'Play+ Card'          => 'icons_yes_no',
				'Cash at Casino Cage' => 'icons_yes_no',
				'Ethereum'            => 'icons_yes_no',
				'Venmo'               => 'icons_yes_no',
				'MasterCard'          => 'icons_yes_no',
				'eCheck'              => 'icons_yes_no',
				'EntroPay'            => 'icons_yes_no',
			],
		];

		$comparison_fields = [];

		foreach ( $comparison_table as $comparison_table_row => $comparison_table_row_data ) {
			$comparison_fields[ $comparison_table_row ] = [];

			foreach ( $comparison_table_row_data as $field => $type ) {
				$label = $field;
				$field = $comparison_table_row . '_' . preg_replace( '/[^A-Za-z0-9\-]/', '_', strtolower( $field ) );

				if ( $type === 'text' ) {
					$comparison_fields[ $comparison_table_row ][] = [
						'key'   => 'field_parameter_' . $field,
						'label' => $label,
						'name'  => 'parameter_' . $field,
						'type'  => 'text',
					];
				} elseif ( $type === 'icons_yes_no' ) {
					$comparison_fields[ $comparison_table_row ][] = [
						'key'   => 'field_parameter_' . $field,
						'label' => $label,
						'name'  => 'parameter_' . $field,
						'type'  => 'true_false',
						'ui'    => 1,
					];
				} elseif ( $type === 'line_rating' ) {
					$comparison_fields[ $comparison_table_row ][] = [
						'key'          => 'field_parameter_' . $field,
						'label'        => $label,
						'name'         => 'parameter_' . $field,
						'type'         => 'number',
						'min'          => 1,
						'max'          => 10,
						'step'         => 1,
						'instructions' => 'Enter a number from 1 to 10.'
					];
				}
			}
		}

		$comparison_groups = [
			'account_details' => 'Account Details',
			'bonuses_offered' => 'Bonuses Offered',
			'overall_ratings' => 'Overall Ratings',
			'casino_features' => 'Casino Features',
			'deposit_methods' => 'Deposit Methods',
		];

		$comparison_parameter_source = [];

		foreach ( $comparison_groups as $comparison_group_key => $comparison_group_title ) {
			$local_field_group_key = 'group_brand_parameters_for_comparison_' . $comparison_group_key;

			acf_add_local_field_group( [
				'key'      => $local_field_group_key,
				'title'    => 'Parameters For Comparison - ' . $comparison_group_title,
				'fields'   => $comparison_fields[ $comparison_group_key ],
				'location' => [
					[
						[
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'brand',
						],
					],
				],
			] );

			$fields = acf_get_fields( $local_field_group_key );
			foreach ( $fields as $field ) {
				$comparison_parameter_source[ $field['key'] ] = $comparison_group_title . ' - ' . $field['label'];
			}
		}

		acf_add_local_field_group( [
			'key'      => 'group_comparison_tables',
			'title'    => 'Comparison Tables',
			'location' => [
				[
					[
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'bm_comparison_tables',
					],
				],
			],
		] );

		acf_add_local_field( [
			'key'           => 'field_comparison_brands',
			'label'         => 'Comparison Brands',
			'name'          => 'comparison_brands',
			'type'          => 'relationship',
			'post_type'     => 'brand',
			'filters'       => [ 'search' ],
			'return_format' => 'id',
			'required'      => 1,
			'parent'        => 'group_comparison_tables',
			'instructions'  => 'Select brands to compare.',
		] );

		acf_add_local_field( [
			'key'           => 'field_show_rating',
			'label'         => 'Show Rating',
			'name'          => 'show_rating',
			'type'          => 'true_false',
			'ui'            => 1,
			'default_value' => 1,
			'parent'        => 'group_comparison_tables',
		] );

		acf_add_local_field( [
			'key'           => 'field_visit_now_btn_text',
			'label'         => 'Visit Now Button Text',
			'name'          => 'visit_now_btn_text',
			'type'          => 'text',
			'default_value' => 'Visit Now',
			'parent'        => 'group_comparison_tables',
		] );

		acf_add_local_field( [
			'key'          => 'field_comparison_rows',
			'label'        => 'Comparison Rows',
			'name'         => 'comparison_rows',
			'type'         => 'repeater',
			'parent'       => 'group_comparison_tables',
			'required'     => 1,
			'layout'       => 'row',
			'button_label' => 'Add Row',
			'instructions' => 'Add rows for comparison.',
			'sub_fields'   => [
				[
					'key'      => 'field_comparison_row_name',
					'label'    => 'Comparison Row Name',
					'name'     => 'comparison_row_name',
					'type'     => 'text',
					'required' => 1,
				],
				[
					'key'           => 'field_comparison_row_closed',
					'label'         => 'Close Row on Desktop By Default',
					'name'          => 'comparison_row_closed',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 0,
				],
				[
					'key'           => 'field_comparison_row_mobile_closed',
					'label'         => 'Close Row on Mobile By Default',
					'name'          => 'comparison_row_mobile_closed',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
				],
				[
					'key'          => 'field_comparison_parameter',
					'label'        => 'Comparison Parameter',
					'name'         => 'comparison_parameter',
					'type'         => 'repeater',
					'required'     => 0,
					'button_label' => 'Add Parameter',
					'instructions' => 'Add a parameter to compare.',
					'layout'       => 'table',
					'sub_fields'   => [
						[
							'key'          => 'field_comparison_parameter_name',
							'label'        => 'Parameter Name',
							'name'         => 'comparison_parameter_name',
							'type'         => 'text',
							'instructions' => 'If left blank, the title will be taken from the source.'
						],
						[
							'key'         => 'field_comparison_parameter_source',
							'label'       => 'Parameter Source',
							'name'        => 'comparison_parameter_source',
							'type'        => 'select',
							'choices'     => $comparison_parameter_source,
							'ui'          => 1,
							'placeholder' => 'Select parameter source.',
							'required'    => 1,
						],
						[
							'key'      => 'field_comparison_parameter_type',
							'label'    => 'Parameter Type',
							'name'     => 'comparison_parameter_type',
							'type'     => 'select',
							'choices'  => [
								'text'         => 'Text',
								'icons_yes_no' => 'Icons Yes / No',
								'line_rating'  => 'Line Rating',
							],
							'required' => 1,
						],
					],
				],
			],
		] );

	}

	private function add_custom_fields_to_recommended_offers_widget(): void {

		acf_add_local_field_group( [
			'key'      => 'group_recommended_offers',
			'title'    => 'Recommended Offers',
			'fields'   => [
				[
					'key'           => 'field_show_title',
					'label'         => 'Show Title',
					'name'          => 'show_title',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
				],
				[
					'key'           => 'field_show_description',
					'label'         => 'Show Description',
					'name'          => 'show_description',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 0,
				],
				[
					'key'           => 'field_title_color',
					'label'         => 'Title Color',
					'name'          => 'title_color',
					'type'          => 'color_picker',
					'ui'            => 1,
					'default_value' => '#FFFFFF',
				],
				[
					'key'           => 'field_description_color',
					'label'         => 'Description Color',
					'name'          => 'description_color',
					'type'          => 'color_picker',
					'ui'            => 1,
					'default_value' => '#FFFFFF',
				],
				[
					'key'   => 'field_warning_message',
					'label' => 'Warning Message',
					'name'  => 'warning_message',
					'type'  => 'text',
				],
				[
					'key'           => 'field_show_warning_message',
					'label'         => 'Show Warning Message',
					'name'          => 'show_warning_message',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
				],
				[
					'key'           => 'field_recommended_offers_list',
					'label'         => 'Recommended Offers',
					'name'          => 'recommended_offers_list',
					'type'          => 'relationship',
					'post_type'     => 'offer',
					'filters'       => [ 'search' ],
					'return_format' => 'id',
					'required'      => 1,
					'min'           => '3',
					'max'           => '3',
					'instructions'  => 'Select 3 offers to display in the widget.',
				],
				[
					'key'           => 'field_recommended_offers_show_review_links',
					'label'         => 'Show Review Links For Offers',
					'name'          => 'recommended_offers_show_review_links',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
				],
			],
			'location' => [
				[
					[
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'bm_recommended_offers',
					],
				],
			],
		] );

	}

	private function add_custom_fields_to_popup_offers_widget(): void {

		acf_add_local_field_group( [
			'key'      => 'group_popup_offers',
			'title'    => 'Popup Offers',
			'fields'   => [
				[
					'key'           => 'field_popup_offers_show_title',
					'label'         => 'Show Title',
					'name'          => 'field_popup_offers_show_title',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
				],
				[
					'key'   => 'field_popup_offers_subtitle',
					'label' => 'Subtitle',
					'name'  => 'field_popup_offers_subtitle',
					'type'  => 'text',
				],
				[
					'key'           => 'field_popup_offers_show_subtitle',
					'label'         => 'Show Subtitle',
					'name'          => 'field_popup_offers_show_subtitle',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
				],
				[
					'key'           => 'field_popup_offers_show_arrow_icon',
					'label'         => 'Show arrow icon',
					'name'          => 'field_popup_offers_show_arrow_icon',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
				],
				[
					'key'          => 'field_popup_offers_list',
					'label'        => 'Рopup offers',
					'name'         => 'field_popup_offers_list',
					'type'         => 'repeater',
					'min'          => 1,
					'max'          => 3,
					'layout'       => 'table',
					'button_label' => '',
					'sub_fields'   => [
						[
							'key'           => 'field_offer',
							'label'         => 'Offer',
							'name'          => 'offer',
							'type'          => 'post_object',
							'post_type'     => array(
								0 => 'offer',
							),
							'taxonomy'      => '',
							'allow_null'    => 0,
							'multiple'      => 0,
							'return_format' => 'id',
							'ui'            => 1,
						],
						[
							'key'   => 'field_highlight_text',
							'label' => 'Highlight text',
							'name'  => 'highlight_text',
							'type'  => 'text',

						],
					],

				]
			],
			'location' => [
				[
					[
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'bm_popup_offers',
					],
				],
			],
		] );

		acf_add_local_field_group( [
			'key'    => 'group_popup_offers_options',
			'title'  => 'Popup Offers Options',
			'fields' => [
				[
					'key'   => 'field_popup_offers_options_csv_array',
					'label' => 'Popup Offers Options Csv Array',
					'name'  => 'popup_offers_options_csv_array',
					'type'  => 'text',
				],
			],
		] );

	}

	private function add_custom_fields_to_pages(): void {

		acf_add_local_field_group( [
			'key'      => 'group_popup_offers_non_affiliated',
			'title'    => 'Popup offers',
			'fields'   => [
				[
					'key'           => 'field_id_of_popup_for_non_affiliated',
					'label'         => 'Popup for non-affiliated',
					'name'          => 'id_of_popup_for_non-affiliated',
					'type'          => 'taxonomy',
					'taxonomy'      => 'bm_popup_offers',
					'field_type'    => 'select',
					'return_format' => 'id',
					'add_term'      => 0,
					'allow_null'    => 1,
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'post',
					],
				],
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'page',
					],
				],
			],
		] );

	}

	private function add_custom_fields_to_sidebar_tables(): void {

		$sidebar_table = [
			'sidebar_features' => [
				'Live Betting'    => 'icons_yes_no',
				'Live Streaming'  => 'icons_yes_no',
				'Early Cashout'   => 'icons_yes_no',
				'Bet Builder'     => 'icons_yes_no',
				'Casino'          => 'icons_yes_no',
				'Poker'           => 'icons_yes_no',
				'eSports'         => 'icons_yes_no',
				'US Sports'       => 'icons_yes_no',
				'Political Bets'  => 'icons_yes_no',
				'Reality TV Bets' => 'icons_yes_no',
				'Self Exclusion'  => 'icons_yes_no',
				'VIP Program'     => 'icons_yes_no',
			],
		];

		$sidebar_fields = [];

		foreach ( $sidebar_table as $sidebar_table_row => $sidebar_table_row_data ) {
			$sidebar_fields[ $sidebar_table_row ] = [];

			foreach ( $sidebar_table_row_data as $field => $type ) {
				$label = $field;
				$field = $sidebar_table_row . '_' . preg_replace( '/[^A-Za-z0-9\-]/', '_', strtolower( $field ) );

				if ( $type === 'icons_yes_no' ) {
					$sidebar_fields[ $sidebar_table_row ][] = [
						'key'   => 'field_' . $field,
						'label' => $label,
						'name'  => $field,
						'type'  => 'true_false',
						'ui'    => 1,
					];
				}
			}
		}

		$sidebar_groups = [
			'sidebar_features' => 'Features',
		];

		foreach ( $sidebar_groups as $sidebar_group_key => $sidebar_group_title ) {
			$local_field_group_key = 'group_brand_' . $sidebar_group_key;

			acf_add_local_field_group( [
				'key'      => $local_field_group_key,
				'title'    => 'Sidebar - ' . $sidebar_group_title,
				'fields'   => $sidebar_fields[ $sidebar_group_key ],
				'location' => [
					[
						[
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'brand',
						],
					],
				],
			] );
		}

	}

	/**
	 * Redefined ajax_add_term method with features for field_regional_campaign_id field.
	 *
	 * Original:
	 * wp-content/plugins/advanced-custom-fields-pro/includes/fields/class-acf-field-taxonomy.php
	 *
	 * @return void
	 */
	public function ajax_add_term(): void {

		// verify nonce
		if ( ! acf_verify_ajax() ) {
			die();
		}

		// vars
		$args = wp_parse_args(
			$_POST,
			array(
				'nonce'       => '',
				'field_key'   => '',
				'term_name'   => '',
				'term_parent' => '',
			)
		);

		// load field
		$field = acf_get_field( $args['field_key'] );
		if ( ! $field ) {
			die();
		}

		// vars
		$taxonomy_obj   = get_taxonomy( $field['taxonomy'] );
		$taxonomy_label = $taxonomy_obj->labels->singular_name;

		// validate cap
		// note: this situation should never occur due to condition of the add new button
		if ( ! current_user_can( $taxonomy_obj->cap->manage_terms ) ) {
			wp_send_json_error(
				array(
					'error' => sprintf( __( 'User unable to add new %s', 'acf' ), $taxonomy_label ),
				)
			);
		}

		// save?
		if ( $args['term_name'] ) {

			// exists
			if ( term_exists( $args['term_name'], $field['taxonomy'], $args['term_parent'] ) ) {
				wp_send_json_error(
					array(
						'error' => sprintf( __( '%s already exists', 'acf' ), $taxonomy_label ),
					)
				);
			}

			// vars
			$extra = array();
			if ( $args['term_parent'] ) {
				$extra['parent'] = (int) $args['term_parent'];
			}

			// insert
			$data = wp_insert_term( $args['term_name'], $field['taxonomy'], $extra );

			// error
			if ( is_wp_error( $data ) ) {
				wp_send_json_error(
					array(
						'error' => $data->get_error_message(),
					)
				);
			}

			// load term
			$term = get_term( $data['term_id'] );

			// Duplicating the term meta for the regional campaign.
			if ( $args['field_key'] === 'field_regional_campaign_id' && isset( $args['post_id'] ) ) {
				global $wpdb;

				$campaign_id = str_replace( 'term_', '', $args['post_id'] );

				$sql_where_condition = self::prepare_sql_where_condition_for_meta_keys_in_regional_campaign();

				try {
					$sql = $wpdb->prepare(
						sprintf(
							"INSERT INTO %s (`term_id`, `meta_key`, `meta_value`) SELECT %%d, `meta_key`, `meta_value` FROM %s WHERE `term_id` = %%d %3s",
							$wpdb->termmeta,
							$wpdb->termmeta,
							$sql_where_condition
						),
						$term->term_id,
						$campaign_id
					);

					$wpdb->query( $sql );
				} catch ( Exception $e ) {
					wp_send_json_error(
						array(
							'error' => 'I can\'t copy metadata.',
						)
					);
				}
			}

			// prepend ancenstors count to term name
			$prefix    = '';
			$ancestors = get_ancestors( $term->term_id, $term->taxonomy );
			if ( ! empty( $ancestors ) ) {
				$prefix = str_repeat( '- ', count( $ancestors ) );
			}

			// success
			wp_send_json_success(
				array(
					'message'     => sprintf( __( '%s added', 'acf' ), $taxonomy_label ),
					'term_id'     => $term->term_id,
					'term_name'   => $term->name,
					'term_label'  => $prefix . $term->name,
					'term_parent' => $term->parent,
				)
			);

		}

		?>
        <form method="post">
		<?php

		acf_render_field_wrap(
			array(
				'label' => __( 'Name', 'acf' ),
				'name'  => 'term_name',
				'type'  => 'text',
			)
		);

		if ( is_taxonomy_hierarchical( $field['taxonomy'] ) ) {

			$choices  = array();
			$response = $this->get_ajax_query( $args );

			if ( $response ) {
				foreach ( $response['results'] as $v ) {

					$choices[ $v['id'] ] = $v['text'];

				}
			}

			acf_render_field_wrap(
				array(
					'label'      => __( 'Parent', 'acf' ),
					'name'       => 'term_parent',
					'type'       => 'select',
					'allow_null' => 1,
					'ui'         => 0,
					'choices'    => $choices,
				)
			);

		}

		?>
        <p class="acf-submit">
            <button class="acf-submit-button button button-primary" type="submit"><?php _e( 'Add', 'acf' ); ?></button>
        </p>
		<?php if ( $args['field_key'] === 'field_regional_campaign_id' ) : ?>
            <p>
                When creating a regional campaign, the list of offers and the settings associated with the offers will be duplicated from the current campaign.
                If you need to create a campaign from scratch, use Brand Management -> Regional Campaigns.
            </p>
		<?php endif; ?>
        </form><?php

		// die
		die;

	}

	public static function prepare_sql_where_condition_for_meta_keys_in_regional_campaign(): string {

		global $wpdb;

		$term_meta_keys_whitelist = [
			'offers_list',
			'rewriting_offer_fields',
			'show_filter_tags',
			'campaign_all_filter_tag_order',
			'campaign_filter_tags',
			'offers_order_within_a_tag',
		];

		$sql_where_condition = '';
		foreach ( $term_meta_keys_whitelist as $index => $meta_key ) {
			if ( array_key_first( $term_meta_keys_whitelist ) === $index ) {
				$sql_where_condition .= ' AND (';
			} else {
				$sql_where_condition .= ' OR ';
			}

			$sql_where_condition .= '`meta_key` LIKE "%%' . $wpdb->esc_like( $meta_key ) . '%%"';

			if ( array_key_last( $term_meta_keys_whitelist ) === $index ) {
				$sql_where_condition .= ')';
			}
		}

		return $sql_where_condition;

	}

	private function add_custom_fields_to_payment_methods(): void {
		acf_add_local_field_group( [
			'key'      => 'group_payment_methods',
			'title'    => 'Payment Methods',
			'fields'   => [
				[
					'key'           => 'field_payment_method_logo',
					'label'         => 'Payment Method Logo',
					'name'          => 'payment_method_logo',
					'type'          => 'image',
					'return_format' => 'url',
					'preview_size'  => 'thumbnail',
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'payment_method',
					],
				],
			],
		] );
	}

	private function redefine_acf_ajax_add_term(): void {

		add_action( 'wp_loaded', function () {
			if ( function_exists( 'remove_filters_for_anonymous_class' ) ) {
				remove_filters_for_anonymous_class( 'wp_ajax_acf/fields/taxonomy/add_term', 'acf_field_taxonomy', 'ajax_add_term', 10 );
				add_action( 'wp_ajax_acf/fields/taxonomy/add_term', [ $this, 'ajax_add_term' ] );
			}
		} );

	}

	private function filter_offers_list_in_regional_campaigns(): void {

		add_filter( 'acf/fields/relationship/query/name=offers_list', static function ( $args, $field, $post_id ) {
			$campaign_region_code = '';

			if ( $post_id !== 'term_0' && str_contains( $post_id, 'term_' ) ) {
				$term = get_term( str_replace( 'term_', '', $post_id ) );

				if ( $term instanceof WP_Term ) {
					$taxonomy = $term->taxonomy;

					if ( $taxonomy === 'bm_regional_campaigns' ) {
						$campaign_region_code = get_field( 'campaign_region', $post_id ) ?? '';
					}
				}
			} elseif ( isset( $_COOKIE['bm_regional_campaigns__campaign_region'] ) ) {
				$campaign_region_code = $_COOKIE['bm_regional_campaigns__campaign_region'];
			}

			if ( ! empty( $campaign_region_code ) ) {
				$args['meta_query'] = [
					'relation' => 'OR',
					[
						'key'     => 'show_in_countries',
						'compare' => 'NOT EXISTS',
					],
					[
						'key'     => 'show_in_countries',
						'value'   => $campaign_region_code,
						'compare' => 'LIKE',
					],
				];
			}

			return $args;
		}, 10, 3 );

	}
}
