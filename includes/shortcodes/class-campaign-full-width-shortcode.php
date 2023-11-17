<?php

class Campaign_Full_Width_Shortcode {
	private array $atts;
	public string $taxonomy_selector;
	private int $view_attr;
	private Campaign_Shortcode $campaign_shortcode;

	public function shortcode( $atts ): string {

		$this->atts = $atts;
		if ( empty( $this->atts['id'] ) ) {
			return '';
		}

		$this->taxonomy_selector = 'bm_campaign_management_' . $this->atts['id'];

		$this->view_attr = 25;
		if ( ! empty( $this->atts['view'] ) ) {
			$this->view_attr = (int) $atts['view'];
		}

		$this->campaign_shortcode                           = new Campaign_Shortcode();
		$this->campaign_shortcode->taxonomy_selector        = $this->taxonomy_selector;
		$this->campaign_shortcode->main_taxonomy_selector   = $this->taxonomy_selector;
		$this->campaign_shortcode->template_sorting_section = 'campaign-full-width-shortcode/sorting.html';
		$this->campaign_shortcode->template_filters_section = 'campaign-full-width-shortcode/sorting-filters-section-slot-template.html';
		$this->campaign_shortcode->template_tags_select     = 'campaign-full-width-shortcode/tags-select.html';
		$this->campaign_shortcode->template_tags_tabs       = 'campaign-full-width-shortcode/tags-tabs.html';
		$this->campaign_shortcode->filter_tags_css_selector = 'campaign-full-width';

		$offers_list = $this->campaign_shortcode->get_offers_list( $this->taxonomy_selector );
		if ( empty( $offers_list ) ) {
			return '';
		}

		if ( count( $offers_list ) !== 1 && $this->campaign_shortcode->is_show_filter_tags() ) {
			$filters_section = $this->campaign_shortcode->build_filters_section_html( $offers_list );
		}

		Brand_Management_Public::load_assets( 'brand-management-campaign-full-width-shortcode' );
		wp_enqueue_script( 'brand-management-campaign-shortcode',
			( new Brand_Management_Template_Loader() )->search_path( 'js/brand-management-campaign-shortcode.js' ), [ 'jquery' ], null, true );

		$templater = $this->campaign_shortcode->get_templater( 'campaign-full-width-shortcode/template.html' );

		return $templater->build_html_with_replacings( [
			'{{ CAMPAIGN_VOTING_TABLE_CLASS }}'    => $this->campaign_shortcode->is_show_likes() ? 'campaign__voting_table' : '',
			'{{TABLE-TITLE-SLOT}}'                 => $this->campaign_shortcode->build_table_title_html( $this->atts['id'] ),
			'{{ID-SLOT}}'                          => $this->atts['id'],
			'{{IS-FILTER-TAGS-DEACTIVATED-CLASS}}' => $this->campaign_shortcode->is_show_filter_tags(),
			'{{FILTER-SECTION-SLOT}}'              => $filters_section ?? '',
			'{{FULLWIDTH-SLIDE-CLASS-SLOT-DATA}}'  => $this->get_shortcode_slider_class(),
			'{{OFFERS-LIST-SLOT}}'                 => $this->build_offers_list( $offers_list ),
			'{{META-DATA}}'                        => Campaign_Shortcode::get_editor_metadata_for_campaign( $this->atts['id'] ),
		] );

	}

