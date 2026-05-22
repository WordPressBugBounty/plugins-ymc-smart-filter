<?php
declare(strict_types=1);

namespace YMCFilterGrids\api\controllers\admin;

defined( 'ABSPATH' ) || exit;

use YMCFilterGrids\api\abstracts\FG_REST_Abstract_Controller;
use YMCFilterGrids\FG_Data_Store as Data_Store;
use YMCFilterGrids\admin\FG_Term as Term;
use WP_REST_Request;
use WP_REST_Server;


/**
 * REST controller for admin term endpoints.
 *
 * Handles term management and related admin operations.
 *
 * @since 3.10.0
 */
class FG_REST_Admin_Terms_Controller extends FG_REST_Abstract_Controller {
    
   protected $rest_base = 'admin/terms';

   public function register_routes() {

      register_rest_route( $this->namespace, '/' . $this->rest_base,
         [
            [
               'methods'             => WP_REST_Server::CREATABLE,
               'callback'            => [ $this, 'get_terms' ],
               'permission_callback' => [ $this, 'check_admin_permissions' ]
            ],
            [
               'methods'             => WP_REST_Server::DELETABLE,
               'callback'            => [ $this, 'clear_terms' ],
               'permission_callback' => [ $this, 'check_admin_permissions' ],
            ],
            [
               'methods'             => 'PUT',
               'callback'            => [ $this, 'update_terms' ],
               'permission_callback' => [ $this, 'check_admin_permissions' ],
            ],
         ]
      );

      register_rest_route( $this->namespace, '/' . $this->rest_base . '/sort',
         [
            [
               'methods'             => 'PUT',
               'callback'            => [ $this, 'update_term_sort' ],
               'permission_callback' => [ $this, 'check_admin_permissions' ],
            ],
         ]
      );

      register_rest_route( $this->namespace, '/' . $this->rest_base . '/icon',
         [
            [
               'methods'             => \WP_REST_Server::CREATABLE,
               'callback'            => [ $this, 'upload_term_icon' ],
               'permission_callback' => [ $this, 'check_upload_permissions' ],
            ],
         ]
      );

   }

   /**
    * Fetch terms for a given taxonomy and post types.
    *   
    * @param \WP_REST_Request $request 
    * @return \WP_REST_Response|\WP_Error
    */
   public function get_terms( WP_REST_Request $request ) { 

      $params = $request->get_json_params();

      $tax = sanitize_text_field( $params['slug'] ?? '' );
      $label = sanitize_text_field( $params['label'] ?? '' );
      $post_id = absint( $params['post_id'] ?? 0 );

		$item_term = [];
		$data = [];

		if (empty($tax) || empty($label) || empty($post_id)) {

         return $this->error_response(
            'invalid_data',
            'Invalid data received.',
            400
         );
		}

		$post_types = (array) Data_Store::get_meta_value($post_id,'ymc_fg_post_types');

		$args = [
			'taxonomy' => $tax,
			'hide_empty' => false
		];
		$terms = get_terms($args);

		if($terms && ! is_wp_error($terms)) {

         $cache_key = Term::get_cache_key($tax, $post_types);
         if (get_transient($cache_key) === false) {
            foreach ($terms as $term) {
               Term::update_post_counts($tax, $term->term_id);
            }
            set_transient($cache_key, true, 60 * MINUTE_IN_SECONDS);
         }

			foreach( $terms as $term ) {           			
				$post_count = Term::get_post_counts($post_types, $term->term_id);

				$item_term[] = [
					'term_id' => $term->term_id,
					'name'    => $term->name,
					'slug'    => $term->slug,
					'count'   => $post_count
				];
			}
		}

		$data['tax_slug']   =  $tax;
		$data['tax_label']  =  $label;
		$data['terms']      =  $item_term;

      return $this->success_response([
         'data_obj'   => $data,
         'post_types' => $post_types,
      ]);

   }


   /**
    * Clear all selected taxonomies and terms.
    *
    * @param \WP_REST_Request $request   
    * @return \WP_REST_Response|\WP_Error
    */
   public function clear_terms( WP_REST_Request $request ) {

      $params = $request->get_json_params();

      $post_id = absint(
         $params['post_id'] ?? 0
      );

      if ( empty( $post_id ) ) {

         return $this->error_response(
            'invalid_data',
            'Invalid data received.',
            400
         );
      }

      update_post_meta($post_id, 'ymc_fg_taxonomies', []);
		update_post_meta($post_id, 'ymc_fg_terms', [] );
		update_post_meta($post_id, 'ymc_fg_tax_attrs', []);
		update_post_meta($post_id, 'ymc_fg_term_attrs', []);
		update_post_meta($post_id, 'ymc_fg_tax_sort', []);
		update_post_meta($post_id, 'ymc_fg_term_sort', []);

      return $this->success_response([
         'message' => 'Terms cleared successfully.',
      ]);
   }


