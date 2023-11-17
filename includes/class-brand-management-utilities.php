<?php

/**
 * Utilities used to ensure the operation of shortcodes.
 *
 * @since      1.0.0
 * @package    Brand_Management
 * @subpackage Brand_Management/includes
 */

/**
 * Checks whether the post_id is an offer.
 *
 * @param   $post_id
 *
 * @return  boolean
 * @since   1.0.0
 */
function bm_is_offer( $post_id ): bool {
	return get_post_type( $post_id ) === 'offer';
}

/**
 * Returns brand_id by post_id if given post_id is an offer.
 * If brand_id isn't set, the offer id is returned.
 *
 * @param   $post_id
 *
 * @return  mixed
 * @since   1.0.0
 */
function bm_get_brand_id( $post_id ) {
	if ( bm_is_offer( $post_id ) ) {
		$brand_id = get_field( 'brand_id', $post_id );

		return get_post_type( $brand_id ) ? $brand_id : $post_id;
	}

	return $post_id;
}

/**
 * Returns a link to the image of the brand logo.
 * If the brand logo isn't set, an empty string is returned.
 * If $return_src = false there will be an attempt to return the attachment id.
 *
 * @param   $post_id
 * @param  bool  $return_src
 *
 * @return  mixed
 * @since   1.0.0
 */
function bm_get_brand_logo( $post_id, bool $return_src = true ) {
	return bm_get_field( 'brand_logo', $post_id, $return_src, true, true ) ?: '';
}

/**
 * Returns a link to the thumbnail image of the brand logo.
 * If the brand logo isn't set, an empty string is returned.
 *
 * @param   $post_id
 * @param  string  $size
 *
 * @return  string
 * @since   2.6.0
 */
function bm_get_optimized_logo( $post_id, string $size = 'bm_large_thumbnail' ): string {
	$brand_logo_attachment_id = bm_get_brand_logo( $post_id, false );
	if ( ! empty( $brand_logo_attachment_id ) ) {
		$optimized_logo_src = wp_get_attachment_image_src( $brand_logo_attachment_id, $size );
	}

	return $optimized_logo_src[0] ?? '';
}

/**
 * Gets the value of a specific field.
 * If we get an empty value, we try to find it at the brand level.
 *
 * @param  string  $selector  The field name or field key.
 * @param  mixed  $post_id  The post ID where the value is saved.
 * @param  bool  $format_value  Whether to apply formatting logic. Defaults to true.
 * @param  string  $taxonomy  The taxonomy ID where the value is saved.
 * @param  bool  $brand_first  Check brand fields first. Defaults to false.
 * @param  bool  $brand_only  Search for a field only at the brand level. Defaults to false.
 *
 * @return  mixed
 * @since   1.0.0
 */
function bm_get_field( string $selector, $post_id, bool $format_value = true, string $taxonomy = '', bool $brand_first = false, bool $brand_only = false ) {
	if ( ! empty( $taxonomy ) ) {
		$rewritten_field = bm_get_rewritten_field( $selector, $post_id, $taxonomy );

		if ( $rewritten_field ) {
			return pre_processing_return_bm_get_field( $rewritten_field, $selector );
		}
	}

	$is_offer = bm_is_offer( $post_id );
	if ( $is_offer ) {
		$brand_id = bm_get_brand_id( $post_id );
	}

	if ( $is_offer && $brand_first ) {
		$field_value = get_field( $selector, $brand_id, $format_value );

		if ( empty( $field_value ) && $brand_only === false ) {
			$field_value = get_field( $selector, $post_id, $format_value );
		}

		return pre_processing_return_bm_get_field( $field_value, $selector );
	}

	$field_value = get_field( $selector, $post_id, $format_value );

	if ( empty( $field_value ) && isset( $brand_id ) ) {
		return pre_processing_return_bm_get_field( get_field( $selector, $brand_id, $format_value ), $selector );
	}

	return pre_processing_return_bm_get_field( $field_value, $selector );
}

/**
 * Returns the value of a specific field with pre-processing.
 *
 * @param   $field_value
 * @param  string  $selector
 *
 * @return  mixed
 * @since   2.7.0
 */
function pre_processing_return_bm_get_field( $field_value, string $selector ) {
	$shortcodes_allowed_selectors = [
		'coupon_code',
		'disclaimer_text',
		'highlighted_label',
		'terms_and_conditions',
		'offer_conditions',
		'offer_description',
	];

	if ( ! empty ( $field_value ) ) {
		if ( is_string( $field_value ) && in_array( $selector, $shortcodes_allowed_selectors ) ) {
			$field_value = do_shortcode( $field_value );
		}

		if ( is_array( $field_value ) && $selector === 'key_features' ) {
			foreach ( $field_value as &$item ) {
				$item['point'] = do_shortcode( $item['point'] );
			}
		}
	}

	return $field_value;
}