	private function build_offers_list( $offers_list ): string {

		$offers_list_html = '';

		$foreach_loop_counter = 1;
		foreach ( $offers_list as $offer_id ) {
			$template_elements = [
				'{{TOOLTIP-ICON-SLOT-DATA}}'     => $this->build_tile_back_side_button_html( $offer_id ),
				'{{CATEGORIES-TAG-SLOT}}'        => $this->campaign_shortcode->get_brand_tags( $offer_id ),
				'{{ID-SLOT}}'                    => $offer_id,
				'{{META-DATA}}'                  => Campaign_Shortcode::get_editor_metadata_for_offer( $offer_id ),
				'{{DATE-SLOT}}'                  => strtotime( get_the_modified_date( 'F d, Y g:i a', $offer_id ) ) ?: 'undefined',
				'{{TOP-PICKER-DATA-SLOT}}'       => $this->build_top_picker_html( $offer_id ),
				'{{REFERRAL-URL-SLOT}}'          => $this->campaign_shortcode->get_unique_visit_link( $offer_id ),
				'{{BRAND-LOGO-IMG-SRC-SLOT}}'    => bm_get_optimized_logo( $offer_id, 'bm_middle_thumbnail' ),
				'{{BRAND-NAME-SLOT}}'            => $this->campaign_shortcode->get_brand_name( $offer_id ),
				'{{NEW-CUSTOMER-SLOT}}'          => $this->campaign_shortcode->get_offer_description( $offer_id ),
				'{{COUPON-CODE-SLOT}}'           => $this->build_coupon_code_html( $offer_id ),
				'{{GOOGLE-CONVERSION-URL-SLOT}}' => $this->campaign_shortcode->get_google_conversion_url( $offer_id ),
				'{{VISIT-LINK-ATTRIBUTES}}'      => bm_get_external_link_attributes( $offer_id ),
				'{{SCORES-SLOT}}'                => $this->campaign_shortcode->get_star_rating_text( $offer_id ),
				'{{RATING-IMG-SLOT}}'            => $this->campaign_shortcode->build_star_rating_html( $offer_id ),
				'{{ READ_REVIEW_BUTTON }}'       => $this->build_read_review_html( $offer_id ),
				'{{CALL-TO-ACTION-BUTTON-SLOT}}' => $this->campaign_shortcode->get_cta_button_label(),
				'{{BOTTOM-TEXT-SLOT}}'           => $this->build_terms_and_conditions_html( $offer_id ),
				'{{ VOTING_SECTION }}'           => $this->campaign_shortcode->print_voting_section( $offer_id ),
				'{{ DATE_TIME_HIDDEN_CLASS }}'   => $this->campaign_shortcode->is_show_date_time() ? '' : 'campaign_fullwidth__date_time_hidden',
				'{{ FORMATTED_DATE }}'           => $this->campaign_shortcode->is_show_date_time() ? $this->get_formatted_date( $offer_id ) : '',
				'{{ DATE_POSTED_TEXT }}'         => _x( 'Date Posted', 'campaign-full-width-shortcode', 'brand-management' ),
				'{{TOOLTIP-SLOT-DATA}}'          => $this->build_tile_back_side_html( $offer_id ),
				'{{CSS-DISPLAY-SLOT}}'           => '',
			];

			if ( ! empty( $this->atts['slider'] ) && $this->atts['slider'] ) {
				$templater = $this->campaign_shortcode->get_templater( 'campaign-full-width-shortcode/slider-item-template.html' );
			} else {
				$templater = $this->campaign_shortcode->get_templater( 'campaign-full-width-shortcode/tile-item-template.html' );

				if ( $foreach_loop_counter >= $this->view_attr + 1 ) {
					$template_elements['{{CSS-DISPLAY-SLOT}}'] = 'style="display: none;"';

					if ( $foreach_loop_counter === $this->view_attr + 1 ) {
						$offers_list_html .= $this->build_show_more_offers_button_html( count( $offers_list ) );
					}
				}
			}

			$offers_list_html .= $templater->build_html_with_replacings( $template_elements );

			$foreach_loop_counter ++;
		}

		return $offers_list_html;

	}

	private function get_shortcode_slider_class(): string {

		if ( ! empty( $this->atts['slider'] ) && $this->atts['slider'] == 1 ) {
			return 'campaign-fullwidth-shortcode-slider';
		}

		return 'campaign-fullwidth-shortcode-not-slider';

	}

	private function get_formatted_date( $offer_id ): string {

		$offer_date_added = get_the_date( 'U', $offer_id );

		$time_passed_since_adding = current_time( 'timestamp' ) - $offer_date_added;
		if ( $time_passed_since_adding < 60 * 60 ) {
			$formatted_date = round( $time_passed_since_adding / 60 ) . _x( 'm', 'campaign-full-width-shortcode', 'brand-management' );
		} elseif ( $time_passed_since_adding < 60 * 60 * 24 ) {
			$formatted_date = floor( $time_passed_since_adding / ( 60 * 60 ) ) . _x( 'h', 'campaign-full-width-shortcode', 'brand-management' );
		} else {
			$formatted_date = get_the_date( bm_get_option( 'date_format' ) ?: 'd/m/y', $offer_id );
		}

		return $formatted_date;

	}