   /**
    * Update term attributes.
    *
    * @param \WP_REST_Request $request   
    * @return \WP_REST_Response|\WP_Error
    */
   public function update_terms( WP_REST_Request $request ) {

      $params = $request->get_json_params();

      $post_id = absint( $params['post_id'] ?? 0 );
      $term_attrs = $params['term_attrs'] ?? [];

      if ( empty( $post_id ) || empty( $term_attrs ) || ! is_array( $term_attrs ) ) {

         return $this->error_response(
            'invalid_data',
            'Invalid data received.',
            400
         );
      }

      /**
       * Normalize term attributes array.
      */
      $term_attrs = array_map(static function( $item ) {
            return is_array( $item ) ? $item : (array) $item;
         },
         $term_attrs
      );

      $result = update_post_meta( $post_id, 'ymc_fg_term_attrs', $term_attrs );

      if ( $result === false ) {
         $status = 'not_updated';

      } elseif ( $result === true ) {
         $status = 'updated';
      } elseif ( is_numeric( $result ) ) {
         $status = 'added';
      } else {
         $status = 'unknown';
      }

      return $this->success_response([
         'response' => $status,
         'message'  =>  'Terms saved.',
      ]);
   }


   /**
    * Update term sort order.
    *
    * @param WP_REST_Request $request   
    * @return \WP_REST_Response|\WP_Error
    */
   public function update_term_sort( WP_REST_Request $request) {

      $params = $request->get_json_params();

      $post_id = absint($params['post_id'] ?? 0);
      $terms_sort = $params['terms_sort'] ?? [];

      if (empty( $post_id ) || empty( $terms_sort ) || ! is_array( $terms_sort )) {

         return $this->error_response(
            'invalid_data',
            'Invalid data received.',
            400
         );
      }

      /**
       * Sanitize terms sort array.
      */
      $terms_sort = array_map('sanitize_text_field', $terms_sort);

      $updated = update_post_meta($post_id, 'ymc_fg_term_sort', $terms_sort);

      return $this->success_response([
         'updated' => (bool) $updated,
      ]);
   }



   /**
    * Upload custom icon for taxonomy term.
    *
    * @param \WP_REST_Request $request
    * @return \WP_REST_Response|\WP_Error
    */
   public function upload_term_icon( \WP_REST_Request $request ) {

      $files = $request->get_file_params();

      if ( empty( $files['icon'] ) ) {

         return $this->error_response(
            'no_file_uploaded',
            'No file uploaded.',
            400
         );
      }

      add_filter('upload_mimes',  static function( $mimes ) {
            $mimes['svg']  = 'image/svg+xml';
            $mimes['webp'] = 'image/webp';
            return $mimes;
         }
      );

      $file = $files['icon'];

      $allowed = [
         'image/svg+xml',
         'image/png',
         'image/jpeg',
         'image/webp',
      ];

      if ( ! in_array( $file['type'], $allowed, true ) ) {

         return $this->error_response(
            'invalid_file_type',
            'Invalid file type.',
            400
         );
      }

      require_once ABSPATH . 'wp-admin/includes/file.php';

      $upload = wp_handle_upload($file,
         [
            'test_form' => false,
         ]
      );

      if ( isset( $upload['error'] ) ) {

         return $this->error_response(
            'upload_failed',
            'Upload failed: ' . $upload['error'],
            400
         );
      }

      if ( ! empty( $upload['url'] ) ) {

         $relative_url = str_replace(site_url(), '', $upload['url']);

         return $this->success_response([
            'url'     => esc_url( $relative_url ),
            'message' =>  'Icon uploaded'
         ]);
      }

      return $this->error_response(
         'upload_failed',
         'Upload failed.',
         400
      );
   }

   
   /**
    * Check upload permissions.
    *
    * @param \WP_REST_Request $request
    * @return bool
    */
   public function check_upload_permissions( $request ) {
      return current_user_can( 'upload_files' );
   }


   public function check_admin_permissions( $request ) {
      return current_user_can( 'manage_options' );
   }
   
}