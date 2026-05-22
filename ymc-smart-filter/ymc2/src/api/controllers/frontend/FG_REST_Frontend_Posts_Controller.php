<?php
declare(strict_types=1);

namespace YMCFilterGrids\api\controllers\frontend;

defined( 'ABSPATH' ) || exit;

use YMCFilterGrids\api\abstracts\FG_REST_Abstract_Controller;
use YMCFilterGrids\FG_Data_Store as Data_Store;
use YMCFilterGrids\frontend\FG_Pagination as Pagination;
use YMCFilterGrids\frontend\FG_Layout_Renderer as Layout_Renderer;
use YMCFilterGrids\FG_Template as Template;
use WP_REST_Request;
use WP_REST_Server;



/**
 * REST controller for frontend post endpoints.
 *
 * Handles frontend post filtering, loading and pagination requests.
 *
 * @since 3.10.0
 */
class FG_REST_Frontend_Posts_Controller extends FG_REST_Abstract_Controller {

   protected $rest_base = 'posts';


   public function register_routes() { 

      register_rest_route( $this->namespace, '/' . $this->rest_base . '/filter',
         [
            [
               'methods'             => WP_REST_Server::CREATABLE,
               'callback'            => [ $this, 'get_filtered_posts' ],
               'permission_callback' => [ $this, 'public_permissions_check' ]
            ],
         ]
      );

      register_rest_route($this->namespace, '/' . $this->rest_base . '/views',
         [
            [
               'methods'             => WP_REST_Server::CREATABLE,
               'callback'            => [ $this, 'update_post_views' ],
               'permission_callback' => [ $this, 'public_permissions_check' ],
            ],
         ]
      );
   }



