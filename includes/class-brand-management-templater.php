<?php
require_once BRAND_MANAGEMENT_PATH . 'includes/class-brand-management-template-loader.php';

/**
 * This class can be used to repeatedly html building from
 * the template. Just put a path to template file in constructor
 * and call method build_html_with_replacing to build your html.
 *
 * Example:
 * ( new Brand_Management_Templater( 'path_to_your_html_template.html' ) )->build_html_with_replacings( [ '{{ SLOT_1 }}' => $string_for_slot_1, '{{ SLOT_2 }}' => $string_for_slot_2 ] );
 */
class Brand_Management_Templater extends Brand_Management_Template_Loader {
	private string $template_path;
	private string $template_html;

	public function __construct( $template_path = '', $template_path_from_public_dir = 'partials/' ) {

		$this->template_path = $template_path;
		$this->template_html = file_get_contents( $this->search_path( $template_path_from_public_dir . $template_path, false ) ) ?? '';

	}

	/**
	 * @param array $replacing_array
	 *
	 * @return string
	 */
	public function build_html_with_replacings( array $replacing_array = [] ): string {

		/**
		 * add_filter( 'bm_templater_replacing_array', 'bm_templater_replacing_array_alt', 10, 2 );
		 * function bm_templater_replacing_array_alt( $replacing_array, $template_path ): array {}
		 */
		$replacing_array = apply_filters( 'bm_templater_replacing_array', $replacing_array, $this->template_path );

		foreach ( $replacing_array as $find => $replace ) {
			$this->template_html = str_replace( (string) $find, (string) $replace, $this->template_html );
		}

		/**
		 * add_filter( 'bm_templater_template_html', 'bm_templater_template_html_alt', 10, 2 );
		 * function bm_templater_template_html_alt( $template_html, $template_path ): string {}
		 */
		$this->template_html = apply_filters( 'bm_templater_template_html', $this->template_html, $this->template_path );

		return $this->template_html;

	}
}