/**
 * Returns an <img> element with an image depending on the rating.
 *
 * @param   $rating
 *
 * @return  string
 * @since   1.0.0
 */
function bm_render_star_rating( $rating ): string {
	if ( is_numeric( $rating ) ) {
		$rating = (float) $rating;

		if ( $rating < 1 ) {
			$rating = 1;
		}

		if ( $rating > 5 && $rating <= 10 ) {
			$rating /= 2;
		}

		$rating = ceil( $rating / 0.5 ) / 2;
		$rating = min( $rating, 5 );
		$rating = (string) $rating;
	} else {
		return '';
	}

	$image_by_rating = [
		'1'   => 'star1.svg',
		'1.5' => 'star2.svg',
		'2'   => 'star3.svg',
		'2.5' => 'star4.svg',
		'3'   => 'star5.svg',
		'3.5' => 'star6.svg',
		'4'   => 'star7.svg',
		'4.5' => 'star8.svg',
		'5'   => 'star9.svg',
	];

	return '<img class="star_rating_img skip-lazy" src="' . BRAND_MANAGEMENT_URL . 'public/images/' . $image_by_rating[ $rating ] . '" width="99" height="20" alt="' . $rating . ' Stars">';
}

/**
 * Returns all brand tags from the term as a string by
 * default, and as an array, if this defined in arguments.
 *
 * @param  mixed  $id
 * @param  string  $taxonomy
 * @param  bool  $return_as_array
 *
 * @return  array|string
 * @since   1.0.0
 */
function bm_get_brand_tags( $id, string $taxonomy = '', bool $return_as_array = false ) {
	$brand_tags = get_the_terms( bm_get_brand_id( $id ), 'bm_filter_tags' );

	if ( ! empty( $taxonomy ) ) {
		$rewritten_tags = bm_get_rewritten_field( 'tag', $id, $taxonomy );

		if ( $rewritten_tags ) {
			foreach ( $rewritten_tags as $rewritten_tag ) {
				$brand_rewritten_tags[] = get_term_by( 'id', (int) $rewritten_tag, 'bm_filter_tags' );
			}
		}
	}

	if ( isset( $brand_rewritten_tags ) && is_array( $brand_rewritten_tags ) ) {
		$brand_tags = $brand_rewritten_tags;
	} elseif ( ! is_array( $brand_tags ) ) {
		return '';
	}

	if ( $return_as_array ) {
		$tags         = [];
		$filter_array = [];

		foreach ( $brand_tags as $tag ) {
			if ( in_array( $tag, $filter_array, true ) ) {
				continue;
			}

			$tags[]         = $tag;
			$filter_array[] = $tag;
		}

		return $tags ?? [];
	}

	$tags         = '';
	$filter_array = [];

	foreach ( $brand_tags as $tag ) {
		if ( in_array( $tag, $filter_array, true ) ) {
			continue;
		}

		$filter_array[] = $tag;

		$name = str_replace( [ ' ', '!' ], [ '_', '' ], $tag->name );
		$tags .= 'custom_tag_' . $name . ' ';
	}

	return $tags ?? '';
}

/**
 * Returns the rewritten value of a specific field if it is.
 *
 * @param  string  $selector  The field name or field key.
 * @param  mixed  $offer_id  The post ID where the value is saved.
 * @param  string  $taxonomy_selector  The taxonomy ID where the value is saved.
 *
 * @return  mixed
 * @since   2.0.0
 */
function bm_get_rewritten_field( string $selector, $offer_id, string $taxonomy_selector ) {
	if ( ! have_rows( 'field_rewriting_offer_fields', $taxonomy_selector ) ) {
		return false;
	}

	while ( have_rows( 'field_rewriting_offer_fields', $taxonomy_selector ) ) : the_row();
		$acf_current_brand_field[] = get_row( true );
	endwhile;

	if ( isset( $acf_current_brand_field ) ) {
		$offer_id_key = array_search( $offer_id, array_column( $acf_current_brand_field, 'rewrite_offer_id' ) );

		if ( $offer_id_key === false ) {
			return false;
		}
	} else {
		return false;
	}

	return $acf_current_brand_field[ $offer_id_key ][ $selector ];
}

/**
 * Truncates the string by the specified number of characters
 * according to the words by adding an ellipsis (...) at the end.
 *
 * @param  string  $text
 * @param  int  $chars
 * @param  bool  $return_original_when_not_trimmed
 *
 * @return string
 */
