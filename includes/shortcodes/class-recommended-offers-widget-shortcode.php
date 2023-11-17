<?php

class Recommended_Offers_Widget_Shortcode {
	private string $taxonomy = 'bm_recommended_offers';
	private string $template = 'recommended-offers-widget-shortcode';

	/**
	 * The shortcode takes an id parameter.
	 * Example - [recommended_offers_widget id="170507"], when id is a tag id on given taxonomy.
	 *
	 * @param $attributes
	 *
	 * @return string
	 */
	public function shortcode( $attributes ): string {

		$taxonomy_tag_id = $attributes['id'];
		if ( empty( $taxonomy_tag_id ) ) {
			return '';
		}

		$taxonomy_selector = $this->taxonomy . '_' . $taxonomy_tag_id;

		$campaign_shortcode                    = new Campaign_Shortcode();
		$campaign_shortcode->taxonomy_selector = $taxonomy_selector;

		$widget_title       = '';
		$widget_title_style = '';
		if ( bm_get_field( 'field_show_title', $taxonomy_selector ) ) {
			$widget_title       = get_term_by( 'id', $taxonomy_tag_id, $this->taxonomy )->name ?: '';
			$widget_title_color = bm_get_field( 'title_color', $taxonomy_selector );
			$widget_title_style = $widget_title_color ? sprintf( 'style="color: %s;"', $widget_title_color ) : '';
		}

		$widget_description       = '';
		$widget_description_style = '';
		if ( bm_get_field( 'field_show_description', $taxonomy_selector ) ) {
			$widget_description       = get_term_by( 'id', $taxonomy_tag_id, $this->taxonomy )->description ?: '';
			$widget_description_color = bm_get_field( 'description_color', $taxonomy_selector );
			$widget_description_style = $widget_description_color ? sprintf( 'style="color: %s;"', $widget_description_color ) : '';
		}

		$warning_message_html = '';
		if ( bm_get_field( 'field_show_warning_message', $taxonomy_selector ) ) {
			$warning_message_field = bm_get_field( 'field_warning_message', $taxonomy_selector );
			if ( ! empty( $warning_message_field ) ) {
				$warning_message_html = '<div class="recommended-offers-widget_warning-message">' . $warning_message_field . '</div>';
			}
		}

		$recommended_offers_html = '';
		$recommended_offers_list = bm_get_field( 'field_recommended_offers_list', $taxonomy_selector );

		foreach ( $recommended_offers_list as $offer_id ) {
			if ( ! Campaign_Shortcode::is_global_active( $offer_id ) ) {
				continue;
			}

			$brand_name  = get_the_title( bm_get_field( 'brand_id', $offer_id ) ?: $offer_id );
			$offer_terms = bm_get_field( 'terms_and_conditions', $offer_id ) ?: '';

			$read_review_link_html = '';
			if ( get_field( 'recommended_offers_show_review_links', $taxonomy_selector ) !== false ) {
				$read_review_url  = bm_get_field( 'read_review_url', $offer_id );
				$read_review_text = $read_review_url ? $brand_name . _x( ' Review', 'recommended-offers-shortcode', 'brand-management' ) : '';

				if ( ! empty( $read_review_url ) ) {
					$read_review_link_html = '<a href="' . $read_review_url . '">' . $read_review_text . '</a>';
				}
			}

			$recommended_offers_html .= $this->template_engine( 'recommended-offer-tile', [
				'{{META-DATA}}'               => Campaign_Shortcode::get_editor_metadata_for_offer( $offer_id ),
				'{{BRAND-LOGO-SRC-SLOT}}'     => bm_get_optimized_logo( $offer_id ),
				'{{BRAND-NAME-SLOT}}'         => $brand_name,
				'{{BRAND-SCORES-SLOT}}'       => $campaign_shortcode->get_star_rating_text( $offer_id ),
				'{{BRAND-RATING-IMG-SLOT}}'   => $campaign_shortcode->build_star_rating_html( $offer_id ),
				'{{READ-REVIEW-LINK-SLOT}}'   => $read_review_link_html,
				'{{TILE-TITLE-SLOT}}'         => $campaign_shortcode->get_offer_description( $offer_id ),
				'{{CUTTED-OFFER-TERMS-SLOT}}' => bm_trim_text( $offer_terms, 30 ),
				'{{OFFER-TERMS-SLOT}}'        => $offer_terms,
				'{{REFERRAL-LINK-SLOT}}'      => $campaign_shortcode->get_unique_visit_link( $offer_id ),
				'{{VISIT-LINK-ATTRIBUTES}}'   => bm_get_external_link_attributes( $offer_id ),
			] );
		}

		$shortcode_meta_data = '';
		if ( is_user_logged_in() ) {
			$shortcode_meta_data = 'shortcode_id="' . $taxonomy_tag_id . '" link_to_edit_shortcode="' . admin_url( 'term.php?taxonomy=bm_recommended_offers&tag_ID=' . $taxonomy_tag_id . '&post_type=offer' ) . '"';
		}

		Brand_Management_Public::load_assets( 'brand-management-recommended-offers-widget-shortcode' );
		wp_enqueue_script( 'brand-management-recommended-offers', ( new Brand_Management_Template_Loader() )->search_path( 'js/brand-management-recommended-offers.js' ), [ 'jquery' ], null, true );

		return $this->template_engine( $this->template, [
			'{{META-DATA}}'                     => $shortcode_meta_data,
			'{{WARNING-MESSAGE-SLOT}}'          => $warning_message_html,
			'{{WIDGET-TITLE-SLOT}}'             => $widget_title,
			'{{WIDGET-DESCRIPTION-SLOT}}'       => $widget_description,
			'{{WIDGET-TITLE-STYLE-SLOT}}'       => $widget_title_style,
			'{{WIDGET-DESCRIPTION-STYLE-SLOT}}' => $widget_description_style,
			'{{RECOMMENDED-OFFERS-SLOT}}'       => $recommended_offers_html,
		] );

	}

	private function template_engine( $template, $array ): string {
		return ( new Brand_Management_Templater( $this->template . '/' . $template . '.html' ) )->build_html_with_replacings( $array );
	}
}
