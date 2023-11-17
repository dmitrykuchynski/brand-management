<?php

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Brand_Management
 * @subpackage Brand_Management/includes
 */
class Brand_Management_Deactivator {

	/**
	 * Unregisters post types, taxonomies and taxonomies for object types.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		unregister_post_type( 'brand' );
		unregister_post_type( 'offer' );
		unregister_taxonomy( 'bm_campaign_management' );
		unregister_taxonomy( 'bm_comparison_tables' );
		unregister_taxonomy( 'bm_filter_tags' );
		unregister_taxonomy_for_object_type( 'bm_campaign_management', 'brand' );
		unregister_taxonomy_for_object_type( 'bm_campaign_management', 'brand' );
		unregister_taxonomy_for_object_type( 'bm_campaign_management', 'brand' );
	}

}
