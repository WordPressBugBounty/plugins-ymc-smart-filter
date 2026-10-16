<?php
declare(strict_types=1);

namespace YMCFilterGrids\api\controllers\frontend;

defined( 'ABSPATH' ) || exit;

use YMCFilterGrids\api\abstracts\FG_REST_Abstract_Controller;
use YMCFilterGrids\FG_Data_Store as Data_Store;
use WP_REST_Request;
use WP_REST_Server;



/**
 * REST controller for frontend search endpoints.
 *
 * Handles frontend search requests such as post search
 * and autocomplete suggestions.
 *
 * @since 3.10.0
 */
class FG_REST_Frontend_Search_Controller extends FG_REST_Abstract_Controller {

   protected $rest_base = 'search';

   public function register_routes() {

      register_rest_route($this->namespace, '/' . $this->rest_base . '/autocomplete',
         [
            [
               'methods'             => WP_REST_Server::CREATABLE,
               'callback'            => [ $this, 'get_autocomplete_suggestions' ],
               'permission_callback' => [ $this, 'public_permissions_check' ],
            ],
         ]
      );
   }


   /**
    * Get autocomplete suggestions.
    *
    * @param WP_REST_Request $request     
    */
   public function get_autocomplete_suggestions( WP_REST_Request $request ) {

      $params = $request->get_json_params();

      $keyword = sanitize_text_field( $params['keyword'] ?? '' );
      $grid_id = absint( $params['grid_id'] ?? 0 );
      $terms   = $params['terms'] ?? [];     

      $suggestions = [];

      if ( empty( $keyword ) || empty( $grid_id ) ) {

         return $this->error_response(
            'invalid_data',
            'Invalid data received.',
            400
         );
      }

      if ( ! is_array( $terms ) ) {
         $terms = [];
      } 
      else {
         $terms = array_map('absint', $terms);
      }

      $tax_relation = Data_Store::get_meta_value($grid_id, 'ymc_fg_tax_relation');
      $taxonomies   = Data_Store::get_meta_value($grid_id, 'ymc_fg_taxonomies');
      $search_mode  = Data_Store::get_meta_value($grid_id, 'ymc_fg_search_mode');
      $exact_phrase = Data_Store::get_meta_value($grid_id, 'ymc_fg_exact_phrase');
      $post_types   = Data_Store::get_meta_value($grid_id, 'ymc_fg_post_types');

      $search_meta_fields = Data_Store::get_meta_value($grid_id, 'ymc_fg_search_meta_fields');
      
      $max_autocomplete_suggestions = max(1,  absint(Data_Store::get_meta_value($grid_id, 'ymc_fg_max_autocomplete_suggestions')));

      $search_filters_enabled = false;

      if ( $search_meta_fields === 'yes' ) {

         self::add_search_filters();

         $search_filters_enabled = true;
      }

      if (is_array($post_types)) {
         $post_types = array_values(array_diff($post_types, ['revision']));
      } 
      elseif ($post_types === 'revision') {
         $post_types = [];
      }

      if (empty($post_types)) {
         return $this->success_response([
            'results' => []
         ]);
      }

      $args = [
         'post_type'      => $post_types,
         'post_status'    => 'publish',
         'posts_per_page' => $max_autocomplete_suggestions,
         'orderby'        => 'title',
         'order'          => 'asc',
         'sentence'       => true,
         's'              => $keyword
      ];

      $args['ymc_search_term'] = $keyword;

      if ( $search_mode === 'filtered' ) {

         self::add_tax_query_args(
            $args,
            $taxonomies,
            $terms,
            $tax_relation,
            $grid_id
         );
      }

      if ( $exact_phrase === 'yes' ) {
         $args['exact'] = true;
      }     

      try {
         $query = new \WP_Query( $args );
      } 
      finally {
         if ( $search_filters_enabled ) {
            static::remove_search_filters();
         }
      }

      if ( $query->have_posts() ) {

         while ( $query->have_posts() ) {

            $query->the_post();

            $post_id = get_the_ID();
            $title   = get_post_field('post_title', $post_id, 'raw');
            $content = get_post_field('post_content', $post_id, 'raw');

            $found = false;

            // Search in title
            $fragment = self::extract_and_highlight_fragment($title, $keyword);

            if ( $fragment ) {

               $suggestions[] = [
                  'id'      => $post_id,
                  'context' => $fragment,
                  'title'   => wp_strip_all_tags( $title ),
                  'source'  => 'title'
               ];

               $found = true;
            }

            // Search in content
            if ( ! $found ) {

               $fragment = self::extract_and_highlight_fragment($content, $keyword);

               if ( $fragment ) {

                  $suggestions[] = [
                     'id'      => $post_id,
                     'context' => $fragment,
                     'title'   => wp_strip_all_tags( $title ),
                     'source'  => 'content'
                  ];

                  $found = true;
               }
            }

            // Search in meta
            if (! $found &&  $search_meta_fields === 'yes') {

               $meta = get_post_meta( $post_id );

               foreach ( $meta as $key => $meta_values ) {

                  foreach ( $meta_values as $meta_value ) {

                     if ( ! is_scalar( $meta_value ) ) {
                        continue;
                     }

                     $fragment = self::extract_and_highlight_fragment( (string) $meta_value, $keyword);                     

                     if ( $fragment ) {

                        $suggestions[] = [
                           'id'      => $post_id,
                           'context' => $fragment,
                           'title'   => wp_strip_all_tags( $title ),
                           'source'  => 'meta (' . $key . ')'
                        ];

                        break 2;
                     }
                  }
               }
            }
         }

         wp_reset_postdata();
      }

      return $this->success_response([
         'results' => $suggestions
      ]);
   }


