<?php
declare(strict_types=1);

namespace YMCFilterGrids\api\controllers\admin;

defined( 'ABSPATH' ) || exit;

use YMCFilterGrids\api\abstracts\FG_REST_Abstract_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use YMCFilterGrids\FG_Data_Store as Data_Store;


class FG_REST_Admin_Layouts_Controller extends FG_REST_Abstract_Controller {

   protected $rest_base = 'admin/layouts';


   public function register_routes() {

      register_rest_route( $this->namespace, '/' . $this->rest_base . '/save-layout',
         [
            [
               'methods'             => \WP_REST_Server::CREATABLE,
               'callback'            => [ $this, 'save_layout' ],               
               'permission_callback' => [ $this, 'check_edit_permissions' ], 
            ],
         ]
      );

      register_rest_route( $this->namespace, '/' . $this->rest_base . '/classic-snapshot',
         [
            [
               'methods'             => \WP_REST_Server::READABLE,
               'callback'            => [ $this, 'get_classic_snapshot' ],
               'permission_callback' => [ $this, 'check_edit_permissions' ],
            ],
         ]
      );

   }



   /**
    * Save layout schema via REST API
    *
    * @param \WP_REST_Request $request
    * @return \WP_REST_Response|\WP_Error
    */
   public function save_layout( \WP_REST_Request $request ) {
      
      $params = $request->get_json_params();

      $post_id = absint( $params['post_id'] ?? 0 );

      if ( ! $post_id ) {
         return $this->error_response( 'Invalid post ID.', 'invalid_post_id', 400 );
      }

      $post = get_post( $post_id );

      if ( ! $post || $post->post_type !== 'ymc_filters' ) {

         return $this->error_response(
            'Invalid filter.',
            'invalid_filter',
            400
         );
      }

      /*
      * Check whether the current user is allowed
      * to edit this specific filter.
      */
      if ( ! current_user_can( 'edit_post', $post_id ) ) {

         return $this->error_response(
            'Access denied.',
            'access_denied',
            403
         );
      }

      $data = $params['data'] ?? [];
      
      if ( empty( $data ) || empty( $data['schema'] ) ) {
         return $this->error_response( 'Schema is missing or invalid.', 'invalid_schema', 400 );
      }
      
      update_post_meta(
         $post_id,
         'ymc_fg_lb_layout_schema',
         wp_slash( wp_json_encode( $data, JSON_UNESCAPED_UNICODE ) )
      );
      
      return $this->success_response([
         'message' => 'Layout saved',
         'post_id' => $post_id
      ]);
   }


   /**
    * Get classic snapshot schema via REST API
    *
    * @param \WP_REST_Request $request
    * @return \WP_REST_Response|\WP_Error
    */
   public function get_classic_snapshot( \WP_REST_Request $request ) {
      
      $filter_id = absint( $request->get_param( 'filter_id' ) );
      
      if ( ! $filter_id ) {
         return $this->error_response( 'Filter ID is missing.', 'missing_filter_id', 400 );
      }

      // Preparing data (as in post-layout-custom.php)      
      $post_type    = Data_Store::get_meta_value( $filter_id, 'ymc_fg_post_types' );
      $terms_attr   = Data_Store::get_meta_value( $filter_id, 'ymc_fg_term_attrs' );
      $popup_enable = Data_Store::get_meta_value( $filter_id, 'ymc_fg_popup_enable' );
      $popup_class  = ( $popup_enable === 'no' ) ? '' : ' js-ymc-popup-trigger';

      // Receive one test post to generate a preview.
      $args = [
         'post_type'      => $post_type,
         'posts_per_page' => 1,
         'post_status'    => 'publish',
      ];
      $test_query = new \WP_Query( $args );

      if ( ! $test_query->have_posts() ) {
         return $this->error_response( 'No posts found to create a snapshot.', 'no_posts_found', 404 );
      }

      // Generating a Snapshot
      $test_query->the_post();
      $post_id = get_the_ID();      
      
      $post_term_settings = function_exists( 'ymc_get_post_terms_settings' ) 
         ? ymc_get_post_terms_settings( $post_id, $terms_attr ) 
         : [];

      // Generate HTML block guide
      $guide_html  = '<div class="filter-custom-guide"><div class="filter-usage"><div class="filter-usage-inner">';
      $guide_html .= '<span class="headline">' . esc_html__( "Classic Layout Placeholder", "ymc-smart-filter" ) . '</span>';
      $guide_html .= '</div></div></div>';

      $final_html = apply_filters( "ymc/post/layout/custom", $guide_html, $post_id, $filter_id, $popup_class, $post_term_settings );
      $final_html = apply_filters( "ymc/post/layout/custom_{$filter_id}", $final_html, $post_id, $filter_id, $popup_class, $post_term_settings );
      
      wp_reset_postdata();

      // Forming the final Schema for the Builder     
      $schema = [
         'type'     => 'card',
         'id'       => 'card_' . wp_generate_password( 8, false ),
         'settings' => (object)[],
         'children' => [
            [
               'type'     => 'row',
               'id'       => 'row_' . wp_generate_password( 8, false ),
               'settings' => (object)[],
               'children' => [
                  [
                     'type'     => 'column',
                     'id'       => 'col_' . wp_generate_password( 8, false ),
                     'settings' => [ 'width' => 100 ],
                     'children' => [
                        [
                           'type'     => 'raw_html',
                           'id'       => 'snapshot_' . wp_generate_password( 8, false ),
                           'settings' => [
                              'content' => $final_html
                           ]
                        ]
                     ]
                  ]
               ]
            ]
         ]
      ];
      
      return $this->success_response( [ 'schema' => $schema ] );
   }



   /**
    * Check if user can edit posts.
    *
    * @param \WP_REST_Request $request
    * @return bool
    */
   public function check_edit_permissions( $request ) {
      return current_user_can( 'edit_posts' );
   }

}









