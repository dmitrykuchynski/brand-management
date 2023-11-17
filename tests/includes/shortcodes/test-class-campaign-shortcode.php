<?php

require_once( dirname( __DIR__ ) . '/class-brand-management-mock-factory.php' );

/**
 * brand-management/includes/shortcodes/class-campaign-shortcode.php
 */
class Campaign_Shortcode_Test extends WP_UnitTestCase {
	public $mock_factory;

	public function setUp(): void {

		parent::setUp();

		$this->mock_factory = new Brand_Management_Mock_Factory( self::factory() );

		// Creating entities for testing.
		$this->mock_factory->create_brands();
		$this->mock_factory->create_offers();
		$this->mock_factory->create_campaign();

		self::factory()->user->create();

	}

	/**
	 * Creates an instance of the Campaign_Shortcode class.
	 * Allows calling protected methods and setting data.
	 *
	 * @param  string  $method_name
	 * @param  array|null  $args
	 * @param  array  $attributes
	 * @param  bool  $set_variables
	 *
	 * @return mixed
	 * @throws ReflectionException
	 */
	private function call_campaign_method( string $method_name, array $args = null, array $attributes = [], bool $set_variables = false ): mixed {

		$campaign_shortcode = new Campaign_Shortcode();

		$campaign_shortcode->set_variable( 'atts', array_merge( [
			'id' => $this->mock_factory->campaign,
		], $attributes ) );

		$campaign_shortcode->set_variable( 'taxonomy_selector', 'bm_campaign_management_' . $this->mock_factory->campaign );

		if ( $set_variables ) {
			$campaign_shortcode->set_variable( 'updated_date_format', 'y/m/d' );
		}

		$method = new ReflectionMethod( 'Campaign_Shortcode', $method_name );
		$method->setAccessible( true );

		if ( isset ( $args ) ) {
			return $method->invokeArgs( $campaign_shortcode, $args );
		}

		return $method->invoke( $campaign_shortcode );

	}

	public function test_is_show_offers_counter() {

		$this->assertTrue(
			$this->call_campaign_method( 'is_show_offers_counter' )
		);

	}

	public function test_build_disclaimer_text_html() {

		$this->assertEmpty(
			$this->call_campaign_method( 'build_disclaimer_text_html', [ $this->mock_factory->offers['empty_offer'] ] )
		);

		$this->assertEquals(
			'<div class="disclaimer_text_brand"><span>There was an offer disclaimer here.<span></div>',
			$this->call_campaign_method( 'build_disclaimer_text_html', [ $this->mock_factory->offers['filled_offer'] ] )
		);

	}

	public function test_build_highlighted_label_html() {

		$this->assertEmpty(
			$this->call_campaign_method( 'build_highlighted_label_html', [ $this->mock_factory->offers['empty_offer'] ] )
		);

		$this->assertEquals(
			'<div class="top-pick">Campaign Top Picker</div>',
			$this->call_campaign_method( 'build_highlighted_label_html', [ $this->mock_factory->offers['filled_offer'] ] )
		);

	}

	public function test_get_editor_metadata_for_campaign() {

		$this->assertEmpty(
			$this->call_campaign_method( 'get_editor_metadata_for_campaign', [ $this->mock_factory->campaign ] )
		);

		wp_set_current_user( 1 );

		$this->assertEquals(
			'campaign_id="' . $this->mock_factory->campaign . '" link_to_edit="' . admin_url( 'term.php?taxonomy=bm_campaign_management&tag_ID=' . $this->mock_factory->campaign . '&post_type=brand' ) . '"',
			$this->call_campaign_method( 'get_editor_metadata_for_campaign', [ $this->mock_factory->campaign ] )
		);

		wp_logout();

	}

	public function test_get_editor_metadata_for_offer() {

		$this->assertEmpty(
			$this->call_campaign_method( 'get_editor_metadata_for_offer', [ $this->mock_factory->offers['empty_offer'] ] )
		);

		wp_set_current_user( 1 );

		$this->assertEquals(
			'offer_id="' . $this->mock_factory->offers['empty_offer'] . '" link_to_edit_offer="' . admin_url( 'post.php?post=' . $this->mock_factory->offers['empty_offer'] . '&action=edit' ) . '"',
			$this->call_campaign_method( 'get_editor_metadata_for_offer', [ $this->mock_factory->offers['empty_offer'] ] )
		);

		$this->assertEquals(
			'offer_id="' . $this->mock_factory->offers['filled_offer'] . '" link_to_edit_offer="' . admin_url( 'post.php?post=' . $this->mock_factory->offers['filled_offer'] . '&action=edit' ) . '"' .
			' brand_id="' . $this->mock_factory->brands['filled_brand'] . '" link_to_edit_brand="' . admin_url( 'post.php?post=' . $this->mock_factory->brands['filled_brand'] . '&action=edit' ) . '"',
			$this->call_campaign_method( 'get_editor_metadata_for_offer', [ $this->mock_factory->offers['filled_offer'] ] )
		);

		wp_logout();

	}

