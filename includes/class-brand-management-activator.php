<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Brand_Management
 * @subpackage Brand_Management/includes
 */
class Brand_Management_Activator {

	/**
	 * Regenerate links after registering new post types.
	 *
	 * @since    1.0.0
	 */
	public static function activate(): void {
		flush_rewrite_rules();
	}

}
