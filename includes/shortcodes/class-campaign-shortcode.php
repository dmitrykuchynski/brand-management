<?php

class Campaign_Shortcode {
	protected array $atts;
	protected string $taxonomy_name = 'bm_campaign_management';
	public string $taxonomy_selector;
	public string $main_taxonomy_selector;
	public static int $regional_campaign_id = 0;
	public string $template_sorting_section = 'campaign-shortcode-common/sorting.html';
	public string $template_filters_section = 'campaign-shortcode-common/sorting-filters-section-template.html';
	public string $template_tags_select = 'campaign-shortcode-common/tags-select.html';
	public string $template_tags_tabs = 'campaign-shortcode-common/tags-tabs.html';
	public string $filter_tags_css_selector = 'campaign-shortcode-table';
	protected string $updated_date_format = '';
	protected bool $ip_geo_filters = false;
	protected bool $show_date_time;
	protected bool $show_likes;
	protected string $country_code = '';
	protected array $user_ip_info = [];

	public function shortcode( $atts ): string {

		if ( ! empty( $atts['reviews'] ) ) {
			$atts['id'] = $atts['reviews'];
			unset( $atts['reviews'] );
		}

		$this->atts  = $atts;
		$id_attr     = $this->atts['id'] ?? '';
		$show_attr   = $this->atts['show'] ?? '';
		$filter_attr = $this->atts['filter'] ?? '';

		if ( empty( $id_attr ) ) {
			return '';
		}

		$this->taxonomy_selector = $this->main_taxonomy_selector = 'bm_campaign_management_' . $id_attr;

		$this->init_geo_filters();

		Brand_Management_Storage::set_campaign( $id_attr, [ 'is_show_disclaimer' => $this->is_show_disclaimer() ] );

		$offers_list = $this->get_offers_list( $this->taxonomy_selector );
		if ( ! empty( $offers_list ) ) {
			if ( ! empty( $show_attr ) ) {
				return $this->build_tiles_shortcode( $offers_list );
			}

			if ( $filter_attr === 'true' ) {
				return $this->build_campaign_shortcode_with_filter( $offers_list );
			}

			return $this->build_campaign_shortcode( $offers_list );
		}

		return '';

	}

	protected function init_geo_filters(): void {

		$this->ip_geo_filters = self::get_ip_geo_filters_status( $this->main_taxonomy_selector );
		if ( $this->ip_geo_filters ) {
			$this->country_code = self::get_country_code();

			$linked_regional_campaigns = bm_get_field( 'linked_regional_campaigns', $this->main_taxonomy_selector ) ?? [];
			if ( ! empty( $linked_regional_campaigns ) ) {
				$regional_campaign_key = array_search( $this->country_code, array_column( $linked_regional_campaigns, 'regional_campaign_country' ), true );

				if ( $regional_campaign_key !== false ) {
					self::$regional_campaign_id = (int) $linked_regional_campaigns[ $regional_campaign_key ]['regional_campaign_id'];

					$this->taxonomy_selector = 'bm_regional_campaigns_' . self::$regional_campaign_id;
				}
			}
		}

	}

	public function single_offer_shortcode( $atts ): string {

		$offer_id = $atts['id'] ?? '';
		if ( ! empty( $offer_id ) && ! self::is_global_active( $offer_id ) ) {
			return '';
		}

		$this->taxonomy_selector = $this->main_taxonomy_selector = '';

		Brand_Management_Public::load_assets( 'brand-management-campaign-shortcode' );
		$this->try_enqueue_script( 'brand-management-campaign-shortcode' );

		$templater = $this->get_templater( 'single-offer-shortcode/single-offer-template.html' );

		return $templater->build_html_with_replacings( [
			'{{ CAMPAIGN_VOTING_TABLE_CLASS }}' => $this->is_show_likes() ? 'campaign__voting_table' : '',
			'{{OFFERS-LIST-SLOT}}'              => $this->build_offers_list_for_campaign_shortcode( [ $offer_id ] ),
		] );

	}

	private function build_tiles_shortcode( $offers_list ): string {

		Brand_Management_Public::load_assets( 'brand-management-campaign-no-empty-show-shortcode' );
		$this->try_enqueue_script( 'brand-management-campaign-shortcode' );

		$templater = $this->get_templater( 'campaign-no-empty-id-no-empty-show-shortcode/no-empty-id-no-empty-show-template.html' );

		return $templater->build_html_with_replacings( [
			'{{OFFERS-LIST-SLOT}}' => $this->build_offers_list_for_tiles_shortcode( $offers_list ),
			'{{META-DATA}}'        => self::get_editor_metadata_for_campaign( $this->atts['id'] ),
		] );

	}

	private function build_offers_list_for_tiles_shortcode( $offers_list = [] ): string {

		$offers_list_html = '';

		$lt_9_template_path = 'campaign-no-empty-id-no-empty-show-shortcode/lt-9.html';
		$eq_9_template_path = 'campaign-no-empty-id-no-empty-show-shortcode/eq-9.html';
		$mt_9_template_path = 'campaign-no-empty-id-no-empty-show-shortcode/mt-9.html';

		$foreach_loop_counter = 1;
		foreach ( $offers_list as $offer_id ) {
			$template_elements = [
				'{{META-DATA}}'                 => self::get_editor_metadata_for_offer( $offer_id ),
				'{{ID-SLOT}}'                   => $offer_id,
				'{{REFERRAL_URL-SLOT}}'         => $this->get_unique_visit_link( $offer_id ),
				'{{LINK-ATTRIBUTES-SLOT-DATA}}' => bm_get_external_link_attributes( $offer_id ),
				'{{TOP-PICKER-DATA-SLOT}}'      => $this->build_highlighted_label_html( $offer_id ),
				'{{BRAND-LOGO-IMG-SRC-SLOT}}'   => $this->get_brand_logo_src( $offer_id ),
				'{{BRAND-NAME-SLOT}}'           => $this->get_brand_name( $offer_id ),
				'{{SCORE-SLOT}}'                => $this->get_star_rating_text( $offer_id ),
				'{{RATING-IMG-SLOT}}'           => $this->build_star_rating_html( $offer_id ),
				'{{NEW-CUSTOMER-SLOT-DATA}}'    => $this->get_offer_description( $offer_id ),
				'{{CLAIM-OFFER-TEXT-SLOT}}'     => _x( 'Claim Offer', 'campaign-shortcode-show-all', 'brand-management' ),
				'{{ UPDATED_DATE_SLOT }}'       => $this->print_updated_date( $offer_id ),
			];

			if ( $foreach_loop_counter <= 8 ) {
				$templater = $this->get_templater( $lt_9_template_path );
			} elseif ( $foreach_loop_counter === 9 ) {
				$show_amount_text                              = _x( 'Show All (%number%+)', 'campaign-shortcode-show-all', 'brand-management' );
				$show_amount_number                            = count( $offers_list ) - $foreach_loop_counter;
				$template_elements['{{SHOW-ALL-AMOUNT-SLOT}}'] = str_replace( '%number%', $show_amount_number, $show_amount_text );
				$templater                                     = $this->get_templater( $eq_9_template_path );
			} else {
				$templater = $this->get_templater( $mt_9_template_path );
			}

			$offers_list_html .= $templater->build_html_with_replacings( $template_elements );

			$foreach_loop_counter ++;
		}

		return $offers_list_html;

	}

