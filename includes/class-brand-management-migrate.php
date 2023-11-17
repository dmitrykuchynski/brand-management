<?php

/**
 * Migration functionality.
 *
 * @package    Brand_Management
 * @subpackage Brand_Management/includes
 */
class Brand_Management_Migrate {

	public static function migrate_acf_fields( bool $echo = true, bool $partially = false, int $offset = 0 ) {

		global $wpdb;

		$acf_dictonary = [
			[
				'new_name'        => 'brand_logo',
				'search_old_key'  => 'field_1',
				'search_old_name' => 'brand_logo',
			],
			[
				'new_name'        => 'gallery_label',
				'search_old_key'  => 'offer_terms_04_01',
				'search_old_name' => 'offer_terms_04_01',
			],
			[
				'new_name'        => 'image_gallery',
				'search_old_key'  => 'offer_terms_05',
				'search_old_name' => 'offer_terms_05',
				'repeater'        => true,
			],
			[
				'new_name'        => 'image',
				'search_old_key'  => 'gallery_08',
				'search_old_name' => 'gallery_08',
				'repeater'        => true,
			],
			[
				'new_name'        => 'unique_visit_link',
				'search_old_key'  => 'field_28',
				'search_old_name' => 'tracking-url',
			],
			[
				'new_name'        => 'regulated_by',
				'search_old_key'  => 'offer_terms_03',
				'search_old_name' => 'offer_terms_03',
			],
			[
				'new_name'        => 'key_features',
				'search_old_key'  => 'field_2',
				'search_old_name' => 'principales',
				'repeater'        => true,
			],
			[
				'new_name'        => 'point',
				'search_old_key'  => 'field_5c18f8ba9941d',
				'search_old_name' => 'point',
				'repeater'        => true,
			],
			[
				'new_name'        => 'google_conversion_url',
				'search_old_key'  => 'field_google_coversion_url',
				'search_old_name' => 'field_google_coversion_url',
			],
			[
				'new_name'        => 'disclaimer_text',
				'search_old_key'  => 'field_disclaimer_text',
				'search_old_name' => 'field_disclaimer_text',
			],
			[
				'new_name'        => 'payout_duration',
				'search_old_key'  => 'payout_duration',
				'search_old_name' => 'payout_duration',
			],
			[
				'new_name'        => 'deposit_method_label',
				'search_old_key'  => 'offer_terms_02_label',
				'search_old_name' => 'offer_terms_02_label',
				'repeater'        => true,
			],
			[
				'new_name'        => 'deposit_method_image',
				'search_old_key'  => 'offer_terms_02_image',
				'search_old_name' => 'offer_terms_02_image',
				'repeater'        => true,
			],
			[
				'new_name'        => 'deposit_method_charge',
				'search_old_key'  => 'offer_terms_charges',
				'search_old_name' => 'offer_terms_charges',
				'repeater'        => true,
			],
			[
				'new_name'        => 'deposit_method_min_deposit',
				'search_old_key'  => 'offer_terms_min_deposit',
				'search_old_name' => 'offer_terms_min_deposit',
				'repeater'        => true,
			],
			[
				'new_name'        => 'deposit_method',
				'search_old_key'  => 'offer_terms_02',
				'search_old_name' => 'offer_terms_02',
				'repeater'        => true,
			],
			[
				'new_name'        => 'withdrawal_method_label',
				'search_old_key'  => 'offer_terms_with_draw_label',
				'search_old_name' => 'offer_terms_with_draw_label',
				'repeater'        => true,
			],
			[
				'new_name'        => 'withdrawal_method_image',
				'search_old_key'  => 'offer_terms_with_draw_image',
				'search_old_name' => 'offer_terms_with_draw_image',
				'repeater'        => true,
			],
			[
				'new_name'        => 'withdrawal_method_min_withdrawal',
				'search_old_key'  => 'offer_terms_with_draw_min_draw',
				'search_old_name' => 'offer_terms_with_draw_min_draw',
				'repeater'        => true,
			],
			[
				'new_name'        => 'withdrawal_method_time',
				'search_old_key'  => 'offer_terms_with_draw_times',
				'search_old_name' => 'offer_terms_with_draw_times',
				'repeater'        => true,
			],
			[
				'new_name'        => 'withdrawal_method',
				'search_old_key'  => 'offer_terms_with_draw',
				'search_old_name' => 'offer_terms_with_draw',
				'repeater'        => true,
			],
			[
				'new_name'        => 'star_rating',
				'search_old_key'  => 'field_3',
				'search_old_name' => 'star_rating',
			],
			[
				'new_name'        => 'star_rating_text',
				'search_old_key'  => 'field_13',
				'search_old_name' => 'score',
			],
			[
				'new_name'        => 'read_review_url',
				'search_old_key'  => 'read_review_URL_01',
				'search_old_name' => 'read_review_URL_01',
			],
			[
				'new_name'        => 'website',
				'search_old_key'  => 'offer_terms_07',
				'search_old_name' => 'offer_terms_07',
			],
			[
				'new_name'        => 'owner',
				'search_old_key'  => 'offer_terms_08',
				'search_old_name' => 'offer_terms_08',
			],
			[
				'new_name'        => 'founded',
				'search_old_key'  => 'offer_terms_09',
				'search_old_name' => 'offer_terms_09',
			],
			[
				'new_name'        => 'headquarters',
				'search_old_key'  => 'offer_terms_10',
				'search_old_name' => 'offer_terms_10',
			],
			[
				'new_name'        => 'call',
				'search_old_key'  => 'offer_terms_Call',
				'search_old_name' => 'offer_terms_Call',
			],
			[
				'new_name'        => 'helpdesk',
				'search_old_key'  => 'offer_terms_Helpdesk',
				'search_old_name' => 'offer_terms_Helpdesk',
			],
			[
				'new_name'        => 'profile_label',
				'search_old_key'  => 'offer_terms_11_1',
				'search_old_name' => 'offer_terms_11_1',
			],
			[
				'new_name'        => 'profile_url',
				'search_old_key'  => 'offer_terms_11',
				'search_old_name' => 'offer_terms_11',
			],
			[
				'new_name'        => 'global_activity',
				'search_old_key'  => 'field_71',
				'search_old_name' => 'globalonoff',
			],
			[
				'new_name'        => 'brand_id',
				'search_old_key'  => 'parent_brand_id',
				'search_old_name' => 'parent_brand_id',
			],
			[
				'new_name'        => 'minimum_deposit',
				'search_old_key'  => 'offer_terms_01',
				'search_old_name' => 'offer_terms_01',
			],
			[
				'new_name'        => 'terms_and_conditions',
				'search_old_key'  => 'field_bottom_text',
				'search_old_name' => 'field_bottom_text',
			],
			[
				'new_name'        => 'unique_visit_link',
				'search_old_key'  => 'offer-tracking-url',
				'search_old_name' => 'tracking-url',
			],
			[
				'new_name'        => 'coupon_code',
				'search_old_key'  => 'coupen_code_0_1',
				'search_old_name' => 'coupen_code_0_1',
			],
			[
				'new_name'        => 'bonus_taken_count',
				'search_old_key'  => 'bonus_claimed_0_1',
				'search_old_name' => 'bonus_claimed_0_1',
			],
			[
				'new_name'        => 'highlighted_label',
				'search_old_key'  => 'field_83',
				'search_old_name' => 'toppicker',
			],
			[
				'new_name'        => 'offer_description',
				'search_old_key'  => 'field_4',
				'search_old_name' => 'new_customer',
			],
			[
				'new_name'        => 'offer_conditions',
				'search_old_key'  => 'offer_terms_04',
				'search_old_name' => 'offer_terms_04',
			],
			[
				'new_name'        => 'bonus_amount',
				'search_old_key'  => 'bonus_amount',
				'search_old_name' => 'bonus_amount',
			],
			[
				'new_name'        => 'show_table_title',
				'search_old_key'  => 'hide_tabletile',
				'search_old_name' => 'hide_tabletile',
			],
			[
				'new_name'        => 'offers_list',
				'search_old_key'  => 'field_44',
				'search_old_name' => 'brand_list',
			],
			[
				'new_name'        => 'rewriting_offer_fields',
				'search_old_key'  => 'custom_element_overridden',
				'search_old_name' => 'custom_element_overridden',
				'repeater'        => true,
			],
			[
				'new_name'        => 'rewrite_offer_id',
				'search_old_key'  => 'field_rewrite_brand_id',
				'search_old_name' => 'rewrite_brand_id',
				'repeater'        => true,
			],
			[
				'new_name'        => 'cta_button_label',
				'search_old_key'  => 'fieldctabtntblelabel',
				'search_old_name' => 'fieldctabtntblelabel',
			],
			[
				'new_name'        => 'active_mobile_slider',
				'search_old_key'  => 'mobileslider',
				'search_old_name' => 'mobileslider',
			],
			[
				'new_name'        => 'unique_visit_link',
				'search_old_key'  => 'field_visit_link',
				'search_old_name' => 'visit_link',
			],
			[
				'new_name'        => 'regulation',
				'search_old_key'  => 'regulation',
				'search_old_name' => 'regulation',
				'repeater'        => true,
			],
			[
				'new_name'        => 'regulation_image',
				'search_old_key'  => 'regulation_img_review',
				'search_old_name' => 'regulation_img_review',
				'repeater'        => true,
			],
			[
				'new_name'        => 'overall_ratings',
				'search_old_key'  => 'overallrating',
				'search_old_name' => 'overallrating',
				'repeater'        => true,
			],
			[
				'new_name'        => 'overall_rating_label',
				'search_old_key'  => 'rating_review_label',
				'search_old_name' => 'rating_review_label',
				'repeater'        => true,
			],
			[
				'new_name'        => 'overall_rating_score',
				'search_old_key'  => 'rating_review_scrore',
				'search_old_name' => 'rating_review_scrore',
				'repeater'        => true,
			],
		];

		$start = microtime( true );

		$result = '';

		if ( $partially ) {
			$acf_dictonary = array_splice( $acf_dictonary, $offset, 20 );
		}

		foreach ( $acf_dictonary as $item ) {

			if ( isset( $item['repeater'] ) ) {
				$wpdb->query( "UPDATE $wpdb->postmeta pm INNER JOIN $wpdb->posts p ON pm.post_id = p.ID SET pm.meta_key = REPLACE(pm.meta_key, '" . $item['search_old_name'] . "', '" . $item['new_name'] . "') WHERE (p.post_type = 'offer' OR p.post_type = 'brand') AND pm.meta_key LIKE '%" . $item['search_old_name'] . "%'" );
				$wpdb->query( "UPDATE $wpdb->termmeta tm INNER JOIN $wpdb->term_taxonomy tt ON tm.term_id = tt.term_id SET tm.meta_key = REPLACE(tm.meta_key, '" . $item['search_old_name'] . "', '" . $item['new_name'] . "') WHERE tt.taxonomy = 'bm_campaign_management' AND tm.meta_key LIKE '%" . $item['search_old_name'] . "%'" );
			} else {
				$wpdb->query( "UPDATE $wpdb->postmeta pm INNER JOIN $wpdb->posts p ON pm.post_id = p.ID SET pm.meta_key = REPLACE(pm.meta_key, '" . $item['search_old_name'] . "', '" . $item['new_name'] . "') WHERE (p.post_type = 'offer' OR p.post_type = 'brand') AND pm.meta_key = '" . $item['search_old_name'] . "'" );
				$wpdb->query( "UPDATE $wpdb->postmeta pm INNER JOIN $wpdb->posts p ON pm.post_id = p.ID SET pm.meta_key = REPLACE(pm.meta_key, '_" . $item['search_old_name'] . "', '_" . $item['new_name'] . "') WHERE (p.post_type = 'offer' OR p.post_type = 'brand') AND pm.meta_key = '_" . $item['search_old_name'] . "'" );

			}

			$wpdb->query( "UPDATE $wpdb->postmeta pm INNER JOIN $wpdb->posts p ON pm.post_id = p.ID SET pm.meta_value = 'field_" . $item['new_name'] . "' WHERE (p.post_type = 'offer' OR p.post_type = 'brand') AND pm.meta_key = '_" . $item['new_name'] . "' AND pm.meta_value = '" . $item['search_old_key'] . "'" );
			$wpdb->query( "UPDATE $wpdb->termmeta tm INNER JOIN $wpdb->term_taxonomy tt ON tm.term_id = tt.term_id SET tm.meta_key = REPLACE(tm.meta_key, '" . $item['search_old_name'] . "', '" . $item['new_name'] . "') WHERE tt.taxonomy = 'bm_campaign_management' AND tm.meta_key = '" . $item['search_old_name'] . "'" );
			$wpdb->query( "UPDATE $wpdb->termmeta tm INNER JOIN $wpdb->term_taxonomy tt ON tm.term_id = tt.term_id SET tm.meta_key = REPLACE(tm.meta_key, '_" . $item['search_old_name'] . "', '_" . $item['new_name'] . "') WHERE tt.taxonomy = 'bm_campaign_management' AND tm.meta_key = '_" . $item['search_old_name'] . "'" );
			$wpdb->query( "UPDATE $wpdb->termmeta tm INNER JOIN $wpdb->term_taxonomy tt ON tm.term_id = tt.term_id SET tm.meta_value = 'field_" . $item['new_name'] . "' WHERE tt.taxonomy = 'bm_campaign_management' AND pm.meta_key = '_" . $item['new_name'] . "' AND tm.meta_value = '" . $item['search_old_key'] . "'" );
			$result .= '<div style="margin-top: 10px; margin-left: 180px;">All meta keys with ' . $item['new_name'] . ' were updated in ' . ( microtime( true ) - $start ) . ' seconds.</div>';

		}

		$wpdb->query( "UPDATE $wpdb->postmeta pm INNER JOIN $wpdb->posts p ON pm.post_id = p.ID SET pm.meta_value = 1 WHERE (p.post_type = 'offer' OR p.post_type = 'brand') AND pm.meta_key = 'global_activity' AND pm.meta_value = 'true'" );
		$wpdb->query( "UPDATE $wpdb->postmeta pm INNER JOIN $wpdb->posts p ON pm.post_id = p.ID SET pm.meta_value = 0 WHERE (p.post_type = 'offer' OR p.post_type = 'brand') AND pm.meta_key = 'global_activity' AND pm.meta_value = 'false'" );

		if ( $echo ) {
			echo '<div class="brand-management-notice">' . $result . '</div>';
		} else {
			return $result;
		}

	}