   public static function add_search_filters() : void {
      add_filter('posts_join', [__CLASS__, 'search_join'], 10, 2);      
      add_filter('posts_search', [ __CLASS__, 'search_where' ], 10, 2);
      add_filter('posts_distinct', [__CLASS__, 'search_distinct'], 10, 2);
   }


   /**
	 * Remove search filters
	 * @return void
	 */
	public static function remove_search_filters() : void {
		remove_filter('posts_join', [__CLASS__, 'search_join'], 10);
		remove_filter('posts_search', [__CLASS__, 'search_where'], 10);
		remove_filter('posts_distinct', [__CLASS__, 'search_distinct'], 10);
	}


   /**
	 * Modify the SQL JOIN statement to include the postmeta table for custom meta data retrieval.
	 *
	 * @param string $join The existing SQL JOIN statement.
    * @param \WP_Query $query
	 * @return string The modified SQL JOIN statement.
	 */
   public static function search_join( string $join, \WP_Query $query): string {
      global $wpdb;

      if (empty($query->get('ymc_search_term'))) {
         return $join;
      }

      $join .= " LEFT JOIN {$wpdb->postmeta} AS pm ON {$wpdb->posts}.ID = pm.post_id ";

      return $join;
   }


   /**
	 * Modify the WHERE clause of the SQL query to include postmeta table for custom meta data search.
	 *
	 * @param string $where The existing WHERE clause of the SQL query.
    * @param \WP_Query $query
	 * @return string The modified WHERE clause including the postmeta table search condition.
	 */
   public static function search_where( string $where, \WP_Query $query ): string {

      global $wpdb;

      $search_term = $query->get('ymc_search_term');

      if (empty($search_term)) {
         return $where;
      }

      // Remove the leading AND from the WordPress search clause.
      $search_sql = trim($where);
      $search_sql = preg_replace( '/^AND\s+/i', '', $search_sql );

      if (empty($search_sql)) {
         return $where;
      }

      $like = '%' . $wpdb->esc_like($search_term) . '%';
      $meta_sql = $wpdb->prepare('pm.meta_value LIKE %s', $like);

      return ' AND ((' . $search_sql . ') OR (' . $meta_sql . '))';
   }