	private function build_campaign_shortcode( $offers_list ): string {

		if ( count( $offers_list ) !== 1 && $this->is_show_filter_tags() ) {
			$filters_section = $this->build_filters_section_html( $offers_list );
		}

		Brand_Management_Public::load_assets( 'brand-management-campaign-shortcode' );
		$this->try_enqueue_script( 'brand-management-campaign-shortcode' );

		$templater = $this->get_templater( 'campaign-no-empty-id-shortcode/no-empty-id-template.html' );

		return $templater->build_html_with_replacings( [
			'{{ CAMPAIGN_VOTING_TABLE_CLASS }}' => $this->is_show_likes() ? 'campaign__voting_table' : '',
			'{{ IP_GEO_FILTERS_CLASS }}'        => $this->ip_geo_filters ? 'campaign_with_geo_filters' : '',
			'{{TABLE-TITLE-SLOT}}'              => $this->build_table_title_and_disclaimer_html( $this->atts['id'] ),
			'{{ID-SLOT}}'                       => $this->atts['id'],
			'{{FILTERS-SECTION-SLOT}}'          => $filters_section ?? '',
			'{{OFFERS-LIST-SLOT}}'              => $this->build_offers_list_for_campaign_shortcode( $offers_list ),
			'{{META-DATA}}'                     => self::get_editor_metadata_for_campaign( $this->atts['id'] ),
		] );

	}

	private function build_offers_list_for_campaign_shortcode( $offers_list = [] ): string {

		$offers_list_html = '';

		$foreach_loop_counter = 1;
		foreach ( $offers_list as $offer_id ) {
			$coupon_code_slot = $this->build_coupon_code_html( $offer_id );
			$metadata_html    = $this->build_metadata_html( $offer_id );

			$templater = $this->get_templater( 'campaign-no-empty-id-shortcode/list-item-template.html' );

			$offers_list_html .= $templater->build_html_with_replacings( [
				'{{IS-NOT-FIRST-ITEM-CLASS-SLOT}}' => $foreach_loop_counter === 1 ? 'brand_table_custom_cl' : '',
				'{{CATEGORIES-TAG-SLOT}}'          => $this->get_brand_tags( $offer_id ),
				'{{ID-SLOT}}'                      => $offer_id,
				'{{META-DATA}}'                    => self::get_editor_metadata_for_offer( $offer_id ),
				'{{LINK-ATTRIBUTES-SLOT-DATA}}'    => bm_get_external_link_attributes( $offer_id ),
				'{{SCORE-SLOT}}'                   => $this->get_star_rating_text( $offer_id ),
				'{{DATE-SLOT}}'                    => $this->get_offer_update_date( $offer_id ),
				'{{ BASIC_POSITION }}'             => $foreach_loop_counter,
				'{{TOP-PICKER-DATA-SLOT}}'         => $this->build_top_picker_html( $offer_id, $foreach_loop_counter ),
				'{{REFERRAL-URL-SLOT}}'            => $this->get_unique_visit_link( $offer_id ),
				'{{RATING-IMG-SLOT}}'              => $this->build_star_rating_html( $offer_id ),
				'{{BRAND-LOGO-IMG-SRC-SLOT}}'      => $this->get_brand_logo_src( $offer_id ),
				'{{BRAND-NAME-SLOT}}'              => $this->get_brand_name( $offer_id ),
				'{{NEW-CUSTOMER-SLOT-DATA}}'       => $this->get_offer_description( $offer_id ),
				'{{PRINCIPALES-LIST-SLOT}}'        => $this->build_key_features_html( $offer_id ),
				'{{COUPON-CODE-SLOT}}'             => $coupon_code_slot['coupon_code_slot'],
				'{{COUPON-CODE-TOOLTIP-SLOT}}'     => $coupon_code_slot['coupon_code_tooltip_slot'],
				'{{GOOGLE-CONVERSION-URL-SLOT}}'   => $this->get_google_conversion_url( $offer_id ),
				'{{CALL-TO-ACTION-BTN-TEXT-SLOT}}' => $this->get_cta_button_label(),
				'{{READ-REVIEW-URL-SLOT}}'         => $this->build_read_review_html( $offer_id ),
				'{{BOTTOM-TEXT-SLOT}}'             => $this->build_terms_and_conditions_html( $offer_id ),
				'{{METADATA-BLOCK-SLOT}}'          => $metadata_html,
				'{{SHOW-MORE-BUTTON-CLASS}}'       => empty( $metadata_html ) ? 'hidden' : '',
				'{{LEARN-MORE-TEXT-SLOT}}'         => _x( 'Learn More', 'campaign-shortcode', 'brand-management' ),
				'{{CLOSE-LEARN-MORE-TEXT-SLOT}}'   => _x( 'Close Learn More', 'campaign-shortcode', 'brand-management' ),
				'{{ VOTING_SECTION }}'             => $this->print_voting_section( $offer_id ),
				'{{ UPDATED_DATE_SLOT }}'          => $this->print_updated_date( $offer_id ),
				'{{NO_COUPON}}'                    => $coupon_code_slot['cta_button_no_coupon_class'],
				'{{SHOW-COUNTER}}'                 => $this->is_show_offers_counter() ? 'bm-with-counter' : '',
			] );

			$foreach_loop_counter ++;
		}

		return $offers_list_html;

	}

	private function build_campaign_shortcode_with_filter( $offers_list = [] ): string {

		$show_more_btn = '<div class="show-more-campaign-list-items">' . $this->get_show_more_btn_text() . '</div>';
		if ( count( $offers_list ) <= ( $this->atts['display'] ?? 5 ) ) {
			$show_more_btn = '';
		}

		if ( count( $offers_list ) !== 1 && $this->is_show_filter_tags() ) {
			$filters_section = $this->build_filters_section_html( $offers_list );
		}

		// Determine whether ajax loading is necessary.
		if ( count( $offers_list ) > ( $this->atts['display'] ?? 5 ) ) {
			$require_ajax_loading_class = 'require_ajax_loading';
		}

		Brand_Management_Public::load_assets( 'brand-management-campaign-shortcode' );
		$this->try_enqueue_script( 'brand-management-campaign-shortcode' );

		$templater = $this->get_templater( 'campaign-no-empty-id-true-filter-empty-show-shortcode/no-empty-id-true-filter-empty-show-template.html' );

		return $templater->build_html_with_replacings( [
			'{{ID-SLOT}}'                       => $this->atts['id'],
			'{{ REQUIRE_AJAX_LOADING_CLASS }}'  => $require_ajax_loading_class ?? '',
			'{{ CAMPAIGN_VOTING_TABLE_CLASS }}' => $this->is_show_likes() ? 'campaign__voting_table' : '',
			'{{ IP_GEO_FILTERS_CLASS }}'        => $this->ip_geo_filters ? 'campaign_with_geo_filters' : '',
			'{{ CAMPAIGN_ATTS }}'               => 'data-atts-filter="' . $this->atts['filter'] . '" data-atts-display="' . ( $this->atts['display'] ?? 5 ) . '"',
			'{{ OFFERS_COUNT }}'                => count( $offers_list ),
			'{{FILTERS-SECTION-SLOT}}'          => $filters_section ?? '',
			'{{TABLE-TITLE-SLOT}}'              => $this->build_table_title_and_disclaimer_html( $this->atts['id'] ),
			'{{OFFERS-LIST-SLOT}}'              => $this->build_offers_list_for_campaign_shortcode_with_filter( $offers_list ),
			'{{ SHOW_MORE_BTN }}'               => $show_more_btn,
			'{{META-DATA}}'                     => self::get_editor_metadata_for_campaign( $this->atts['id'] ),
		] );

	}