	private function build_tile_back_side_html( $offer_id ): string {

		$tile_back_side_html = '';

		$rows_on_card_back_side = bm_get_field( 'rows_on_card_back_side', $offer_id, true, '', true ) ?: '';
		if ( ! empty( $rows_on_card_back_side ) ) {
			foreach ( $rows_on_card_back_side as $parameter ) {
				if ( $parameter['type'] === 'icons_yes_no' ) {
					if ( $parameter['value'] === 'yes' ) {
						$value_slot = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M16 8C16 12.4183 12.4183 16 8 16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8Z" fill="#0F9960"/>
											<path d="M11.1098 4L7.07766 9.52321L4.69375 7.04821L3.59375 8.19107L7.25984 12L12.3937 5.14286L11.1098 4Z" fill="white"/>
										</svg>';
					} else {
						$value_slot = '<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path fill-rule="evenodd" clip-rule="evenodd" d="M16.5 8C16.5 12.4183 12.9183 16 8.5 16C4.08172 16 0.5 12.4183 0.5 8C0.5 3.58172 4.08172 0 8.5 0C12.9183 0 16.5 3.58172 16.5 8ZM12.005 4.4929C12.3955 4.88342 12.3955 5.51658 12.005 5.90711L9.91213 8L12.005 10.0929C12.3955 10.4834 12.3955 11.1166 12.005 11.5071C11.6145 11.8976 10.9813 11.8976 10.5908 11.5071L8.49792 9.41422L6.40502 11.5071C6.0145 11.8976 5.38133 11.8976 4.99081 11.5071C4.60029 11.1166 4.60029 10.4834 4.99081 10.0929L7.0837 8L4.99081 5.90711C4.60029 5.51658 4.60029 4.88342 4.99081 4.4929C5.38133 4.10237 6.0145 4.10237 6.40502 4.4929L8.49792 6.58579L10.5908 4.4929C10.9813 4.10237 11.6145 4.10237 12.005 4.4929Z" fill="#C6CDE0"/>
										</svg>';
					}
				} else {
					$value_slot = $parameter['value'];
				}

				$tile_back_side_html .= $this->campaign_shortcode->get_templater( 'campaign-full-width-shortcode/back-side-card-content-template.html' )->build_html_with_replacings( [
					'{{TITLE-SLOT}}' => $parameter['name'],
					'{{VALUE-SLOT}}' => $value_slot,
				] );
			}
		}

		return $tile_back_side_html;

	}

	private function build_tile_back_side_button_html( $offer_id ): string {

		$tile_back_side_button_html = '';

		$rows_on_card_back_side = bm_get_field( 'rows_on_card_back_side', $offer_id, true, '', true ) ?: '';
		if ( ! empty( $rows_on_card_back_side ) ) {
			$tile_back_side_button_html = '
				<div class="slider-item-tooltip">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M7.9987 14.6666C11.6806 14.6666 14.6654 11.6819 14.6654 7.99998C14.6654 4.31808 11.6806 1.33331 7.9987 1.33331C4.3168 1.33331 1.33203 4.31808 1.33203 7.99998C1.33203 11.6819 4.3168 14.6666 7.9987 14.6666Z" stroke="#3CB371" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M8 10.6667V8" stroke="#3CB371" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M8 5.33331H8.00667" stroke="#3CB371" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</div>';
		}

		return $tile_back_side_button_html;

	}

	private function build_read_review_html( $offer_id ): string {

		$read_review_url = bm_get_field( 'read_review_url', $offer_id ) ?: '';
		if ( ! empty( $read_review_url ) ) {
			$read_review_button_label = bm_get_field( 'read_review_button_label', $offer_id ) ?: _x( 'Review', 'campaign-full-width-shortcode',
				'brand-management' );

			$read_review_html = '<a href="' . $read_review_url . '">' . $read_review_button_label . '<svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.06641 9L5.06641 5L1.06641 1" stroke="#1C2642" stroke-linecap="round" stroke-linejoin="round"/></svg></a>';
		}

		return $read_review_html ?? '';

	}

	private function build_terms_and_conditions_html( $offer_id ): string {

		$terms_and_conditions_html = '';

		$terms_and_conditions = bm_get_field( 'terms_and_conditions', $offer_id ) ?: '';
		if ( ! empty( $terms_and_conditions ) ) {
			$cropped_terms_and_conditions_desktop = bm_trim_text( strip_tags( $terms_and_conditions ), 30 );
			$cropped_terms_and_conditions_mobile  = bm_trim_text( strip_tags( $terms_and_conditions ), 60 );

			$terms_and_conditions_html = '
										<div class="cell_bottom">
											<span class="cell_bottom_desktop">' . $cropped_terms_and_conditions_desktop . '</span>
											<span class="cell_bottom_mobile">' . $cropped_terms_and_conditions_mobile . '</span>
											<p class="hidden-tip">' . esc_html( $terms_and_conditions ) . '</p>
										</div>';
		}

		return $terms_and_conditions_html ?: '<div class="empty_terms_and_conditions"></div>';

	}