	public function test_build_metadata_html() {

		$this->assertEmpty(
			$this->call_campaign_method( 'build_metadata_html', [ $this->mock_factory->offers['empty_offer'] ] )
		);

		$this->assertNotEmpty(
			$this->call_campaign_method( 'build_metadata_html', [ $this->mock_factory->offers['filled_offer'] ] )
		);

	}

	public function test_is_show_filter_tags() {

		$this->assertTrue(
			$this->call_campaign_method( 'is_show_filter_tags' )
		);

	}

	public function test_get_brand_name() {

		$this->assertEquals(
			'New Offer 2022 3',
			$this->call_campaign_method( 'get_brand_name', [ $this->mock_factory->offers['empty_offer'] ] )
		);

		$this->assertEquals(
			'Top Bookmaker Ever 2',
			$this->call_campaign_method( 'get_brand_name', [ $this->mock_factory->offers['filled_offer'] ] )
		);

	}

	public function test_get_brand_logo_src() {

		$expected_attachment_url = wp_get_attachment_image_src( $this->mock_factory->attachment, 'bm_large_thumbnail' )[0];

		$this->assertEquals(
			$expected_attachment_url,
			$this->call_campaign_method( 'get_brand_logo_src', [ $this->mock_factory->brands['filled_brand'] ] )
		);

		$this->assertEquals(
			$expected_attachment_url,
			$this->call_campaign_method( 'get_brand_logo_src', [ $this->mock_factory->offers['filled_offer'] ] )
		);

	}

	public function test_build_star_rating_html() {

		$this->assertStringContainsString(
			'/images/star9.svg',
			$this->call_campaign_method( 'build_star_rating_html', [ $this->mock_factory->brands['filled_brand'] ] )
		);

		$this->assertStringContainsString(
			'/images/star9.svg',
			$this->call_campaign_method( 'build_star_rating_html', [ $this->mock_factory->offers['filled_offer'] ] )
		);

	}

	public function test_get_offer_description() {

		$this->assertEquals(
			get_field( 'offer_description', $this->mock_factory->brands['filled_brand'] ),
			$this->call_campaign_method( 'get_offer_description', [ $this->mock_factory->brands['filled_brand'] ] )
		);

		$this->assertEquals(
			get_field( 'offer_description', $this->mock_factory->offers['filled_offer'] ),
			$this->call_campaign_method( 'get_offer_description', [ $this->mock_factory->offers['filled_offer'] ] )
		);

	}

	public function test_get_star_rating_text() {

		$this->assertEquals(
			get_field( 'star_rating_text', $this->mock_factory->brands['filled_brand'] ),
			$this->call_campaign_method( 'get_star_rating_text', [ $this->mock_factory->brands['filled_brand'] ] )
		);

		$this->assertEmpty(
			$this->call_campaign_method( 'get_star_rating_text', [ $this->mock_factory->brands['empty_brand'] ] )
		);

	}

	public function test_build_key_features_html() {

		$this->assertMatchesRegularExpression(
			'/(?=.*Feature 1)(?=.*Feature 2)(?=.*Feature 3)/',
			$this->call_campaign_method( 'build_key_features_html', [ $this->mock_factory->brands['filled_brand'] ] )
		);

		$this->assertEmpty(
			$this->call_campaign_method( 'build_key_features_html', [ $this->mock_factory->brands['empty_brand'] ] )
		);

	}

	public function test_get_cta_button_label() {

		$this->assertEquals(
			'Click Me',
			$this->call_campaign_method( 'get_cta_button_label', [] )
		);

	}

	public function test_build_terms_and_conditions_html() {

		$this->assertStringContainsString(
			'Terms and conditions.',
			$this->call_campaign_method( 'build_terms_and_conditions_html', [ $this->mock_factory->offers['filled_offer'] ] )
		);

		$this->assertEmpty(
			$this->call_campaign_method( 'build_terms_and_conditions_html', [ $this->mock_factory->offers['empty_offer'] ] )
		);

	}

	public function test_build_read_review_html() {

		$this->assertEmpty(
			$this->call_campaign_method( 'build_read_review_html', [ $this->mock_factory->brands['empty_brand'] ] )
		);

		$this->assertStringContainsString(
			'https://example.com/read_review_url/',
			$this->call_campaign_method( 'build_read_review_html', [ $this->mock_factory->offers['filled_offer'] ] )
		);

	}