   /**
    * Get filtered posts.
    */
   public function get_filtered_posts( WP_REST_Request $request ) {

      $request_params = $request->get_json_params();

      $params = $request_params['params'] ?? [];

      if ( empty( $params ) || ! is_array( $params ) || empty( $params['filter_id'] ) ) {

         return $this->error_response(
            'Invalid data received.',
            'invalid_params',
            400
         );
      }

      $filter_id           = absint( $params['filter_id'] );
      $counter             = absint( $params['counter'] ?? 1 );
      $page_id             = absint( $params['page_id'] ?? 0 );
      $paged               = max( 1, absint( $params['paged'] ?? 1 ) );

      $data_response       = [];
      $pagination_rendered = '';

      // Post types
      $post_types = $params['post_types'] ?? [ 'post' ];

      // Taxonomies and terms
      $taxonomies = $params['taxonomies'] ?? [];
      $terms      = $params['terms'] ?? [];

      // Per page
      $per_page = $params['per_page'] ?? Data_Store::get_meta_value($filter_id, 'ymc_fg_per_page');

      // Taxonomies relation
      $tax_relation = $params['tax_relation'] ?? Data_Store::get_meta_value($filter_id, 'ymc_fg_tax_relation');

      // Selected posts
		$selected_posts      = $params['selected_posts'] ?? Data_Store::get_meta_value($filter_id, 'ymc_fg_selected_posts');
		$excluded_posts     =  $params['excluded_posts'] ?? Data_Store::get_meta_value($filter_id, 'ymc_fg_excluded_posts');

		// Order posts
		$post_order          = $params['post_order'] ?? Data_Store::get_meta_value($filter_id, 'ymc_fg_post_order');
		$post_order_by       = $params['post_order_by'] ?? Data_Store::get_meta_value($filter_id, 'ymc_fg_post_order_by');
		$order_meta_key      = $params['order_meta_key'] ?? Data_Store::get_meta_value($filter_id, 'ymc_fg_order_meta_key');
		$order_meta_value    = $params['order_meta_value'] ?? Data_Store::get_meta_value($filter_id, 'ymc_fg_order_meta_value');
		$post_order_multiple = $params['post_order_by_multiple'] ?? Data_Store::get_meta_value($filter_id, 'ymc_fg_post_order_by_multiple');

		// Post status
		$post_status         = $params['post_status'] ?? Data_Store::get_meta_value($filter_id, 'ymc_fg_post_status');

		// Meta query
		$meta_query_raw      = $params['meta_query'] ?? [];
		$meta_query_relation = strtoupper($params['meta_query_relation'] ?? 'AND');

      // Date query
		$date_query          = $params['date_query'] ?? null;

		// Search query
		$keyword_search      = $params['search'] ?? null;
      $search_post_id      = $params['search_post_id'] ?? 0;

		// Sort posts by ajax
		$ajax_orderby        = $params['orderby'] ?? null;
		$ajax_order          = $params['order'] ?? null;

		// Date filter
		$filter_date         = $params['date_filter'] ?? null;

		// Alphabetical filter
		$alphabetical_letter = $params['letter'] ?? null;

      // Get data from DB
		$post_layout        = Data_Store::get_meta_value($filter_id, 'ymc_fg_post_layout');
		$no_results_message = Data_Store::get_meta_value($filter_id, 'ymc_fg_no_results_message');
      $no_results_message = apply_filters('wpml_translate_single_string', $no_results_message, 'ymc-smart-filter', 'No results message - '. $filter_id);
		$pagination_hidden  = Data_Store::get_meta_value($filter_id, 'ymc_fg_pagination_hidden');
		$pagination_type    = Data_Store::get_meta_value($filter_id, 'ymc_fg_pagination_type');
		$search_mode        = Data_Store::get_meta_value($filter_id, 'ymc_fg_search_mode');
		$exact_phrase       = Data_Store::get_meta_value($filter_id, 'ymc_fg_exact_phrase');
		$search_meta_fields = Data_Store::get_meta_value($filter_id, 'ymc_fg_search_meta_fields');
		$advanced_query     = Data_Store::get_meta_value($filter_id, 'ymc_fg_enable_advanced_query');
		$advanced_query_type = Data_Store::get_meta_value( $filter_id, 'ymc_fg_advanced_query_type' );
		$suppress_filters   = Data_Store::get_meta_value($filter_id, 'ymc_fg_advanced_suppress_filters');
		$scroll_to_filters_on_load  = Data_Store::get_meta_value($filter_id, 'ymc_fg_scroll_to_filters_on_load');
		$debug_mode         = Data_Store::get_meta_value($filter_id, 'ymc_fg_debug_mode');
		$filtered_posts_label = Data_Store::get_meta_value($filter_id, 'ymc_fg_filtered_posts_label');

		// Arguments
		$args = [
			'post_type'      => $post_types,
			'posts_per_page' => $per_page,
			'paged'          => $paged,
			'post_status'    => $post_status,
			'order'          => $post_order,
			'orderby'        => $post_order_by
		];

      // Taxonomies and terms
		self::add_tax_query_args($args, $taxonomies, $terms, $tax_relation, $filter_id);

		// Selected Posts
		if ($selected_posts) {
         
			$key = ($excluded_posts === 'no') ? 'post__in' : 'post__not_in';
			$args[$key] = array_map('intval', (array) $selected_posts);
		}

		// Post Order
		if ($post_order_by) {

			switch ($post_order_by) {
				case 'meta_key':
					$args['meta_key'] = $order_meta_key;
					$args['orderby']  = $order_meta_value;
					break;

				case 'multiple_fields':
					if($post_order_multiple['fields']) {
						$result = array_combine(
							array_column($post_order_multiple['fields'], 'field_name'),
							array_column($post_order_multiple['fields'], 'order_type')
						);
						$args['orderby']  = $result;
						unset($args['order']);
					}
					break;

				default:
					$args['orderby'] = $post_order_by;
					break;
			}
		}

		// Meta query
		if (!empty($meta_query_raw) && is_array($meta_query_raw)) {

			$meta_query = [
				'relation' => in_array($meta_query_relation, ['AND', 'OR']) ? $meta_query_relation : 'AND'
			];

			foreach ($meta_query_raw as $meta_condition) {
				if (!isset($meta_condition['key'], $meta_condition['value'])) {
					continue;
				}

				$meta_query[] = [
					'key'     => sanitize_text_field($meta_condition['key']),
					'value'   => sanitize_text_field($meta_condition['value']),
					'compare' => isset($meta_condition['compare']) ? sanitize_text_field($meta_condition['compare']) : '=',
					'type'    => isset($meta_condition['type']) ? sanitize_text_field($meta_condition['type']) : 'CHAR'
				];
			}

			if (count($meta_query) > 1) {
				$args['meta_query'] = $meta_query;
			}
		}

		// Date query
		if (!empty($date_query) && is_array($date_query)) {
			$args['date_query'] = $date_query;
		}

      // Date filter
		if ( !empty( $filter_date ) && is_array( $filter_date ) ) {

			$type = $filter_date['type'] ?? '';
			$date_query = [];

			switch ( $type ) {
				case 'today':
				case 'yesterday':
					$offset = ( $type === 'yesterday' ) ? -1 : 0;
					$target = getdate( strtotime( "$offset day" ) );
					$date_query[] = [
						'year'     => $target['year'],
						'monthnum' => $target['mon'],
						'day'      => $target['mday'],
					];
					break;

				case '3_days':
					$date_query[] = [
						'after'     => '3 days ago',
						'inclusive' => true,
					];
					break;

				case 'last_week':
					$date_query[] = [
						'after'     => '7 days ago',
						'inclusive' => true,
					];
					break;

				case 'last_month':
					$date_query[] = [
						'after'     => '30 days ago',
						'inclusive' => true,
					];
					break;

				case 'last_year':
					$date_query[] = [
						'after'     => '1 year ago',
						'inclusive' => true,
					];
					break;

				case 'other_time':
					if ( isset( $filter_date['from'], $filter_date['to'] ) ) {
						$from_ts = (int) $filter_date['from'];
						$to_ts   = (int) $filter_date['to'];

						if ( $from_ts && $to_ts ) {
							$date_query[] = [
								'after'     => [
									'year'  => gmdate( 'Y', $from_ts ),
									'month' => gmdate( 'm', $from_ts ),
									'day'   => gmdate( 'd', $from_ts ),
								],
								'before'    => [
									'year'  => gmdate( 'Y', $to_ts ),
									'month' => gmdate( 'm', $to_ts ),
									'day'   => gmdate( 'd', $to_ts ),
								],
								'inclusive' => true,
							];
						}
					}
					break;

				default: break;
			}

			if ( !empty( $date_query ) ) {
				$args['date_query'] = $date_query;
			}
		}

		// Search query
		$search_filters_enabled = false;

		if (!empty($keyword_search)) {

			if($search_meta_fields === 'yes') {
				self::add_search_filters();
				$search_filters_enabled = true;
			}

			if($search_mode === 'global') {
				unset($args['tax_query']);
				unset($args['meta_query']);
				unset($args['date_query']);
			}

			if($exact_phrase === 'yes') {
				$args['exact'] = true;
			}

			$args['s'] = $keyword_search;
			$args['sentence'] = true;

			$results_text = Data_Store::get_meta_value($filter_id, 'ymc_fg_results_found_text');
			$results_text = apply_filters('ymc/search/results_found_text',  $results_text);
			$results_text = apply_filters('ymc/search/results_found_text_'. $filter_id, $results_text);
			$results_text = apply_filters('ymc/search/results_found_text_'. $filter_id .'_'. $counter, $results_text);
			$data_response['results_text'] = $results_text;
		}

      // Search post ID Autocomplete
      if ( ! empty( $search_post_id ) ) {

         $args['post__in'] = [ absint( $search_post_id ) ];

         unset( $args['s'] );
         unset( $args['sentence'] );
         unset( $args['exact'] );

         if($search_mode === 'global') {
				unset($args['tax_query']);
				unset($args['meta_query']);
				unset($args['date_query']);
			}         
      }

      // Advanced query
		if ($advanced_query === 'yes') {
			if($suppress_filters === 'yes') {
				$args['suppress_filters'] = true;
			}

			if ( $advanced_query_type === 'callback' ) {
				$allowed_callback = Data_Store::get_meta_value( $filter_id, 'ymc_fg_query_allowed_callback' );
				$whitelist = apply_filters( 'ymc/filter/query/wp/allowed_callbacks', [] );
				$whitelist = apply_filters( 'ymc/filter/query/wp/allowed_callbacks_'.$filter_id, $whitelist );

				if (is_callable( $allowed_callback) && in_array($allowed_callback, $whitelist, true)) {

               $extra_args = [];

               if ( ! empty( $params['extra_args'] ) && is_array( $params['extra_args'] ) ) {
                  $extra_args = $params['extra_args'];
               }

               $extra_args = ymc_sanitize_deep($extra_args);

               if (!empty($filter_date['from'])) {
                  $extra_args['data_from'] = (int) $filter_date['from'];
               }

               if (!empty($filter_date['to'])) {
                  $extra_args['data_to'] = (int) $filter_date['to'];
               }

               $filter_params = [
                  'post_type' => $post_types,
                  'taxonomy'  => $taxonomies,
                  'terms'     => $terms,
                  'page_id'   => $page_id,
                  'extra_args' => $extra_args
               ];                             
               
               $query_args = call_user_func( $allowed_callback, $filter_params );

					if ( is_array( $query_args ) ) {
						$args = array_merge( $args, $query_args );
					}
				}
			}

			if ($advanced_query_type === 'advanced') {
				$user_input = Data_Store::get_meta_value( $filter_id, 'ymc_fg_advanced_query' );

				if ( is_string($user_input) && trim($user_input) !== '' ) {
					parse_str( $user_input, $parsed_args );

					if ( is_array($parsed_args) && ! empty($parsed_args) ) {						
						$args = array_merge( $args, $parsed_args );
					}
				}
			}
		}

      // Sort posts by ajax
		if (!empty($ajax_orderby)) {
			$args['orderby'] = sanitize_key($ajax_orderby);
		}
		if (!empty($ajax_order)) {
			$args['order'] = strtoupper($ajax_order) === 'ASC' ? 'ASC' : 'DESC';
		}

		if ( is_string( $alphabetical_letter ) ) {
			$alphabetical_letter = sanitize_text_field( $alphabetical_letter );
		} 
      else {
			$alphabetical_letter = null;
		}

      // Alphabetical filter
		$alphabetical_enabled = false;

		if ( ! empty( $alphabetical_letter ) && strtolower( $alphabetical_letter ) !== 'all' ) {
			self::add_alphabetical_filter();
			$args['alphabetical'] = $alphabetical_letter;
			$alphabetical_enabled = true;
		}


		$query = new \WP_Query($args);

      if ($search_filters_enabled) {
			self::remove_search_filters();
		}

		// Remove alphabetical filter
		if ( $alphabetical_enabled ) {
			self::remove_alphabetical_filter();
		}

		ob_start();

      if ($query->have_posts()) {

			// Post layout
			$template_file = 'post-layout-' . str_replace( 'layout_', '', $post_layout );
			$template_path = YMC_ABSPATH . 'src/frontend/views/templates/posts/' . $template_file . '.php';

			// Requires swiper
			if ($template_file === 'post-layout-carousel') {
				$data_response['requires_swiper'] = true;
			}
			
			$template_args = [
				'query'       => $query,
				'post_layout' => $post_layout,
				'filter_id'   => $filter_id,
				'counter'     => $counter,
				'per_page'    => $per_page,
				'paged'       => $paged
			];

			$use_template = false;

			if ($post_layout !== 'layout_custom') {
				// Standard / Carousel
        		$use_template = true;
			}
			else {
				// Custom post layout
				$post_layout_mode = Data_Store::get_meta_value($filter_id, 'ymc_fg_custom_layout_builder');

				if (isset($post_layout_mode['mode']['type']) && $post_layout_mode['mode']['type'] === 'classic') {
					// Custom + Classic
					$use_template = true;
				}
			}


			if ($use_template) {

				if (file_exists($template_path)) {
					Template::render($template_path, $template_args);
				} 
				else {
					echo '<div class="text">'. esc_html("Template not found: ") . esc_url($template_path) .'</div>';
				}
			}
			else {
				// Custom + Builder				
				$full_data = self::get_layout_schema($filter_id);
				$schema    = $full_data['schema'] ?? [];

				if (!empty($schema)) {
					foreach ($query->posts as $index => $post) {
						// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
						 Layout_Renderer::render($schema, [
							'post'      => $post,
							'query'     => $query,
							'filter_id' => $filter_id,
							'index'     => $index,
							'post_id'   => $post->ID,
                     'counter'   => $counter
						]);
						// phpcs:enable
					}
				}
			}


			if ($pagination_hidden === 'no' && $template_file !== 'post-layout-carousel') {
				switch ($pagination_type) {
					case 'numeric' :
						$pagination_rendered = Pagination::create_numeric_pagination($query, $paged, $filter_id, $counter);
						break;
					case 'loadmore' :
						$pagination_rendered = Pagination::create_load_more_pagination($query, $filter_id, $counter);
						break;
				}
			}

		} 
      else {
			echo '<div class="text">'. esc_html( $no_results_message ) .'</div>';
		}

      // Clear rendered posts and pagination
		$rendered_posts = ob_get_clean();
		$rendered_posts = preg_replace('/\s+/', ' ', $rendered_posts);
		$rendered_posts = preg_replace('/>\s+</', '><', $rendered_posts);
		$rendered_posts = trim($rendered_posts);
		$pagination_rendered = preg_replace('/[\r\n\t]+/', '', $pagination_rendered);

		wp_reset_postdata();

		$data_response['rendered_posts']      = $rendered_posts;
		$data_response['rendered_pagination'] = $pagination_rendered;
		$data_response['found_posts']         = $query->found_posts;
		$data_response['max_num_pages']       = $query->max_num_pages;
		$data_response['posts_count']         = $query->post_count;
		$data_response['paged']               = $paged;
		$data_response['pagination_type']     = $pagination_type;
		$data_response['scroll_filter_bar']   = $scroll_to_filters_on_load;
		$data_response['filtered_posts_label'] = $filtered_posts_label;

		// Debug Mode
		if($debug_mode === 'yes' && current_user_can('manage_options')) {
			$data_response['debug_mode'] = [
				'args' => $args,
				'found_posts'       => $query->found_posts,
				'max_num_pages'     => $query->max_num_pages,
				'posts_count'       => $query->post_count,
				'sql_request'       => $query->request,
				'is_main_query'     => $query->is_main_query(),
				'query_vars'        => $query->query_vars,
				'queried_object'    => $query->get_queried_object(),
				'queried_object_id' => $query->get_queried_object_id()
			];
		}


      return $this->success_response( $data_response );

   }
 

