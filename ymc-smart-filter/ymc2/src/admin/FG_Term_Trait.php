<?php declare( strict_types = 1 );

namespace YMCFilterGrids\admin;

defined( 'ABSPATH' ) || exit;

/**
 * FG_Term_Trait
 *
 * @since 3.14.0
 */
trait FG_Term_Trait {

   /**
	 * Class icon
	 * Icon associated with the term.
	 * Value for data attribute is class name by font awesome. Ex.: "fa-solid fa-image".
	 * @var string
	 */
	protected static string $icon_class = '';

	/**
	 * Icon alignment
	 * Value for data attribute is "left" or "right".
	 * @var string
	 */
	protected static string $icon_alignment = '';

	/**
	 * Icon color
	 * Value for data attribute is color in hash code. Ex.: "#ff0000".
	 * @var string
	 */
	protected static string $icon_color = '';


	/**
	 * Related terms
	 * Custom related terms for the term
	 * @var string
	 */
	protected static string $related_terms = '';


	/**
	 * Icon URL
	 * The attachment_id value for the loaded term icon
	 * @var string
	 */
	protected static string $icon_url = '';


	/**
	 * Term background
	 * Value for data attribute is background color in hash code. Ex.: "#ff0000".
	 * @var string
	 */
	protected static string $term_background = '';

	/**
	 * Term color
	 * Value for data attribute is color in hash code. Ex.: "#ff0000".
	 * @var string
	 */
	protected static string $term_color = '';

	/**
	 * Class term
	 * Value for data attribute is class name. Ex.: "my-class".
	 * @var string
	 */
	protected static string $term_class = '';

	/**
	 * Term default
	 * Value for data attribute is "true" or "false".
	 * Determines whether the term will be used by default.
	 * @var string
	 */
	protected static string $term_default = '';

	/**
	 * Term hide
	 * Value for data attribute is "true" or "false".
	 * Determines whether the term will be visible.
	 * @var string
	 */
	protected static string $term_visible = '';

	/**
	 * Term name
	 * Value for data attribute is term name.
	 * @var string
	 */
	protected static string $term_name = '';


	/**
	 * Term checked
	 * Value for data attribute is "true" or "false".
	 * Determines whether the term will be selected.
	 * @var string
	 */
	protected static string $term_checked = '';


	/**
	 * Status term
	 * Value for data attribute is "changed" or "".
	 * Determines whether changes have been made to the term settings.
	 * @var string
	 */
	protected static string $term_status = '';


	/**
	 * Taxonomy label
	 * @var string
	 */
	protected static string $tax_label = '';


	/**
	 * Cache key
	 * @var string
	 */
	protected static string $base_cache_key = 'ymc_fg_term_post_counts';


   /**
	 * Get cache key
	 * @return string
	 */
   public static function get_cache_key(string $tax_name = '', array $post_types = []) : string {
      if ($tax_name && $post_types) {
         return self::build_cache_key($tax_name, $post_types);
      }
      
      return self::$base_cache_key;
   }


	/**
	 * Clear data attributes for term
	 *
	 * @return void
	 */
	protected static function clear_data_attributes() : void {
		self::$icon_class = '';
		self::$icon_alignment = '';
		self::$icon_color = '';
		self::$icon_url = '';
		self::$term_background = '';
		self::$term_color = '';
		self::$term_class = '';
		self::$term_default = 'false';
		self::$term_visible = 'true';
		self::$term_name = '';
		self::$term_checked = 'false';
		self::$term_status = '';
		self::$related_terms = '';
	}


	/**
	 * Set data attributes for term
	 * Override default values
	 *
	 * @param int $term_id
	 * @param array $term_attrs
	 *
	 * @return void
	 */
	protected static function set_data_attributes(int $term_id, array &$term_attrs) : void {
		if($term_attrs) {
			foreach ($term_attrs as $items) {
				if($term_id === (int) $items['term_id']) {
					self::$icon_class      = $items['icon_class'] ?? '';
					self::$icon_alignment  = $items['icon_alignment'] ?? '';
					self::$icon_color      = $items['icon_color'] ?? '';
					self::$icon_url        = $items['icon_url'] ?? '';
					self::$term_background = $items['term_background'] ?? '';
					self::$term_color      = $items['term_color'] ?? '';
					self::$term_class      = $items['term_class'] ?? '';
					self::$term_default    = $items['term_default'] ?? '';
					self::$term_visible    = $items['term_visible'] ?? '';
					self::$term_name       = $items['term_name'] ?? '';
					self::$term_checked    = $items['term_checked'] ?? '';
					self::$term_status     = $items['term_status'] ?? '';
					self::$related_terms   = $items['related_terms'] ?? '';
					break;
				}
			}
		}
	}


