<?php

/**
 * brand-management/includes/class-brand-management-template-loader.php
 */
class Brand_Management_Template_Loader_Test extends WP_UnitTestCase {
	public function test_search_path_in_bm_extended() {

		$template_loader = new Brand_Management_Template_Loader;

		$path = $template_loader->search_path( '/js/brand-management-campaign-shortcode.js' );

		$this->assertNotEmpty( $path, 'The file does not exist!' );
		$this->assertFileIsReadable( $path );
		$this->assertNotNull( strripos( $path, '/brand-management-extended/' ) );

	}

	public function test_search_path_in_bm_core() {

		$template_loader = new Brand_Management_Template_Loader;

		$path = $template_loader->search_path( '/js/brand-management-slick-carousel.js' );

		$this->assertNotEmpty( $path, 'The file does not exist!' );
		$this->assertFileIsReadable( $path );
		$this->assertNotNull( strripos( $path, '/brand-management/' ) );

	}
}
