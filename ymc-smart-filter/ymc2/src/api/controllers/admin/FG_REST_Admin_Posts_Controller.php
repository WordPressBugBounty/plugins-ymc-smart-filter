<?php
declare(strict_types=1);

namespace YMCFilterGrids\api\controllers\admin;

defined( 'ABSPATH' ) || exit;

use YMCFilterGrids\api\abstracts\FG_REST_Abstract_Controller;
use YMCFilterGrids\FG_Data_Store as Data_Store;
use WP_REST_Request;
use WP_REST_Server;



/**
 * REST controller for admin post endpoints.
 *
 * Handles admin post management and related operations.
 *
 * @since 3.10.0
 */
class FG_REST_Admin_Posts_Controller extends FG_REST_Abstract_Controller {

   protected $rest_base = 'admin/posts';

   public function register_routes() {

      register_rest_route( $this->namespace, '/' . $this->rest_base . '/load',
         [
            [
               'methods'             => WP_REST_Server::CREATABLE,
               'callback'            => [ $this, 'load_posts' ],
               'permission_callback' => [ $this, 'check_admin_permissions' ],
            ],
         ]
      );

      register_rest_route( $this->namespace, '/' . $this->rest_base . '/search',
         [
            [
               'methods'             => WP_REST_Server::CREATABLE,
               'callback'            => [ $this, 'search_posts' ],
               'permission_callback' => [ $this, 'check_admin_permissions' ],
            ],
         ]
      );
   }


   /**
    * Load posts for infinite scroll.
    *
    * @param WP_REST_Request $request
    * @return \WP_REST_Response|\WP_Error
    */
   public function load_posts( WP_REST_Request $request ) {

      $params = $request->get_json_params();

      $post_types = $params['post_types'] ?? [];
      $paged = absint( $params['paged'] ?? 1 );
      $posts_per_page = absint($params['posts_per_page'] ?? 20);
      $post_id = absint($params['post_id'] ?? 0);

      if (empty( $post_id ) || empty( $post_types ) || ! is_array( $post_types )) {

         return $this->error_response(
            'invalid_data',
            'Invalid data received.',
            400
         );
      }

      $post_types = array_map('sanitize_text_field', $post_types);
      $posts = [];

      $query = new \WP_Query([
         'post_type'      => $post_types,
         'orderby'        => 'title',
         'order'          => 'ASC',
         'paged'          => $paged,
         'posts_per_page' => $posts_per_page,
      ]);

      if ( $query->have_posts() ) {
         
         $selected_posts = array_map('intval', (array) Data_Store::get_meta_value($post_id, 'ymc_fg_selected_posts'));

         while ( $query->have_posts() ) {
            $query->the_post();
            $pid = get_the_ID();

            $is_disabled = in_array($pid, $selected_posts, true) ? 'is-disabled' : '';

            $posts[] = [
               'id' => $pid,
               'title' => get_the_title( $pid ),
               'is_disabled' => $is_disabled,
            ];
         }

         wp_reset_postdata();
      }

      return $this->success_response([
         'posts_loaded' => count( $posts ),
         'found_posts'  => $query->found_posts,
         'posts'        => $posts,
      ]);
   }


   /**
    * Search posts.
    *
    * @param WP_REST_Request $request
    * @return \WP_REST_Response|\WP_Error
    */
   public function search_posts( WP_REST_Request $request ) {

      $params = $request->get_json_params();

      $post_types = $params['post_types'] ?? [];
      $phrase = sanitize_text_field(
         trim( $params['phrase'] ?? '' )
      );

      $post_id = absint($params['post_id'] ?? 0);

      if ( empty( $post_id ) || empty( $post_types ) || ! is_array( $post_types )) {

         return $this->error_response(
            'invalid_data',
            'Invalid data received.',
            400
         );
      }

      $post_types = array_map('sanitize_text_field', $post_types);

      $posts = [];

      $query = new \WP_Query([
         'post_type'      => $post_types,
         'posts_per_page' => 60,
         'orderby'        => 'title',
         'order'          => 'ASC',
         'sentence'       => true,
         's'              => $phrase,
      ]);

      if ( $query->have_posts() ) {
         
         $selected_posts = array_map('intval', (array) Data_Store::get_meta_value($post_id, 'ymc_fg_selected_posts'));

         while ( $query->have_posts() ) {
            $query->the_post();
            $pid = get_the_ID();

            $is_disabled = in_array( $pid, $selected_posts, true ) ? 'is-disabled' : '';

            $posts[] = [
               'id' => $pid,
               'title' => get_the_title( $pid ),
               'is_disabled' => $is_disabled,
            ];
         }

         wp_reset_postdata();
      }

      return $this->success_response([
         'found_posts' => $query->found_posts,
         'posts'       => $posts,
      ]);
   }



   public function check_admin_permissions( $request ) {
         return current_user_can( 'manage_options' );
   }

}