	private function build_offers_list_for_campaign_shortcode_with_filter( $offers_list ): string {

		$offers_list_html = '';

		$is_ajax_loading = $this->atts['ajax'] ?? false;

		$rebuild_campaign_table = $this->atts['rebuild_campaign_table'] ?? false;
		if ( $rebuild_campaign_table ) {
			$is_ajax_loading = false;
		}

		if ( isset( $this->atts['display'] ) && $this->atts['display'] ) {
			$show_offers_count = $this->atts['display'];
		} else {
			$show_offers_count = 5;
		}

		// We load only the remaining offers when requesting ajax.
		if ( $is_ajax_loading ) {
			$offers_list = array_slice( $offers_list, $show_offers_count, count( $offers_list ) );
		} elseif ( $rebuild_campaign_table === false ) {
			$offers_list = array_slice( $offers_list, 0, $show_offers_count );
		}

		$foreach_loop_counter = 1;
		if ( $is_ajax_loading ) {
			$foreach_loop_counter = $show_offers_count + 1;
		}
		foreach ( $offers_list as $offer_id ) {
			$coupon_code_slot = $this->build_coupon_code_html( $offer_id );
			$metadata_html    = $this->build_metadata_html( $offer_id );

			$template_elements = [
				'{{ID-SLOT}}'                      => $offer_id,
				'{{META-DATA}}'                    => self::get_editor_metadata_for_offer( $offer_id ),
				'{{LINK-ATTRIBUTES-SLOT-DATA}}'    => bm_get_external_link_attributes( $offer_id ),
				'{{IS-NOT-FIRST-ITEM-CLASS-SLOT}}' => $foreach_loop_counter === 1 ? 'brand_table_custom_cl' : '',
				'{{CATEGORIES-TAG-SLOT}}'          => $this->get_brand_tags( $offer_id ),
				'{{BRAND-LIST-ITEM-SLOT}}'         => $offer_id,
				'{{SCORE-SLOT}}'                   => $this->get_star_rating_text( $offer_id ),
				'{{DATE-SLOT}}'                    => $this->get_offer_update_date( $offer_id ),
				'{{TOP-PICKER-DATA-SLOT}}'         => $this->build_top_picker_html( $offer_id, $foreach_loop_counter ),
				'{{REFERRAL-URL-SLOT-DATA}}'       => $this->get_unique_visit_link( $offer_id ),
				'{{BRAND-LOGO-IMG-SRC-SLOT}}'      => $this->get_brand_logo_src( $offer_id ),
				'{{BRAND-NAME-SLOT}}'              => $this->get_brand_name( $offer_id ),
				'{{NEW-CUSTOMER-SLOT-DATA}}'       => $this->get_offer_description( $offer_id ),
				'{{PRINCIPALES-LIST-SLOT}}'        => $this->build_key_features_html( $offer_id ),
				'{{COUPON-CODE-SLOT}}'             => $coupon_code_slot['coupon_code_slot'],
				'{{COUPON-CODE-TOOLTIP-SLOT}}'     => $coupon_code_slot['coupon_code_tooltip_slot'],
				'{{RATING-IMG-SLOT}}'              => $this->build_star_rating_html( $offer_id ),
				'{{READ-REVIEW-URL-SLOT}}'         => $this->build_read_review_html( $offer_id ),
				'{{GOOGLE-CONVERSION-URL-SLOT}}'   => $this->get_google_conversion_url( $offer_id ),
				'{{CALL-TO-ACTION-BTN-TEXT-SLOT}}' => $this->get_cta_button_label(),
				'{{BOTTOM-TEXT-SLOT}}'             => $this->build_terms_and_conditions_html( $offer_id ),
				'{{DISCLAIMER-TEXT-SLOT}}'         => $this->build_disclaimer_text_html( $offer_id ),
				'{{METADATA-BLOCK-SLOT}}'          => $metadata_html,
				'{{SHOW-MORE-BUTTON-CLASS}}'       => empty( $metadata_html ) ? 'hidden' : '',
				'{{LEARN-MORE-TEXT-SLOT}}'         => _x( 'Learn More', 'campaign-management', 'brand-management' ),
				'{{CLOSE-LEARN-MORE-TEXT-SLOT}}'   => _x( 'Close Learn More', 'campaign-management', 'brand-management' ),
				'{{ VOTING_SECTION }}'             => $this->print_voting_section( $offer_id ),
				'{{ UPDATED_DATE_SLOT }}'          => $this->print_updated_date( $offer_id ),
				'{{NO_COUPON}}'                    => $coupon_code_slot['cta_button_no_coupon_class'],
				'{{SHOW-COUNTER}}'                 => $this->is_show_offers_counter() ? 'bm-with-counter' : '',
			];

			if ( $is_ajax_loading ) {
				$template_elements['{{DISPLAY-NONE-STYLE}}'] = 'style="display: none !important;"';
			} elseif ( $foreach_loop_counter <= $show_offers_count ) {
				$template_elements['{{DISPLAY-NONE-STYLE}}'] = '';
			} else {
				$template_elements['{{DISPLAY-NONE-STYLE}}'] = 'style="display: none !important;"';
			}

			$templater = $this->get_templater( 'campaign-no-empty-id-true-filter-empty-show-shortcode/item-template.html' );

			$offers_list_html .= $templater->build_html_with_replacings( $template_elements );

			$foreach_loop_counter ++;
		}

		return $offers_list_html;

	}

	public function is_show_offers_counter(): bool {
		return (bool) get_field( 'show_offers_counter', $this->main_taxonomy_selector );
	}

	public function is_show_date_time(): bool {
		if ( empty( $this->show_date_time ) ) {
			$this->show_date_time = ( bm_get_option( 'show_date_time_in_campaigns' ) ?? true ) === true;
		}

		return $this->show_date_time;
	}

	public function is_show_likes(): bool {
		if ( empty( $this->show_likes ) ) {
			$this->show_likes = ( bm_get_option( 'show_likes_in_campaigns' ) ?? true ) === true;
		}

		return $this->show_likes;
	}

	private function build_disclaimer_text_html( $offer_id ): string {

		$disclaimer_text = bm_get_field( 'disclaimer_text', $offer_id );
		if ( ! empty( $disclaimer_text ) ) {
			$disclaimer_text_html = '<div class="disclaimer_text_brand"><span>' . $disclaimer_text . '<span></div>';
		}

		return $disclaimer_text_html ?? '';

	}

