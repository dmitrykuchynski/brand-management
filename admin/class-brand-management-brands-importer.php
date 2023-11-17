<?php

/**
 * Functionality for creating a parent brand for an offer.
 *
 * @since      1.0.0
 * @package    Brand_Management
 * @subpackage Brand_Management/admin
 */
class Brand_Management_Brands_Importer {

	/**
	 * The script is initialized in the admin panel.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function init(): void {
		add_action( 'admin_init', [ $this, 'check_request' ] );
	}

	/**
	 * Checking the incoming for GET parameters.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function check_request(): void {
		if ( isset( $_GET['brand_management'] ) ) {
			if ( in_array( $_GET['brand_management'], [
				'ajax_create_brands',
				'ajax_update_network',
				'ajax_toggle_show_table_title',
				'ajax_move_tags_from_offer_to_brands',
				'ajax_move_sidebar_features_from_offer_to_brand',
				'ajax_migrate_filter_tags_from_offers_to_brands',
			] ) ) {
				?>
                <div class="brand-management-notice"></div>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"
                        integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ=="
                        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
                <script>
                    function call(url) {
                        $.ajax({
                            method: 'GET',
                            dataType: 'json',
                            url: url,
                        }).done(function (output) {
                            let notice = $('.brand-management-notice').html();
                            $('.brand-management-notice').html(notice + '<p>' + output.result + '</p>');
                            if (output.next) {
                                window.setTimeout(call(output.next), 2000);
                            } else {
                                $('.brand-management-notice').html($('.brand-management-notice').html() + '<p>All done.</p>');
                            }
                        });
                    }
                </script>
				<?php
			}

			switch ( $_GET['brand_management'] ) {
				case 'create_brand_from_offer' :
					$this->create_brand_from_offer( $_GET['offer_id'] );
					break;

				case 'migrate_post_and_term_types' :
					Brand_Management_Migrate::migrate_post_and_term_types();
					break;

				case 'migrate_acf_fields' :
					Brand_Management_Migrate::migrate_acf_fields( $_GET['echo'] ?: true, $_GET['partially'] ?: 0, $_GET['offset'] ?: 0 );
					break;

				case 'update_payment_methods' :
					Brand_Management_Migrate::migrate_withdrawal_and_deposit_methods_from_payment_groups();
					break;

				case 'create_brands_from_all_offers' :
					$this->create_brands_from_all_offers( $_GET['offset'] ?: 0 );
					break;

				case 'update_network' :
					$this->update_network( $_GET['position'] ?: 0 );
					break;

				case 'toggle_show_table_title' :
					$this->toggle_show_table_title( $_GET['position'] ?: 0 );
					break;

				case 'move_tags_from_offer_to_brands' :
					$this->move_tags_from_offer_to_brands( $_GET['position'] ?: 0 );
					break;

				case 'move_sidebar_features_from_offer_to_brand' :
					$this->move_sidebar_features_from_offer_to_brand( $_GET['position'] ?: 0 );
					break;

				case 'migrate_filter_tags_from_offers_to_brands' :
					$this->migrate_filter_tags_from_offers_to_brands( $_GET['position'] ?: 0, $_GET['remove_processed_terms'] ?: 0 );
					break;

				case 'ajax_move_tags_from_offer_to_brands' :
					echo '<script>call("' . admin_url( '?brand_management=move_tags_from_offer_to_brands' ) . '");</script>';
					break;

				case 'ajax_toggle_show_table_title' :
					echo '<script>call("' . admin_url( '?brand_management=toggle_show_table_title' ) . '");</script>';
					break;

				case 'ajax_create_brands' :
					echo '<script>call("' . admin_url( '?brand_management=create_brands_from_all_offers' ) . '");</script>';
					break;

				case 'ajax_update_network' :
					echo '<script>call("' . admin_url( '?brand_management=update_network' ) . '");</script>';
					break;

				case 'ajax_move_sidebar_features_from_offer_to_brand' :
					echo '<script>call("' . admin_url( '?brand_management=move_sidebar_features_from_offer_to_brand' ) . '");</script>';
					break;

				case 'ajax_migrate_filter_tags_from_offers_to_brands' :
					echo '<script>call("' . admin_url( '?brand_management=migrate_filter_tags_from_offers_to_brands&remove_processed_terms=' . $_GET['remove_processed_terms'] ?: 0 ) . '");</script>';
					break;
			}
		}
	}

	/**
	 * An attempt was made to create a brand using the offer data.
	 *
	 * @param   $offer_id
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function create_brand_from_offer( $offer_id ): void {
		$offer = get_post( $offer_id );

		if ( isset( $offer ) ) {
			$brand_id = $this->create_brand( $offer );

			if ( $brand_id ) {
				$this->update_offer_meta( $offer->ID, $brand_id );

				wp_redirect( admin_url( 'post.php?action=edit&post=' . $brand_id ) );
				die();
			}

			wp_die( 'Brand creation failed, can\'t create a brand from given offer.' );
		} else {
			wp_die( 'Brand creation failed, could not find offer by post_id: ' . $offer_id . '.' );
		}
	}

	/**
	 * Create brand using the offer data.
	 *
	 * @param   $offer
	 * @param string $title
	 * @param string $status
	 *
	 * @return  mixed
	 * @since   1.0.0
	 */
	private function create_brand( $offer, string $title = '', string $status = 'draft' ) {
		$brand_data = [
			'post_author' => get_current_user_id(),
			'post_name'   => $title ?: $offer->post_name,
			'post_status' => $status,
			'post_title'  => $title ?: $offer->post_title,
			'post_type'   => 'brand',
		];

		$brand_id = wp_insert_post( $brand_data );

		if ( $brand_id ) {
			$this->copy_offer_metadata( $offer, $brand_id );

			return $brand_id;
		}

		return false;
	}

