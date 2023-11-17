<?php

class Brand_Comparison_Table_Shortcode {
	/**
	 * The shortcode takes an id parameter.
	 * Example - [brand_comparison_table id='170106'], when id is a tag id on given taxonomy.
	 *
	 * @param $attributes
	 *
	 * @return string
	 */
	public function shortcode( $attributes ): string {

		$comparison_table_id = $attributes['id'];
		// There is no taxonomy with the specified id.
		if ( empty( $comparison_table_id ) ) {
			return '';
		}

		$comparison_table_selector = 'bm_comparison_tables_' . $comparison_table_id;

		$comparison_brands = bm_get_field( 'field_comparison_brands', $comparison_table_selector );
		// There are no brands assigned in this table.
		if ( empty( $comparison_brands ) ) {
			return '';
		}

		$comparison_rows = bm_get_field( 'field_comparison_rows', $comparison_table_selector );
		// Parameters for comparison are not specified.
		if ( empty( $comparison_rows ) ) {
			return '';
		}

		// Additional settings.
		$is_show_rating     = bm_get_field( 'field_show_rating', $comparison_table_selector );
		$visit_now_btn_text = bm_get_field( 'field_visit_now_btn_text', $comparison_table_selector ) ?: _x( 'Visit Now', 'comparison-table-shortcode', 'brand-management' );

		// Begins to form the head of the table.
		$table_header_html = '';
		$table_body_html   = '';
		$rating_row_html   = '';

		$hidden_table_html                = '<table style="display: none" class="do-not-wrap"><thead><tr><th></th>';
		$rating_row_for_hidden_table_html = '<tr><td>' . _x( 'Rating', 'comparison-table-shortcode', 'brand-management' ) . '</td>';

		foreach ( $comparison_brands as $brand_id ) {
			if ( ! Campaign_Shortcode::is_global_active( $brand_id ) ) {
				continue;
			}

			$brand_name        = get_the_title( $brand_id ) ?: '';
			$highlighted_label = bm_get_field( 'highlighted_label', $brand_id );
			$highlighted_label = ! empty( $highlighted_label ) ? '<p>' . $highlighted_label . '</p>' : '';

			$is_hide_terms_and_conditions = bm_get_field( 'hide_terms_and_conditions', $brand_id ) ?? false;
			if ( ! $is_hide_terms_and_conditions ) {
				$terms_and_conditions         = bm_get_field( 'terms_and_conditions', $brand_id ) ?: '';
				$trimmed_terms_and_conditions = bm_trim_text( $terms_and_conditions, 20 );
			}

			if ( $is_show_rating ) {
				$star_rating_text = bm_get_field( 'star_rating_text', $brand_id );
				$star_rating_html = bm_render_star_rating( bm_get_field( 'star_rating', $brand_id ) );

				$rating_row_html .= '<div class="bm-cmprs-tbl_cell bm-cmprs-tbl_bold bm-cmprs-tbl_rating" data-brand-id="' . $brand_id . '">' . $star_rating_html . '<span>' . ( $star_rating_text ?: '' ) . '</span></div>';

				$rating_row_for_hidden_table_html .= '<td>' . $star_rating_html . '<span>' . ( $star_rating_text ?: '' ) . '</span></td>';
			}

			$brand_meta_data = '';
			if ( is_user_logged_in() ) {
				$brand_meta_data = 'link_to_edit_brand="' . admin_url( 'post.php?post=' . $brand_id . '&action=edit' ) . '"';
			}

			$table_header_html .= $this->template_engine( 'table-header-brand', [
				'{{META-DATA}}'               => $brand_meta_data,
				'{{VISIT-LINK-ATTRIBUTES}}'   => bm_get_external_link_attributes( $brand_id ),
				'{{BRAND-ID-SLOT}}'           => $brand_id,
				'{{BRAND-NAME-SLOT}}'         => $brand_name,
				'{{HIGHLIGHTED-LABEL}}'       => $highlighted_label,
				'{{LOGO-SRC-SLOT}}'           => bm_get_optimized_logo( $brand_id, 'bm_middle_thumbnail' ),
				'{{BRAND-TITLE-SLOT}}'        => bm_get_field( 'bonus_text_as_title', $brand_id ) ?: $brand_name ?: '',
				'{{VISIT-LINK}}'              => bm_get_field( 'unique_visit_link', $brand_id ) ?: '',
				'{{DISCLAIMER-SLOT}}'         => $terms_and_conditions ?? '',
				'{{TRIMMED-DISCLAIMER-SLOT}}' => $trimmed_terms_and_conditions ?? '',
				'{{VISIT-NOW-TEXT}}'          => $visit_now_btn_text,
			] );

			$hidden_table_html .= '<th>' . $brand_name . '</th>';
		}

		$hidden_table_html .= '</tr></thead><tbody>';

		if ( ! empty( $rating_row_html ) ) {
			$rating_row_html = '
				<div class="bm-cmprs-tbl_row" data-row="rating">
					<div class="bm-cmprs-tbl_cell bm-cmprs-tbl_row-title-cell">
						' . _x( 'Rating', 'comparison-table-shortcode', 'brand-management' ) . '
					</div>
					<div class="bm-cmprs-tbl_row-cells-wrapper">
						' . $rating_row_html . '
						<div class="bm-cmprs-tbl_cell bm-cmprs-tbl_if-deleted"></div>
					</div>
				</div>';

			$hidden_table_html .= $rating_row_for_hidden_table_html;
		}

		foreach ( $comparison_rows as $comparison_row ) {
			$comparison_row_name                   = $comparison_row['comparison_row_name'];
			$comparison_parameters                 = $comparison_row['comparison_parameter'];
			$comparison_row_collapsed_class        = $comparison_row['comparison_row_closed'] ? 'bm-cmprs-tbl_title-row_closed' : '';
			$comparison_row_mobile_collapsed_class = $comparison_row['comparison_row_mobile_closed'] ? '' : 'bm-cmprs-tbl_title-row_mobile_opened';
			$even_odd_row_condition                = true;

			// Begins to form comparison rows.
			$comparison_rows_html = '
				<div class="bm-cmprs-tbl_title-row ' . $comparison_row_collapsed_class . ' ' . $comparison_row_mobile_collapsed_class . '">
					<div class="bm-cmprs-tbl_title-row-title-cell">' . $comparison_row_name . '</div>
				</div>';

			$hidden_table_html .= '<tr><td colspan="' . ( count( $comparison_brands ) + 1 ) . '">' . $comparison_row_name . '</td></tr>';

			foreach ( $comparison_parameters as $parameter ) {
				$comparison_parameter_source = $parameter['comparison_parameter_source'];
				$comparison_parameter_type   = $parameter['comparison_parameter_type'];
				$comparison_parameter_name   = $parameter['comparison_parameter_name'] ?: get_field_object( $comparison_parameter_source )['label'];

				$class_even_odd_row = $even_odd_row_condition ? 'bm-cmprs-tbl_odd-row' : '';

				$comparison_row_html = '
					<div class="bm-cmprs-tbl_row ' . $class_even_odd_row . '" data-row="' . $comparison_parameter_source . '">
						<div class="bm-cmprs-tbl_cell bm-cmprs-tbl_row-title-cell">' . $comparison_parameter_name . '</div>
							<div class="bm-cmprs-tbl_row-cells-wrapper">';

				$hidden_table_html .= '<tr><td>' . $comparison_parameter_name . '</td>';

				$even_odd_row_condition = ! $even_odd_row_condition;

				// Populates comparison rows with brands.
				foreach ( $comparison_brands as $brand_id ) {
					$comparison_parameter = bm_get_field( $comparison_parameter_source, $brand_id ) ?? '';

					$row_inner = '';
					$row_class = '';

					$hidden_table_html .= '<td>';

					if ( $comparison_parameter ) {
						if ( $comparison_parameter_type === 'text' ) {
							$row_inner = $comparison_parameter;
							$row_class = 'bm-cmprs-tbl_text';

							$hidden_table_html .= $comparison_parameter;
						}

						if ( $comparison_parameter_type === 'line_rating' ) {
							$row_class   = 'bm-cmprs-tbl_bold bm-cmprs-tbl_bar';
							$width_class = $comparison_parameter;

							if ( (int) $comparison_parameter < 5 ) {
								$color_class = 'red';
							} elseif ( (int) $comparison_parameter < 6 ) {
								$color_class = 'amber';
							} else {
								$color_class = 'green';
							}

							$row_inner         = '<div class="bar bar_' . $color_class . ' bar_' . $width_class . '"><div></div></div><span>' . $comparison_parameter . '/10</span>';
							$hidden_table_html .= $comparison_parameter . '/10';
						}
					}

					if ( $comparison_parameter_type === 'icons_yes_no' ) {
						$row_class = 'bm-cmprs-tbl_icon';

						if ( $comparison_parameter ) {
							$row_inner = '<svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M10.5 20C16.0228 20 20.5 15.5228 20.5 10C20.5 4.47715 16.0228 0 10.5 0C4.97715 0 0.5 4.47715 0.5 10C0.5 15.5228 4.97715 20 10.5 20ZM9.35488 11.904L14.3951 5L16 6.42857L9.58262 15L5 10.2388L6.375 8.81027L9.35488 11.904Z" fill="#0F9960"/></svg>';

							$hidden_table_html .= '+';
						} else {
							$row_inner = '<svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M20.5 10C20.5 15.5228 16.0228 20 10.5 20C4.97715 20 0.5 15.5228 0.5 10C0.5 4.47715 4.97715 0 10.5 0C16.0228 0 20.5 4.47715 20.5 10ZM14.7071 5.79289C15.0976 6.18342 15.0976 6.81658 14.7071 7.20711L11.9142 10L14.7071 12.7929C15.0976 13.1834 15.0976 13.8166 14.7071 14.2071C14.3166 14.5976 13.6834 14.5976 13.2929 14.2071L10.5 11.4142L7.70711 14.2071C7.31658 14.5976 6.68342 14.5976 6.29289 14.2071C5.90237 13.8166 5.90237 13.1834 6.29289 12.7929L9.08579 10L6.29289 7.20711C5.90237 6.81658 5.90237 6.18342 6.29289 5.79289C6.68342 5.40237 7.31658 5.40237 7.70711 5.79289L10.5 8.58579L13.2929 5.79289C13.6834 5.40237 14.3166 5.40237 14.7071 5.79289Z" fill="#737373"/></svg>';

							$hidden_table_html .= '-';
						}
					}

					$comparison_row_html .= '<div class="bm-cmprs-tbl_cell ' . $row_class . '" data-brand-id="' . $brand_id . '">' . $row_inner . '</div>';

					$hidden_table_html .= '</td>';
				}

				$comparison_row_html  .= '<div class="bm-cmprs-tbl_cell bm-cmprs-tbl_if-deleted"></div></div></div>';
				$comparison_rows_html .= $comparison_row_html;

				$hidden_table_html .= '</tr>';
			}

			$table_body_html .= $comparison_rows_html;
		}

		$hidden_table_html .= '</tbody></table>';

		$shortcode_meta_data = '';
		if ( is_user_logged_in() ) {
			$shortcode_meta_data = 'comparison_table_id="' . $comparison_table_id . '" link_to_edit_comparison_table="' . admin_url( 'term.php?taxonomy=bm_comparison_tables&tag_ID=' . $comparison_table_id . '&post_type=offer' ) . '"';
		}

		Brand_Management_Public::load_assets( 'brand-management-brand-comparison-table-shortcode' );
		wp_enqueue_script( 'brand-management-brand-comparison-table-shortcode', ( new Brand_Management_Template_Loader() )->search_path( 'js/brand-management-side-by-side-tables.js' ), [ 'jquery' ], null, true );

		return $this->template_engine( 'comparison-table', [
			'{{HIDDEN-TABLE}}'     => $hidden_table_html,
			'{{META-DATA}}'        => $shortcode_meta_data,
			'{{TABLE-HEADER}}'     => $table_header_html,
			'{{RATING-ROW}}'       => $rating_row_html,
			'{{TABLE-BODY}}'       => $table_body_html,
			'{{SELECT-TEXT-SLOT}}' => _x( 'Select Bookmaker', 'comparison-table-shortcode', 'brand-management' ),
		] );

	}

	private function template_engine( $template, $array ): string {
		return ( new Brand_Management_Templater( 'brand-comparison-table-shortcode/' . $template . '.html' ) )->build_html_with_replacings( $array );
	}
}
