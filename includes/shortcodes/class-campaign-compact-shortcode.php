<?php

class Campaign_Compact_Shortcode extends Campaign_Shortcode {
	public function shortcode( $atts ): string {

		$this->atts = $atts;
		$id_attr    = $this->atts['id'] ?? '';

		if ( empty( $id_attr ) ) {
			return '';
		}

		$this->taxonomy_selector = $this->main_taxonomy_selector = 'bm_campaign_management_' . $id_attr;

		Brand_Management_Storage::set_campaign( $id_attr, [ 'is_show_disclaimer' => $this->is_show_disclaimer() ] );

		$this->init_geo_filters();

		$offers_list = $this->get_offers_list( $this->taxonomy_selector );
		if ( ! empty( $offers_list ) ) {
			return $this->build_shortcode( $offers_list );
		}

		return '';

	}

	private function build_shortcode( $offers_list = [] ): string {

		$show_more_btn = '<div class="campaign-compact-table__show-more-btn">' . $this->get_show_more_btn_text() . '</div>';
		if ( ! isset( $this->atts['display'] ) || count( $offers_list ) <= ( $this->atts['display'] ) ) {
			$show_more_btn = '';
		}

		if ( count( $offers_list ) !== 1 && $this->is_show_filter_tags() ) {
			$filters_section = $this->build_filters_section_html( $offers_list );
		}

		$extra_classes = [];

		// Determine whether ajax loading is necessary.
		if ( isset( $this->atts['display'] ) && count( $offers_list ) > ( $this->atts['display'] ) ) {
			$extra_classes[] = 'require_ajax_loading';
		}

		if ( $this->ip_geo_filters ) {
			$extra_classes[] = 'campaign_with_geo_filters';
		}

		if ( $this->is_show_offers_counter() ) {
			$extra_classes[] = 'numbered';
		}

		$campaign_atts = 'data-id="' . $this->atts['id'] . '" data-atts-filter="' . ( $this->atts['filter'] ?? '' ) . '"';
		$campaign_atts .= isset( $this->atts['display'] ) ? ' data-atts-display="' . $this->atts['display'] . '"' : '';
		$campaign_atts .= ' ' . self::get_editor_metadata_for_campaign( $this->atts['id'] );

		Brand_Management_Public::load_assets( 'brand-management-campaign-compact-table' );
		$this->try_enqueue_script( 'brand-management-campaign-shortcode' );

		return $this->get_templater( 'campaign-compact-shortcode/template.html' )->build_html_with_replacings( [
			'{{ EXTRA_CLASSES }}'   => implode( ' ', $extra_classes ),
			'{{ CAMPAIGN_ATTS }}'   => $campaign_atts,
			'{{ TABLE_TITLE }}'     => $this->build_table_title_and_disclaimer_html( $this->atts['id'] ),
			'{{ FILTERS_SECTION }}' => $filters_section ?? '',
			'{{ OFFERS_COUNT }}'    => count( $offers_list ),
			'{{ OFFERS_LIST }}'     => $this->build_offers_list( $offers_list ),
			'{{ SHOW_MORE_BTN }}'   => $show_more_btn,
		] );

	}

