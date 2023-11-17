<?php

/**
 * Registers post types, taxonomies and taxonomies for object types.
 *
 * @since      1.0.0
 * @package    Brand_Management
 * @subpackage Brand_Management/includes
 */
class Brand_Management_Register_Post_Types {
	public function register_all(): void {

		add_action( 'init', [ $this, 'register_taxonomies' ] );
		add_action( 'init', [ $this, 'register_post_types' ] );

	}

	public function register_post_types(): void {

		$brand_post_type_args = [
			'labels'          => [
				'name'          => 'Brands',
				'singular_name' => 'Brand',
				'search_items'  => 'Search Brands',
				'edit_item'     => 'Edit Brand',
				'update_item'   => 'Update Brand',
				'add_new'       => 'Add New Brand',
				'add_new_item'  => 'Add New Brand',
				'new_item_name' => 'New Brand Name',
				'menu_name'     => 'Brand Management',
				'all_items'     => 'All Brands',
			],
			'public'          => false,
			'show_ui'         => true,
			'menu_position'   => '20',
			'menu_icon'       => 'dashicons-superhero-alt',
			'capability_type' => 'post',
			'supports'        => [
				'title',
				'revisions',
			],
			'taxonomies'      => [
				'bm_campaign_management',
				'bm_comparison_tables',
				'bm_filter_tags',
			],
			'query_var'       => false,
		];
		register_post_type( 'brand', $brand_post_type_args );

		$offer_post_type_args = [
			'labels'          => [
				'name'          => 'Offers',
				'singular_name' => 'Offer',
				'search_items'  => 'Search Offers',
				'edit_item'     => 'Edit Offer',
				'update_item'   => 'Update Offer',
				'add_new'       => 'Add New Offer',
				'add_new_item'  => 'Add New Offer',
				'new_item_name' => 'New Offer Name',
				'menu_name'     => 'Brand Management',
				'all_items'     => 'All Offers',
			],
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'edit.php?post_type=brand',
			'capability_type' => 'post',
			'supports'        => [
				'title',
				'revisions',
			],
			'query_var'       => false,
		];
		register_post_type( 'offer', $offer_post_type_args );

		$payment_methods_post_type_args = [
			'labels'          => [
				'name'          => 'Payment Methods',
				'singular_name' => 'Payment Method',
				'search_items'  => 'Search',
				'edit_item'     => 'Edit',
				'update_item'   => 'Update',
				'add_new'       => 'Add',
				'add_new_item'  => 'Add Payment Method',
				'new_item_name' => 'New Payment Method Name',
				'all_items'     => 'All Payment Methods',
			],
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => false,
			'capability_type' => 'post',
			'supports'        => [
				'title',
			],
			'query_var'       => false,
		];
		register_post_type( 'payment_method', $payment_methods_post_type_args );
	}

	public function register_taxonomies(): void {
		$campaign_management_taxonomy_args = [
			'labels'            => [
				'name'          => 'Campaign Management',
				'singular_name' => 'Campaign Management',
				'search_items'  => 'Search Campaigns',
				'edit_item'     => 'Edit Campaign',
				'update_item'   => 'Update Campaign',
				'add_new_item'  => 'Add Campaign',
				'new_item_name' => 'New Campaign Name',
				'not_found'     => 'No campaigns found.',
			],
			'public'            => false,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'meta_box_cb'       => false,
		];
		register_taxonomy( 'bm_campaign_management', 'brand', $campaign_management_taxonomy_args );

		$regional_campaigns_taxonomy_args = [
			'labels'            => [
				'name'          => 'Regional Campaigns',
				'singular_name' => 'Regional Campaign',
				'search_items'  => 'Search Regional Campaigns',
				'edit_item'     => 'Edit Regional Campaign',
				'update_item'   => 'Update Regional Campaign',
				'add_new_item'  => 'Add Regional Campaign',
				'new_item_name' => 'New Regional Campaign Name',
				'not_found'     => 'No regional campaigns found.',
			],
			'public'            => false,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'meta_box_cb'       => false,
		];
		register_taxonomy( 'bm_regional_campaigns', 'brand', $regional_campaigns_taxonomy_args );

		$comparison_tables_taxonomy_args = [
			'description'       => 'Allows you to create comparison tables for brands.',
			'labels'            => [
				'name'          => 'Comparison Tables',
				'singular_name' => 'Comparison Table',
				'search_items'  => 'Search Tables',
				'edit_item'     => 'Edit Comparison Table',
				'update_item'   => 'Update Comparison Table',
				'add_new_item'  => 'Add New Table',
				'new_item_name' => 'New Comparison Table Name',
				'not_found'     => 'No comparison tables found.',

			],
			'public'            => false,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'rewrite'           => false,
			'meta_box_cb'       => false,
		];
		register_taxonomy( 'bm_comparison_tables', 'brand', $comparison_tables_taxonomy_args );

		$filter_tags_taxonomy_args = [
			'labels'       => [
				'name'          => 'Filter Tags',
				'singular_name' => 'Filter Tag',
				'search_items'  => 'Search Tags',
				'edit_item'     => 'Edit Tag',
				'update_item'   => 'Update Tag',
				'add_new_item'  => 'Add New Tag',
				'new_item_name' => 'New Tag Name',
			],
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => false,
		];
		register_taxonomy( 'bm_filter_tags', 'brand', $filter_tags_taxonomy_args );

		$recommended_offers_taxonomy_args = [
			'description'       => 'Allows you to create widget with three recommended offers.',
			'labels'            => [
				'name'          => 'Recommended Offers Widgets',
				'singular_name' => 'Recommended Offers Widget',
				'search_items'  => 'Search Widget',
				'edit_item'     => 'Edit Recommended Offers Widget',
				'update_item'   => 'Update Recommended Offers Widget',
				'add_new_item'  => 'Add New Recommended Offers Widget',
				'new_item_name' => 'New Recommended Offers Widget Name',
				'not_found'     => 'No Recommended Offers Widgets found.',

			],
			'public'            => false,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'rewrite'           => false,
			'meta_box_cb'       => false,
		];
		register_taxonomy( 'bm_recommended_offers', 'brand', $recommended_offers_taxonomy_args );

		$popup_offers_taxonomy_args = [
			'description'       => 'Allows you to create popup with three offers for non affiliated pages.',
			'labels'            => [
				'name'          => 'Popup for non-affiliated pages',
				'singular_name' => 'Popup for non-affiliated pages',
				'search_items'  => 'Search Widget',
				'edit_item'     => 'Edit Popup for non-affiliated pages',
				'update_item'   => 'Update Popup for non-affiliated pages',
				'add_new_item'  => 'Add New Popup for non-affiliated pages',
				'new_item_name' => 'New Popup Name',
				'not_found'     => 'No Popup found.',

			],
			'public'            => false,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'rewrite'           => false,
			'meta_box_cb'       => false,
		];
		register_taxonomy( 'bm_popup_offers', 'brand', $popup_offers_taxonomy_args );

	}
}
