<?php

class Popup_Non_Affiliated_Shortcode {
	private string $taxonomy = 'bm_popup_offers';
	private string $template = 'popup-offers-shortcode';

	public function __construct() {
		add_action( 'wp_footer', [ $this, 'render_shortcode_in_footer' ] );
	}

	public function render_shortcode_in_footer(): void {

		$current_post_id = get_the_ID();

		$popup_id = get_field( 'field_id_of_popup_for_non_affiliated', $current_post_id );
		if ( empty( $popup_id ) ) {
			$popups_csv_array = get_option( 'popup_offers_options_csv_array' );
			if ( ! empty( $popups_csv_array ) ) {
				$popups_csv_array = unserialize( $popups_csv_array );
				if ( ! empty( $popups_csv_array['data'][ $current_post_id ] ) ) {
					$popup_id = $popups_csv_array['data'][ $current_post_id ];
				}
			}
		}

		if ( ! empty( $popup_id ) ) {
			echo do_shortcode( '[popup_offers id="' . $popup_id . '"]' );
		}

	}

	/**
	 * The shortcode takes an id parameter.
	 * Example - [popup_offers id="1151"], when id is a tag id on given taxonomy.
	 *
	 * @param $attributes
	 *
	 * @return string
	 */
	public function shortcode( $attributes ): string {

		$popup_id = $attributes['id'];
		// There is no taxonomy with the specified id.
		if ( empty( $popup_id ) ) {
			return '';
		}

		$taxonomy_selector = $this->taxonomy . '_' . $popup_id;

		$offers_list = bm_get_field( 'field_popup_offers_list', $taxonomy_selector );
		if ( empty( $offers_list ) ) {
			return '';
		}

		$campaign_shortcode                    = new Campaign_Shortcode();
		$campaign_shortcode->taxonomy_selector = $taxonomy_selector;

		$popup_title = '';
		if ( bm_get_field( 'field_popup_offers_show_title', $taxonomy_selector ) ) {
			$popup_title = get_term_by( 'id', $popup_id, $this->taxonomy )->name ?: '';
		}

		$popup_title_arrow_icon = '';
		if ( bm_get_field( 'field_popup_offers_show_arrow_icon', $taxonomy_selector ) ) {
			$popup_title_arrow_icon = '
				<svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M11.8327 5.16666L5.16602 11.8333" stroke="#1C2642" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M11.8327 11.8333H5.16602V5.16666" stroke="#1C2642" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>';
		}

		$popup_subtitle_html = '';
		if ( bm_get_field( 'field_popup_offers_show_subtitle', $taxonomy_selector ) ) {
			$popup_subtitle = bm_get_field( 'field_popup_offers_subtitle', $taxonomy_selector );
			if ( ! empty( $popup_subtitle ) ) {
				$popup_subtitle_html = '<div class="recommended-offers-subtitle">' . $popup_title_arrow_icon . $popup_subtitle . '</div>';
			}
		}

		$popup_offers_html = '';

		$foreach_loop_counter = 0;
		foreach ( $offers_list as $offer ) {
			$offer_id = $offer['offer'];

			if ( ! Campaign_Shortcode::is_global_active( $offer_id ) ) {
				continue;
			}

			$highlighted_label_html  = '';
			$highlighted_label_class = '';
			if ( ! empty( $offer['highlight_text'] ) ) {
				$highlighted_label_html  = '<div class="top-pick">' . $offer['highlight_text'] . '</div>';
				$highlighted_label_class = 'highlited';
			}

			$read_review_link_html = '';
			if ( ! empty( $read_review_url ) ) {
				$read_review_url  = bm_get_field( 'read_review_url', $offer_id );
				$read_review_text = $read_review_url ? _x( ' Review', 'popup-offers-shortcode', 'brand-management' ) : '';

				$read_review_link_html = '
					<a class="review-link" href="' . $read_review_url . '">
						' . $read_review_text . '
						<svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M1 9L5 5L1 1" stroke="#1C2642" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</a>';
			}

			$coupon_code = bm_get_field( 'coupon_code', $offer_id ) ?: '';

			$empty_coupon_code_class = '';
			if ( empty( $coupon_code ) ) {
				$coupon_code             = _x( 'NO CODE REQUIRED', 'popup-offers-shortcode', 'brand-management' );
				$empty_coupon_code_class = 'not-required-coupon-code';
			}

			$popup_offers_html .= $this->template_engine( 'popup-offer-tile', [
				'{{META-DATA}}'                    => Campaign_Shortcode::get_editor_metadata_for_offer( $offer_id ),
				'{{BRAND-LOGO-SRC-SLOT}}'          => bm_get_optimized_logo( $offer_id, 'bm_small_thumbnail' ) ?: '',
				'{{BRAND-NAME-SLOT}}'              => get_the_title( bm_get_field( 'brand_id', $offer_id ) ?: $offer_id ),
				'{{BRAND-SCORES-SLOT}}'            => $campaign_shortcode->get_star_rating_text( $offer_id ),
				'{{BRAND-PRINCIPALES-SLOT}}'       => $campaign_shortcode->build_key_features_html( $offer_id, 'popup-offers-widget__key-features' ),
				'{{HIGHLIGHT-CLASS}}'              => $highlighted_label_class,
				'{{HIGHLIGHT-SLOT}}'               => $highlighted_label_html,
				'{{CALL-TO-ACTION-BTN-TEXT-SLOT}}' => _x( 'Claim Bonus', 'popup-offers-shortcode', 'brand-management' ),
				'{{BRAND-RATING-IMG-SLOT}}'        => $campaign_shortcode->build_star_rating_html( $offer_id ),
				'{{READ-REVIEW-LINK-SLOT}}'        => $read_review_link_html,
				'{{NO-COUPON-SLOT}}'               => $empty_coupon_code_class,
				'{{COPIED-TEXT-SLOT}}'             => _x( 'Copied', 'popup-offers-shortcode', 'brand-management' ),
				'{{COPY-TEXT-SLOT}}'               => _x( 'Copy', 'popup-offers-shortcode', 'brand-management' ),
				'{{COUPON-SLOT}}'                  => $coupon_code,
				'{{TILE-TITLE-SLOT}}'              => strip_tags( $campaign_shortcode->get_offer_description( $offer_id ) ),
				'{{OFFER-TERMS-SLOT}}'             => bm_get_field( 'terms_and_conditions', $offer_id ) ?: '',
				'{{CUTTED-OFFER-TERMS-SLOT}}'      => bm_get_field( 'terms_and_conditions', $offer_id ) ?: '',
				'{{REFERRAL-LINK-SLOT}}'           => $campaign_shortcode->get_unique_visit_link( $offer_id ),
				'{{VISIT-LINK-ATTRIBUTES}}'        => bm_get_external_link_attributes( $offer_id ),
			] );

			$foreach_loop_counter ++;
		}

		$shortcode_meta_data = '';
		if ( is_user_logged_in() ) {
			$shortcode_meta_data = 'popup_id="' . $popup_id . '" link_to_edit_popup="' . admin_url( 'term.php?taxonomy=bm_popup_offers&tag_ID=' . $popup_id . '&post_type=offer' ) . '"';
		}

		Brand_Management_Public::load_assets( 'brand-management-popup-offers-shortcode' );
		wp_enqueue_script( 'brand-management-popup-offers', ( new Brand_Management_Template_Loader() )->search_path( 'js/brand-management-popup-offers.js' ), [ 'jquery' ], null, true );

		return $this->template_engine( $this->template, [
			'{{META-DATA}}'         => $shortcode_meta_data,
			'{{COUNT-OFFERS-SLOT}}' => $foreach_loop_counter,
			'{{SUBTITLE-SLOT}}'     => $popup_subtitle_html,
			'{{TITLE-SLOT}}'        => $popup_title,
			'{{POPUP-OFFERS-SLOT}}' => $popup_offers_html,
		] );

	}

	private function template_engine( $template, $array ): string {
		return ( new Brand_Management_Templater( $this->template . '/' . $template . '.html' ) )->build_html_with_replacings( $array );
	}
}