	private function build_offers_list( $offers_list ): string {

		$offers_list_html = '';

		$is_ajax_loading = $this->atts['ajax'] ?? false;

		$rebuild_campaign_table = $this->atts['rebuild_campaign_table'] ?? false;
		if ( $rebuild_campaign_table ) {
			$is_ajax_loading = false;
		}

		if ( isset( $this->atts['display'] ) && $this->atts['display'] ) {
			$show_offers_count = $this->atts['display'];
		} else {
			$show_offers_count = count( $offers_list );
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
			$template_elements = [
				'{{ IS_NOT_FIRST_ITEM_CLASS }}'  => $foreach_loop_counter === 1 ? 'brand_table_custom_cl' : '',
				'{{ CATEGORIES_TAG_SLOT }}'      => $this->get_brand_tags( $offer_id ),
				'{{ OFFER_ID }}'                 => $offer_id,
				'{{ META_DATA }}'                => self::get_editor_metadata_for_offer( $offer_id ),
				'{{ EXTERNAL_LINK_ATTRIBUTES }}' => bm_get_external_link_attributes( $offer_id ),
				'{{ UPDATE_DATE }}'              => $this->get_offer_update_date( $offer_id ),
				'{{ STAR_RATING_TEXT }}'         => $this->get_star_rating_text( $offer_id ),
				'{{ UNIQUE_VISIT_LINK }}'        => $this->get_unique_visit_link( $offer_id ),
				'{{ BRAND_LOGO_SRC }}'           => $this->get_brand_logo_src( $offer_id ),
				'{{ BRAND_NAME }}'               => $this->try_get_redefined_brand_name( $offer_id ),
				'{{ OFFER_DESCRIPTION }}'        => $this->get_offer_description( $offer_id ),
				'{{ KEY_FEATURES }}'             => $this->build_key_features_html( $offer_id ),
				'{{ TERMS_AND_CONDITIONS }}'     => $this->build_terms_and_conditions_html( $offer_id,
					[ '<td class="campaign-compact-table__offer-terms">', '</td>' ] ),
				'{{ STAR_RATING_HTML }}'         => $this->build_star_rating_html( $offer_id ),
				'{{ CTA_BUTTON_LABEL }}'         => $this->get_cta_button_label(),
				'{{ COUPON_CODE }}'              => $this->build_coupon_code_html( $offer_id ),
			];

			if ( $is_ajax_loading ) {
				$template_elements['{{ STYLE_DISPLAY_NONE }}'] = 'style="display: none !important;"';
			} elseif ( $foreach_loop_counter <= $show_offers_count ) {
				$template_elements['{{ STYLE_DISPLAY_NONE }}'] = '';
			} else {
				$template_elements['{{ STYLE_DISPLAY_NONE }}'] = 'style="display: none !important;"';
			}

			$templater = $this->get_templater( 'campaign-compact-shortcode/item-template.html' );

			$offers_list_html .= $templater->build_html_with_replacings( $template_elements );

			$foreach_loop_counter ++;
		}

		return $offers_list_html;

	}

	private function build_coupon_code_html( $offer_id ): string {

		$coupon_code_html = '';

		if ( $this->is_show_coupon_codes() === false ) {
			return $coupon_code_html;
		}

		$coupon_code = bm_get_field( 'coupon_code', $offer_id );
		if ( ! empty( $coupon_code ) ) {
			$coupon_code_html .= '
				<div class="campaign-compact-table__offer-has-coupon-code">
					<div class="coupon-code__wrapper">
						<span class="coupon-code_copied">
							<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M1.37109 3.92015L4.5107 7.05975L10.63 0.94043" stroke="#1C2642" stroke-width="2"/>
							</svg>
							' . _x( 'Copied', 'campaign-shortcode', 'brand-management' ) . '
						</span>
						<div class="coupon-code">' . $coupon_code . '</div>
					</div>
					<div class="coupon-code__copy-btn">
						<img src="' . BRAND_MANAGEMENT_URL . 'public/images/copy-icon.svg" height="14" width="14" alt="Copy" />
					</div>
					<div class="coupon-code__copy-text">
						' . _x( 'Copy', 'campaign-shortcode', 'brand-management' ) . '
					</div>
				</div>
			';
		} else {
			$coupon_code_html = '
				<div class="campaign-compact-table__offer-no-coupon-code">
					' . _x( 'NO CODE REQUIRED', 'campaign-shortcode', 'brand-management' ) . '
				</div>
			';
		}

		return $coupon_code_html;

	}

	private function try_get_redefined_brand_name( $offer_id ): string {

		$redefined_brand_name = bm_get_field( 'redefined_brand_name', $offer_id ) ?: '';

		return $redefined_brand_name ?: $this->get_brand_name( $offer_id );

	}
}