	/**
	 * Copying metadata from the offer to the brand.
	 *
	 * @param   $offer
	 * @param   $brand_id
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function copy_offer_metadata( $offer, $brand_id ): void {
		$offer_metadata = get_post_custom( $offer->ID );

		foreach ( $offer_metadata as $key => $values ) {
			foreach ( $values as $value ) {
				add_post_meta( $brand_id, $key, maybe_unserialize( $value ) );
			}
		}
	}

	/**
	 * The brand data is updated in the offer.
	 *
	 * The update_field function requires an active Advanced Custom Fields plugin.
	 *
	 * @param   $offer_id
	 * @param   $brand_id
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function update_offer_meta( $offer_id, $brand_id ): void {
		update_post_meta( $offer_id, 'brand_id', $brand_id );
		update_field( 'brand_id', $brand_id, $offer_id );
	}

	private function create_brands_from_all_offers( int $offset = 0 ): void {
		$brands_dictonary = [
			'/visit/1xbet'                          => '1xbet',
			'/visit/2kbet'                          => '2kbet',
			'/visit/10bet'                          => '10bet',
			'/visit/12bet'                          => '12bet',
			'/visit/20bet'                          => '20bet',
			'/visit/21couk'                         => '21.co.uk',
			'/visit/22bet'                          => '22bet',
			'/visit/22bet-telegram'                 => '22bet-telegram',
			'/visit/24monaco'                       => '24mcasino',
			'/visit/32red-casino'                   => '32red',
			'/visit/188bet'                         => '188bet',
			'/visit/777-casino'                     => '777casino',
			'/visit/888bingo'                       => '888Sport',
			'/visit/888casino'                      => '888Sport',
			'/visit/888poker'                       => '888Sport',
			'/visit/888sport'                       => '888Sport',
			'/visit/888sport-betbuilder'            => '888Sport',
			'/visit/888sport-bog'                   => '888Sport',
			'/visit/888sport-cricket'               => '888Sport',
			'/visit/888Sport-enhanced1'             => '888Sport',
			'/visit/888Sport-enhanced2'             => '888Sport',
			'/visit/888Sport-enhanced3'             => '888Sport',
			'/visit/888Sport-enhanced4'             => '888Sport',
			'/visit/888Sport-enhanced5'             => '888Sport',
			'/visit/888sport-esports'               => '888Sport',
			'/visit/888sport-horseracing'           => '888Sport',
			'/visit/888sport-nfl'                   => '888Sport',
			'/visit/888sport-politics'              => '888Sport',
			'/visit/888sport-rugby'                 => '888Sport',
			'/visit/888Sport60'                     => '888Sport',
			'/visit/888Sport100'                    => '888Sport',
			'/visit/888SportENGLVSSCO'              => '888Sport',
			'/visit/agentnowager'                   => 'agentnowager',
			'/visit/aladdinslots-casino'            => 'aladdinslots',
			'/visit/allbritishcasino'               => 'allbritishcasino',
			'/visit/andyrobsontips'                 => 'andyrobsontips',
			'/visit/bet22'                          => '22bet',
			'/visit/bet365'                         => 'bet365',
			'/visit/bet365-telegram'                => 'bet365-telegram',
			'/visit/betathome'                      => 'betathome',
			'/visit/betbull'                        => 'betbull',
			'/visit/betburger'                      => 'betburger',
			'/visit/betdaq'                         => 'betdaq',
			'/visit/betduel'                        => 'betduel',
			'/visit/betdukes'                       => 'betdukes',
			'/visit/beteast'                        => 'betvision',
			'/visit/betfair'                        => 'betfair',
			'/visit/betfair-casino'                 => 'betfair casino',
			'/visit/betfair-enhanced'               => 'betfair',
			'/visit/betfair-enhanced1'              => 'betfair',
			'/visit/betflip'                        => 'betflip',
			'/visit/betfred'                        => 'betfred',
			'/visit/betfred-enhanced1'              => 'betfred',
			'/visit/betfred-enhanced2'              => 'betfred',
			'/visit/betfred-freespins'              => 'betfred casino',
			'/visit/betfred-hpfeautred'             => 'betfred',
			'/visit/betfred30'                      => 'betfred',
			'/visit/bethard'                        => 'bethard',
			'/visit/betiton'                        => 'betiton',
			'/visit/betmvp'                         => 'betmvp',
			'/visit/betneptune'                     => 'betneptune',
			'/visit/betnow'                         => 'betnow',
			'/visit/betregal'                       => 'betregal',
			'/visit/betspawn'                       => 'betspawn',
			'/visit/betsson'                        => 'betsson',
			'/visit/betstorm'                       => 'betstorm',
			'/visit/betswagger'                     => 'betswagger',
			'/visit/bettarget'                      => 'bettarget',
			'/visit/bettingexpert'                  => 'bettingexpert',
			'/visit/betuk'                          => 'betuk',
			'/visit/betvictor'                      => 'betvictor',
			'/visit/betway'                         => 'betway',
			'/visit/betway-accurate-sports'         => 'betway-accurate-sports',
			'/visit/betway-enhanced'                => 'betway',
			'/visit/betway-enhanced2'               => 'betway',
			'/visit/betway-enhanced3'               => 'betway',
			'/visit/betway-sports-telegram'         => 'betway-sports-telegram',
			'/visit/betwinner'                      => 'betwinner',
			'/visit/bitcasino.io'                   => 'bitcasino.io',
			'/visit/bk8king'                        => 'bk8',
			'/visit/bk8king-asia'                   => 'bk8',
			'/visit/bonusboss'                      => 'bonusboss',
			'/visit/bonusboss-casino'               => 'bonusboss casino',
			'/visit/boylesports'                    => 'boylesports',
			'/visit/breaking-bet'                   => 'breaking-bet',
			'/visit/britainbet'                     => 'britainbet',
			'/visit/britainbet-casino'              => 'britainbet casino',
			'/visit/bwin'                           => 'bwin',
			'/visit/capital'                        => 'capital',
			'/visit/casharcade-casino'              => 'casharcade casino',
			'/visit/cashmo-casino'                  => 'cashmo casino',
			'/visit/casimba-casino'                 => 'casimba casino',
			'/visit/casinolab-casino'               => 'casinolab casino',
			'/visit/casiplay-casino'                => 'casiplay casino',
			'/visit/casumo'                         => 'casumo',
			'/visit/casumo-casino'                  => 'casumo casino',
			'/visit/casushi-casino'                 => 'casushi casino',
			'/visit/cloudbet'                       => 'cloudbet',
			'/visit/clover-casino'                  => 'clover casino',
			'/visit/colossusbets'                   => 'colossusbets',
			'/visit/coral'                          => 'coral',
			'/visit/coral-casino'                   => 'coral casino',
			'/visit/crazystar'                      => 'crazystar',
			'/visit/dafabet'                        => 'dafabet',
			'/visit/dreamvegas'                     => 'dreamvegas',
			'/visit/drslotcasino'                   => 'drslot casino',
			'/visit/energybet'                      => 'energybet',
			'/visit/fansbet'                        => 'fansbet',
			'/visit/fanteam'                        => 'fanteam',
			'/visit/feverslots-casino'              => 'feverslots casino',
			'/visit/fezbet'                         => 'fezbet',
			'/visit/fixed-tips-arena'               => 'fixed-tips-arena',
			'/visit/footballindex'                  => 'ceased to exist',
			'/visit/footballpools'                  => 'footballpools',
			'/visit/footstock'                      => 'ceased to exist',
			'/visit/fortuneclock'                   => 'fortuneclock',
			'/visit/freespinsbingo-casino'          => 'freespinsbingo casino',
			'/visit/freesupertips'                  => 'freesupertips',
			'/visit/fun88'                          => 'fun88',
			'/visit/funbet'                         => 'funbet',
			'/visit/funcasino'                      => 'funcasino',
			'/visit/gatobet'                        => 'gatobet',
			'/visit/genesis-casino'                 => 'genesis casino',
			'/visit/ggbet'                          => 'ggbet',
			'/visit/gntingbet'                      => 'gentingbet',
			'/visit/greatbritain-casino'            => 'greatbritain casino',
			'/visit/grosvenor-casino'               => 'grosvenor casino',
			'/visit/grosvenorsport'                 => 'grosvenor',
			'/visit/guts'                           => 'guts',
			'/visit/highbet'                        => 'highbet',
			'/visit/hollywoodbets'                  => 'hollywoodbets',
			'/visit/hopa'                           => 'hopa',
			'/visit/infernobet'                     => 'infernobet',
			'/visit/intertops'                      => 'intertops',
			'/visit/jamesmurphytips'                => 'jamesmurphytips',
			'/visit/karamba'                        => 'karamba',
			'/visit/karamba-casino'                 => 'karamba casino',
			'/visit/kingjcasino'                    => 'kingjcasino',
			'/visit/kwiff'                          => 'kwiff',
			'/visit/kwiff-casino'                   => 'kwiff casino',
			'/visit/ladbrokes'                      => 'ladbrokes',
			'/visit/ladbrokes-casino'               => 'ladbrokes casino',
			'/visit/ladbrokes50'                    => 'ladbrokes',
			'/visit/leovegas'                       => 'leovegas',
			'/visit/leovegas-casino'                => 'leovegas casino',
			'/visit/livescorebet'                   => 'livescorebet',
			'/visit/lootbet'                        => 'lootbet',
			'/visit/lovebet'                        => 'lovebet',
			'/visit/luckybet'                       => 'luckybet',
			'/visit/lvbet'                          => 'lvbet',
			'/visit/lvbet-casino'                   => 'lvbet casino',
			'/visit/m88'                            => 'm88',
			'/visit/majesticbingo-casino'           => 'majesticbingo casino',
			'/visit/manbetx'                        => 'manbetx',
			'/visit/mansionbet'                     => 'mansionbet',
			'/visit/mansioncasino'                  => 'mansionbet casino',
			'/visit/maplebet'                       => 'maplebet',
			'/visit/marathonbet'                    => 'marathonbet',
			'/visit/markohaire'                     => 'markohaire',
			'/visit/matchbook'                      => 'matchbook',
			'/visit/maximum-casino'                 => 'maximum casino',
			'/visit/mayfaircasino'                  => 'mayfaircasino',
			'/visit/megapari'                       => 'megapari',
			'/visit/melbet'                         => 'melbet',
			'/visit/mfortune-casino'                => 'mfortune casino',
			'/visit/mightytips'                     => 'mightytips',
			'/visit/mobilewins'                     => 'mobilewins',
			'/visit/monster-casino'                 => 'monster casino',
			'/visit/monster-casino200'              => 'monster casino',
			'/visit/monster-casino500'              => 'monster casino',
			'/visit/monstercasino-sport'            => 'monstercasino sports',
			'/visit/mrfixitstips'                   => 'mrfixittips',
			'/visit/mrgeen'                         => 'mrgeen',
			'/visit/mrgreen'                        => 'mrgeen',
			'/visit/mrgreen-casino'                 => 'mrgeen casino',
			'/visit/mrgreen-esports'                => 'mrgeen',
			'/visit/mrmega'                         => 'mrmega',
			'/visit/mrplay'                         => 'mrplay',
			'/visit/mrplay-casino'                  => 'mrplay casino',
			'/visit/mrq-casino'                     => 'mrq casino',
			'/visit/mrspincasino'                   => 'mrspincasino',
			'/visit/netbet'                         => 'netbet',
			'/visit/netbet-casino'                  => 'netbet casino',
			'/visit/newspinscasino'                 => 'newspinscasino',
			'/visit/novibet'                        => 'novibet',
			'/visit/novibet-enhanced'               => 'novibet',
			'/visit/novibet-enhanced1'              => 'novibet',
			'/visit/oddsmonkey'                     => 'oddsmonkey',
			'/visit/opesports'                      => 'vbet',
			'/visit/paddypower'                     => 'paddypower',
			'/visit/paddypower-casino'              => 'paddypower casino',
			'/visit/parimatch'                      => 'parimatch',
			'/visit/pinnacle'                       => 'pinnacle',
			'/visit/playatharrys'                   => 'playatharrys',
			'/visit/playojo'                        => 'playojo',
			'/visit/playzee-casino'                 => 'playzee casino',
			'/visit/plazaroyal'                     => 'plazaroyal',
			'/visit/pokerstars-casino'              => 'pokerstars casino',
			'/visit/profitaccumulator'              => 'profitaccumulator',
			'/visit/profitsquad'                    => 'profitsquad',
			'/visit/queenplay-casino'               => 'queenplay casino',
			'/visit/quinnbet'                       => 'quinnbet',
			'/visit/racebets'                       => 'racebets',
			'/visit/racingpost'                     => 'racingpost',
			'/visit/rebelbetting'                   => 'rebelbetting',
			'/visit/regent-casino'                  => 'regent casino',
			'/visit/rizk'                           => 'rizk',
			'/visit/royaltigerbet'                  => 'royaltigerbet',
			'/visit/rubybet'                        => 'rubybet',
			'/visit/safebettingsites-telegram-tips' => 'safebettingsites-telegram-tips',
			'/visit/sbk'                            => 'sbk',
			'/visit/sbobet'                         => 'sbobet',
			'/visit/simbaslots-casino'              => 'simbaslots casino',
			'/visit/skybet'                         => 'skybet',
			'/visit/slotsanimal-casino'             => 'slotsanimal casino',
			'/visit/sloty-casino'                   => 'sloty casino',
			'/visit/smarkets'                       => 'smarkets',
			'/visit/spinitcasino'                   => 'spinitcasino',
			'/visit/sportingindex'                  => 'sportingindex',
			'/visit/sportinglife'                   => 'sportinglife',
			'/visit/sportito'                       => 'sportito',
			'/visit/sportnation'                    => 'sportnation',
			'/visit/sportsbet.io'                   => 'sportsbet.io',
			'/visit/sportsinteraction'              => 'sportsinteraction',
			'/visit/sportspread'                    => 'sportspread',
			'/visit/spreadex'                       => 'spreadex',
			'/visit/spreadex-cashback'              => 'spreadex',
			'/visit/spreadex-cheltenham'            => 'spreadex',
			'/visit/starspreads'                    => 'starspreads',
			'/visit/sts'                            => 'sts',
			'/visit/tebwin'                         => 'tebwin',
			'/visit/thegeordietips'                 => 'thegeordietips',
			'/visit/theonlinecasino'                => 'theonlinecasino',
			'/visit/thepools'                       => 'thepools',
			'/visit/theredlioncasino'               => 'theredlioncasino',
			'/visit/tlcbet'                         => 'tlcbet',
			'/visit/tonybet'                        => 'tonybet',
			'/visit/tote'                           => 'tote',
			'/visit/unibet'                         => 'unibet',
			'/visit/unibet-casino'                  => 'unibet casino',
			'/visit/unibet-casino40'                => 'unibet casino',
			'/visit/unibet-esports'                 => 'unibet',
			'/visit/unibet-poker'                   => 'unibet',
			'/visit/vbet'                           => 'vbet',
			'/visit/virginbet'                      => 'virginbet',
			'/visit/w88'                            => 'w88',
			'/visit/williamhill'                    => 'williamhill',
			'/visit/williamhill-casino'             => 'williamhill casino',
			'/visit/williamhill-enhanced'           => 'williamhill',
			'/visit/williamhill-enhanced2'          => 'williamhill',
			'/visit/williamhill-enhanced3'          => 'williamhill',
			'/visit/williamhillm40'                 => 'williamhill',
			'/visit/winkslots-casino'               => 'winkslots casino',
			'/visit/worldwide-fixed-matches'        => 'worldwide-fixed-matches',
			'/visit/yeti-casino'                    => 'yeti casino',
			'/visit/zeusbingo-casino'               => 'zeusbingo casino',
		];

		global $wpdb;

		$start = microtime( true );

		// Get all offers.
		$offers = $wpdb->get_results( "SELECT ID, post_title, post_name FROM $wpdb->posts WHERE post_type = 'offer' LIMIT 500 OFFSET $offset" );

		$i = 0;

		foreach ( $offers as $offer ) {
			// If offer already has brand_id.
			if ( bm_is_offer( bm_get_brand_id( $offer->ID ) ) === false ) {
				continue;
			}

			$unique_visit_link = bm_get_field( 'unique_visit_link', $offer->ID );
			// If an offer has visit link.
			if ( empty( $unique_visit_link ) ) {
				continue;
			}

			$brand_name_from_dictonary = array_filter(
				$brands_dictonary,
				static function ( $value ) use ( $unique_visit_link ) {
					return ( strpos( $unique_visit_link, $value ) !== false );
				}
			);

			if ( empty( $brand_name_from_dictonary ) ) {
				continue;
			}

			$blog_path = get_blog_details()->path;
			if ( $blog_path === '/' ) {
				$country = 'UK';
			} else {
				$country = strtoupper( str_replace( '/', '', get_blog_details()->path ) );
			}

			$title = array_values( $brand_name_from_dictonary )[0] . ' ' . $country;

			$brand_found = get_page_by_title( $title, OBJECT, 'brand' );
			if ( $brand_found ) {
				$brand_id = $brand_found->ID;
			} else {
				$brand_id = $this->create_brand( $offer, $title, 'publish' );
				$i ++;
			}

			if ( $brand_id ) {
				$this->update_offer_meta( $offer->ID, $brand_id );
			}
		}

		$next = false;

		if ( count( $offers ) === 500 ) {
			$offset += 500;
			$next   = admin_url( '?brand_management=create_brands_from_all_offers&offset=' . $offset );
		}

		header( 'Content-Type: application/json' );
		echo json_encode( [
			'next'   => $next,
			'result' => 'Created ' . $i . ' brands from ' . count( $offers ) . ' offers in ' . ( microtime( true ) - $start ) . ' seconds with ' . round( memory_get_peak_usage() / 1024 / 1024 ) . ' MB memory usage.',
		], JSON_THROW_ON_ERROR );

		exit();
	}

	private function update_network( int $position = 0 ): void {
		if ( is_multisite() ) {
			global $wpdb;

			$need_to_migrate_payment_methods = false;

			if ( ! empty( $wpdb->query( "SELECT * FROM $wpdb->postmeta WHERE meta_key LIKE '%group_paymentmethod%' LIMIT 1" ) ) ) {
				$need_to_migrate_payment_methods = true;
			}

			$blogs = $wpdb->get_results( "SELECT * FROM $wpdb->blogs" );
			if ( ! empty( $blogs ) ) {
				switch_to_blog( $blogs[ $position ]->blog_id );

				$result_migrate_deposit_and_withdrawal_methods = '';
				if ( $need_to_migrate_payment_methods ) {
					$result_migrate_deposit_and_withdrawal_methods = Brand_Management_Migrate::migrate_withdrawal_and_deposit_methods_from_payment_groups();
				}
				$result_migrate_post_and_term_types = Brand_Management_Migrate::migrate_post_and_term_types( false );
				$result_migrate_acf_fields          = Brand_Management_Migrate::migrate_acf_fields( false );

				$current_blog_id = $blogs[ $position ]->blog_id;

				wp_reset_query();
				restore_current_blog();

				$position ++;

				$next = false;

				if ( isset( $blogs[ $position ] ) ) {
					$next = admin_url( '?brand_management=update_network&position=' . $position );
				}

				header( 'Content-Type: application/json' );
				echo json_encode( [
					'next'   => $next,
					'result' => $result_migrate_post_and_term_types . $result_migrate_acf_fields . $result_migrate_deposit_and_withdrawal_methods . 'Blog with id ' . $current_blog_id . ' was updated.',
				], JSON_THROW_ON_ERROR );

				exit();
			}
		}
	}

	private function toggle_show_table_title( int $position = 0 ): void {
		if ( is_multisite() ) {
			global $wpdb;

			$blogs = $wpdb->get_results( "SELECT * FROM $wpdb->blogs" );
			if ( ! empty( $blogs ) ) {
				switch_to_blog( $blogs[ $position ]->blog_id );

				$result_toggle_show_table_title = Brand_Management_Migrate::toggle_show_table_title( false );

				$current_blog_id = $blogs[ $position ]->blog_id;

				wp_reset_query();
				restore_current_blog();

				$position ++;

				$next = false;

				if ( isset( $blogs[ $position ] ) ) {
					$next = admin_url( '?brand_management=toggle_show_table_title&position=' . $position );
				}

				header( 'Content-Type: application/json' );
				echo json_encode( [
					'next'   => $next,
					'result' => $result_toggle_show_table_title . ' Blog with id ' . $current_blog_id . ' was updated.',
				], JSON_THROW_ON_ERROR );

				exit();
			}
		}
	}

	private function move_tags_from_offer_to_brands( int $position = 0 ): void {
		if ( is_multisite() ) {
			global $wpdb;

			$blogs = $wpdb->get_results( "SELECT * FROM $wpdb->blogs" );
			if ( ! empty( $blogs ) ) {
				switch_to_blog( $blogs[ $position ]->blog_id );

				$result_moving_tags = Brand_Management_Migrate::move_tags_from_offer_to_brand( false );

				$current_blog_id = $blogs[ $position ]->blog_id;

				wp_reset_query();
				restore_current_blog();

				$position ++;

				$next = false;

				if ( isset( $blogs[ $position ] ) ) {
					$next = admin_url( '?brand_management=move_tags_from_offer_to_brands&position=' . $position );
				}

				header( 'Content-Type: application/json' );
				echo json_encode( [
					'next'   => $next,
					'result' => $result_moving_tags . ' Blog with id ' . $current_blog_id . ' was updated.',
				], JSON_THROW_ON_ERROR );

				exit();
			}
		}
	}

	private function move_sidebar_features_from_offer_to_brand( int $position = 0 ): void {
		if ( is_multisite() ) {
			global $wpdb;

			$blogs = $wpdb->get_results( "SELECT * FROM $wpdb->blogs" );
			if ( ! empty( $blogs ) ) {
				switch_to_blog( $blogs[ $position ]->blog_id );

				$result_moving_tags = Brand_Management_Migrate::move_sidebar_features_from_offer_to_brand( false );

				$current_blog_id = $blogs[ $position ]->blog_id;

				wp_reset_query();
				restore_current_blog();

				$position ++;

				$next = false;

				if ( isset( $blogs[ $position ] ) ) {
					$next = admin_url( '?brand_management=move_sidebar_features_from_offer_to_brand&position=' . $position );
				}

				header( 'Content-Type: application/json' );
				echo json_encode( [
					'next'   => $next,
					'result' => $result_moving_tags . ' Blog with id ' . $current_blog_id . ' was updated.',
				], JSON_THROW_ON_ERROR );

				exit();
			}
		}
	}

	private function migrate_filter_tags_from_offers_to_brands( int $position = 0, int $remove_processed_terms = 0 ): void {
		if ( is_multisite() ) {
			global $wpdb;

			$blogs = $wpdb->get_results( "SELECT * FROM $wpdb->blogs" );
			if ( ! empty( $blogs ) ) {
				switch_to_blog( $blogs[ $position ]->blog_id );

				$output = Brand_Management_Migrate::migrate_filter_tags_from_offers_to_brands( $remove_processed_terms );

				$current_blog_id = $blogs[ $position ]->blog_id;

				wp_reset_query();
				restore_current_blog();

				$position ++;

				$next = false;

				if ( isset( $blogs[ $position ] ) ) {
					$next = admin_url( '?brand_management=migrate_filter_tags_from_offers_to_brands&position=' . $position . '&remove_processed_terms=' . $remove_processed_terms );
				}

				header( 'Content-Type: application/json' );
				echo json_encode( [
					'next'   => $next,
					'result' => $output . ' Blog with id ' . $current_blog_id . ' was updated.',
				], JSON_THROW_ON_ERROR );

				exit();
			}
		}
	}

}