	private function build_highlighted_label_html( $offer_id ): string {

		$highlighted_label = bm_get_field( 'highlighted_label', $offer_id, true, $this->taxonomy_selector );
		if ( ! empty( $highlighted_label ) ) {
			$highlighted_label_html = '<div class="top-pick">' . $highlighted_label . '</div>';
		}

		return $highlighted_label_html ?? '';

	}

	public static function get_editor_metadata_for_campaign( $campaign_id ): string {

		$metadata = '';
		if ( is_user_logged_in() ) {
			$metadata = 'campaign_id="' . $campaign_id . '" link_to_edit="' . admin_url( 'term.php?taxonomy=bm_campaign_management&tag_ID=' . $campaign_id . '&post_type=brand' ) . '"';

			if ( self::$regional_campaign_id > 0 ) {
				$metadata .= ' regional_campaign_id="' . self::$regional_campaign_id . '"';
			}
		}

		return $metadata;

	}

	public static function get_editor_metadata_for_offer( $offer_id ): string {

		$metadata = '';
		if ( is_user_logged_in() ) {
			$metadata = 'offer_id="' . $offer_id . '" link_to_edit_offer="' . admin_url( 'post.php?post=' . $offer_id . '&action=edit' ) . '"';

			$brand_id = bm_get_brand_id( $offer_id );
			if ( ! empty( $brand_id ) && $brand_id !== $offer_id ) {
				$metadata .= ' brand_id="' . $brand_id . '" link_to_edit_brand="' . admin_url( 'post.php?post=' . $brand_id . '&action=edit' ) . '"';
			}
		}

		return $metadata;

	}

	private function build_metadata_html( $offer_id ): string {

		// minimum_deposit
		$minimum_deposit      = bm_get_field( 'minimum_deposit', $offer_id, true, '', true );
		$minimum_deposit_slot = '';
		if ( ! empty( $minimum_deposit ) ) {
			$minimum_deposit_slot = '<label>' . _x( 'Min Deposit', 'campaign-shortcode', 'brand-management' ) . '</label><span>' . $minimum_deposit . '</span>';
		}

		// deposit_method
		$deposit_method_slot = $this->get_deposit_methods_slot_html( $offer_id );

		// regulated_by
		$regulated_by = bm_get_field( 'regulated_by', $offer_id, true, '', true );
		if ( ! empty( $regulated_by ) ) {
			$regulated_by_slot = '<label>' . _x( 'Regulated By', 'campaign-shortcode', 'brand-management' ) . '</label><span>' . $regulated_by . '</span>';
		} else {
			$regulated_by_slot = '';
		}

		// license_no & license_link
		$license_no   = bm_get_field( 'license_no', $offer_id );
		$license_link = bm_get_field( 'license_link', $offer_id );
		$license_slot = '';
		if ( ! empty( $license_no ) ) {
			if ( ! empty( $license_link ) ) {
				$license_slot = '<div class="metadata_license"><label>' . _x( 'License No.', 'campaign-shortcode',
						'brand-management' ) . '</label><a href="' . $license_link . '" target="_blank" rel="noopener noreferrer"><span>' . $license_no . '</span></a></div>';
			} else {
				$license_slot = '<div class="metadata_license"><label>' . _x( 'License No.', 'campaign-shortcode',
						'brand-management' ) . '</label><span>' . $license_no . '</span></div>';
			}
		}

		// offer_conditions
		$offer_conditions      = bm_get_field( 'offer_conditions', $offer_id );
		$offer_conditions_slot = '';
		if ( ! empty( $offer_conditions ) ) {
			$offer_conditions_slot = '<div class="offer_term_section2"><label>' . _x( 'Learn More', 'campaign-shortcode-details-section',
					'brand-management' ) . '</label><div class="brand_term_description">' . $offer_conditions . '</div></div>';
		}

		// website
		$website      = bm_get_field( 'website', $offer_id, true, '', true );
		$website_slot = '';
		if ( ! empty( $website ) ) {
			$website_slot = '<label>' . _x( 'Website', 'campaign-shortcode', 'brand-management' ) . ':</label><span>' . $website . '</span>';
		}

		// owner
		$owner      = bm_get_field( 'owner', $offer_id, true, '', true );
		$owner_slot = '';
		if ( ! empty( $owner ) ) {
			$owner_slot = '<label>' . _x( 'Owner', 'campaign-shortcode', 'brand-management' ) . ':</label><span>' . $owner . '</span>';
		}

		// founded
		$founded      = bm_get_field( 'founded', $offer_id, true, '', true );
		$founded_slot = '';
		if ( ! empty( $founded ) ) {
			$founded_slot = '<label>' . _x( 'Founded', 'campaign-shortcode', 'brand-management' ) . ':</label><span>' . $founded . '</span>';
		}

		// headquarters
		$headquarters     = bm_get_field( 'headquarters', $offer_id, true, '', true );
		$headquarter_slot = '';
		if ( ! empty( $headquarters ) ) {
			$headquarter_slot = '<label>' . _x( 'Headquarters', 'campaign-shortcode', 'brand-management' ) . ':</label><span>' . $headquarters . '</span>';
		}

		// profile_url & profile_label
		$profile_url             = bm_get_field( 'profile_url', $offer_id, true, '', true, true );
		$profile_link_attributes = $this->try_append_attributes_if_external_link( $profile_url, $offer_id );
		$profile_label           = bm_get_field( 'profile_label', $offer_id, true, '', true, true ) ?: '';
		$profile_slot            = '';
		$profile_svg_arrow       = '<svg width="11" height="12" viewBox="0 0 11 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.09725 9.93479C3.97531 9.67185 4.0244 9.36129 4.2215 9.14879L6.51126 6.68014C6.86703 6.29658 6.86703 5.70362 6.51126 5.32006L4.2215 2.85141C4.0244 2.63891 3.97531 2.32834 4.09725 2.06541C4.31084 1.60485 4.9233 1.50803 5.26854 1.88024L8.45908 5.32006C8.81485 5.70362 8.81485 6.29658 8.45908 6.68014L5.26854 10.12C4.9233 10.4922 4.31084 10.3953 4.09725 9.93479Z" fill="#1C2642"/></svg>';
		if ( ! empty( $profile_url ) && ! empty( $profile_label ) ) {
			$profile_slot = '<a href="' . $profile_url . '" ' . $profile_link_attributes . '>' . $profile_label . $profile_svg_arrow . '</a>';
		}

		$no_data_about_offer_class  = '';
		$no_data_about_offer_notice = '';
		if ( empty( $minimum_deposit_slot ) && empty( $deposit_method_slot ) && empty( $regulated_by_slot ) && empty( $offer_conditions_slot ) && empty( $image_gallery_slot ) && empty( $website_slot ) && empty( $owner_slot ) && empty( $founded_slot ) && empty( $headquarter_slot ) ) {
			return '';
		}

		$no_contact_data_style = '';
		if ( empty( $website_slot ) && empty( $owner_slot ) && empty( $founded_slot ) && empty( $headquarter_slot ) ) {
			$no_contact_data_style = 'style="display: none;"';
		}

		$templater = new Brand_Management_Templater( 'campaign-shortcode-common/campaign-shortcode-metadata-block-template.html' );

		$metadata_html = $templater->build_html_with_replacings( [
			'{{NO-DATA-ABOUT-OFFER-CLASS-SLOT}}'  => $no_data_about_offer_class,
			'{{MINIMAL-DEPOSIT-SLOT}}'            => $minimum_deposit_slot,
			'{{MIN-DEPOSIT-METHOD-SLOT}}'         => $deposit_method_slot,
			'{{REGULATED-BY-SLOT}}'               => $regulated_by_slot,
			'{{LICENSE-SLOT}}'                    => $license_slot,
			'{{DESCRIPTION-SLOT}}'                => $offer_conditions_slot,
			'{{GALLERY-SLOT}}'                    => $this->build_image_gallery_html( $offer_id ),
			'{{NO-CONTACT-DATA-STYLE-SLOT}}'      => $no_contact_data_style,
			'{{NO-DATA-ABOUT-OFFER-NOTICE-SLOT}}' => $no_data_about_offer_notice,
			'{{WEBSITE-SLOT}}'                    => $website_slot,
			'{{OWNER-SLOT}}'                      => $owner_slot,
			'{{FOUNDED-SLOT}}'                    => $founded_slot,
			'{{HEADQUARTER-SLOT}}'                => $headquarter_slot,
			'{{PROFILE-LINK-SLOT}}'               => $profile_slot,
		] );

		return $metadata_html;

	}

