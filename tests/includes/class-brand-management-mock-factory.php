<?php

class Brand_Management_Mock_Factory {
	public int $attachment;
	public array $brands;
	public array $offers;
	public int $campaign;
	public string $taxonomy_selector;

	public function __construct( private $factory ) {
	}

	public function create_brands(): void {

		$this->brands['empty_brand'] = $this->factory->post->create( [
			'post_title' => 'Best Bookmaker Title 1',
			'post_type'  => 'brand',
		] );

		$this->brands['filled_brand'] = $this->factory->post->create( [
			'post_title' => 'Top Bookmaker Ever 2',
			'post_type'  => 'brand',
		] );

		$this->attachment = $this->factory->attachment->create_upload_object(
			__DIR__ . '/assets/dummy-attachment.png',
			$this->brands['filled_brand']
		);

		// Filling brand fields.
		update_field( 'brand_logo', $this->attachment, $this->brands['filled_brand'] );
		update_field( 'star_rating', '5', $this->brands['filled_brand'] );
		update_field( 'star_rating_text', '7', $this->brands['filled_brand'] );
		update_field( 'website', 'https://google.com/', $this->brands['filled_brand'] );
		update_field( 'key_features', [ [ 'point' => 'Feature 1' ], [ 'point' => 'Feature 2' ], [ 'point' => 'Feature 3' ] ], $this->brands['filled_brand'] );
		update_field( 'read_review_url', 'https://example.com/read_review_url/', $this->brands['filled_brand'] );
		update_field( 'highlighted_label', 'Top Picker From Brand', $this->brands['filled_brand'] );
		update_field( 'open_visit_links_in_new_tab', false, $this->brands['filled_brand'] );

	}

	public function create_offers(): void {

		$this->offers['empty_offer'] = $this->factory->post->create( [
			'post_title' => 'New Offer 2022 3',
			'post_type'  => 'offer',
		] );

		$this->offers['filled_offer'] = $this->factory->post->create( [
			'post_title' => 'Offer From Best Bookmaker 4',
			'post_type'  => 'offer',
		] );

		// Filling offer fields.
		update_field( 'brand_id', $this->brands['filled_brand'], $this->offers['filled_offer'] );
		update_field( 'offer_description', 'My new offer description.', $this->offers['filled_offer'] );
		update_field( 'terms_and_conditions', 'Terms and conditions.', $this->offers['filled_offer'] );
		update_field( 'bonus_amount', '50 $', $this->offers['filled_offer'] );
		update_field( 'coupon_code', 'COUPON_CODE', $this->offers['filled_offer'] );
		update_field( 'bonus_taken_count', '666', $this->offers['filled_offer'] );
		update_field( 'highlighted_label', 'Top Picker', $this->offers['filled_offer'] );
		update_field( 'disclaimer_text', 'There was an offer disclaimer here.', $this->offers['filled_offer'] );
		update_field( 'offer_likes', 50, $this->offers['filled_offer'] );
		update_field( 'offer_dislikes', 10, $this->offers['filled_offer'] );
		update_field( 'unique_visit_link', 'https://google.com/', $this->offers['filled_offer'] );
		update_field( 'google_conversion_url', 'https://finixio.com/', $this->offers['filled_offer'] );

	}

	public function create_campaign(): void {

		$this->campaign = $this->factory->term->create( [
			'taxonomy' => 'bm_campaign_management',
		] );

		$this->taxonomy_selector = 'bm_campaign_management_' . $this->campaign;

		// Filling campaign fields.
		update_field( 'cta_button_label', 'Click Me', $this->taxonomy_selector );
		update_field( 'show_table_title', true, $this->taxonomy_selector );
		update_field( 'advertiser_disclosure', 'Test Disclosure', $this->taxonomy_selector );
		update_field( 'show_offers_counter', true, $this->taxonomy_selector );
		update_field( 'show_filter_tags', true, $this->taxonomy_selector );

		add_term_meta( $this->campaign, 'offers_list', serialize( [ $this->offers['filled_offer'], $this->offers['empty_offer'] ] ) );
		add_term_meta( $this->campaign, 'rewriting_offer_fields', '1' );
		add_term_meta( $this->campaign, 'rewriting_offer_fields_0_rewrite_offer_id', $this->offers['filled_offer'] );
		add_term_meta( $this->campaign, 'rewriting_offer_fields_0_highlighted_label', 'Campaign Top Picker' );

		wp_set_object_terms( $this->brands['filled_brand'], [ 'bm_tag_3' ], 'bm_filter_tags' );
		wp_set_object_terms( $this->brands['filled_brand'], [ 'bm_tag_1', 'bm_tag_2' ], 'bm_filter_tags' );

		$term = get_term_by( 'slug', 'bm_tag_3', 'bm_filter_tags' );

		add_term_meta( $this->campaign, 'rewriting_offer_fields_0_tag', [ $term->term_id ] );

	}
}
