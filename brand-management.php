<?php

/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           Brand_Management
 *
 * @wordpress-plugin
 * Plugin Name:       Brand Management
 * Description:       Brand Management plugin which you can add and manage brand via shortcode or filter. The Brand Management plugin requires active <a href='https://www.advancedcustomfields.com/' target='_blank'>Advanced Custom Fields Pro</a>.
 * Version:           3.5.0
 * Text Domain:       brand-management
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'BRAND_MANAGEMENT_VERSION', '3.5.0' );

/**
 * Define the plugin path.
 */
define( 'BRAND_MANAGEMENT_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Define the plugin url.
 */
define( 'BRAND_MANAGEMENT_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-brand-management-activator.php.
 */
function activate_brand_management() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-brand-management-activator.php';
	Brand_Management_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-brand-management-deactivator.php
 */
function deactivate_brand_management() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-brand-management-deactivator.php';
	Brand_Management_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_brand_management' );
register_deactivation_hook( __FILE__, 'deactivate_brand_management' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-brand-management.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_brand_management() {

	$plugin = new Brand_Management();
	$plugin->run();

}

run_brand_management();
