<?php
declare(strict_types=1);

namespace YMCFilterGrids\api\controllers\admin;

defined( 'ABSPATH' ) || exit;

use YMCFilterGrids\api\abstracts\FG_REST_Abstract_Controller;
use YMCFilterGrids\FG_Data_Store as Data_Store;
use WP_REST_Request;
use WP_REST_Server;


/**
 * REST controller for admin taxonomy endpoints.
 *
 * Handles taxonomy management and related admin operations.
 *
 * @since 3.10.0
 */
class FG_REST_Admin_Taxonomies_Controller extends FG_REST_Abstract_Controller {
    
   protected $rest_base = 'admin/taxonomies';

   public function register_routes() {

      register_rest_route( $this->namespace, '/' . $this->rest_base, 
         [
            [
               'methods'             => WP_REST_Server::CREATABLE,
               'callback'            => [ $this, 'get_taxonomies' ],
               'permission_callback' => [ $this, 'check_admin_permissions' ]
            ],
            [
               'methods'             => 'PUT',
               'callback'            => [ $this, 'update_taxonomy_sort' ],
               'permission_callback' => [ $this, 'check_admin_permissions' ],
            ],
         ]
      );

      register_rest_route( $this->namespace, '/' . $this->rest_base . '/refresh',
         [
            [
               'methods'             => WP_REST_Server::CREATABLE,
               'callback'            => [ $this, 'refresh_taxonomies' ],
               'permission_callback' => [ $this, 'check_admin_permissions' ],
            ],
         ]
      );

      register_rest_route( $this->namespace, '/' . $this->rest_base . '/attrs',
         [
            [
               'methods'             => 'PUT',
               'callback'            => [ $this, 'update_taxonomy_attrs' ],
               'permission_callback' => [ $this, 'check_admin_permissions' ],
            ],
         ]
      );

   }

   /**
    * Fetch taxonomies for given post types and reset related meta.
    * @param \WP_REST_Request $request
    * @return \WP_REST_Response|\WP_Error
    */
   public function get_taxonomies( WP_REST_Request $request ) {

      $params = $request->get_json_params();
      $post_id = absint( $params['post_id'] ?? 0 );
      $post_types = $params['post_types'] ?? [];

		$list_taxonomies = [];
		$list_posts = [];

      if (empty( $post_id ) || ! is_array( $post_types ) || empty( $post_types )) {

         return $this->error_response(
            'invalid_data', 'Invalid data received.',
            400
         ); 
      }		

      $post_types = array_map( 'sanitize_text_field', $post_types );

		update_post_meta( $post_id, 'ymc_fg_post_types', $post_types );
		update_post_meta( $post_id, 'ymc_fg_taxonomies', []);
		update_post_meta( $post_id, 'ymc_fg_terms', []);
		update_post_meta( $post_id, 'ymc_fg_tax_attrs', []);
		update_post_meta( $post_id, 'ymc_fg_term_attrs', []);
		update_post_meta( $post_id, 'ymc_fg_tax_sort', []);
		update_post_meta( $post_id, 'ymc_fg_term_sort', []);
		update_post_meta( $post_id, 'ymc_fg_selected_posts', []);
		update_post_meta( $post_id, 'ymc_fg_filter_options', []);
		update_post_meta( $post_id, 'ymc_fg_filter_type', 'default');
		update_post_meta( $post_id, 'ymc_fg_filter_dependent_settings', []);		
		
		// Reset builder layout
		delete_post_meta( $post_id, 'ymc_fg_lb_layout_schema' );
		// Reset custom layout builder only if it's preset
		$data = Data_Store::get_meta_value( $post_id, 'ymc_fg_custom_layout_builder');
		if (is_array($data) && isset($data['start']['type'])) {
			$data['start']['type'] = 'preset';
			update_post_meta( $post_id, 'ymc_fg_custom_layout_builder', $data);
		}		

		// Get taxonomies
		$data_object = get_object_taxonomies($post_types, 'objects');
		if(!empty($data_object)) {
			foreach ($data_object as $value) {
				$list_taxonomies[$value->name] = $value->label;
			}
			asort($list_taxonomies);
		}

		// Get post types
		$query = new \WP_query([
			'post_type' => $post_types,
			'orderby' => 'title',
			'order' => 'ASC',
			'posts_per_page' => 20
		]);
		$found_posts = $query->found_posts;
		if ( $query->have_posts() ) {
			while ($query->have_posts()) {
				$query->the_post();
				$current_post_id = get_the_ID();
				$list_posts[] = [
					'id' => $current_post_id,
					'title' => get_the_title($current_post_id)
				];
			}
         wp_reset_postdata();
		}

      return $this->success_response([
         'taxonomies' => $list_taxonomies,
         'posts' => $list_posts,
         'found_posts' => $found_posts,
      ]);

   }