	private function try_append_attributes_if_external_link( $link, $offer_id ): string {

		$domain = parse_url( home_url() )['host'];

		$is_affiliate_link = str_contains( $link, '/visit/' ) || str_contains( $link, '/click/' );
		$is_internal_link  = str_contains( $link, $domain ) || str_starts_with( $link, '/' );

		if ( $is_affiliate_link || ! $is_internal_link ) {
			return bm_get_external_link_attributes( $offer_id );
		}

		return '';

	}

	private function build_image_gallery_html( $offer_id ): string {

		$image_gallery = bm_get_field( 'image_gallery', $offer_id, true, '', true );
		if ( empty( $image_gallery ) || ! is_array( $image_gallery ) ) {
			return '';
		}

		$text_gallery       = _x( 'Gallery', 'campaign-shortcode', 'brand-management' );
		$gallery_inner_html = '';
		$gallery_image_alt  = $this->get_brand_name( $offer_id ) . ' ' . $text_gallery;

		$images_count = 0;
		foreach ( $image_gallery as $gallery_item ) {
			if ( ! empty( $gallery_item['image'] ) ) {
				$gallery_inner_html .= '
					<li>
						<a href="' . $gallery_item['image']['url'] . '" aria-label="' . $gallery_image_alt . '">
							<img class="gdev" src="' . $gallery_item['image']['sizes']['thumbnail'] . '" alt="' . $gallery_image_alt . '">
						</a>
					</li>';

				$images_count ++;
			}
		}

		if ( $images_count === 0 ) {
			return '';
		}

		$gallery_label = bm_get_field( 'gallery_label', $offer_id, true, '', true ) ?: '';

		return '
			<div class="metadata-section_3">
				<div class="brand_gallery_outer">
					<label>' . $gallery_label . ' ' . $text_gallery . ' (' . $images_count . ')</label>
					<ul class="brand_gallery_slide">
						' . $gallery_inner_html . '
					</ul>
				</div>
			</div>';

	}

	public function is_show_filter_tags(): bool {
		return bm_get_field( 'show_filter_tags', $this->taxonomy_selector ) !== false;
	}

	public function build_filters_section_html( $offers_list ): string {
		return $this->get_templater( $this->template_filters_section )->build_html_with_replacings( [
			'{{ IS_SORTABLE }}'     => bm_get_option( 'sorting_in_campaign_tables' ) ? 'sortable' : '',
			'{{ SORTING_SECTION }}' => $this->print_sorting_section(),
			'{{ TAGS_SECTION }}'    => $this->build_campaign_filter_tags_html( $offers_list ),
		] );
	}

	public function build_campaign_filter_tags_html( $offers_list ): string {

		$campaign_filter_tags            = bm_get_field( 'campaign_filter_tags', $this->taxonomy_selector ) ?: [];
		$campaign_filter_tags_with_names = array_map( static function ( $tag_id ) {
			return [
				'id'   => $tag_id,
				'name' => get_term( $tag_id )->name,
			];
		}, $campaign_filter_tags );

		$offers_filter_tags_with_names = [];
		foreach ( $offers_list as $offer_id ) {
			$brand_tags = bm_get_brand_tags( $offer_id, $this->taxonomy_selector, true ) ?: [];

			foreach ( $brand_tags as $tag ) {
				$offers_filter_tags_with_names[] = [
					'id'   => $tag->term_id,
					'name' => $tag->name,
				];
			}
		}

		$offers_filter_tags_ids = array_map( static function ( $tag ) {
			return (string) $tag['id'];
		}, $offers_filter_tags_with_names );

		if ( ! empty( $campaign_filter_tags_with_names ) ) {
			$final_tags_array = array_values( array_unique( array_filter( $campaign_filter_tags_with_names,
				static function ( $tag ) use ( $offers_filter_tags_ids ) {
					return in_array( (string) $tag['id'], $offers_filter_tags_ids );
				} ), SORT_REGULAR ) );
		} else {
			$campaign_all_filter_tag_order = bm_get_field( 'campaign_all_filter_tag_order', $this->taxonomy_selector ) ?: [];

			$campaign_all_filter_tag_order_with_names = [];
			foreach ( $campaign_all_filter_tag_order as $tag_id ) {
				if ( in_array( (string) $tag_id, $offers_filter_tags_ids ) ) {
					$campaign_all_filter_tag_order_with_names[] = [
						'id'   => $tag_id,
						'name' => get_term( $tag_id )->name,
					];
				}
			}

			$final_tags_array = array_values( array_unique( array_merge( $campaign_all_filter_tag_order_with_names, $offers_filter_tags_with_names ),
				SORT_REGULAR ) );
		}

		$tags_section_ui = bm_get_option( 'tags_ui_in_campaign_tables' ) ?: false;

		if ( $tags_section_ui ) {
			return $this->get_tags_select_html( $final_tags_array );
		} else {
			return $this->get_tags_tabs_html( $final_tags_array );
		}

	}