	/**
	 * Set taxonomy label
	 * @param string $name
	 * @param array $tax_attrs
	 *
	 * @return void
	 */
	protected static function set_taxonomy_label(string $name, array &$tax_attrs) : void {
		if($tax_attrs) {
			foreach ($tax_attrs as $items) {
				if($name === $items['name']) {
					self::$tax_label = $items['label'];
					break;
				}
			}
		}
	}


	/**
	 * Determine term sorting
	 * @param string $term_sort_direction
	 * @param array  $term_sort
	 * @param array  $terms
	 *
	 * @return void
	 */
	protected static function determine_term_sorting(string $term_sort_direction, array $term_sort, array &$terms) : void {
		if ($term_sort_direction === 'asc') {
			asort($terms);
		} elseif ($term_sort_direction === 'desc') {
			arsort($terms);
		} else {
			// Manual sorting
			$temp_array = [];
			if (!empty($term_sort)) {
				// Add sorted terms (if they are in $terms)
				foreach ($term_sort as $term_id) {
					if (isset($terms[$term_id])) {
						$temp_array[$term_id] = $terms[$term_id];
					}
				}
			}
			// Add new terms (which are not in $term_sort)
			foreach ($terms as $term_id => $term_name) {
				if (!isset($temp_array[$term_id])) {
					$temp_array[$term_id] = $term_name;
				}
			}
			$terms = $temp_array;
		}
	}


	/**
	 * Check if all terms are selected
	 * @param array $terms
	 * @param array $selected_terms
	 *
	 * @return bool
	 */
	protected static function checked_selected_terms(array $terms, array $selected_terms) : bool {
		$selected_terms = array_map(function ($item) { return (int) $item; }, $selected_terms);
		$selected_terms = array_flip($selected_terms);
		return count(array_intersect_key($terms, $selected_terms)) === count($terms);
	}


	/**
	 * Update post counts
	 * @param string $tax_name
	 * @param int $term_id	 
	 *
	 * @return void
	 */
	public static function update_post_counts(string $tax_name, int $term_id) : void {

      $taxonomy = get_taxonomy($tax_name);

      if (!$taxonomy) {
         return;
      }

      $post_types = (array) $taxonomy->object_type;
      
      foreach ($post_types as $type) {
         $args = [
            'posts_per_page' => 1,
            'post_status' => 'publish',
            'post_type' => $type,
            'fields' => 'ids',
            'no_found_rows' => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'tax_query' => [
               [
                  'taxonomy' => $tax_name, 
                  'field' => 'term_id',
                  'terms' => $term_id
               ]
            ]
         ];
         $countQuery = new \WP_Query($args);
         update_term_meta($term_id, "ymc_fg_count_{$type}", (int) $countQuery->found_posts);
      }

      wp_reset_postdata();		
	}


	/**
	 * Get post counts
	 * @param array $post_types
	 * @param int $term_id
	 *
	 * @return int
	 */
	public static function get_post_counts(array $post_types, int $term_id): int {
		$post_count = 0;
		foreach ($post_types as $type) {
			$post_count += (int) get_term_meta( $term_id, "ymc_fg_count_{$type}", true);
		}
		return $post_count;
	}


	/**
	 * Get terms by taxonomy
	 * @param string $tax
	 *
	 * Key is term id and value is term name
	 * Sort by term name ASC
	 * @return array
	 */
	public static function get_terms( string $tax ) : array {
		$args = [
			'taxonomy' => $tax,
			'hide_empty' => false
		];
		$list_terms = [];
		$terms = get_terms($args);
		if( $terms && ! is_wp_error( $terms ) ) {
			foreach( $terms as $term ) {
				$list_terms[$term->term_id] = $term->name;
			}
		}

		return $list_terms;
	}


   protected static function build_cache_key(string $tax_name, array $post_types): string {
      sort($post_types);
      return 'ymc_fg_term_post_counts_' . $tax_name . '_' . md5(implode('|', $post_types));
   }


   public static function get_original_term_id(int $term_id, string $taxonomy): int {

      if (! function_exists('apply_filters') || empty($taxonomy)) {
         return $term_id;
      }

      $default_language = apply_filters('wpml_default_language',  null);

      $original_term_id = apply_filters(
         'wpml_object_id',
         $term_id,
         $taxonomy,
         true,
         $default_language
      );

      return $original_term_id  ? (int) $original_term_id : $term_id;
   }



}