   /**
	 * Returns the DISTINCT keyword used in SQL queries.
	 *
	 * @param string $distinct
    * @param \WP_Query $query
	 * @return string The DISTINCT keyword
	 */
   public static function search_distinct( string $distinct, \WP_Query $query): string {
      if (empty($query->get('ymc_search_term'))) {
         return $distinct;
      }
      return 'DISTINCT';
   }


   /**
	 * Add tax query args
	 * @param array $args
	 * @param array $taxonomies
	 * @param array $terms
	 * @param string $tax_relation
	 * @param int $filter_id
	 *
	 * @since 3.0.0
	 * @return void
	 */
	private static function add_tax_query_args(array &$args, array $taxonomies,	array $terms, string $tax_relation = 'AND', int $filter_id = 0): void {
		if (!empty($taxonomies) && !empty($terms)) {
			$tax_query = ['relation' => $tax_relation];
			$term_tax_map = [];

			foreach ($terms as $term_id) {
				$term = get_term((int) $term_id);
				if ($term && !is_wp_error($term)) {
					$term_tax_map[$term->taxonomy][] = (int) $term->term_id;
				}
			}
			foreach ($taxonomies as $taxonomy) {
				if (!empty($term_tax_map[$taxonomy])) {
					$tax_query[] = [
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $term_tax_map[$taxonomy],
					];
				}
				else {
					$tax_query[] = [
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => self::get_terms_by_taxonomy($taxonomy, $filter_id),
					];
				}
			}
			if (count($tax_query) > 1) {
				$args['tax_query'] = $tax_query;
			}
		}
	}


   /**
    * Gets text fragments containing a keyword and highlights them.
    *
    * @param string $text — text to search
    * @param string $keyword — keyword
    * @param int $padding — number of characters before/after the word
    *
    * @since 3.0.0
    * @return string|null — fragment with backlight
    */   
   public static function extract_and_highlight_fragment( string $text, string $keyword, int $padding = 60 ): ?string {
      $text = strip_tags($text);
      $text = preg_replace('/\[[\/]?[^\]]+\]/', '', $text);

      if ($keyword === '' || $text === '') {
         return null;
      }

      // Find the keyword in the original text.
      // PREG_OFFSET_CAPTURE returns a byte offset, so use substr() below.
      $matched = preg_match(
         '/' . preg_quote($keyword, '/') . '/iu',
         $text,
         $matches,
         PREG_OFFSET_CAPTURE
      );

      if ($matched !== 1) {
         return null;
      }

      $match_text = $matches[0][0];
      $match_offset = $matches[0][1];

      // Keep the existing behavior: start the snippet at the keyword.
      $tail = substr(
         $text,
         $match_offset + strlen($match_text)
      );

      $padding = max(0, $padding);
      $tail_fragment = mb_substr($tail, 0, $padding);

      // Escape all source text; only the plugin-generated <b> remains HTML.
      $fragment = '<b>' . esc_html($match_text) . '</b>'
         . esc_html($tail_fragment);

      if (mb_strlen($tail) > $padding) {
         $fragment .= '...';
      }

      return $fragment;
   }


   	/**
	 * Get terms IDs by taxonomy and selected terms
	 * For display terms mode: Selected Terms and Selected Terms (Hide Empty)
	 *
	 * @param string $taxonomies
	 * @param int $filter_id
	 *
	 * @return array
	 */
	private static function get_terms_by_taxonomy(string $taxonomies, int $filter_id): array {
		$display_terms_mode = (string) Data_Store::get_meta_value($filter_id, 'ymc_fg_display_terms_mode');

		$hide_empty = $display_terms_mode === 'all_terms_hide_empty';
		$term_selected = in_array($display_terms_mode, ['all_terms', 'all_terms_hide_empty'], true)
			? []
			: array_map('intval', (array) Data_Store::get_meta_value($filter_id, 'ymc_fg_terms'));

		$terms = get_terms([
			'taxonomy'   => $taxonomies,
			'include'    => $term_selected,
			'hide_empty' => $hide_empty
		]);

		if (is_wp_error($terms) || empty($terms)) {
			return [];
		}

		return array_map(fn($term) => $term->term_id, $terms);
	}

}
