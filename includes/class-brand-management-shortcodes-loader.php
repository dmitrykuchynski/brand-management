<?php

/**
 * The class loads and adds all the required shortcodes.
 *
 * @since      1.0.0
 * @package    Brand_Management
 * @subpackage Brand_Management/includes
 */
class Brand_Management_Shortcodes_Loader {

	public function __construct() {
		$this->load_shortcodes();

		$shortcodes = [
			'brand'                     => [ ( new Brand_Shortcode() ), 'shortcode' ],
			'offer'                     => [ ( new Brand_Shortcode() ), 'shortcode' ],
			'campaign'                  => [ ( new Campaign_Shortcode() ), 'shortcode' ],
			'campaign_compact'          => [ ( new Campaign_Compact_Shortcode() ), 'shortcode' ],
			'campaignfullwidth'         => [ ( new Campaign_Full_Width_Shortcode() ), 'shortcode' ],
			'sidebar'                   => [ ( new Sidebar_Shortcode() ), 'shortcode' ],
			'sidebar_new'               => [ ( new Sidebar_Shortcode() ), 'shortcode' ],
			'comparison_table'          => [ ( new Brand_Comparison_Table_Shortcode() ), 'shortcode' ],
			'recommended_offers_widget' => [ ( new Recommended_Offers_Widget_Shortcode() ), 'shortcode' ],
			'popup_offers'              => [ ( new Popup_Non_Affiliated_Shortcode() ), 'shortcode' ],
			'single_offer'              => [ ( new Campaign_Shortcode() ), 'single_offer_shortcode' ],
		];

		/**
		 * Adding custom shortcodes into the core version of the plugin.
		 *
		 * @param  array  $shortcodes  {
		 *      Required. An array of shortcodes.
		 *      Key @type string $shortcode_name Shortcode name.
		 *      Value @type array [ $shortcode_class, 'render_shortcode_method' ].
		 * }
		 *
		 * @return array
		 * @since 1.1.0
		 *
		 */
		$shortcodes = apply_filters( 'bm_shortcodes_loader_register_shortcodes', $shortcodes );

		$this->add_shortcodes( $shortcodes );
	}

	private function load_shortcodes(): void {
		$dependencies = [
			BRAND_MANAGEMENT_PATH . 'includes/shortcodes/class-brand-shortcode.php',
			BRAND_MANAGEMENT_PATH . 'includes/shortcodes/class-campaign-shortcode.php',
			BRAND_MANAGEMENT_PATH . 'includes/shortcodes/class-campaign-compact-shortcode.php',
			BRAND_MANAGEMENT_PATH . 'includes/shortcodes/class-campaign-full-width-shortcode.php',
			BRAND_MANAGEMENT_PATH . 'includes/shortcodes/class-sidebar-shortcode.php',
			BRAND_MANAGEMENT_PATH . 'includes/shortcodes/class-brand-comparison-table-shortcode.php',
			BRAND_MANAGEMENT_PATH . 'includes/shortcodes/class-recommended-offers-widget-shortcode.php',
			BRAND_MANAGEMENT_PATH . 'includes/shortcodes/class-popup-non-affiliated-shortcode.php',
		];

		/**
		 * Load the required dependencies for registering the shortcode.
		 *
		 * @param  array  $dependencies  {
		 *      Required. An array of dependencies.
		 *      Value @type string $dependency_classpath Full path to dependency class.
		 * }
		 *
		 * @return array
		 * @since 1.1.0
		 *
		 */
		$dependencies = apply_filters( 'bm_shortcodes_loader_load_dependencies', $dependencies );

		foreach ( $dependencies as $dependency ) {
			require_once $dependency;
		}
	}

	private function add_shortcodes( $shortcodes ): void {
		foreach ( $shortcodes as $tag => $function ) {
			add_shortcode( $tag, $function );
		}
	}

}
