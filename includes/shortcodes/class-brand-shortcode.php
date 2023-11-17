<?php

class Brand_Shortcode {
	private string $brand_id;
	private Campaign_Shortcode $campaign_shortcode;

	public function shortcode( $atts ): string {

		$this->brand_id = $atts['id'] ?? '';

		if ( ! empty( $this->brand_id ) && Campaign_Shortcode::is_global_active( $this->brand_id ) ) {
			$this->campaign_shortcode                    = new Campaign_Shortcode();
			$this->campaign_shortcode->taxonomy_selector = '';

			Brand_Management_Public::load_assets( 'brand-management-brand-shortcode' );

			return $this->build_shortcode();
		}

		return '';

	}

	public function build_shortcode(): string {

		$templater = new Brand_Management_Templater( 'brand-shortcode/brand-shortcode-list-template.html' );

		return $templater->build_html_with_replacings( [
			'{{TOP-PICKER-SLOT}}'            => $this->build_top_picker_html( $this->brand_id ),
			'{{REFERRAL-URL-SLOT}}'          => bm_get_field( 'unique_visit_link', $this->brand_id ) ?: '',
			'{{BRAND-LOGO-IMG-SRC-SLOT}}'    => bm_get_optimized_logo( $this->brand_id ),
			'{{BRAND-NAME-SLOT}}'            => $this->campaign_shortcode->get_brand_name( $this->brand_id ),
			'{{NEW-CUSTOMER-SLOT}}'          => $this->campaign_shortcode->get_offer_description( $this->brand_id ),
			'{{PRINCIPALES-LIST-SLOT}}'      => $this->campaign_shortcode->build_key_features_html( $this->brand_id ),
			'{{GOOGLE-CONVERSION-URL-SLOT}}' => $this->campaign_shortcode->get_google_conversion_url( $this->brand_id ),
			'{{SCORE-SLOT}}'                 => bm_get_field( 'star_rating_text', $this->brand_id ) ?: '',
			'{{STAR-RATING-IMG-SLOT}}'       => $this->campaign_shortcode->build_star_rating_html( $this->brand_id ),
			'{{CLAIM-OFFER-TEXT-SLOT}}'      => _x( 'Claim Offer', 'brand-shortcode', 'brand-management' ),
		] );

	}

	private function build_top_picker_html( $brand_id ): string {

		$coupon_code_is_not_empty = ! empty( bm_get_field( 'coupon_code', $brand_id ) );

		$bonus_claimed_html = '';
		if ( $coupon_code_is_not_empty ) {
			$bonus_taken_count = bm_get_field( 'bonus_taken_count', $brand_id );
			if ( ! empty( $bonus_taken_count ) ) {
				$bonus_claimed_html = '<div class="brand_boun_claimed"><span>' . $bonus_taken_count . ' ' . _x( 'Codes claimed', 'brand-shortcode', 'brand-management' ) . '</span></div>';
			} else {
				$bonus_claimed_html = '<div class="brand_boun_claimed"><span>' . ( rand( 100, 999 ) ) . ' ' . _x( 'Codes claimed', 'brand-shortcode', 'brand-management' ) . '</span></div>';
			}
		}

		$highlighted_label = bm_get_field( 'highlighted_label', $brand_id );
		if ( ! empty( $highlighted_label ) ) {
			$highlighted_label_html = '<div class="top-pick">' . $highlighted_label . '</div>';
		}

		return '<div class="toppike-out">' . ( $highlighted_label_html ?? '' ) . $bonus_claimed_html . '</div>';

	}
}