	public function test_build_coupon_code_html() {

		$coupon_code_html_from_filled_offer = $this->call_campaign_method( 'build_coupon_code_html', [ $this->mock_factory->offers['filled_offer'] ] );
		$this->assertStringContainsString(
			'COUPON_CODE',
			$coupon_code_html_from_filled_offer['coupon_code_slot']
		);

		$coupon_code_html_from_empty_offer = $this->call_campaign_method( 'build_coupon_code_html', [ $this->mock_factory->offers['empty_offer'] ] );
		$this->assertStringContainsString(
			'NO CODE REQUIRED',
			$coupon_code_html_from_empty_offer['coupon_code_tooltip_slot']
		);

	}

	public function test_build_top_picker_html() {

		$top_picker_html_first = $this->call_campaign_method( 'build_top_picker_html', [ $this->mock_factory->offers['filled_offer'], 1 ] );
		$this->assertStringContainsString(
			'Top Picker',
			$top_picker_html_first
		);
		$this->assertStringContainsString(
			'Codes claimed',
			$top_picker_html_first
		);

		$top_picker_html_following = $this->call_campaign_method( 'build_top_picker_html', [ $this->mock_factory->offers['filled_offer'], 5 ] );
		$this->assertStringContainsString(
			'Top Picker',
			$top_picker_html_following
		);
		$this->assertStringNotContainsString(
			'Codes claimed',
			$top_picker_html_following
		);

		$this->assertEmpty(
			$this->call_campaign_method( 'build_top_picker_html', [ $this->mock_factory->offers['empty_offer'], 1 ] )
		);

		$this->assertStringNotContainsString(
			'<div class="top-pick">',
			$this->call_campaign_method( 'build_top_picker_html', [ $this->mock_factory->offers['empty_offer'], 1 ] )
		);

	}

	public function test_get_offer_update_date() {

		$this->assertEquals(
			strtotime( get_the_date( 'd.m.Y H:i:s', $this->mock_factory->offers['empty_offer'] ) ),
			$this->call_campaign_method( 'get_offer_update_date', [ $this->mock_factory->offers['filled_offer'] ] )
		);

		$this->assertEquals(
			strtotime( get_the_modified_date( 'd.m.Y H:i:s', $this->mock_factory->offers['filled_offer'] ) ),
			$this->call_campaign_method( 'get_offer_update_date', [ $this->mock_factory->offers['filled_offer'] ] )
		);

	}

	public function test_get_templater() {

		$this->assertInstanceOf(
			Brand_Management_Templater::class,
			$this->call_campaign_method( 'get_templater',
				[ 'campaign-shortcode-common/campaign-shortcode-metadata-block-template.html' ] )
		);

	}

	public function test_build_table_title_html() {

		$this->assertEmpty(
			$this->call_campaign_method( 'build_table_title_html', [ 0 ] )
		);

		$this->assertStringContainsString(
			'<h2>Term',
			$this->call_campaign_method( 'build_table_title_html', [ $this->mock_factory->campaign ] )
		);

	}

	public function test_print_updated_date() {

		$this->assertStringContainsString(
			get_the_modified_date( 'd/m/y', $this->mock_factory->offers['filled_offer'] ),
			$this->call_campaign_method( 'print_updated_date', [ $this->mock_factory->offers['filled_offer'] ] )
		);

		$this->assertStringContainsString(
			get_the_modified_date( 'y/m/d', $this->mock_factory->offers['filled_offer'] ),
			$this->call_campaign_method( 'print_updated_date', [ $this->mock_factory->offers['filled_offer'] ], [], true )
		);

	}

	public function test_print_voting_section() {

		$this->assertStringContainsString(
			'<div class="campaign__voting_section__text likes-value">50</div>',
			( new Campaign_Shortcode() )->print_voting_section( $this->mock_factory->offers['filled_offer'] )
		);

		$this->assertStringContainsString(
			'<div class="campaign__voting_section__text dislikes-value">10</div>',
			( new Campaign_Shortcode() )->print_voting_section( $this->mock_factory->offers['filled_offer'] )
		);

	}

	public function test_get_unique_visit_link() {

		$this->assertEquals(
			'https://google.com/',
			$this->call_campaign_method( 'get_unique_visit_link', [ $this->mock_factory->offers['filled_offer'] ] )
		);

	}

	public function test_get_google_conversion_url() {

		$this->assertEquals(
			'https://finixio.com/',
			$this->call_campaign_method( 'get_google_conversion_url', [ $this->mock_factory->offers['filled_offer'] ] )
		);

	}

	public function test_is_global_active() {

		$this->assertTrue(
			Campaign_Shortcode::is_global_active( $this->mock_factory->offers['filled_offer'] )
		);

	}
}
