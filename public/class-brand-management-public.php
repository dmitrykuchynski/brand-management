<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Brand_Management
 * @subpackage Brand_Management/public
 */
class Brand_Management_Public {

	/**
	 * Register critical stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_critical_styles(): void {
		wp_enqueue_style( 'brand-management-public', ( new Brand_Management_Template_Loader() )->search_path( 'css/brand-management-public.css' ) );
	}

	/**
	 * Adds plugin styles and scripts to the output
	 * queue if they haven't been initialized yet.
	 *
	 * @since    2.0.0
	 */
	public static function load_assets( $style_file_name, $prevent_enqueue_scripts = false ): void {

		$styles = [
			$style_file_name,
		];

		if ( $prevent_enqueue_scripts === false ) {
			$styles[] = 'brand-management-slick';
		}

		foreach ( $styles as $style ) {
			if ( $style && ! wp_style_is( $style ) ) {
				wp_enqueue_style( $style, ( new Brand_Management_Template_Loader() )->search_path( 'css/' . $style . '.css' ) );
			}
		}

		if ( $prevent_enqueue_scripts === false ) {
			$scripts = [
				'brand-management-slick-carousel',
				'brand-management-slick-lightbox',
			];

			foreach ( $scripts as $script ) {
				if ( ! wp_script_is( $style_file_name ) ) {
					wp_enqueue_script( $script, ( new Brand_Management_Template_Loader() )->search_path( 'js/' . $script . '.js' ), [ 'jquery' ], null, true );
				}
			}
		}

	}

	/**
	 * POST request handler for voting for the offer.
	 * Expect 'nonce', 'offer_id' and 'operation' fields.
	 * Checks existing cookies and writes new data to them.
	 * Writes the new vote value for the offer.
	 *
	 * @since   2.3.0
	 */
	public function likes_handler(): void {

		// Execution of the script will be stopped if the nonce cannot be verified.
		check_ajax_referer( 'ajax_handler-nonce', 'nonce' );

		$already_voted_offers = [];

		if ( ! isset( $_POST['offer_id'], $_POST['operation'] ) ) {
			$this->json_response( [
				'fail' => _x( 'Incorrect request.', 'likes-ajax-handler', 'brand-management' ),
			] );
		}

		$blog_id   = get_current_blog_id();
		$offer_id  = htmlspecialchars( $_POST['offer_id'] );
		$operation = htmlspecialchars( $_POST['operation'] );

		if ( isset( $_COOKIE['bm_already_voted_offers'] ) ) {
			$already_voted_offers = json_decode( stripslashes( $_COOKIE['bm_already_voted_offers'] ), true ) ?? [];

			if ( ! empty( $already_voted_offers ) && array_key_exists( $blog_id, $already_voted_offers ) && in_array( $offer_id,
					array_column( $already_voted_offers[ $blog_id ], 'offer_id' ), true ) ) {
				$this->json_response( [
					'fail' => _x( 'You have already voted.', 'likes-ajax-handler', 'brand-management' ),
				] );
			}
		}

		$likes    = (int) get_field( 'offer_likes', $offer_id );
		$dislikes = (int) get_field( 'offer_dislikes', $offer_id );

		if ( $operation === 'like' ) {
			$likes ++;

			update_field( 'offer_likes', $likes, $offer_id );
		} elseif ( $operation === 'dislike' ) {
			$dislikes ++;

			update_field( 'offer_dislikes', $dislikes, $offer_id );
		}

		if ( ! array_key_exists( $blog_id, $already_voted_offers ) ) {
			$already_voted_offers[ $blog_id ] = [];
		}

		$already_voted_offers[ $blog_id ] = array_merge( $already_voted_offers[ $blog_id ], [
			[
				'offer_id' => $offer_id,
				'vote'     => $operation,
			]
		] );

		setcookie( 'bm_already_voted_offers', json_encode( $already_voted_offers ), time() + 60 * 60 * 24 * 30, '/' );

		$this->json_response( [
			'likes'    => $likes,
			'dislikes' => $dislikes,
		] );

	}

	/**
	 * A JSON response is returned and the app terminates.
	 *
	 * @since   2.3.0
	 */
	private function json_response( $response ): void {

		header( 'Content-Type: application/json; charset=utf-8' );
		echo json_encode( $response );
		wp_die();

	}