	private function get_tags_tabs_html( array $tags_array ): string {

		$result_html     = '';
		$filters_counter = isset( $this->atts['filter'] ) && $this->atts['filter'] ? 9 : 4;

		foreach ( $tags_array as $tag ) {
			$tag_name     = str_replace( [ ' ', '!' ], [ '_', '' ], $tag['name'] );
			$hidden_class = $filters_counter <= 0 ? 'desktop-hidden' : '';

			$offers_order_in_tag = '';
			$offers_order_schema = $this->build_offers_order_json( $tag['id'], $this->taxonomy_selector );
			if ( ! empty( $offers_order_schema ) ) {
				$offers_order_in_tag = 'data-offers-order="' . htmlspecialchars( $offers_order_schema ) . '"';
			}

			$result_html .= '<li class="' . $tag_name . ' ' . $this->filter_tags_css_selector . '_filter-list-item ' . $hidden_class . '" ' . $offers_order_in_tag . ' data-tag="custom_tag_' . $tag_name . '">
								   ' . $tag['name'] . '
                             </li>';

			$filters_counter --;
		}

		if ( $filters_counter < 0 ) {
			$result_html .= '<li class="' . $this->filter_tags_css_selector . '_filter-list-item ' . $this->filter_tags_css_selector . '_filter-more-btn">
								   ' . _x( 'More Filters', 'campaign-shortcode', 'brand-management' ) . '
                             </li>';
		}

		return $this->get_templater( $this->template_tags_tabs )->build_html_with_replacings( [
			'{{FILTER-BY-TAG-IN-MORE-FILTER-SLOT}}' => $result_html,
			'{{ALL-TEXT-SLOT}}'                     => _x( 'All', 'campaign-shortcode', 'brand-management' ),
		] );

	}

	private function get_tags_select_html( array $tags_array ): string {

		$result_html = '';

		foreach ( $tags_array as $tag ) {
			$tag_name = str_replace( [ ' ', '!' ], [ '_', '' ], $tag['name'] );

			$offers_order_in_tag = '';
			$offers_order_schema = $this->build_offers_order_json( $tag['id'], $this->taxonomy_selector );
			if ( ! empty( $offers_order_schema ) ) {
				$offers_order_in_tag = 'data-offers-order="' . htmlspecialchars( $offers_order_schema ) . '"';
			}

			$result_html .= '<option value="' . $tag_name . '" data-tag="custom_tag_' . $tag_name . '" ' . $offers_order_in_tag . '>' . $tag['name'] . '</option>';
		}

		return $this->get_templater( $this->template_tags_select )->build_html_with_replacings( [
			'{{ALL-TEXT-SLOT}}' => _x( 'All', 'campaign-shortcode', 'brand-management' ),
			'{{TAGS_SET}}'      => $result_html,
		] );

	}

	public function get_unique_visit_link( $offer_id ): string {
		return bm_get_field( 'unique_visit_link', $offer_id, true, $this->taxonomy_selector ) ?: '';
	}

	public function get_google_conversion_url( $offer_id ): string {
		return bm_get_field( 'google_conversion_url', $offer_id ) ?: '';
	}

	public function get_brand_name( $offer_id ): string {
		return get_the_title( bm_get_field( 'brand_id', $offer_id ) ?: $offer_id );
	}

	protected function get_brand_logo_src( $offer_id ): string {
		return bm_get_optimized_logo( $offer_id );
	}

	public function build_star_rating_html( $offer_id ): string {
		return bm_render_star_rating( bm_get_field( 'star_rating', $offer_id, true, $this->taxonomy_selector, true ) ?: '' );
	}

	public function get_offer_description( $offer_id ): string {
		return bm_get_field( 'offer_description', $offer_id ) ?: '';
	}

	public function get_star_rating_text( $offer_id ): string {
		return bm_get_field( 'star_rating_text', $offer_id, true, $this->taxonomy_selector, true ) ?: '';
	}

	public function build_key_features_html( $offer_id, $ul_class = '' ): string {

		$key_features_list = '';

		$key_features = bm_get_field( 'key_features', $offer_id, true, $this->taxonomy_selector, true );
		if ( ! empty( $key_features ) && is_array( $key_features ) ) {
			$key_features_list .= '<ul class="' . $ul_class . '">';

			foreach ( $key_features as $key_feature ) {
				$key_features_list .= '<li class="principales-list-item"><span class="list-check-icon"></span>' . $key_feature['point'] . '</li>';
			}

			$key_features_list .= '</ul>';
		}

		return $key_features_list;

	}

	public function get_cta_button_label(): string {

		$cta_button_label = bm_get_field( 'cta_button_label', $this->main_taxonomy_selector );
		if ( empty( $cta_button_label ) ) {
			$cta_button_label = _x( 'Claim Bonus', 'campaign-shortcode', 'brand-management' );
		}

		return $cta_button_label;

	}

	protected function build_terms_and_conditions_html( $offer_id, $wrapper = '' ): string {

		$terms_and_conditions = bm_get_field( 'terms_and_conditions', $offer_id );
		if ( ! empty( $terms_and_conditions ) ) {
			$terms_and_conditions_html = '<div class="cell_bottom">' . $terms_and_conditions . '</div>';

			if ( isset( $wrapper[0], $wrapper[1] ) ) {
				$terms_and_conditions_html = $wrapper[0] . $terms_and_conditions_html . $wrapper[1];
			}
		}

		return $terms_and_conditions_html ?? '';

	}

	private function build_read_review_html( $offer_id ): string {

		$read_review_url = bm_get_field( 'read_review_url', $offer_id );
		if ( ! empty( $read_review_url ) ) {
			$read_review_button_label = bm_get_field( 'read_review_button_label', $offer_id ) ?: _x( 'Review', 'campaign-shortcode', 'brand-management' );
			$read_review_html         = '<div class="read_review_url"><a href="' . $read_review_url . '">' . $read_review_button_label . '</a><svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.4668 8.98193L5.4668 4.98193L1.4668 0.981934" stroke="#1C2642" stroke-linecap="round" stroke-linejoin="round"/></svg></div>';
		}

		return $read_review_html ?? '';

	}

	private function build_coupon_code_html( $offer_id ): array {

		if ( $this->is_show_coupon_codes() === false ) {
			return [
				'coupon_code_slot'           => '',
				'coupon_code_tooltip_slot'   => '',
				'cta_button_no_coupon_class' => 'coupon_code_disabled',
			];
		}

		$coupon_code = bm_get_field( 'coupon_code', $offer_id );
		if ( ! empty( $coupon_code ) ) {
			$coupon_code_slot = '
				<div class="country_code_left">
					<span class="copiedtext">
						<svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M1.37109 3.92015L4.5107 7.05975L10.63 0.94043" stroke="#1C2642" stroke-width="2"/>
						</svg>
						' . _x( 'Copied', 'campaign-shortcode', 'brand-management' ) . '
					</span>
					<div class="coupon_code">' . $coupon_code . '</div>
				</div>
				<div class="country_code_right" data-toggle="tooltip"  data-placement="bottom">
					<img src="' . BRAND_MANAGEMENT_URL . 'public/images/copy-icon.svg" height="14" width="14" alt="Copy" />
				</div>
				<div class="tooltip-inner-adv">
					' . _x( 'Copy', 'campaign-shortcode', 'brand-management' ) . '
				</div>';
		} else {
			$coupon_code_slot = '
				<div class="country_code_left no_country_code_left">
					<div class="coupen_code_inner">
						<div class="empty-coupon-code">' . _x( 'NO CODE REQUIRED', 'campaign-shortcode', 'brand-management' ) . '</div>
					</div>
				</div>';
		}

		if ( ! empty( $coupon_code ) ) {
			$coupon_code_tooltip_slot = '<span class="campaign-list-item_bonus-code-tooltip">' . _x( 'Use bonus code', 'campaign-shortcode',
					'brand-management' ) . '</span>';
		} else {
			$coupon_code_tooltip_slot = $coupon_code_slot;
			$coupon_code_slot         = '';
		}

		if ( empty( $coupon_code_slot ) ) {
			$cta_button_no_coupon_class = 'no-coupon';
		} else {
			$cta_button_no_coupon_class = '';
		}

		return [
			'coupon_code_slot'           => $coupon_code_slot,
			'coupon_code_tooltip_slot'   => $coupon_code_tooltip_slot,
			'cta_button_no_coupon_class' => $cta_button_no_coupon_class,
		];

	}