   /**
    * Refresh available taxonomies by selected post types.
    *
    * @param \WP_REST_Request $request
    * @return \WP_REST_Response|\WP_Error
    */
   public function refresh_taxonomies( WP_REST_Request $request ) {

      $params = $request->get_json_params();

      $post_types = $params['post_types'] ?? [];

      if ( empty( $post_types ) || ! is_array( $post_types ) ) {

         return $this->error_response(
            'invalid_data',
            'Invalid data received.',
            400
         );
      }

      $post_types = array_map( 'sanitize_text_field', $post_types );

      $list_taxonomies = [];

      $data_object = get_object_taxonomies( $post_types, 'objects' );

      if ( ! empty( $data_object ) ) {
         foreach ( $data_object as $taxonomy ) {
            $list_taxonomies[ $taxonomy->name ] = $taxonomy->label;
         }
         asort( $list_taxonomies );
      }

      return $this->success_response([
         'taxonomies' => $list_taxonomies,
      ]);
   }


   /**
    * Update taxonomy sort order.
    *
    * @param WP_REST_Request $request   
    * @return \WP_REST_Response|\WP_Error
    */
   public function update_taxonomy_sort( WP_REST_Request $request) {

      $params = $request->get_json_params();

      $post_id = absint($params['post_id'] ?? 0);
      $tax_sort = $params['tax_sort'] ?? [];

      if ( empty( $post_id ) || empty( $tax_sort ) || ! is_array( $tax_sort ) ) {

         return $this->error_response(
            'invalid_data',
            'Invalid data received.',
            400
         );
      }

      /**
       * Sanitize taxonomy sort array.
      */
      $tax_sort = array_map('sanitize_text_field', $tax_sort);

      $updated = update_post_meta($post_id, 'ymc_fg_tax_sort', $tax_sort);

      return $this->success_response([
         'updated' => (bool) $updated,
      ]);
   }


   /**
    * Update taxonomy attributes.
    *
    * @param WP_REST_Request $request   
    * @return \WP_REST_Response|\WP_Error
    */
   public function update_taxonomy_attrs( WP_REST_Request $request ) {

      $params = $request->get_json_params();

      $post_id  = absint( $params['post_id'] ?? 0 );
      $tax_attrs = $params['tax_attrs'] ?? [];

      if (empty( $post_id ) || empty( $tax_attrs ) || ! is_array( $tax_attrs )) {

         return $this->error_response(
            'invalid_data',
            'Invalid data received.',
            400
         );
      }

      /**
       * Normalize taxonomy attributes.
      */
      $tax_attrs = array_map(
         static function( $item ) {
            return is_array( $item ) ? $item : (array) $item;
         },
         $tax_attrs
      );

      $updated = update_post_meta($post_id, 'ymc_fg_tax_attrs', $tax_attrs);

      return $this->success_response([
         'response' => (bool) $updated,
         'message'  => 'Taxonomy saved'
      ]);
   }


   
   public function check_admin_permissions( $request ) {
      return current_user_can( 'manage_options' );
   }
   
}