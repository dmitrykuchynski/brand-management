<?php

require_once( 'class-brand-management-mock-factory.php' );

/**
 * brand-management/includes/class-brand-management-utilities.php
 */
class Brand_Management_Utilities_Test extends WP_UnitTestCase {
	public $mock_factory;

	public function setUp(): void {

		parent::setUp();

		$this->mock_factory = new Brand_Management_Mock_Factory( self::factory() );

		// Creating entities for testing.
		$this->mock_factory->create_brands();
		$this->mock_factory->create_offers();
		$this->mock_factory->create_campaign();

	}

	public function test_bm_is_offer() {

		$this->assertTrue( bm_is_offer( $this->mock_factory->offers['empty_offer'] ) );
		$this->assertFalse( bm_is_offer( $this->mock_factory->brands['filled_brand'] ) );

	}

	public function test_bm_get_brand_id() {

		$this->assertSame(
			$this->mock_factory->brands['filled_brand'],
			bm_get_brand_id( $this->mock_factory->offers['filled_offer'] )
		);

		$this->assertSame(
			$this->mock_factory->offers['empty_offer'],
			bm_get_brand_id( $this->mock_factory->offers['empty_offer'] )
		);

	}

	public function test_bm_get_brand_logo() {

		$expected_attachment_url = wp_get_attachment_image_src( $this->mock_factory->attachment, 'full' )[0];

		$this->assertEquals(
			$expected_attachment_url,
			bm_get_brand_logo( $this->mock_factory->brands['filled_brand'] )
		);

		$this->assertEquals(
			$expected_attachment_url,
			bm_get_brand_logo( $this->mock_factory->offers['filled_offer'] )
		);

	}

	public function test_bm_get_optimized_logo() {

		$expected_attachment_url = wp_get_attachment_image_src( $this->mock_factory->attachment, 'bm_large_thumbnail' )[0];

		$this->assertEquals(
			$expected_attachment_url,
			bm_get_optimized_logo( $this->mock_factory->brands['filled_brand'] )
		);

		$this->assertEquals(
			$expected_attachment_url,
			bm_get_optimized_logo( $this->mock_factory->offers['filled_offer'] )
		);

	}

	public function test_bm_get_field() {

		$this->assertSame(
			'Top Picker',
			bm_get_field( 'highlighted_label', $this->mock_factory->offers['filled_offer'] )
		);

		$this->assertSame(
			'Top Picker From Brand',
			bm_get_field( 'highlighted_label', $this->mock_factory->offers['filled_offer'], true, '', true )
		);

		$this->assertSame(
			'Campaign Top Picker',
			bm_get_field( 'highlighted_label', $this->mock_factory->offers['filled_offer'], true, $this->mock_factory->taxonomy_selector )
		);

	}

	public function test_bm_render_star_rating() {

		$test_cases = [
			'0.5'  => 1,
			'1'    => 1,
			'1.2'  => 2,
			'1.5'  => 2,
			'1.7 ' => 3,
			'2'    => 3,
			'2.5'  => 4,
			'3'    => 5,
			'3.5'  => 6,
			'4'    => 7,
			'4.5'  => 8,
			'5'    => 9,
			'6'    => 5,
			'7'    => 6,
			'8'    => 7,
			'9.3'  => 9,
		];

		foreach ( $test_cases as $rating => $expected_stars_svg ) {
			$test_string = 'src="' . BRAND_MANAGEMENT_URL . 'public/images/star' . $expected_stars_svg . '.svg"';

			$this->assertStringContainsString(
				$test_string,
				bm_render_star_rating( $rating )
			);
		}

	}

	public function test_bm_get_brand_tags() {

		$expected_static_tags    = 'custom_tag_bm_tag_1 custom_tag_bm_tag_2';
		$expected_rewritten_tags = 'custom_tag_bm_tag_3';

		$brand_tags_as_array            = bm_get_brand_tags( $this->mock_factory->offers['filled_offer'], '', true ) ?: [];
		$brand_tags_as_string           = bm_get_brand_tags( $this->mock_factory->offers['filled_offer'] ) ?: '';
		$rewritten_brand_tags_as_array  = bm_get_brand_tags( $this->mock_factory->offers['filled_offer'], $this->mock_factory->taxonomy_selector, true ) ?: [];
		$rewritten_brand_tags_as_string = bm_get_brand_tags( $this->mock_factory->offers['filled_offer'], $this->mock_factory->taxonomy_selector ) ?: '';

		$string_from_brand_tags_as_array = '';
		foreach ( $brand_tags_as_array as $tag ) {
			$string_from_brand_tags_as_array .= 'custom_tag_' . $tag->name . ' ';
		}

		$string_from_rewritten_brand_tags_as_array = '';
		foreach ( $rewritten_brand_tags_as_array as $tag ) {
			$string_from_rewritten_brand_tags_as_array .= 'custom_tag_' . $tag->name . ' ';
		}

		$this->assertSame( trim( $string_from_brand_tags_as_array ), $expected_static_tags );
		$this->assertSame( trim( $brand_tags_as_string ), $expected_static_tags );
		$this->assertSame( trim( $string_from_rewritten_brand_tags_as_array ), $expected_rewritten_tags );
		$this->assertSame( trim( $rewritten_brand_tags_as_string ), $expected_rewritten_tags );

	}

	public function test_bm_get_rewritten_field() {

		$this->assertSame(
			'Campaign Top Picker',
			bm_get_field( 'highlighted_label', $this->mock_factory->offers['filled_offer'], true, $this->mock_factory->taxonomy_selector )
		);

	}

	public function test_bm_trim_text() {

		$text = 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.';

		$this->assertSame(
			10,
			strlen( bm_trim_text( $text, 10 ) )
		);

		$this->assertSame(
			15,
			strlen( bm_trim_text( $text, 20 ) )
		);

		$this->assertSame(
			32,
			strlen( bm_trim_text( $text, 30 ) )
		);

	}

	public function test_bm_get_external_link_attributes() {

		$this->assertStringContainsString(
			'target="_self"',
			bm_get_external_link_attributes( $this->mock_factory->offers['filled_offer'] )
		);

		$this->assertStringContainsString(
			'target="_blank"',
			bm_get_external_link_attributes( $this->mock_factory->offers['empty_offer'] )
		);

	}
}