	/**
	 * Data is given for the work of the handler on the frontend.
	 *
	 * @since   2.3.0
	 */
	public function likes_ajax_handler(): void {

		wp_register_script( 'brand-management-ajax-handler', '' );

		wp_enqueue_script( 'brand-management-ajax-handler' );

		wp_localize_script( 'brand-management-ajax-handler', 'likes_handler', [
			'id'        => get_current_blog_id(),
			'url'       => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'ajax_handler-nonce' ),
			'fail_text' => _x( 'You have already voted.', 'likes-ajax-handler', 'brand-management' ),
		] );

	}

	/**
	 * Gets the campaign id's as a parameter via post request.
	 * Returns data on the results of voting on offers.
	 *
	 * @since   2.5.0
	 */
	public function ajax_get_voting_data(): void {

		// Execution of the script will be stopped if the nonce cannot be verified.
		check_ajax_referer( 'ajax_handler-nonce', 'nonce' );

		global $wpdb;

		if ( ! isset( $_POST['campaign_ids'] ) ) {
			$this->json_response( [
				'fail' => 'An error occurred in data processing.',
			] );
		}

		$campaign_offer_ids           = [];
		$campaign_offer_ids_for_query = '';
		$processed_voting_results     = [];

		$campaign_ids = json_decode( stripslashes( $_POST['campaign_ids'] ), true ) ?? [];
		if ( ! is_array( $campaign_ids ) ) {
			$this->json_response( [
				'fail' => 'An error occurred in data processing.',
			] );
		}
		foreach ( $campaign_ids as $campaign_id ) {
			$campaign_offer_ids[] = bm_get_field( 'offers_list', 'bm_campaign_management_' . $campaign_id );
		}

		$campaign_offer_ids = array_merge( ...$campaign_offer_ids );
		foreach ( $campaign_offer_ids as $key => $campaign_offer_id ) {
			if ( $key === array_key_last( $campaign_offer_ids ) ) {
				$campaign_offer_ids_for_query .= 'post_id = ' . esc_sql( $campaign_offer_id );
			} else {
				$campaign_offer_ids_for_query .= 'post_id = ' . esc_sql( $campaign_offer_id ) . ' OR ';
			}
		}

		if ( empty( $campaign_offer_ids_for_query ) ) {
			$this->json_response( [
				'fail' => 'An error occurred in data processing.',
			] );
		}

		$offers_voting_results = $wpdb->get_results( "SELECT post_id, meta_key, meta_value FROM $wpdb->postmeta WHERE (" . $campaign_offer_ids_for_query . ") AND (meta_key = 'offer_likes' OR meta_key = 'offer_dislikes')" );
		if ( ! is_array( $offers_voting_results ) ) {
			$this->json_response( [
				'fail' => 'An error occurred in data processing.',
			] );
		}
		foreach ( $offers_voting_results as $offer_voting_result ) {
			if ( $offer_voting_result->meta_key === 'offer_likes' ) {
				$processed_voting_results[ $offer_voting_result->post_id ]['l'] = $offer_voting_result->meta_value;
			} elseif ( $offer_voting_result->meta_key === 'offer_dislikes' ) {
				$processed_voting_results[ $offer_voting_result->post_id ]['d'] = $offer_voting_result->meta_value;
			}
		}

		$this->json_response( $processed_voting_results );

	}

	/**
	 * Gets the campaign_id and additional attributes of the
	 * shortcode, such as campaign_filter and campaign_display.
	 * Returns the campaign table without already displayed offers.
	 *
	 * @since   3.0.0
	 */
	public function ajax_get_campaign_offers(): void {

		// Execution of the script will be stopped if the nonce cannot be verified.
		check_ajax_referer( 'ajax_handler-nonce', 'nonce' );

		if ( ! isset( $_POST['campaign_id'] ) ) {
			$this->json_response( [
				'fail' => 'An error occurred in data processing.',
			] );
		}

		$campaign_id            = (int) $_POST['campaign_id'];
		$campaign_type          = esc_attr( $_POST['campaign_type'] ?? '' );
		$campaign_filter        = esc_attr( $_POST['campaign_filter'] ?? '' );
		$campaign_display       = esc_attr( $_POST['campaign_display'] ?? '' );
		$rebuild_campaign_table = esc_attr( $_POST['rebuild_campaign_table'] ?? '' );

		$shortcode_atts = [
			'id'      => $campaign_id,
			'filter'  => $campaign_filter,
			'display' => $campaign_display,
			'ajax'    => true,
		];

		if ( $rebuild_campaign_table === 'true' ) {
			$shortcode_atts['rebuild_campaign_table'] = true;
		}

		if ( $campaign_type === 'campaign_compact' ) {
			$shortcode_html = ( new Campaign_Compact_Shortcode() )->shortcode( $shortcode_atts );
		} else {
			$shortcode_html = ( new Campaign_Shortcode() )->shortcode( $shortcode_atts );
		}

		echo $shortcode_html;

		wp_die();

	}

	public function try_hide_default_page_disclaimer(): void {

		$campaign_tables = Brand_Management_Storage::$campaign_tables;

		if ( ! empty( $campaign_tables ) && in_array( true, array_column( $campaign_tables, 'is_show_disclaimer' ), true ) ) {
			$default_page_disclaimer_selector = bm_get_option( 'default_page_disclaimer_selector' );

			$styles = $default_page_disclaimer_selector . ' { display: none !important; }';

			wp_register_style( 'brand-management-inline', false );
			wp_enqueue_style( 'brand-management-inline' );
			wp_add_inline_style( 'brand-management-inline', $styles );
		}

	}

}
