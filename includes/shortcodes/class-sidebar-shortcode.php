<?php

class Sidebar_Shortcode {
	private string $template = 'sidebar-shortcode';

	/**
	 * The shortcode takes an id parameter.
	 * Example - [sidebar id='161429'], when id is a tag id on given taxonomy.
	 *
	 * @param $attributes
	 *
	 * @return string
	 */
	public function shortcode( $attributes ): string {

		$campaign_id = $attributes['id'];
		if ( empty( $campaign_id ) ) {
			return '';
		}

		$campaign_selector = 'bm_campaign_management_' . $campaign_id;

		$campaign_shortcode                    = new Campaign_Shortcode();
		$campaign_shortcode->taxonomy_selector = $campaign_selector;

		$offers_list = $campaign_shortcode->get_offers_list( $campaign_selector );
		if ( empty( $offers_list ) ) {
			return '';
		}

		$table_title = '';
		if ( ( bm_get_field( 'show_table_title', $campaign_selector ) ?? false ) !== false ) {
			$campaign_term = get_term_by( 'id', $campaign_id, 'bm_campaign_management' );
			$table_title   = do_shortcode( '<p class="widget-title">' . $campaign_term->name . '</p>' );
		}

		if ( ! empty( bm_get_field( 'active_mobile_slider', $campaign_selector ) ) ) {
			$class_mobile_slider = 'brand_mobile_slide';
			$js_slot             = $this->template_engine( 'sidebar-shortcode-template-js-slot', [] );
		}

		// Sidebar view options.
		$sidebar_view_options = bm_get_field( 'sidebar_section_appearance_settings', $campaign_selector );

		$is_show_cta_button        = $sidebar_view_options['sidebar_section_appearance_cta_on_off'] ?? 1;
		$ordinal_numbers_on_off    = $sidebar_view_options['sidebar_section_appearance_ordinal_numbers_on_off'] ?? 1;
		$is_show_updated_date      = $sidebar_view_options['sidebar_section_appearance_date_stamp_on_off'] ?? 1;
		$is_show_read_review_link  = $sidebar_view_options['sidebar_section_appearance_review_redirect_button_on_off'] ?? 1;
		$is_show_offer_description = $sidebar_view_options['sidebar_section_appearance_offer_text_on_off'] ?? 1;

		if ( $is_show_cta_button ) {
			$cta_button_type = $sidebar_view_options['sidebar_section_appearance_cta_text_or_button'] ?? 'button';
			$cta_button_text = $sidebar_view_options['sidebar_section_appearance_cta_button_text'] ?? '';
		}

		if ( $ordinal_numbers_on_off ) {
			$class_ordinal_numbers = 'ordinals_on';
			$show_counter_data     = 'bm-with-counter';
		}

		$section_appearance = $sidebar_view_options['sidebar_section_appearance_choose_appearance'] ?? 'view_1';
		$section_appearance = $section_appearance === 'default' ? 'view_1' : $section_appearance;

		$offers_list_html = '';

		$foreach_loop_counter = 1;
		foreach ( $offers_list as $offer_id ) {
			$google_conversion_url = $campaign_shortcode->get_google_conversion_url( $offer_id );
			$unique_visit_link     = $campaign_shortcode->get_unique_visit_link( $offer_id );

			if ( $is_show_offer_description ) {
				$offer_description = $campaign_shortcode->get_offer_description( $offer_id );
			}

			$class_brands_counter = '';
			if ( ( $section_appearance === 'default' ) && $foreach_loop_counter === 1 ) {
				$class_brands_counter = 'first-item-in-sidebar';
			}

			$updated_date = '';
			if ( $is_show_updated_date ) {
				$updated_date = $campaign_shortcode->print_updated_date( $offer_id );
			}

			if ( $is_show_read_review_link ) {
				$read_review_url  = bm_get_field( 'read_review_url', $offer_id );
				$read_review_text = _x( ' Review', 'recommended-offers-shortcode', 'brand-management' );
				$read_review_link = '';

				if ( ! empty( $read_review_url ) ) {
					$read_review_link = '
						<div class="read_review_url">
							<a href="' . $read_review_url . '">
								' . $read_review_text . '
							</a>
							<svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M1.4668 8.98193L5.4668 4.98193L1.4668 0.981934" stroke="#1C2642" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>';
				}
			}

			if ( $is_show_cta_button ) {
				if ( $cta_button_type === 'button' ) {
					$cta_button_class = '';

					if ( empty( $cta_button_text ) ) {
						$cta_button_text = _x( 'Claim Bonus', 'sidebar-shortcode', 'brand-management' );
					}

					$cta_button = '<a class="campaign-list-item_cta-btn" data-url="' . $google_conversion_url . '" href="' . $unique_visit_link . '" target="_blank" rel="nofollow">' . $cta_button_text . '</a>';
				} else {
					$cta_button_class = 'align_to_right';

					if ( empty( $cta_button_text ) ) {
						$cta_button_text = _x( 'Visit Site', 'sidebar-shortcode', 'brand-management' );
					}

					$cta_button = '<a class="campaign-sidebar-shortcode_see-more" data-url="' . $google_conversion_url . '" href="' . $unique_visit_link . '" target="_blank" rel="nofollow">' . $cta_button_text . '</a>';
				}
			}

			$offers_list_html .= $this->template_engine( 'sidebar-shortcode-template-brands-' . $section_appearance, [
				'{{ META-DATA }}'             => Campaign_Shortcode::get_editor_metadata_for_offer( $offer_id ),
				'{{ GOOGLE_CONVERSION_URL }}' => $google_conversion_url ?: '',
				'{{ VISIT-LINK-ATTRIBUTES }}' => bm_get_external_link_attributes( $offer_id ),
				'{{ REFERRAL_URL }}'          => $unique_visit_link,
				'{{ CLASS_BRANDS_COUNTER }}'  => $class_brands_counter,
				'{{ BRAND_LOGO }}'            => bm_get_optimized_logo( $offer_id, 'bm_small_thumbnail' ),
				'{{BRAND-NAME-SLOT}}'         => get_the_title( bm_get_field( 'brand_id', $offer_id ) ?: $offer_id ) ?? '',
				'{{ NEW_CUSTOMER }}'          => $offer_description ?? '',
				'{{ SCORE }}'                 => $campaign_shortcode->get_star_rating_text( $offer_id ),
				'{{ STAR_RATING }}'           => $campaign_shortcode->build_star_rating_html( $offer_id ),
				'{{ CTA_BUTTON }}'            => $cta_button ?? '',
				'{{ CTA_BUTTON_CLASS }}'      => $cta_button_class ?? '',
				'{{ SHOW_COUNTER }}'          => $show_counter_data ?? '',
				'{{ REVIEW_LINK }}'           => $read_review_link ?? '',
				'{{ UPDATED_DATE }}'          => $updated_date,
			] );

			$foreach_loop_counter ++;
		}

		Brand_Management_Public::load_assets( 'brand-management-sidebar-shortcode' );

		return $this->template_engine( 'sidebar-shortcode-template', [
			'{{ JS_SLOT }}'               => $js_slot ?? '',
			'{{ META-DATA }}'             => Campaign_Shortcode::get_editor_metadata_for_campaign( $campaign_id ),
			'{{ TABLE_TITLE }}'           => $table_title,
			'{{ CLASS_MOBILE_SLIDER }}'   => $class_mobile_slider ?? '',
			'{{ CLASS_ORDINAL_NUMBERS }}' => $class_ordinal_numbers ?? '',
			'{{ CLASS_APPEARANCE }}'      => $section_appearance ?? '',
			'{{ BRANDS_SLOT }}'           => $offers_list_html,
		] );

	}

	private function template_engine( $template, $array ): string {
		return ( new Brand_Management_Templater( $this->template . '/' . $template . '.html' ) )->build_html_with_replacings( $array );
	}
}
