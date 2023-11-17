<?php

/**
 * /includes/class-brand-management-templater.php
 */
class Brand_Management_Templater_Test extends WP_UnitTestCase {
	/**
	 * @dataProvider provide_template_example
	 */
	public function test_build_html_with_replacings( $replacing_array, $template, $expected ) {

		file_put_contents( 'public/partials/template.html', $template );

		$templater = new Brand_Management_Templater( '/template.html' );

		$html_with_replacings = $templater->build_html_with_replacings( $replacing_array );

		$this->assertEquals( $html_with_replacings, $expected );

		unlink( 'public/partials/template.html' );

	}

	public function provide_template_example(): array {
		return [
			'template_example' => [
				[
					'{{ TITLE }}'      => 'Title',
					'{{ TEXT }}'       => 'Text',
					'{{ ANNOTATION }}' => 'Annotation',
					'{{ OFFER }}'      => 'Offer',
				],
				'<div class="main"><h1>{{ TITLE }}</h1><p>{{ TEXT }}</p><div class="annotation">{{ ANNOTATION }}</div><div class="offer">{{ OFFER }}</div></div>',
				'<div class="main"><h1>Title</h1><p>Text</p><div class="annotation">Annotation</div><div class="offer">Offer</div></div>',
			],
		];
	}
}
