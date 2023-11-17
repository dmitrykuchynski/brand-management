<?php

/**
 * Template loader for Brand Management plugin.
 *
 * Only need to specify class properties here.
 *
 * @package    Brand_Management
 * @subpackage Brand_Management/includes
 */
class Brand_Management_Template_Loader {

	/**
	 * Prefix for filter names.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $filter_prefix = 'brand_management';

	/**
	 * Directory name where custom templates for this plugin should be found in the theme.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $theme_template_directory = 'brand-management';

	/**
	 * Reference to the root directory path of this plugin.
	 *
	 * Can either be a defined constant, or a relative reference from where the subclass lives.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $plugin_directory = BRAND_MANAGEMENT_PATH;

	/**
	 * Directory name where templates are found in this plugin.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	protected $plugin_template_directory = 'public';

	/**
	 * Retrieve a template part.
	 *
	 * @param string $slug Template slug.
	 *
	 * @return string
	 * @since 1.0.0
	 *
	 */
	public function search_path( $slug, $relative_path = true ) {
		// Get files names of templates, for given slug and name.
		$templates = $this->get_template_file_names( $slug );

		// Return the part that is found.
		return $this->locate_template( $templates, $relative_path );
	}

	/**
	 * Given a slug and optional name, create the file names of templates.
	 *
	 * @param string $slug Template slug.
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	protected function get_template_file_names( $slug ) {
		$templates   = array();
		$templates[] = $slug;

		/**
		 * Allow template choices to be filtered.
		 *
		 * The resulting array should be in the order of most specific first, to least specific last.
		 * e.g. 0 => recipe-instructions.php, 1 => recipe.php
		 *
		 * @param array $templates Names of template files that should be looked for, for given slug and name.
		 * @param string $slug Template slug.
		 *
		 * @since 1.0.0
		 *
		 */
		return apply_filters( $this->filter_prefix . '_get_template_part', $templates, $slug );
	}

	/**
	 * Retrieve the name of the highest priority template file that exists.
	 *
	 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
	 * inherit from a parent theme can just overload one file. If the template is
	 * not found in either of those, it looks in the theme-compat folder last.
	 *
	 * @param string|array $template_names Template file(s) to search for, in order.
	 *                                     Has no effect if $load is false.
	 *
	 * @return string The template filename if one is located.
	 * @since 1.0.0
	 *
	 */
	public function locate_template( $template_names, $relative_path ) {

		// Use $template_names as a cache key - either first element of array or the variable itself if it's a string.
		$cache_key = is_array( $template_names ) ? $template_names[0] : $template_names;

		// If the key is in the cache array, we've already located this file.
		if ( isset( $this->template_path_cache[ $cache_key ] ) ) {
			$located = $this->template_path_cache[ $cache_key ];
		} else {
			// No file found yet.
			$located = false;

			// Remove empty entries.
			$template_names = array_filter( (array) $template_names );
			$template_paths = $this->get_template_paths();

			// Try to find a template file.
			foreach ( $template_names as $template_name ) {
				// Trim off any slashes from the template name.
				$template_name = ltrim( $template_name, '/' );

				// Try locating this template file by looping through the template paths.
				foreach ( $template_paths as $template_path ) {
					if ( file_exists( $template_path . $template_name ) ) {
						$located = $template_path . $template_name;

						// Store the template path in the cache.
						$this->template_path_cache[ $cache_key ] = $located;
						break 2;
					}
				}
			}
		}


		if ( $relative_path ) {
			return str_replace( [ untrailingslashit( ABSPATH ), '\\' ], [ '', '/' ], $located );
		}

		return $located;
	}

	/**
	 * Return a list of paths to check for template locations.
	 *
	 * Default is to check in a child theme (if relevant) before a parent theme, so that themes which inherit from a
	 * parent theme can just overload one file. If the template is not found in either of those, it looks in the
	 * theme-compat folder last.
	 *
	 * @return mixed|void
	 * @since 1.0.0
	 *
	 */
	protected function get_template_paths() {
		$theme_directory = trailingslashit( $this->theme_template_directory );

		$file_paths = array(
			10 => trailingslashit( get_template_directory() ) . $theme_directory,
			30 => $this->get_templates_dir(),
		);

		if ( defined( 'BRAND_MANAGEMENT_EXTENDED_PATH' ) ) {
			$file_paths[20] = trailingslashit( BRAND_MANAGEMENT_EXTENDED_PATH ) . $this->plugin_template_directory;
		}

		// Only add this conditionally, so non-child themes don't redundantly check active theme twice.
		if ( get_stylesheet_directory() !== get_template_directory() ) {
			$file_paths[1] = trailingslashit( get_stylesheet_directory() ) . $theme_directory;
		}

		/**
		 * Allow ordered list of template paths to be amended.
		 *
		 * @param array $var Default is directory in child theme at index 1, parent theme at 10, and plugin at 100.
		 *
		 * @since 1.0.0
		 *
		 */
		$file_paths = apply_filters( $this->filter_prefix . '_template_paths', $file_paths );

		// Sort the file paths based on priority.
		ksort( $file_paths, SORT_NUMERIC );

		return array_map( 'trailingslashit', $file_paths );
	}

	/**
	 * Return the path to the templates directory in this plugin.
	 *
	 * May be overridden in subclass.
	 *
	 * @return string
	 * @since 1.0.0
	 *
	 */
	protected function get_templates_dir() {
		return trailingslashit( $this->plugin_directory ) . $this->plugin_template_directory;
	}

}