	private function build_top_picker_html( $offer_id, $counter ): string {

		$coupon_code_is_not_empty = ! empty( bm_get_field( 'coupon_code', $offer_id ) );

		$bonus_claimed_html = '';
		if ( $counter === 1 && $coupon_code_is_not_empty ) {
			$bonus_taken_count = bm_get_field( 'bonus_taken_count', $offer_id );
			if ( ! empty( $bonus_taken_count ) ) {
				$bonus_claimed_html = '<div class="brand_boun_claimed"><span>' . $bonus_taken_count . ' ' . _x( 'Codes claimed', 'campaign-shortcode',
						'brand-management' ) . '</span></div>';
			} else {
				$bonus_claimed_html = '<div class="brand_boun_claimed"><span>' . ( rand( 100, 999 ) ) . ' ' . _x( 'Codes claimed', 'campaign-shortcode',
						'brand-management' ) . '</span></div>';
			}
		}

		$highlighted_label = bm_get_field( 'highlighted_label', $offer_id, true, $this->taxonomy_selector );
		if ( ! empty( $highlighted_label ) ) {
			return '<div class="toppike-out"><div class="top-pick">' . $highlighted_label . '</div>' . $bonus_claimed_html . '</div>';
		}

		return '';

	}

	public function get_brand_tags( $offer_id ): string {
		return bm_get_brand_tags( $offer_id, $this->taxonomy_selector ) ?: '';
	}

	protected function get_offer_update_date( $offer_id ): string {
		return strtotime( get_the_modified_date( "d.m.Y H:i:s", $offer_id ) ) ?: strtotime( get_the_date( "d.m.Y H:i:s", $offer_id ) );
	}

	public function build_table_title_and_disclaimer_html( $campaign_id ): string {

		$table_title_and_disclaimer_html = '';

		$table_title_html = $this->build_table_title_html( $campaign_id );
		$disclaimer_html  = $this->is_show_disclaimer() ? $this->get_disclaimer_html() : '';

		$table_title_and_disclaimer_class = 'campaign__table-title-and-disclaimer';
		if ( empty( $table_title_html ) && ! empty( $disclaimer_html ) ) {
			$table_title_and_disclaimer_class .= ' disclaimer-only';
		}

		if ( ! empty ( $table_title_html ) || ! empty ( $disclaimer_html ) ) {
			$table_title_and_disclaimer_html = '
				<div class="' . $table_title_and_disclaimer_class . '">
					' . $table_title_html . '
					' . $disclaimer_html . '
				</div>
			';
		}

		return $table_title_and_disclaimer_html;

	}

	public function build_table_title_html( $campaign_id ): string {

		$table_title_html = '';

		$show_table_title = bm_get_field( 'show_table_title', $this->main_taxonomy_selector ) ?? false;
		$category         = get_term_by( 'id', $campaign_id, $this->taxonomy_name );
		if ( $show_table_title && ! empty( $category->name ) ) {
			$table_title_html = '<h2>' . $category->name . '</h2>';
		}

		return do_shortcode( $table_title_html );

	}

	/**
	 * The voting section is given to the front-end.
	 *
	 * @param $offer_id
	 *
	 * @return string
	 * @since 1.2.0 CENTGAM
	 */
	public function print_voting_section( $offer_id ): string {

		if ( ! $this->is_show_likes() ) {
			return '';
		}

		return $this->get_templater( 'campaign-shortcode-common/voting.html' )->build_html_with_replacings( [
			'{{ LIKES_VALUE_SLOT }}'    => bm_get_field( 'offer_likes', $offer_id ) ?? 0,
			'{{ DISLIKES_VALUE_SLOT }}' => bm_get_field( 'offer_dislikes', $offer_id ) ?? 0,
		] );
	}

	/**
	 * The updated date section is given to the front-end.
	 * Uses the date format set for the company.
	 *
	 * @param $offer_id
	 *
	 * @return string
	 * @since 1.2.0 CENTGAM
	 */
	public function print_updated_date( $offer_id ): string {

		if ( ! $this->is_show_date_time() ) {
			return '';
		}

		if ( empty( $this->updated_date_format ) ) {
			$this->updated_date_format = bm_get_option( 'date_format' ) ?: 'd/m/y';
		}

		return $this->get_templater( 'campaign-shortcode-common/updated.html' )->build_html_with_replacings( [
			'{{ UPDATED_DATE }}' => get_the_modified_date( $this->updated_date_format, $offer_id ),
		] );

	}

	/**
	 * The sorting section is given to the front-end.
	 *
	 * @return string
	 */
	public function print_sorting_section(): string {

		if ( bm_get_option( 'sorting_in_campaign_tables' ) ) {
			return $this->get_templater( $this->template_sorting_section )->build_html_with_replacings( [
				'{{ TEXT_SORT_BY }}'    => _x( 'Sort By', 'campaign-management', 'brand-management' ),
				'{{ TEXT_BEST_RATED }}' => _x( 'Best Rated', 'campaign-management', 'brand-management' ),
				'{{ TEXT_NEWEST }}'     => _x( 'Newest', 'campaign-management', 'brand-management' ),
			] );
		}

		return '';

	}

	/**
	 * Helper method to get json schema of offers
	 * order within a specific tag in this campaign.
	 *
	 * @param $tag_id
	 * @param $taxonomy_selector
	 *
	 * @return string
	 */
	public function build_offers_order_json( $tag_id, $taxonomy_selector ): string {

		$filtered_offers_ids = [];

		$offers_list = $this->get_offers_list( $taxonomy_selector );
		foreach ( $offers_list as $offer_id ) {
			$brand_tags = bm_get_brand_tags( $offer_id, $taxonomy_selector, true ) ?: [];
			foreach ( $brand_tags as $tag ) {
				if ( $tag->term_id === (int) $tag_id ) {
					$filtered_offers_ids[] = $offer_id;
					break;
				}
			}
		}

		$is_offers_order_rewritten = false;

		$reordered_tags = bm_get_field( 'field_offers_order_within_a_tag', $taxonomy_selector ) ?: [];
		foreach ( $reordered_tags as $reordered_tag ) {
			if ( (int) $reordered_tag['ordering_tag'] === (int) $tag_id ) {
				$all_filtered_offers = $filtered_offers_ids;
				$ordered_old_value   = $reordered_tag['ordering_offers'] ?: [];

				// offers which order is already known
				$first_part = array_diff( $ordered_old_value, array_diff( $ordered_old_value, $all_filtered_offers ) );

				// new offers without order
				$last_part           = array_diff( $all_filtered_offers, $ordered_old_value );
				$filtered_offers_ids = array_merge( $first_part, $last_part );

				$is_offers_order_rewritten = true;
			}
		}

		if ( $is_offers_order_rewritten ) {
			return json_encode( $filtered_offers_ids ) ?: '';
		}

		return '';

	}

