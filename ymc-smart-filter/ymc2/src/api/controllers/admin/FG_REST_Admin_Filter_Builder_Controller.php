<?php
declare(strict_types=1);

namespace YMCFilterGrids\api\controllers\admin;

defined( 'ABSPATH' ) || exit;

use YMCFilterGrids\api\abstracts\FG_REST_Abstract_Controller;
use YMCFilterGrids\FG_Data_Store as Data_Store;
use YMCFilterGrids\admin\FG_UiLabels as UiLabels;
use WP_REST_Request;
use WP_REST_Server;


/**
 * REST controller for admin filter builder endpoints.
 *
 * Handles filter builder configuration and related admin operations.
 *
 * @since 3.10.0
 */
class FG_REST_Admin_Filter_Builder_Controller extends FG_REST_Abstract_Controller {

   protected $rest_base = '/admin/filter-builder';

   public function register_routes() {

      register_rest_route( $this->namespace, '/' . $this->rest_base,
         [
            [
               'methods'             => WP_REST_Server::CREATABLE,
               'callback'            => [ $this, 'get_filter_builder_data' ],
               'permission_callback' => [ $this, 'check_admin_permissions' ],
            ],
         ]
      );      
   }


    /**
    * Get filter builder configuration data.
    *
    * @param \WP_REST_Request $request
    * @return \WP_REST_Response|\WP_Error
    */
   public function get_filter_builder_data( WP_REST_Request $request ) {

      $params = $request->get_json_params();

      $post_id = absint($params['post_id'] ?? 0);

      $placements   = [];
      $taxonomies   = [];
      $filter_types = [];

      if ( empty( $post_id ) ) {

         return $this->error_response(
            'invalid_data',
            'Invalid data received.',
            400
         );
      }

      /**
       * Filter types
       */
      $filter_types_raw = UiLabels::all( 'filter_types' );

      unset( $filter_types_raw['composite'] );

      if ( ! empty( $filter_types_raw ) ) {
         foreach ( $filter_types_raw as $key => $value ) {
            $filter_types[] = [
               'slug'  => $key,
               'label' => $value,
            ];
         }
      }

      /**
       * Selected taxonomies
       */
      $post_types = Data_Store::get_meta_value($post_id, 'ymc_fg_post_types');
      $tax_selected = Data_Store::get_meta_value($post_id, 'ymc_fg_taxonomies');

      $tax_selected_raw = array_intersect_key(
         ymc_get_taxonomies( $post_types ),
         array_flip( $tax_selected )
      );

      if ( ! empty( $tax_selected_raw ) ) {
         foreach ( $tax_selected_raw as $key => $value ) {
            $taxonomies[] = [
               'slug'  => $key,
               'label' => $value,
            ];
         }
      }

      /**
       * Placements
       */
      $placements_raw = UiLabels::all( 'placements' );

      if ( ! empty( $placements_raw ) ) {
         foreach ( $placements_raw as $key => $value ) {
            $placements[] = [
               'slug'  => $key,
               'label' => $value,
            ];
         }
      }

      return $this->success_response([
         'taxSelected' => $taxonomies,
         'filtersTypes' => $filter_types,
         'placements' => $placements,
      ]);
   }


   public function check_admin_permissions( $request ) {
      return current_user_can( 'manage_options' );
   }

   
}



