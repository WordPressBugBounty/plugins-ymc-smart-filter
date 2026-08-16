<?php
declare(strict_types=1);

namespace YMCFilterGrids\api\controllers\frontend;

defined( 'ABSPATH' ) || exit;

use YMCFilterGrids\api\abstracts\FG_REST_Abstract_Controller;
use YMCFilterGrids\FG_Data_Store as Data_Store;
use YMCFilterGrids\frontend\FG_Filter_Dropdown;
use WP_REST_Request;
use WP_REST_Server;



/**
 * REST controller for frontend filter endpoints.
 *
 * Handles frontend filter requests such as dropdown terms,
 * dependent taxonomy terms and other filter-related operations.
 *
 * @since 3.10.0
 */

class FG_REST_Frontend_Filter_Controller extends FG_REST_Abstract_Controller {

   protected $rest_base = 'filters';


   public function register_routes() {

      register_rest_route($this->namespace, '/' . $this->rest_base . '/dropdown/terms',
         [
            [
               'methods'             => WP_REST_Server::CREATABLE,
               'callback'            => [ $this, 'get_dropdown_terms' ],
               'permission_callback' => [ $this, 'public_permissions_check' ]
            ],
         ]
      );

      register_rest_route($this->namespace, '/' . $this->rest_base . '/dependent/terms',
         [
            [
               'methods'             => WP_REST_Server::CREATABLE,
               'callback'            => [ $this, 'load_dependent_terms' ],
               'permission_callback' => [ $this, 'public_permissions_check' ]
            ],
         ]
      );

   }


   /**
    * Get terms
    *   
    * @param \WP_REST_Request $request 
    * @return \WP_REST_Response|\WP_Error
    */
   public function get_dropdown_terms( WP_REST_Request $request ) {

      $params = $request->get_json_params();

      $query     = sanitize_text_field( $params['query'] ?? '' );
      $taxonomy  = sanitize_key( $params['taxonomy'] ?? '' );
      $filter_id = absint( $params['filter_id'] ?? 0 );

      if ( empty( $taxonomy ) || empty( $filter_id ) ) {

         return $this->error_response(
            'missing_params',
            'Missing parameters',
            400
         );
      }

      $per_page = max( 1, absint( $params['per_page'] ?? 20 ) );
      $offset   = max( 0, absint( $params['offset'] ?? 0 ) );

      $display_mode = Data_Store::get_meta_value($filter_id, 'ymc_fg_display_terms_mode');
      $post_types   = (array) Data_Store::get_meta_value($filter_id, 'ymc_fg_post_types');     
      $direction    = Data_Store::get_meta_value($filter_id, 'ymc_fg_term_sort_direction');
      $show_post_count = (string) Data_Store::get_meta_value($filter_id, 'ymc_fg_show_post_count');

      $filter = new FG_Filter_Dropdown(); 
      $filter->init_filter($filter_id);    

      if (in_array($display_mode, ['selected_terms', 'selected_terms_hide_empty'])) {                
         $all_terms = $filter->fetch_selected_terms_by_taxonomy($filter_id, $taxonomy);
      } 
      else {
         $args = [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false
         ];

         $terms = get_terms($args);

         if (is_wp_error($terms)) {
               wp_send_json_error([
                  'message' => $terms->get_error_message()
               ]);
         }

         $all_terms = [];

         foreach ($terms as $term) {
            $all_terms[$term->term_id] = $term->name;
         }
      }      

      if (in_array($display_mode, ['all_terms_hide_empty', 'selected_terms_hide_empty'])) {

         foreach ($all_terms as $term_id => $name) {

               $has_posts = false;

               foreach ($post_types as $type) {

                  $count = get_term_meta($term_id, "ymc_fg_count_{$type}", true);

                  if ((int)$count > 0) {
                     $has_posts = true;
                     break;
                  }
               }

               if (!$has_posts) {
                  unset($all_terms[$term_id]);
               }
         }
      }      

      if (!empty($query)) {

         $query = mb_strtolower($query);

         $all_terms = array_filter($all_terms, function ($name) use ($query) {

            return mb_strpos(mb_strtolower($name), $query) !== false;

         });
      }
     
      if ($direction === 'manual') {
         $all_terms = $filter->apply_manual_sort($all_terms, $filter_id);
      }     
      else {
         if ($direction === 'desc') {
            arsort($all_terms, SORT_NATURAL | SORT_FLAG_CASE);
         } else {
            asort($all_terms, SORT_NATURAL | SORT_FLAG_CASE);
         }
      }      

      $total_count = count($all_terms);

      $paged_terms = array_slice(
         $all_terms,
         $offset,
         $per_page,
         true
      );      

      ob_start();

      if (!empty($paged_terms)) {
         // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
         foreach ($paged_terms as $term_id => $term_name) {            
            echo $filter->render_term_button(
                  $term_id,
                  $term_name,
                  [$taxonomy],
                  $filter_id,
                  $post_types,
                  $show_post_count
               );
         }
      } 
      elseif ($offset === 0 && !empty($query)) {

         echo '<li class="ymc-dropdown__empty js-dropdown-empty">'
            . esc_html__('No terms found', 'ymc-smart-filter')
            . '</li>';
      }

      $terms_html = ob_get_clean();

      $terms_html = trim(preg_replace('/>\s+</', '><', $terms_html));      

      $close_button =
         '<li class="ymc-dropdown__close">
               <button type="button" class="dropdown-close-btn" aria-label="Close dropdown">×</button>
         </li>';

      return $this->success_response([
         'terms'       => $terms_html,
         'terms_count' => count($paged_terms),
         'total_count' => $total_count,
         'close_btn'   => $close_button,
      ]);

   }