	public static function migrate_withdrawal_and_deposit_methods_from_payment_groups() {

		global $wpdb;

		$payment_fields = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE meta_key LIKE '%offer_terms_with_draw_image%' OR meta_key LIKE '%offer_terms_02_image%'" );
		foreach ( $payment_fields as $payment_field ) {
			$meta_key     = $payment_field->meta_key;
			$label_key    = str_replace( 'image', 'label', $meta_key );
			$post_id      = $payment_field->post_id;
			$payment_post = unserialize( $payment_field->meta_value )[0];
			if ( ! $payment_post ) {
				continue;
			}

			$payment_post_image  = get_post_meta( $payment_post, 'group_paymentmethod_icon', true );
			$payment_post_title  = get_the_title( $payment_post );
			$payment_label_field = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE meta_key = '$label_key' AND post_id = $post_id" );
			if ( empty( $payment_label_field ) ) {
				$wpdb->query( "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES ($post_id, '$label_key', '$payment_post_title')" );
			}
			$wpdb->query( "UPDATE $wpdb->postmeta SET meta_value = $payment_post_image WHERE post_id = $post_id AND meta_key = '$meta_key'" );
		}

		return '<div class="brand-management-notice">payment methods updated</div>';

	}

	public static function migrate_post_and_term_types( bool $echo = true ) {

		global $wpdb;

		$start = microtime( true );

		if ( empty( $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE post_type = 'offer' LIMIT 1" ) ) ) {
			$wpdb->query( "UPDATE $wpdb->posts SET post_type = 'offer' WHERE post_type = 'brand'" );
		}

		$wpdb->query( "UPDATE $wpdb->posts SET post_type = 'brand' WHERE post_type = 'parent-brand'" );
		$wpdb->query( "UPDATE $wpdb->term_taxonomy SET taxonomy = 'bm_campaign_management' WHERE taxonomy = 'campaign-management'" );
		$wpdb->query( "UPDATE $wpdb->term_taxonomy SET taxonomy = 'bm_comparison_tables' WHERE taxonomy = 'bm_brand_comparison_tables'" );
		$wpdb->query( "UPDATE $wpdb->term_taxonomy SET taxonomy = 'bm_filter_tags' WHERE taxonomy = 'create-campaign-management'" );

		$result = '<div style="margin-top: 10px; margin-left: 180px;">All post types and taxonomy types in blog ' . get_current_blog_id() . ' were updated in ' . ( microtime( true ) - $start ) . ' seconds.</div>';

		if ( $echo ) {
			echo '<div class="brand-management-notice">' . $result . '</div>';
		} else {
			return $result;
		}

	}

	public static function toggle_show_table_title( bool $echo = true ) {
		global $wpdb;

		$start = microtime( true );

		$wpdb->query( "UPDATE $wpdb->termmeta SET meta_value = 1 - meta_value WHERE meta_key = 'show_table_title'" );

		$result = '<div style="margin-top: 10px; margin-left: 180px;">All table titles in blog ' . get_current_blog_id() . ' were toggled in ' . ( microtime( true ) - $start ) . ' seconds.</div>';

		if ( $echo ) {
			echo '<div class="brand-management-notice">' . $result . '</div>';
		} else {
			return $result;
		}

	}

	public static function move_tags_from_offer_to_brand( bool $echo = true ) {
		global $wpdb;

		$start = microtime( true );

		$tags = $wpdb->get_results( "SELECT term_id as id FROM $wpdb->term_taxonomy WHERE taxonomy='bm_filter_tags'" );

		foreach ( $tags as $tag ) {
			$tag_id = $tag->id;

			$offers_of_tag = $wpdb->get_results( "SELECT p.id FROM $wpdb->posts AS p JOIN $wpdb->term_relationships AS tr ON p.id=tr.object_id WHERE p.post_type='offer' AND tr.term_taxonomy_id=$tag_id" );

			foreach ( $offers_of_tag as $offer ) {
				$brand_id = get_field( "brand_id", $offer->id );
				if ( empty( $brand_id ) || get_post_type( $brand_id ) !== "brand" ) {
					continue;
				}
				$is_in_database = $wpdb->get_results( "SELECT * FROM $wpdb->term_relationships WHERE object_id=$brand_id AND term_taxonomy_id=$tag_id" );
				if ( ! empty( $is_in_database ) ) {
					$wpdb->query( "UPDATE $wpdb->term_relationships SET object_id = $brand_id WHERE object_id = $offer->id AND term_taxonomy_id = $tag_id" );
				} else {
					$wpdb->query( "DELETE FROM $wpdb->term_relationships WHERE object_id = $offer->id AND term_taxonomy_id = $tag_id" );
				}
			}
		}

		$result = '<div style="margin-top: 10px; margin-left: 180px;">All tags moved in blog ' . get_current_blog_id() . ' in ' . ( microtime( true ) - $start ) . ' seconds.</div>';

		if ( $echo ) {
			echo '<div class="brand-management-notice">' . $result . '</div>';
		} else {
			return $result;
		}

	}

	public static function move_sidebar_features_from_offer_to_brand( bool $echo = true ) {
		global $wpdb;

		$start = microtime( true );

		$offers_features = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE meta_key='review_features'" );
		foreach ( $offers_features as $offer_features ) {
			$brand_id = bm_get_brand_id( $offer_features->post_id );
			$features = unserialize( $offer_features->meta_value );

			foreach ( $features as $feature ) {
				$meta_key = 'sidebar_features_' . preg_replace( '/[^A-Za-z0-9\-]/', '_', strtolower( $feature ) );
				if ( empty( $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE post_id = $brand_id AND meta_key = '$meta_key'" ) ) ) {
					$wpdb->query( "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES ($brand_id, '$meta_key', '1')" );
				}
			}

		}

		$result = '<div style="margin-top: 10px; margin-left: 180px;">All featutes moved from offer to brand in blog ' . get_current_blog_id() . ' in ' . ( microtime( true ) - $start ) . ' seconds.</div>';

		if ( $echo ) {
			echo '<div class="brand-management-notice">' . $result . '</div>';
		} else {
			return $result;
		}

	}

	public static function migrate_filter_tags_from_offers_to_brands( $remove_processed_terms = 0 ): string {
		$output           = '';
		$stopwatch        = microtime( true );
		$count_success    = 0;
		$count_skipped    = 0;
		$processed_brands = [];

		$offers = get_posts( [
			'numberposts' => - 1,
			'post_type'   => 'offer',
			'fields'      => 'ids',
		] );
		foreach ( $offers as $offer_id ) {
			$brand_id = bm_get_brand_id( $offer_id );
			if ( $brand_id !== $offer_id ) {
				$offer_filter_tags = wp_get_object_terms( $offer_id, 'bm_filter_tags' );
				if ( ! empty( $offer_filter_tags ) ) {
					foreach ( $offer_filter_tags as $filter_tag ) {
						wp_set_object_terms( $brand_id, $filter_tag->term_id, 'bm_filter_tags', true );

						if ( $remove_processed_terms === 1 ) {
							wp_remove_object_terms( $offer_id, $filter_tag->term_id, 'bm_filter_tags' );
						}

						$processed_brands[ $brand_id ][] = $filter_tag->name;
					}

					$processed_brands[ $brand_id ] = array_unique( $processed_brands[ $brand_id ] );

					$count_success ++;
				} else {
					$count_skipped ++;
				}
			} else {
				$count_skipped ++;
			}
		}

		foreach ( $processed_brands as $brand_id => $filter_tags ) {
			$output .= 'Brand ' . get_the_title( $brand_id ) . ' (ID ' . $brand_id . ') - ';

			foreach ( $filter_tags as $filter_tag ) {
				$output .= $filter_tag . ', ';
			}

			$output .= '<br>';
		}

		$output .= '<b>Success ' . $count_success . ' | Skipped ' . $count_skipped . '</b><br>';
		$output .= 'Completed for ' . ( microtime( true ) - $stopwatch ) . ' seconds.<br>';

		return '<div class="brand-management-notice"><div style="margin-top: 10px; margin-left: 180px;">' . $output . '</div></div>';
	}

}
