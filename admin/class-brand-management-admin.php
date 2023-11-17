<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Brand_Management
 * @subpackage Brand_Management/admin
 */
class Brand_Management_Admin {
	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $brand_management The ID of this plugin.
	 */
	private string $brand_management;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $version The current version of this plugin.
	 */
	private string $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param  string  $brand_management  The name of this plugin.
	 * @param  string  $version  The version of this plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $brand_management, $version ) {

		$this->brand_management = $brand_management;
		$this->version          = $version;

	}

	/**
	 * Registration of styles for the admin panel.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles(): void {

		wp_enqueue_style( $this->brand_management, plugin_dir_url( __FILE__ ) . 'css/brand-management-admin.css', [], $this->version );
		wp_enqueue_style( 'jquery-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css' );

	}

	/**
	 * Registration of scripts for the admin panel.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts(): void {

		wp_enqueue_script( $this->brand_management, plugin_dir_url( __FILE__ ) . 'js/brand-management-admin.js', [ 'jquery' ], $this->version );
		wp_localize_script( $this->brand_management, 'brand_management_admin_data', [ 'admin_ajax_url' => admin_url( 'admin-ajax.php' ) ] );
		wp_enqueue_script( 'jquery-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', [ 'jquery' ] );

	}

	/**
	 * Formation of the menu for the admin panel.
	 *
	 * @since 1.0.0
	 */
	public function menu_formation(): void {

		add_submenu_page(
			'edit.php?post_type=brand',
			'Add New Offer',
			'Add New Offer',
			'export',
			'post-new.php?post_type=offer',
		);

		add_submenu_page(
			'edit.php?post_type=brand',
			'Edit Filter Tags',
			'Edit Filter Tags',
			'export',
			'edit-tags.php?taxonomy=bm_filter_tags',
		);

		add_submenu_page(
			'edit.php?post_type=brand',
			'Payment Methods',
			'Payment Methods',
			'export',
			'edit.php?post_type=payment_method',
		);

		add_filter( 'custom_menu_order', [ $this, 'submenu_order' ] );

	}

	/**
	 * Formation of the plugin submenu.
	 *
	 * @since 2.1.0
	 */
	public function submenu_order( $menu_order ): bool {

		global $submenu;

		foreach ( $submenu['edit.php?post_type=brand'] as $key => $item ) {
			if ( in_array( 'edit.php?post_type=offer', $item, true ) ) {
				$submenu['edit.php?post_type=brand'][1] = $submenu['edit.php?post_type=brand'][ $key ];
				unset( $submenu['edit.php?post_type=brand'][ $key ] );
				ksort( $submenu['edit.php?post_type=brand'] );
			}
			if ( in_array( 'post-new.php?post_type=offer', $item, true ) ) {
				$submenu['edit.php?post_type=brand'][2] = $submenu['edit.php?post_type=brand'][ $key ];
				unset( $submenu['edit.php?post_type=brand'][ $key ] );
				ksort( $submenu['edit.php?post_type=brand'] );
			}
			if ( in_array( 'acf-options-brand-management-options', $item, true ) ) {
				$submenu['edit.php?post_type=brand'][50] = $submenu['edit.php?post_type=brand'][ $key ];
				unset( $submenu['edit.php?post_type=brand'][ $key ] );
				ksort( $submenu['edit.php?post_type=brand'] );
			}
		}

		return $menu_order;

	}

	/**
	 * Remove unused meta boxes from admin screens.
	 *
	 * @since 1.0.0
	 */
	public function remove_unused_meta_boxes(): void {

		remove_meta_box( 'slugdiv', 'brand', 'normal' );
		remove_meta_box( 'slugdiv', 'offer', 'normal' );
		remove_meta_box( 'wpseo_meta', 'brand', 'normal' );
		remove_meta_box( 'wpseo_meta', 'offer', 'normal' );
		remove_meta_box( 'td_post_theme_settings_metabox', 'brand', 'normal' );
		remove_meta_box( 'td_post_theme_settings_metabox', 'offer', 'normal' );

	}

	public function form_upload_csv_for_popups(): void {

		if ( ! empty( $_FILES['popup_csv_file'] ) ) {
			$file       = $_FILES['popup_csv_file']['tmp_name'];
			$data_array = [];
			if ( is_file( $_FILES['popup_csv_file']['tmp_name'] ) ) {
				file_put_contents( $file, str_replace( ';', ',', file_get_contents( $file ) ) );
			}
			if ( ( $handle = fopen( $file, "r" ) ) !== false ) {
				while ( ( $data = fgetcsv( $handle, 1000, "," ) ) !== false ) {
					$data_array['data'][ $data[0] ] = $data[1];
				}
				fclose( $handle );
			}
			$data_array['modified'] = date( 'Y-m-d H:i:s' );
			$data_array['user']     = get_user_meta( get_current_user_id(), 'nickname' )[0];
			echo '<div class="postbox"><div class="inside">File was loaded successful</div></div>';
			$data_array = serialize( $data_array );
			update_option( 'popup_offers_options_csv_array', $data_array );
		} else {
			echo '<form enctype="multipart/form-data" method="POST" class="postbox" action="/wp-admin/edit-tags.php?taxonomy=bm_popup_offers">';
			$csv_array_field = get_option( 'popup_offers_options_csv_array' );
			if ( ! empty( $csv_array_field ) ) {
				$csv_array_data = unserialize( $csv_array_field );
				echo '<div class="popup-csv-form__notice">Last updated ' . $csv_array_data['modified'] . ' by user ' . $csv_array_data['user'] . '</div>';
			}
			echo '<div class="popup-csv-form__title">Popup csv bulk setting</div>';
			echo '<div class="popup-csv-form__instructions">Upload csv file with two columns (postID, popupID) to arrange popups by bulk. If a page/post have it\'s own popup which was set up while editing a post/page, csv settings will have lower priority.</div>';
			echo '<div class="inside"><input name="popup_csv_file" type="file" required accept=".csv"><button type="submit" class="button button-primary">Upload</button></div></form>';
		}

	}

	/**
	 * Filters the column headers for a list table on a specific screen.
	 * Fires in each custom column on the posts list table. Allows you
	 * to add or remove (unset) custom columns to the list page.
	 *
	 * @since 1.0.0
	 */
	public function columns_filter(): void {

		add_filter( 'pre_get_terms', [ $this, 'extend_admin_search' ] );

		// Customization bm_filter_tag search.
		add_filter( 'acf/fields/taxonomy/query', [ $this, 'bm_filters_tag_taxonomy_query' ] );

		// Geo filtering functionality.
		add_filter( 'acf/load_field/name=bm_options_country_listing', [ $this, 'bm_options_load_country_listing_field' ] );
		add_filter( 'acf/load_field/name=show_in_countries', [ $this, 'load_geo_settings_country_listing' ] );
		add_filter( 'acf/load_field/name=regional_campaign_country', [ $this, 'load_geo_settings_country_listing' ] );
		add_filter( 'acf/load_field/name=campaign_region', [ $this, 'load_geo_settings_country_listing' ] );

		// Customization offer columns.
		add_filter( 'manage_edit-offer_columns', [ $this, 'edit_offer_columns' ] );
		add_action( 'manage_offer_posts_custom_column', [
			$this,
			'offer_posts_custom_column'
		], 10, 2 );

		// Customization brand columns.
		add_filter( 'manage_edit-brand_columns', [ $this, 'edit_brand_columns' ] );
		add_action( 'manage_brand_posts_custom_column', [
			$this,
			'brand_posts_custom_column'
		], 10, 2 );

		// Customization campaign management columns.
		add_filter( 'manage_edit-bm_campaign_management_columns', [ $this, 'edit_bm_campaign_management_columns' ] );
		add_action( 'manage_bm_campaign_management_custom_column', [
			$this,
			'bm_campaign_management_custom_column'
		], 10, 3 );

		// Customization comparison tables columns.
		add_filter( 'manage_edit-bm_comparison_tables_columns', [ $this, 'edit_bm_comparison_tables_columns' ] );
		add_action( 'manage_bm_comparison_tables_custom_column', [
			$this,
			'bm_comparison_tables_custom_column'
		], 10, 3 );

		// Customization recommended offers widget columns.
		add_filter( 'manage_edit-bm_recommended_offers_columns', [
			$this,
			'edit_bm_recommended_offers_widget_tables_columns'
		] );
		add_action( 'manage_bm_recommended_offers_custom_column', [
			$this,
			'bm_recommended_offers_widget_custom_column'
		], 10, 3 );

		// Customization popup offers for non-affiliated columns.
		add_filter( 'manage_edit-bm_popup_offers_columns', [
			$this,
			'edit_bm_popup_offers_tables_columns'
		] );
		add_action( 'manage_bm_popup_offers_custom_column', [
			$this,
			'bm_popup_offers_widget_custom_column'
		], 10, 3 );

		// Customization payment methods columns.
		add_filter( 'manage_edit-payment_method_columns', [
			$this,
			'edit_payment_method_tables_columns'
		] );
		add_action( 'manage_payment_method_posts_custom_column', [
			$this,
			'payment_method_custom_column'
		], 10, 3 );

	}

	public function bm_filters_tag_taxonomy_query( $args ) {

		if ( $args['taxonomy'] === 'bm_filter_tags' && empty( trim( $_POST['s'] ) ) ) {
			$bm_filter_tags = get_terms( [
				'taxonomy'   => 'bm_filter_tags',
				'orderby'    => 'count',
				'order'      => 'DESC',
				'hide_empty' => false,
			] );

			$bm_filter_tags = array_map( function ( $tag ) {
				$result_tag       = new stdClass();
				$result_tag->id   = $tag->term_id;
				$result_tag->text = $tag->name;

				return $result_tag;
			}, $bm_filter_tags );

			$response          = new stdClass();
			$response->results = $bm_filter_tags;
			$response->more    = false;
			$response          = json_encode( $response );

			echo $response;
			wp_die();
		}

		return $args;

	}

	public function extend_admin_search( $q ) {

		global $pagenow;
		global $wpdb;

		if ( $pagenow === 'edit-tags.php' && $q->query_vars['taxonomy'][0] === 'bm_campaign_management' && ! empty( $q->query_vars['search'] ) && $q->query_vars['search'] !== '' ) {
			$search = $q->query_vars['search'];
			if ( str_contains( $search, '[campaign id="' ) || str_contains( $search, '[sidebar id="' ) ) {
				$search                            = mb_substr( mb_stristr( $search, 'id="' ), 4 );
				$search                            = strstr( $search, '"', true );
				$q->query_vars['term_taxonomy_id'] = array( (int) $search );
			} else {
				$like = '%' . $search . '%';

				//search campaigns by name
				$searched_by_name_query = "SELECT term_id FROM `{$wpdb->prefix}terms` WHERE `name` LIKE %s
AND term_id IN (SELECT term_id from `{$wpdb->prefix}term_taxonomy` WHERE `taxonomy` = 'bm_campaign_management')";
				$searched_by_name       = $wpdb->get_results( $wpdb->prepare( $searched_by_name_query, $like ),
					ARRAY_A );
				$searched_by_name_ids   = array_column( $searched_by_name, 'term_id' );

				//search campaigns by id
				$searched_by_id_query = "SELECT term_id FROM `{$wpdb->prefix}terms` WHERE term_id = %s
AND term_id IN (SELECT term_id from `{$wpdb->prefix}term_taxonomy` WHERE `taxonomy` = 'bm_campaign_management')";
				$searched_by_id       = $wpdb->get_results( $wpdb->prepare( $searched_by_id_query, intval( $search ) ),
					ARRAY_A );
				$searched_by_id_ids   = array_column( $searched_by_id, 'term_id' );

				//search offers by title
				$searched_offers_ids_query = "SELECT ID FROM {$wpdb->prefix}posts
WHERE post_type = 'offer' 
AND post_title LIKE %s";
				$searched_offers           = $wpdb->get_results( $wpdb->prepare( $searched_offers_ids_query, $like ),
					ARRAY_A );
				$searched_offers_ids       = array_column( $searched_offers, 'ID' );

				// Search campaigns by offers.
				$searched_by_offers_ids = [];
				if ( ! empty( $searched_offers_ids ) ) {
					$searched_by_offers_query = "
					SELECT term_id
					FROM `{$wpdb->prefix}termmeta`
					WHERE term_id IN (SELECT term_id from `{$wpdb->prefix}term_taxonomy` WHERE `taxonomy` = 'bm_campaign_management')
					    AND (meta_key = 'offers_list' AND (";
				foreach ( $searched_offers_ids as $i => $offer_id ) {
						if ( $i !== 0 ) {
						$searched_by_offers_query .= "OR ";
					}
					$searched_by_offers_query .= "meta_value LIKE '%\"{$offer_id}\"%'";
				}
				$searched_by_offers_query .= "))";
				$searched_by_offers       = $wpdb->get_results( $wpdb->prepare( $searched_by_offers_query, $like ),
					ARRAY_A );
				$searched_by_offers_ids   = array_column( $searched_by_offers, 'term_id' );
				}

				//merge all results
				$searched_ids                      = array_unique( array_merge( $searched_by_id_ids,
					$searched_by_name_ids, $searched_by_offers_ids ) );
				$searched_ids                      = array_map( 'intval', $searched_ids );
				$q->query_vars['term_taxonomy_id'] = $searched_ids;
			}
			if ( count( $q->query_vars['term_taxonomy_id'] ) ) {
				$q->query_vars['name__like'] = '';
				$q->query_vars['search']     = '';
			}
		}

		return $q;

	}

	public function edit_offer_columns(): array {

		return [
			'cb'     => '<input type="checkbox" />',
			'title'  => 'Offer',
			'brand'  => 'Brand',
			'global' => 'Global ON / OFF',
			'short'  => 'Shortcode',
			'date'   => 'Date',
		];

	}

	public function offer_posts_custom_column( $column, $post_id ): void {

		switch ( $column ) {
			case 'brand' :
				$brand_id = bm_get_brand_id( $post_id );
				if ( bm_is_offer( $brand_id ) ) {
					echo 'The brand is not defined for this offer.<br> You can <a href="' . get_blog_details()->path . 'wp-admin?brand_management=create_brand_from_offer&offer_id=' . $post_id . '">create a brand based on this offer</a>.';
				} else {
					echo '<a href="' . get_edit_post_link( $brand_id ) . '">' . get_the_title( $brand_id ) . '</a>';
				}
				break;

			case 'global' :
				echo $this->global_activity_switch( $post_id );
				break;

			case 'short' :
				echo '<code class="shortcode"">[offer id="' . $post_id . '"]</code>';
				break;

			default :
				break;
		}

	}

	private function global_activity_switch( $post_id ): string {
		return '<label class="switch">
					<input class="' . ( ( get_post_meta( $post_id, 'global_activity', true ) === '0' ) ?: 'checked' ) . '" type="checkbox" value="' . $post_id . '" />
					<span class="slider round"></span>
				</label>';
	}

	public function edit_brand_columns(): array {
		return [
			'cb'     => '<input type="checkbox" />',
			'title'  => 'Brand',
			'image'  => 'Logotype',
			'global' => 'Global ON / OFF',
			'short'  => 'Shortcode',
			'date'   => 'Date',
		];
	}

	public function brand_posts_custom_column( $column, $post_id ): void {

		switch ( $column ) {
			case 'image' :
				echo empty( bm_get_brand_logo( $post_id ) ) ? 'Logotype is not defined.' : '<img src="' . bm_get_brand_logo( $post_id ) . '" style="width: 30%;">';
				break;

			case 'global' :
				echo $this->global_activity_switch( $post_id );
				break;

			case 'short' :
				echo '<code class="shortcode">[brand id="' . $post_id . '"]</code>';
				break;

			default :
				break;
		}

	}

	public function edit_bm_campaign_management_columns( $columns ): array {
		return [
			'title'      => 'Title',
			'offers'     => 'Offers',
			'shortcodes' => 'Shortcodes',
		];
	}

	public function bm_campaign_management_custom_column( $value, $name, $post_id ): void {

		$taxonomy = 'bm_campaign_management';

		switch ( $name ) {
			case 'title' :
				$campaign           = get_term_by( 'id', $post_id, $taxonomy );
				$campaign_edit_link = get_edit_term_link( $post_id, $taxonomy, 'brand' );
				echo '<strong><a class="row-title" href="' . $campaign_edit_link . '" aria-label="Campaign">' . $campaign->name . '</a></strong><div class="row-actions"><span class="edit"><a href="' . $campaign_edit_link . '" aria-label="Edit “' . $campaign->name . '”">Edit</a></span> | <span class="duplicate-campaign" data-id="' . $post_id . '" aria-label="Duplicate">Duplicate</span> | <span class="create-regional-campaign" data-id="' . $post_id . '" aria-label="Create Regional Campaign">Create Regional Campaign</span></div>';
				break;

			case 'offers' :
				$offers      = get_field( 'offers_list', $taxonomy . '_' . $post_id, true );
				$offers_list = '';

				if ( ! empty( $offers ) ) {
					foreach ( $offers as $offer ) {
						$offers_list .= get_the_title( $offer ) . ', ';
					}

					$offers_list = rtrim( $offers_list, ', ' );
				} else {
					$offers_list = 'No offers selected.';
				}
				echo $offers_list;
				break;

			case 'shortcodes' :
				echo '
					<code class="shortcode">[campaign id="' . $post_id . '"]</code><br>
					<code class="shortcode">[campaign_compact id="' . $post_id . '"]</code><br>
					<code class="shortcode">[campaignfullwidth id="' . $post_id . '"]</code><br>
					<code class="shortcode">[sidebar id="' . $post_id . '"]</code>
				';
				break;
		}

	}

	public function edit_bm_comparison_tables_columns( $columns ): array {
		return [
			'table_name'        => 'Table Name',
			'comparison_brands' => 'Comparison Brands',
			'shortcode'         => 'Shortcode',
		];
	}

	public function bm_comparison_tables_custom_column( $value, $name, $post_id ): void {

		$taxonomy = 'bm_comparison_tables';

		switch ( $name ) {
			case 'table_name' :
				$comparison_table           = get_term_by( 'id', $post_id, $taxonomy );
				$comparison_table_edit_link = get_edit_term_link( $post_id, $taxonomy, 'brand' );
				echo '<strong><a class="row-title" href="' . $comparison_table_edit_link . '" aria-label="Comparison Table">' . $comparison_table->name . '</a></strong><div class="row-actions"><span class="edit"><a href="' . $comparison_table_edit_link . '" aria-label="Edit “' . $comparison_table->name . '”">Edit</a></span></div>';
				break;

			case 'comparison_brands' :
				$comparison_brands      = get_field( 'comparison_brands', $taxonomy . '_' . $post_id, true );
				$comparison_brands_list = '';

				if ( ! empty( $comparison_brands ) ) {
					foreach ( $comparison_brands as $comparison_brand ) {
						$comparison_brands_list .= get_the_title( $comparison_brand ) . ', ';
					}

					$comparison_brands_list = rtrim( $comparison_brands_list, ', ' );
				} else {
					$comparison_brands_list = 'No brands selected.';
				}

				echo $comparison_brands_list;
				break;

			case 'shortcode' :
				echo '<code class="shortcode">[comparison_table id="' . $post_id . '"]</code>';
				break;
		}

	}

	public function edit_bm_recommended_offers_widget_tables_columns( $columns ): array {
		return [
			'table_name' => 'Widget Name',
			'offers'     => 'Offers',
			'shortcode'  => 'Shortcode',
		];
	}

	public function bm_recommended_offers_widget_custom_column( $value, $name, $post_id ): void {

		$taxonomy = 'bm_recommended_offers';
		switch ( $name ) {
			case 'table_name' :
				$widget           = get_term_by( 'id', $post_id, $taxonomy );
				$widget_edit_link = get_edit_term_link( $post_id, $taxonomy, 'offer' );
				echo '<strong><a class="row-title" href="' . $widget_edit_link . '" aria-label="Comparison Table">' . $widget->name . '</a></strong><div class="row-actions"><span class="edit"><a href="' . $widget_edit_link . '" aria-label="Edit “' . $widget->name . '”">Edit</a></span></div>';
				break;

			case 'offers' :
				$offers      = get_field( 'recommended_offers_list', $taxonomy . '_' . $post_id, true );
				$offers_list = '';

				if ( ! empty( $offers ) ) {
					foreach ( $offers as $offer ) {
						$offers_list .= get_the_title( $offer ) . ', ';
					}

					$offers_list = rtrim( $offers_list, ', ' );
				} else {
					$offers_list = 'No offers selected.';
				}
				echo $offers_list;
				break;

			case 'shortcode' :
				echo '<code class="shortcode">[recommended_offers_widget id="' . $post_id . '"]</code>';
				break;
		}

	}

	public function edit_bm_popup_offers_tables_columns( $columns ): array {
		return [
			'table_name' => 'Popup Name',
			'offers'     => 'Offers',
			'shortcode'  => 'Shortcode',
		];
	}

	public function bm_popup_offers_widget_custom_column( $value, $name, $post_id ): void {

		$taxonomy = 'bm_popup_offers';
		switch ( $name ) {
			case 'table_name' :
				$widget           = get_term_by( 'id', $post_id, $taxonomy );
				$widget_edit_link = get_edit_term_link( $post_id, $taxonomy, 'offer' );
				echo '<strong><a class="row-title" href="' . $widget_edit_link . '" aria-label="Comparison Table">' . $widget->name . '</a></strong><div class="row-actions"><span class="edit"><a href="' . $widget_edit_link . '" aria-label="Edit “' . $widget->name . '”">Edit</a></span></div>';
				break;

			case 'offers' :
				$offers = get_field( 'field_popup_offers_list', $taxonomy . '_' . $post_id ) ?: [];
				$offers = array_filter( $offers, function ( $offer ) {
					return Campaign_Shortcode::is_global_active( $offer );
				} );

				if ( ! empty( $offers ) ) {
					$offers_names_array = [];
					foreach ( $offers as $offer ) {
						$offers_names_array[] = get_the_title( $offer['offer'] );
					}

					$offers_list = implode( ', ', $offers_names_array );
				} else {
					$offers_list = 'No offers selected.';
				}
				echo $offers_list;
				break;

			case 'shortcode' :
				echo '<code class="shortcode">[popup_offers id="' . $post_id . '"]</code>';
				break;
		}

	}


	public function edit_payment_method_tables_columns( $columns ): array {
		$columns['image'] = 'Image';

		return $columns;
	}

	public function payment_method_custom_column( $column, $payment_method_id ): void {

		if ( $column === 'image' ) {
			$payment_method_logo = get_field( 'payment_method_logo', $payment_method_id );
			if ( ! empty( $payment_method_logo ) ) {
				echo '<img  class="payment_method__admin_image" src="' . $payment_method_logo . '" />';
			} else {
				echo 'No Image.';
			}
		}

	}

	/**
	 * The presence of the global_on_off parameter in the post is checked.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function ajax_global_activity(): void {

		if ( isset( $_POST['post_id'], $_POST['do'] ) ) {
			switch ( $_POST['do'] ) {
				case 'on' :
					if ( metadata_exists( 'post', $_POST['post_id'], 'global_activity' ) ) {
						update_post_meta( $_POST['post_id'], 'global_activity', '1' );
					} else {
						add_post_meta( $_POST['post_id'], 'global_activity', '1' );
					}
					break;

				case 'off' :
					update_post_meta( $_POST['post_id'], 'global_activity', '0' );
					break;
			}
		}

		wp_die();

	}

	public function ajax_get_ordered_offers(): void {

		if ( isset( $_POST['tag_id'], $_POST['campaign_id'] ) ) {
			$tag_id             = $_POST['tag_id'];
			$campaign           = 'bm_campaign_management_' . $_POST['campaign_id'];
			$offers_of_campaign = bm_get_field( 'offers_list', $campaign );
			$filtered_offers    = [];
			foreach ( $offers_of_campaign as $offer ) {
				$tags_array = bm_get_brand_tags( $offer, $campaign, true ) ?: [];
				foreach ( $tags_array as $tag ) {
					if ( $tag->term_id === (int) $tag_id ) {
						$filtered_offers[] = $offer;
						break;
					}
				}
			}
			$reordered_tags = bm_get_field( 'field_offers_order_within_a_tag', $campaign ) ?: [];
			foreach ( $reordered_tags as $reordered_tag ) {
				if ( $reordered_tag['ordering_tag'] === $tag_id ) {
					$ordered_old_value   = $reordered_tag['ordering_offers'] ?: [];
					$all_filtered_offers = $filtered_offers;
					// offers which order is already known
					$first_part = array_diff( $ordered_old_value,
						array_diff( $ordered_old_value, $all_filtered_offers ) );
					// new offers without order
					$last_part       = array_diff( $all_filtered_offers, $ordered_old_value );
					$filtered_offers = array_merge( $first_part, $last_part );
				}
			}
			$filtered_offers = array_map( function ( $id ) {
				return [
					'id'   => $id,
					'name' => get_the_title( $id ) ?: ''
				];
			}, $filtered_offers );
			echo json_encode( $filtered_offers );
		}
		wp_die();

	}

	public function ajax_get_ordered_tags(): void {

		if ( isset( $_POST['campaign_id'], $_POST['campaign_type'] ) ) {
			$campaign_type = $_POST['campaign_type'];
			$taxonomy      = ( $campaign_type === 'campaign' ) ? 'bm_campaign_management_' : 'bm_regional_campaigns_';

			$campaign       = $taxonomy . $_POST['campaign_id'];
			$reordered_tags = bm_get_field( 'field_offers_order_within_a_tag', $campaign ) ?: [];
			$result         = [];
			foreach ( $reordered_tags as $tag ) {
				$result[] = $tag['ordering_tag'];
			}
			echo json_encode( $result );
		}
		wp_die();

	}

	public function ajax_get_auto_filter_tags(): void {

		if ( isset( $_POST['campaign_id'] ) ) {
			$campaign           = 'bm_campaign_management_' . $_POST['campaign_id'];
			$offers_of_campaign = bm_get_field( 'offers_list', $campaign );
			$tags               = [];
			foreach ( $offers_of_campaign as $offer ) {
				$tags_array = bm_get_brand_tags( $offer, $campaign, true ) ?: [];
				foreach ( $tags_array as $tag ) {
					$tags[] = [
						'id'   => $tag->term_id,
						'name' => $tag->name,
					];
				}
			}
			$tags_ids                  = array_map( function ( $item ) {
				return (string) $item['id'];
			}, $tags );
			$previous_value_tags_id    = bm_get_field( 'campaign_all_filter_tag_order', $campaign ) ?: [];
			$previous_value_tags_array = [];
			foreach ( $previous_value_tags_id as $tag_id ) {
				if ( in_array( (string) $tag_id, $tags_ids ) ) {
					$previous_value_tags_array[] = [
						'id'   => $tag_id,
						'name' => get_term( $tag_id )->name,
					];
				}
			}
			$tags = array_merge( $previous_value_tags_array, $tags );
			$tags = array_unique( $tags, SORT_REGULAR );
			$tags = array_values( $tags );
			echo json_encode( $tags );
		}
		wp_die();

	}

	public function add_options_page(): void {

		if ( ! ( function_exists( 'acf_add_options_page' ) && function_exists( 'acf_add_local_field_group' ) ) ) {
			return;
		}

		acf_add_options_page( [
			'page_title'  => '
			
			Brand Management Options',
			'menu_title'  => 'Brand Management Options',
			'menu_slug'   => 'acf-options-brand-management-options',
			'parent_slug' => 'edit.php?post_type=brand',
		] );

		acf_add_local_field_group( [
			'key'      => 'group_brand_management_options',
			'title'    => 'Brand Management Options',
			'fields'   => [
				[
					'key'           => 'field_bm_options_sorting_in_campaign_tables',
					'label'         => 'Sorting In Brand Management Campaign Tables',
					'name'          => 'bm_options_sorting_in_campaign_tables',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 0,
					'ui_on_text'    => 'Enabled',
					'ui_off_text'   => 'Disabled',
				],
				[
					'key'           => 'field_bm_options_tags_ui_in_campaign_tables',
					'label'         => 'Tags UI In Brand Management Campaign Tables',
					'name'          => 'bm_options_tags_ui_in_campaign_tables',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 0,
					'ui_on_text'    => 'Select',
					'ui_off_text'   => 'Tabs',
				],
				[
					'key'           => 'field_bm_options_show_date_time_in_campaigns',
					'label'         => 'Show/Hide Date And Time In Campaigns',
					'name'          => 'bm_options_show_date_time_in_campaigns',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
					'ui_on_text'    => 'Show',
					'ui_off_text'   => 'Hide',
				],
				[
					'key'     => 'field_bm_options_date_format',
					'label'   => 'Date Format',
					'name'    => 'bm_options_date_format',
					'type'    => 'select',
					'choices' => [
						'd/m/y' => 'Day Month Year',
						'm/d/y' => 'Month Day Year',
						'y/m/d' => 'Year Month Day',
					],
				],
				[
					'key'           => 'field_bm_options_show_likes_in_campaigns',
					'label'         => 'Show/Hide Likes/Dislikes In Campaigns',
					'name'          => 'bm_options_show_likes_in_campaigns',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
					'ui_on_text'    => 'Show',
					'ui_off_text'   => 'Hide',
				],
				[
					'key'   => 'field_bm_options_default_page_disclaimer_selector',
					'label' => 'Default Page Disclaimer Selector',
					'name'  => 'bm_options_default_page_disclaimer_selector',
					'type'  => 'text',
				],
			],
			'location' => [
				[
					[
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'acf-options-brand-management-options',
					],
				],
			],
		] );

		acf_add_local_field_group( [
			'key'      => 'group_brand_management_geo_settings',
			'title'    => 'Brand Management Geo Settings',
			'fields'   => [
				[
					'key'           => 'field_bm_options_country_listing',
					'label'         => 'Country Listing',
					'name'          => 'bm_options_country_listing',
					'type'          => 'select',
					'allow_null'    => 0,
					'ui'            => 1,
					'placeholder'   => 'Select countries...',
					'return_format' => 'array',
					'multiple'      => 1,
					'instructions'  => 'Defines a list of countries that will be available at the offer level to enable or disable the display of the offer, depending on the user\'s geolocation.',
				],
			],
			'location' => [
				[
					[
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'acf-options-brand-management-options',
					],
				],
			],
		] );

		if ( ! is_main_site() ) {
			add_action( 'admin_head', function () {
				echo '<style>#acf-group_brand_management_multisite_options { display: none !important; }</style>';
			} );
		}

	}

	public function ajax_get_unique_visit_links(): void {

		global $wpdb;
		$redirection_visit_links = [];

		$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_items ORDER BY url ASC" );
		foreach ( $results as $row ) {
			$redirection_visit_links[] = $row->url;
		}

		echo json_encode( $redirection_visit_links );

		wp_die();

	}

	public function ajax_duplicate_or_create_regional_campaign(): void {

		if ( $_POST['campaign_id'] ) {
			global $wpdb;

			$taxonomy = $new_taxonomy = 'bm_campaign_management';

			$campaign_id = $_POST['campaign_id'];

			$create_regional_campaign = false;
			if ( $_POST['create_regional_campaign'] === 'true' ) {
				$create_regional_campaign = true;

				$new_taxonomy = 'bm_regional_campaigns';
			}

			$term = get_term( $campaign_id, $taxonomy );

			if ( is_wp_error( $term ) ) {
				echo json_encode( [ 'status' => 'error', 'message' => 'The campaign does not exist.' ] );
				wp_die();
			}

			$new_term = wp_insert_term( $this->get_new_campaign_name( $term->name, $create_regional_campaign ), $new_taxonomy );

			if ( is_wp_error( $new_term ) ) {
				echo json_encode( [ 'status' => 'error', 'message' => 'Failed to create a new campaign.' ] );
				wp_die();
			}

			try {
				$sql = $wpdb->prepare(
					sprintf(
						"INSERT INTO %s (`term_id`, `meta_key`, `meta_value`) SELECT %%d, `meta_key`, `meta_value` FROM %s WHERE `term_id` = %%d",
						$wpdb->termmeta,
						$wpdb->termmeta
					),
					$new_term['term_id'],
					$campaign_id
				);

				if ( $create_regional_campaign ) {
					$sql_where_condition = Brand_Management_Acf_Loader::prepare_sql_where_condition_for_meta_keys_in_regional_campaign();

					$sql = $wpdb->prepare(
						sprintf(
							"INSERT INTO %s (`term_id`, `meta_key`, `meta_value`) SELECT %%d, `meta_key`, `meta_value` FROM %s WHERE `term_id` = %%d %3s",
							$wpdb->termmeta,
							$wpdb->termmeta,
							$sql_where_condition
						),
						$new_term['term_id'],
						$campaign_id
					);
				}

				$wpdb->query( $sql );
			} catch ( Exception $e ) {
				echo json_encode( [ 'status' => 'error', 'message' => 'The campaign metadata could not be copied.' ] );
				wp_die();
			}

			$edit_link_to_new_campaign = get_edit_term_link( $new_term['term_id'], $new_taxonomy, 'brand' );

			echo json_encode( [ 'status' => 'success', 'link' => $edit_link_to_new_campaign ] );
		} else {
			echo json_encode( [ 'status' => 'error', 'message' => 'Campaign creation failed.' ] );
		}

		wp_die();

	}

	private function get_new_campaign_name( $name, $create_regional_campaign ): string {

		$taxonomy = 'bm_campaign_management';
		if ( $create_regional_campaign ) {
			$taxonomy = 'bm_regional_campaigns';
		}

		$i = 1;
		do {
			$new_name = 'Copy ' . ( $i ++ ) . ' | ' . $name;
		} while ( term_exists( $new_name, $taxonomy ) );

		return $new_name;

	}

	public function bm_options_load_country_listing_field( $field ) {

		$country_listing = (array) json_decode( file_get_contents( 'http://country.io/names.json' ) );

		ksort( $country_listing );

		$field['choices'] = $country_listing;

		return $field;

	}

	public function load_geo_settings_country_listing( $field ): array {

		$country_listing = bm_get_option( 'country_listing' ) ?? [];

		if ( is_array( $country_listing ) && ! empty( array_filter( $country_listing ) ) ) {
			foreach ( $country_listing as $country ) {
				$field['choices'][ $country['value'] ] = $country['label'];
			}
		}

		return $field;

	}

	public function ajax_update_field_campaign_region(): void {

		if ( isset( $_POST['campaign_id'], $_POST['campaign_region'] ) ) {
			update_field( 'campaign_region', $_POST['campaign_region'], 'bm_regional_campaigns_' . $_POST['campaign_id'] );

			echo json_encode( [ 'status' => 'success' ] );
		} else {
			echo json_encode( [ 'status' => 'error' ] );
		}

		wp_die();

	}
}
