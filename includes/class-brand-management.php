<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Brand_Management
 * @subpackage Brand_Management/includes
 */
class Brand_Management {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Brand_Management_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $brand_management The string used to uniquely identify this plugin.
	 */
	protected $brand_management;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		if ( defined( 'BRAND_MANAGEMENT_VERSION' ) ) {
			$this->version = BRAND_MANAGEMENT_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$this->brand_management = 'brand-management';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->add_new_image_sizes();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for storing plugin data globally.
		 */
		require_once BRAND_MANAGEMENT_PATH . 'includes/class-brand-management-storage.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once BRAND_MANAGEMENT_PATH . 'includes/class-brand-management-loader.php';
		$this->loader = new Brand_Management_Loader();

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once BRAND_MANAGEMENT_PATH . 'includes/class-brand-management-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once BRAND_MANAGEMENT_PATH . 'admin/class-brand-management-admin.php';

		/**
		 * The class responsible for creating a parent brand for an offer.
		 */
		require_once BRAND_MANAGEMENT_PATH . 'admin/class-brand-management-brands-importer.php';
		( new Brand_Management_Brands_Importer() )->init();

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once BRAND_MANAGEMENT_PATH . 'public/class-brand-management-public.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once BRAND_MANAGEMENT_PATH . 'includes/class-brand-management-register-post-types.php';

		/**
		 * This class can be used to repeatedly HTML building from template.
		 */
		require_once BRAND_MANAGEMENT_PATH . 'includes/class-brand-management-templater.php';

		/**
		 * Utilities used to ensure the operation of shortcodes.
		 */
		require_once BRAND_MANAGEMENT_PATH . 'includes/class-brand-management-utilities.php';

		/**
		 * The class responsible for registration custom fields.
		 */
		require_once BRAND_MANAGEMENT_PATH . 'includes/class-brand-management-acf-loader.php';
		( new Brand_Management_Acf_Loader() )->bootstrap();

		/**
		 * The class responsible for loads and adds all the required shortcodes.
		 */
		require_once BRAND_MANAGEMENT_PATH . 'includes/class-brand-management-shortcodes-loader.php';
		( new Brand_Management_Shortcodes_Loader() );

		/**
		 * Template loader.
		 */
		require_once BRAND_MANAGEMENT_PATH . 'includes/class-brand-management-template-loader.php';

		/**
		 * Custom fields migrations.
		 */
		require_once BRAND_MANAGEMENT_PATH . 'includes/class-brand-management-migrate.php';

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Brand_Management_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Brand_Management_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Brand_Management_Admin( $this->get_brand_management(), $this->get_version() );
		$plugin_admin->columns_filter();
		$plugin_admin->add_options_page();

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'menu_formation' );
		$this->loader->add_action( 'wp_ajax_global_activity', $plugin_admin, 'ajax_global_activity' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'remove_unused_meta_boxes', 99 );
		$this->loader->add_action( 'bm_popup_offers_pre_add_form', $plugin_admin, 'form_upload_csv_for_popups' );
		$this->loader->add_action( 'wp_ajax_get_ordered_offers', $plugin_admin, 'ajax_get_ordered_offers' );
		$this->loader->add_action( 'wp_ajax_get_ordered_tags', $plugin_admin, 'ajax_get_ordered_tags' );
		$this->loader->add_action( 'wp_ajax_get_auto_filter_tags', $plugin_admin, 'ajax_get_auto_filter_tags' );
		$this->loader->add_action( 'wp_ajax_get_unique_visit_links', $plugin_admin, 'ajax_get_unique_visit_links' );
		$this->loader->add_action( 'wp_ajax_duplicate_or_create_regional_campaign', $plugin_admin, 'ajax_duplicate_or_create_regional_campaign' );
		$this->loader->add_action( 'wp_ajax_update_field_campaign_region', $plugin_admin, 'ajax_update_field_campaign_region' );

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_brand_management() {
		return $this->brand_management;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Brand_Management_Public();

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_critical_styles', 101 );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'likes_ajax_handler', 99 );
		$this->loader->add_action( 'wp_ajax_likes_handler', $plugin_public, 'likes_handler' );
		$this->loader->add_action( 'wp_ajax_nopriv_likes_handler', $plugin_public, 'likes_handler' );
		$this->loader->add_action( 'wp_ajax_get_voting_data', $plugin_public, 'ajax_get_voting_data' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_voting_data', $plugin_public, 'ajax_get_voting_data' );
		$this->loader->add_action( 'wp_ajax_get_campaign_offers', $plugin_public, 'ajax_get_campaign_offers' );
		$this->loader->add_action( 'wp_ajax_nopriv_get_campaign_offers', $plugin_public, 'ajax_get_campaign_offers' );
		$this->loader->add_action( 'get_footer', $plugin_public, 'try_hide_default_page_disclaimer' );

	}

	/**
	 * Register new thumbnail sizes
	 * of the plugin.
	 *
	 * @since    2.6.0
	 * @access   private
	 */
	private function add_new_image_sizes() {

		add_image_size( 'bm_large_thumbnail', 130, 63, true );
		add_image_size( 'bm_middle_thumbnail', 104, 50, true );
		add_image_size( 'bm_small_thumbnail', 83, 40, true );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		( new Brand_Management_Register_Post_Types() )->register_all();
		$this->loader->run();

	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Brand_Management_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

}