	public function get_templater( $template_path ): Brand_Management_Templater {
		return ( new Brand_Management_Templater( $template_path ) );
	}

	public static function is_global_active( $offer_id ): bool {

		$brand_id = get_field( 'brand_id', $offer_id );
		if ( ! empty( $brand_id ) && get_field( 'global_activity', $brand_id ) === false ) {
			return false;
		}

		if ( get_field( 'global_activity', $offer_id ) === false ) {
			return false;
		}

		return true;

	}

	public function get_offers_list( $taxonomy ): array {

		$offers_list = bm_get_field( 'offers_list', $taxonomy ) ?: [];

		return $this->filter_offers_list( $offers_list );

	}

	public function filter_offers_list( $offers_list ): array {
		return array_filter( $offers_list, function ( $offer_id ) {
			return get_post_status( $offer_id ) === 'publish' && $this->is_show_in_country( $offer_id ) && self::is_global_active( $offer_id );
		} );
	}

	private function is_show_in_country( $offer_id ): bool {

		if ( $this->ip_geo_filters === false ) {
			return true;
		}

		if ( empty( $this->country_code ) ) {
			if ( empty( $this->user_ip_info ) ) {
				$this->user_ip_info = (array) json_decode( file_get_contents( 'http://www.geoplugin.net/json.gp?ip=' . self::get_user_ip() ) ) ?: [ false ];
			}

			$this->country_code = $this->user_ip_info['geoplugin_countryCode'] ?? '';
		}

		$show_in_countries = get_field( 'show_in_countries', $offer_id );
		if ( empty( $show_in_countries ) ) {
			return true;
		}

		if ( in_array( $this->country_code, $show_in_countries, true ) ) {
			return true;
		}

		return false;

	}

	public static function get_user_ip(): string {
		return $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'];
	}

	public static function get_country_code(): string {
		return $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '';
	}

	public static function get_ip_geo_filters_status( $taxonomy_selector ): bool {
		return ( get_field( 'ip_geo_filters', $taxonomy_selector ) ?? false ) !== false;
	}

	public function set_variable( $variable, $value ) {
		return $this->$variable = $value;
	}

	protected function get_show_more_btn_text(): string {
		return '+ Show More';
	}

	protected function try_enqueue_script( $script_name ): void {
		wp_enqueue_script( $script_name, ( new Brand_Management_Template_Loader() )->search_path( 'js/' . $script_name . '.js' ), [ 'jquery' ], null, true );
	}

	public function is_show_coupon_codes(): bool {
		return ( get_field( 'show_coupon_codes', $this->taxonomy_selector ) ?? true ) !== false;
	}

	public function is_show_disclaimer(): bool {
		return ( get_field( 'campaign_show_disclaimer', $this->taxonomy_selector ) ?? false ) !== false;
	}

	public function get_disclaimer_html(): string {

		$disclaimer_html         = '';
		$disclaimer_button_label = get_field( 'campaign_disclaimer_button_label', $this->taxonomy_selector ) ?? '';
		$disclaimer_text         = get_field( 'campaign_disclaimer_text', $this->taxonomy_selector ) ?? '';

		if ( ! empty( $disclaimer_button_label ) && ! empty( $disclaimer_text ) ) {
			$disclaimer_html .= '
				<div class="campaign__disclaimer">
					<div class="campaign__disclaimer-btn">
						' . $disclaimer_button_label . '
					</div>
					<div class="campaign__disclaimer-tip">
						' . $disclaimer_text . '
					</div>
				</div>
			';
		}

		return $disclaimer_html;

	}

	private function get_deposit_methods_slot_html( $offer_id ): string {

		$is_use_new_flow = get_field( 'adding_deposit_methods_flow', $offer_id );

		$deposit_methods_slot_html = '';
		if ( $is_use_new_flow ) {
			$deposit_methods = get_field( 'deposit_methods_new_flow', $offer_id ) ?: [];

			foreach ( $deposit_methods as $deposit_method ) {
				if ( get_post_status( $deposit_method['payment_method_id'] ) !== 'publish' ) {
					continue;
				}

				$deposit_method_label = get_the_title( $deposit_method['payment_method_id'] );
				$deposit_method_image = get_field( 'payment_method_logo', $deposit_method['payment_method_id'] ) ?: '';

				$deposit_methods_slot_html .= '<li>';
				if ( ! empty( $deposit_method_image ) ) {
					$deposit_methods_slot_html .= '<img class="mindepst_method" data-placement="bottom" data-toggle="tooltip" src="' . $deposit_method_image . '" alt="' . $deposit_method_label . '">';
				} else {
					$deposit_methods_slot_html .= '<label class="mindepst_method" data-placement="bottom"  data-toggle="tooltip">' . $deposit_method_label . '</label>';
				}
				$deposit_methods_slot_html .= '<p class="hover-tip">' . $deposit_method_label . '</p></li>';
			}
		} else {
			$deposit_methods = bm_get_field( 'deposit_method', $offer_id );
			if ( ! empty( $deposit_methods ) && is_array( $deposit_methods ) ) {
				foreach ( $deposit_methods as $deposit_method ) {
					$sub_field_label = $deposit_method['deposit_method_label'] ?: '';

					$deposit_methods_slot_html .= '<li>';

					if ( ! empty( $deposit_method['deposit_method_image'] ) ) {
						$deposit_methods_slot_html .= '<img class="mindepst_method" data-placement="bottom" data-toggle="tooltip" src="' . $deposit_method['deposit_method_image'] . '" alt="' . $this->get_brand_name( $offer_id ) . ' ' . _x( 'Deposit Method',
								'campaign - shortcode', 'brand - management' ) . '">';
					} else {
						$deposit_methods_slot_html .= '<label class="mindepst_method" data-placement="bottom"  data-toggle="tooltip">' . $sub_field_label . '</label>';
					}

                    $sub_field_label = pathinfo($deposit_method['deposit_method_image'])['basename'];
                    $sub_field_label = preg_replace("/[^a-zA-Z]+/", "", ucfirst(substr($sub_field_label, 0, strpos($sub_field_label, "."))));
                    $deposit_methods_slot_html .= '<p class="hover-tip">' . $sub_field_label . '</p></li>';
				}
			}
		}

		if ( ! empty( $deposit_methods_slot_html ) ) {
			$deposit_methods_slot_html = '<label>' . _x( 'Deposit Method', 'campaign-shortcode',
					'brand-management' ) . '</label><span><ul>' . $deposit_methods_slot_html . '</ul></span>';
		}

		return $deposit_methods_slot_html;

	}
}