   /**
    * Update post views
    */
   public function update_post_views( WP_REST_Request $request ) {

      $post_id = absint($request->get_param( 'post_id' ));

      if ( empty( $post_id ) ) {

         return $this->error_response(
            'missing_post_id',
            'No post_id',
            400
         );
      }

      if ( ! get_post_status( $post_id ) ) {

         return $this->error_response(
            'invalid_post',
            'Invalid post',
            400
         );
      }

      $views = (int) get_post_meta($post_id, 'ymc_fg_post_views_count', true);

      $views++;

      update_post_meta($post_id, 'ymc_fg_post_views_count', $views);

      return $this->success_response([
         'views' => $views
      ]);
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


   /**
	 * Modify the SQL JOIN statement to include the postmeta table for custom meta data retrieval.
	 *
	 * @param string $join The existing SQL JOIN statement.
	 * @return string The modified SQL JOIN statement.
	 */
	public static function search_join( string $join ) : string {
		global $wpdb;
		$join .= " LEFT JOIN $wpdb->postmeta AS pm ON ID = pm.post_id ";
		return $join;
	}

	/**
	 * Modify the WHERE clause of the SQL query to include postmeta table for custom meta data search.
	 *
	 * @param string $where The existing WHERE clause of the SQL query.
	 * @return string The modified WHERE clause including the postmeta table search condition.
	 */
	public static function search_where( string $where ) : string {
		global $wpdb;

		$pattern = "/\(\s*{$wpdb->posts}.post_title\s+LIKE\s*'([^']*)'\s*\)/";

		$where = preg_replace_callback( $pattern, function( $matches ) use ( $wpdb ) {
			$raw = $matches[1];

			$raw = wp_unslash( $raw );
			$like = $wpdb->esc_like( $raw );

			$quoted_like = $wpdb->prepare( "'%s'", '%' . $like . '%' );

			return "({$wpdb->posts}.post_title LIKE {$quoted_like}) OR (pm.meta_value LIKE {$quoted_like})";
		}, $where );

		return $where;
	}


	/**
	 * Returns the DISTINCT keyword used in SQL queries.
	 *
	 * @param string $where
	 * @return string The DISTINCT keyword
	 */
	public static function search_distinct( string $where ) : string {
		return  'DISTINCT';	
   }


	/**
	 * Grouped search filters
	 *
	 * @return void
	 */
	public static function add_search_filters() : void {
		add_filter('posts_join', [__CLASS__, 'search_join']);
		add_filter('posts_where', [__CLASS__, 'search_where']);
		add_filter('posts_distinct', [__CLASS__, 'search_distinct']);
	}


	/**
	 * Remove search filters
	 * @return void
	 */
	public static function remove_search_filters() : void {
		remove_filter('posts_join', [__CLASS__, 'search_join']);
		remove_filter('posts_where', [__CLASS__, 'search_where']);
		remove_filter('posts_distinct', [__CLASS__, 'search_distinct']);
	}


   /**
	 * Registers alphabetical filtering for WP_Query by post title first letter.
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public static function add_alphabetical_filter() : void {
		add_filter(	'posts_where',	[ __CLASS__, 'alphabetical_where' ], 10, 2 );
	}


   /**
	 * Removes alphabetical filtering from WP_Query.
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public static function remove_alphabetical_filter() : void {
		if ( has_filter( 'posts_where', [ __CLASS__, 'alphabetical_where' ] ) ) {
			return;
		}
		remove_filter(	'posts_where',	[ __CLASS__, 'alphabetical_where' ], 10 );
	}


   /**
	 * Get layout schema
	 * 
	 * @since 3.6.0
	 * @return array
	 */
	public static function get_layout_schema(int $filter_id) : array {
		$raw = Data_Store::get_meta_value($filter_id, 'ymc_fg_lb_layout_schema');

		if (!is_string($raw) || $raw === '') {
			return [];
		}

		$decoded = json_decode($raw, true);
		return (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
			? $decoded
			: [];
	}

}