   /**
    * Load dependent terms
    *   
    * @param \WP_REST_Request $request
    * @return \WP_REST_Response|\WP_Error
    */
   public function load_dependent_terms( WP_REST_Request $request ) {

      $params = $request->get_json_params();

      $filter_id = absint( $params['filter_id'] ?? 0 );
      $taxonomy  = sanitize_text_field( $params['taxonomy'] ?? '' );
      $selected  = array_map( 'absint', $params['selected'] ?? [] );

      if ( empty( $filter_id ) || empty( $taxonomy ) ) {

         return $this->error_response(
            'missing_params',
            'Invalid params',
            400
         );
      }

      $settings = Data_Store::get_meta_value($filter_id, 'ymc_fg_filter_dependent_settings');
		if (is_string($settings)) {
			$settings = maybe_unserialize($settings);
		}

		$sequence       = array_map('trim', explode(',', $settings['tax_sequence']));
		$current_index  = array_search($taxonomy, $sequence, true);

      if ($current_index === false || !isset($sequence[$current_index + 1])) {

         return $this->success_response(
            'No next taxonomy ',             
            200
         );
		}

      $next_taxonomy = $sequence[$current_index + 1];

		// Related terms
		$term_attrs = Data_Store::get_meta_value($filter_id, 'ymc_fg_term_attrs');
		$term_attrs = is_array($term_attrs) ? $term_attrs : maybe_unserialize($term_attrs);

		$related_ids = [];

      foreach ($term_attrs as $attr) {
			if (in_array((int)$attr['term_id'], $selected, true)) {
				$ids = !empty($attr['related_terms'])
					? json_decode($attr['related_terms'], true)
					: [];
				if (is_array($ids)) {
					$related_ids = array_merge($related_ids, $ids);
				}
			}
		}
		
      $related_ids = array_unique(
         array_map( 'absint', $related_ids )
      );

      // Fallback: children by hierarchy
		if (empty($related_ids)) {
			$children = [];
			foreach ($selected as $parent_id) {
				$terms = get_terms([
					'taxonomy'   => $next_taxonomy,
					'parent'     => $parent_id,
					'hide_empty' => false
				]);
				foreach ($terms as $t) {
					$children[] = $t->term_id;
				}
			}
			$related_ids = array_unique($children);
		}

      // Fetch terms
		$terms = !empty($related_ids)
			? get_terms([
				'taxonomy'   => $next_taxonomy,
				'include'    => $related_ids,
				'hide_empty' => false
			])
			: [];

		// Render HTML
		$filter = new \YMCFilterGrids\frontend\FG_Filter_Dependent();
		$mode   = $filter->get_tax_mode($next_taxonomy, $settings);
		$html   = $filter->render_dropdown($next_taxonomy, $terms, $mode, '',false, $filter_id);
		$html   = preg_replace('/\s+/', ' ', $html);
		$html   = trim(preg_replace('/[\r\n\t]+/', '', $html));

      return $this->success_response(
         [
            'taxonomy' => $next_taxonomy,
            'html'     => $html
         ]
      );

   }


}