	private function build_show_more_offers_button_html( $offers_list_count ): string {

		$number_of_offers_to_display = $offers_list_count - $this->view_attr;

		return '<p class="show-more-items">
					<button>' . str_replace( '%', $number_of_offers_to_display,
				_x( 'Show % more offers +', 'campaign-full-width-shortcode', 'brand-management' ) ) . '</button>
				</p>';

	}

	private function build_top_picker_html( $offer_id ): string {

		$coupon_code_is_not_empty = ! empty( bm_get_field( 'coupon_code', $offer_id ) );

		$bonus_claimed_html = '';
		if ( $coupon_code_is_not_empty ) {
			$bonus_taken_count = bm_get_field( 'bonus_taken_count', $offer_id );
			if ( ! empty( $bonus_taken_count ) ) {
				$bonus_claimed_html = '<div class="brand_boun_claimed"><span>' . $bonus_taken_count . ' ' . _x( 'Codes claimed',
						'campaign-full-width-shortcode', 'brand-management' ) . '</span></div>';
			} else {
				$bonus_claimed_html = '<div class="brand_boun_claimed"><span>' . ( rand( 100, 999 ) ) . ' ' . _x( 'Codes claimed',
						'campaign-full-width-shortcode', 'brand-management' ) . '</span></div>';
			}
		}

		return '<div class="toppike-out">' . $bonus_claimed_html . '</div>';

	}

	private function build_coupon_code_html( $offer_id ): string {

		if ( $this->campaign_shortcode->is_show_coupon_codes() === false ) {
			return '';
		}

		$coupon_code = bm_get_field( 'coupon_code', $offer_id );
		if ( ! empty( $coupon_code ) ) {
			$coupon_code_html = '
				<div class="country_code_left">
					<span class="copiedtext">
						<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M1.37109 5.92015L4.5107 9.05975L10.63 2.94043" stroke="#1C2642" stroke-width="2"/>
						</svg>
						' . _x( 'Copied', 'campaign-full-width-shortcode', 'brand-management' ) . '
					</span>
					<input type="text" value="' . $coupon_code . '" readonly id="coupen_' . $offer_id . '">
				</div>
				<div class="country_code_right" data-toggle="tooltip" data-placement="bottom">
					<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M14.75 2.0835H7.625C6.75417 2.0835 6.04167 2.796 6.04167 3.66683V13.1668C6.04167 14.0377 6.75417 14.7502 7.625 14.7502H14.75C15.6208 14.7502 16.3333 14.0377 16.3333 13.1668V3.66683C16.3333 2.796 15.6208 2.0835 14.75 2.0835ZM14.75 13.1668H7.625V3.66683H14.75V13.1668ZM2.875 12.3752V10.7918H4.45833V12.3752H2.875ZM2.875 8.021H4.45833V9.60433H2.875V8.021ZM8.41667 16.3335H10V17.9168H8.41667V16.3335ZM2.875 15.146V13.5627H4.45833V15.146H2.875ZM4.45833 17.9168C3.5875 17.9168 2.875 17.2043 2.875 16.3335H4.45833V17.9168ZM7.22917 17.9168H5.64583V16.3335H7.22917V17.9168ZM11.1875 17.9168V16.3335H12.7708C12.7708 17.2043 12.0583 17.9168 11.1875 17.9168ZM4.45833 5.25016V6.8335H2.875C2.875 5.96266 3.5875 5.25016 4.45833 5.25016Z" fill="#1C2642"/>
					</svg>
					<div class="tooltip-inner-adv">
						' . _x( 'Copy', 'campaign-full-width-shortcode', 'brand-management' ) . '
					</div>
				</div>';
		} else {
			$coupon_code_html = '
				<div class="country_code_left">
					<div class="coupen_code_inner no_code_required">
						<input type="text" value="' . _x( 'NO CODE REQUIRED', 'campaign-full-width-shortcode',
					'brand-management' ) . '" readonly id="coupen_' . $offer_id . '">
					</div>
				</div>';
		}

		return $coupon_code_html;

	}
}