function bm_trim_text( string $text, int $chars, bool $return_original_when_not_trimmed = false ): string {
	$chars += ( strlen( $text ) - strlen( wp_strip_all_tags( $text ) ) );

	$wrapped_text = mb_wordwrap( $text, $chars );
	$wrapped_text = rtrim( substr( $wrapped_text, 0, strpos( $wrapped_text, PHP_EOL ) ) );

	if ( ! empty( $wrapped_text ) ) {
		return rtrim( $wrapped_text, '.' ) . '...';
	}

	if ( $return_original_when_not_trimmed ) {
		return $text;
	}

	return '';
}

/**
 * Returns array of values of group fields.
 *
 * @param  string  $selector
 * @param  mixed  $post_id
 * @param  mixed  $value
 *
 * @return array
 */
function bm_get_group_fields( string $selector, $post_id, $value ): array {
	global $wpdb;

	if ( bm_is_offer( $post_id ) ) {
		$post_id = bm_get_brand_id( $post_id );
	}

	$query = "SELECT meta_value, meta_key FROM $wpdb->postmeta WHERE post_id = $post_id AND meta_key LIKE '$selector%'";

	if ( isset( $value ) ) {
		$query = "SELECT meta_value, meta_key FROM $wpdb->postmeta WHERE post_id = $post_id AND meta_key LIKE '$selector%' AND meta_value = $value";
	}

	return $wpdb->get_results( $query );
}

/**
 * Returns the value of the plugin options.
 * If the value isn't set, checks for the presence
 * of a variable in Brand_Management_Storage::$options.
 *
 * @param  string  $key
 *
 * @return mixed
 */
function bm_get_option( string $key ) {
	$option = get_field( 'bm_options_' . $key, 'option' );

	if ( empty( $option ) && class_exists( 'Brand_Management_Storage' ) && ! empty( Brand_Management_Storage::$options ) ) {
		$option = Brand_Management_Storage::$options[ $key ] ?? $option;
	}

	return $option;
}

/**
 * Returns the value of the plugin global multisite settings.
 *
 * @param  string  $option
 *
 * @return mixed
 */
function bm_get_multisite_option( string $option ): mixed {
	if ( is_multisite() && get_current_blog_id() !== 1 ) {
		switch_to_blog( 1 );
	}

	$option = get_field( 'bm_options_' . $option, 'option' );

	if ( is_multisite() && ms_is_switched() ) {
		restore_current_blog();
	}

	return $option;
}

/**
 * Returns set of attributes for visit links.
 *
 * @param  string|int  $offer_id
 *
 * @return string
 */
function bm_get_external_link_attributes( $offer_id ): string {
	$attributes = '';

	$is_open_in_new_tab = bm_get_field( 'open_visit_links_in_new_tab', $offer_id, true, '', true, true );
	if ( $is_open_in_new_tab !== false ) {
		$attributes .= 'target="_blank" ';
	} else {
		$attributes .= 'target="_self" ';
	}

	$attributes .= 'rel="nofollow sponsored"';

	return $attributes;
}

if ( ! function_exists( 'mb_wordwrap' ) ) {
	/**
	 * Multibyte aware alternative to wordwrap.
	 *
	 * Wraps a string to a given number of characters.
	 *
	 * @see http://www.php.net/manual/en/function.wordwrap.php
	 *
	 * @param  string  $string
	 * @param  int  $length
	 * @param  string  $break  the line is broken using the optional break parameter
	 *
	 * @return string the given string wrapped at the specified column
	 *
	 * @since  3.1.0
	 */
	function mb_wordwrap( string $string, int $length = 75, string $break = PHP_EOL ): string {
		$str_length = mb_strlen( $string );

		$wrapped_substrings = [];
		for ( $i = 0; $i < $str_length; $i += $length ) {
			$wrapped_substrings[] = mb_substr( $string, $i, $length );
		}

		return implode( $break, $wrapped_substrings );
	}
}

if ( ! function_exists( 'remove_filters_for_anonymous_class' ) ) {
	/**
	 * Allow to remove method for a hook when,
	 * it's a class method used and class doesn't
	 * have a variable, but we know the class name.
	 */
	function remove_filters_for_anonymous_class( $hook_name = '', $class_name = '', $method_name = '', $priority = 0 ): bool {
		global $wp_filter;

		if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
			return false;
		}

		foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
			if ( isset( $filter_array['function'] ) &&
			     is_array( $filter_array['function'] ) &&
			     is_object( $filter_array['function'][0] ) &&
			     get_class( $filter_array['function'][0] ) &&
			     get_class( $filter_array['function'][0] ) === $class_name &&
			     $filter_array['function'][1] === $method_name
			) {
				unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
			}
		}

		return false;
	}
}
