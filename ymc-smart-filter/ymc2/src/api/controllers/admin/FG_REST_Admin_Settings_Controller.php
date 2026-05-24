<?php
declare(strict_types=1);

namespace YMCFilterGrids\api\controllers\admin;

defined( 'ABSPATH' ) || exit;

use YMCFilterGrids\api\abstracts\FG_REST_Abstract_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use YMCFilterGrids\FG_Template as Template;



/**
 *  REST controller for admin post endpoints.
 * 
 * Controller for managing plugin settings in the admin area.
 * 
 * @since 3.10.0
 */
class FG_REST_Admin_Settings_Controller extends FG_REST_Abstract_Controller {

   protected $rest_base = 'admin/settings';

   public function register_routes() {

      register_rest_route($this->namespace, '/' . $this->rest_base . '/export-settings',
         [
            [
               'methods'             => WP_REST_Server::CREATABLE,
               'callback'            => [ $this, 'export_settings' ],
               'permission_callback' => [ $this, 'check_admin_permissions' ],
            ],
         ]
      );

      register_rest_route($this->namespace, '/' . $this->rest_base . '/import-settings',
         [
            [
               'methods'             => WP_REST_Server::CREATABLE,
               'callback'            => [ $this, 'import_settings' ],
               'permission_callback' => [ $this, 'check_admin_permissions' ],
            ],
         ]
      );

      register_rest_route( $this->namespace, '/' . $this->rest_base . '/usage',
         [
            [
               'methods'             => \WP_REST_Server::READABLE,
               'callback'            => [ $this, 'get_usage_page' ],
               'permission_callback' => [ $this, 'check_admin_permissions' ],
            ],
         ]
      );

      register_rest_route( $this->namespace, '/' . $this->rest_base . '/scan-usage',
         [
            [
               'methods'             => \WP_REST_Server::CREATABLE,
               'callback'            => [ $this, 'scan_existing_posts' ],
               'permission_callback' => [ $this, 'check_admin_permissions' ],
            ],
         ]
      );

   }


   /**
    * Export settings
    * 
    * @param WP_REST_Request $request
    * 
    * @return WP_REST_Response
    */
   public function export_settings( WP_REST_Request $request ) {

      $post_id = absint( $request->get_param( 'post_id' ) );

      if (empty( $post_id )) {

         return $this->error_response(
            'Invalid data received.',
            'invalid_post_id',
            400
         );
      }

      $output  = [];
      $options = get_post_meta( $post_id );

      if ( ! empty( $options ) && is_array( $options ) ) {

         foreach ( $options as $key => $value ) {

            if ( str_starts_with( $key, 'ymc_fg_' ) ) {

               if ($key !== 'ymc_fg_custom_css' && $key !== 'ymc_fg_custom_js') {

                  foreach ( $value as $item ) {
                     $output[ $key ] = maybe_unserialize( $item );
                  }

               } else {
                  $output[ $key ] = '';
               }
            }
         }
      }      

      $response = new WP_REST_Response( $output );

      $response->header('Content-Type', 'application/json');

      $response->header('Content-Disposition', 'attachment; filename="ymc-fg-settings-export.json"');

      return $response;
   }


   /**
    * Import settings
    *
    * @param WP_REST_Request $request
    *
    * @return WP_REST_Response
    */
   public function import_settings( WP_REST_Request $request ) {

      if ( ! current_user_can( 'upload_files' ) ) {

         return $this->error_response(
            'permission_denied',
            'Permission denied.',
            403
         );
      }

      $post_id = absint( $request->get_param( 'post_id' ) );

      if ( empty( $post_id ) ) {

         return $this->error_response(
            'Invalid data received.',
            'invalid_post_id',
            400
         );
      }

      $files = $request->get_file_params();

      if ( empty( $files ) || empty( $files['file'] ) ) {

         return $this->error_response(
            'No file uploaded.',
            'missing_file',
            400
         );
      }

      $file = $files['file'];

      if ( empty( $file['tmp_name'] ) ) {

         return $this->error_response(
            'Invalid uploaded file.',
            'invalid_file',
            400
         );
      }

      $json = file_get_contents( $file['tmp_name'] );

      if ( $json === false ) {

         return $this->error_response(
            'Unable to read uploaded file.',
            'read_error',
            400
         );
      }

      $json = trim( $json );

      $json = preg_replace( '/^\xEF\xBB\xBF/', '', $json );

      if ( $json === '' ) {

         return $this->error_response(
            'File is empty.',
            'empty_file',
            400
         );
      }

      $clean_data = json_decode( $json, true );

      if ( is_string( $clean_data ) ) {
         $clean_data = json_decode( $clean_data, true );
      }

      if ( json_last_error() !== JSON_ERROR_NONE ) {

         return $this->error_response(
            'Invalid JSON format.',
            'invalid_json',
            400
         );
      }

      if ( ! is_array( $clean_data ) || empty( $clean_data ) ) {

         return $this->error_response(
            'Import of settings unsuccessful.',
            'invalid_data',
            400
         );
      }

      foreach ( $clean_data as $meta_key => $meta_value ) {

         if ( ! str_starts_with( $meta_key, 'ymc_fg_' ) ) {
            continue;
         }

         update_post_meta(
            $post_id,
            sanitize_key( $meta_key ),
            $meta_value
         );
      }

      return $this->success_response([
         'message' => 'Imported settings successfully.'
      ]);

   }


   /**
    * Load usage page for filter
    *
    * @param \WP_REST_Request $request
    * @return \WP_REST_Response|\WP_Error
    */
   public function get_usage_page( \WP_REST_Request $request ) {
      $filter_id = absint( $request->get_param( 'filter_id' ) );
      $paged     = max( 1, absint( $request->get_param( 'page' ) ) );

      if ( empty( $filter_id ) ) {
         return $this->error_response( 'Invalid filter ID.', 'invalid_data', 400 );
      }

      $post_types = get_post_types( [ 'public' => true ], 'names' );           
      $post_types = array_diff( $post_types, [ 'attachment', 'nav_menu_item' ] );        
      $post_types = apply_filters( 'ymc_fg_usage_post_types', $post_types ); 

      ob_start();

      $args = [
         'post_type'      => $post_types,
         'post_status'    => [ 'publish', 'draft', 'private', 'pending', 'future' ],
         'posts_per_page' => 20,
         'paged'          => $paged,
         'meta_query'     => [
            [
               'key'     => 'ymc_fg_filter_usage',
               'value'   => 'i:' . $filter_id . ';',
               'compare' => 'LIKE',
            ],
         ],
         'orderby' => 'title',
         'order'   => 'ASC',
      ];

      $query = new \WP_Query( $args );

      if ( $query->have_posts() ) {
         while ( $query->have_posts() ) {
            $query->the_post();
            $post_id = get_the_ID();          

            Template::render( YMC_ABSPATH . '/src/admin/php-templates/tmpl-usage-filter.php', [
               'post_id' => $post_id
            ] );
         }
      }    

      wp_reset_postdata();
      
      $html = ob_get_clean();
      $html = str_replace( '> <', '><', trim( preg_replace( '/\s+/', ' ', $html ) ) );

      return $this->success_response( [
         'html'  => $html,
         'page'  => $paged,
         'pages' => $query->max_num_pages,
      ] );
   }



   /**
    * Scan existing posts for usage of filters
    *
    * @param \WP_REST_Request $request
    * @return \WP_REST_Response|\WP_Error
    */
   public function scan_existing_posts( \WP_REST_Request $request ) {      
      
      $paged = max( 1, absint( $request->get_param( 'page' ) ?? 1 ) );
      
      $per_page   = 20;
      $post_types = get_post_types( [ 'public' => true ], 'names' );           
      $post_types = array_diff( $post_types, [ 'attachment', 'nav_menu_item' ] );        
      $post_types = apply_filters( 'ymc_fg_usage_post_types', $post_types ); 

      $args = [
         'post_type'      => $post_types,
         'post_status'    => [ 'publish', 'draft', 'private', 'pending', 'future' ],
         'posts_per_page' => $per_page,
         'paged'          => $paged,
         'fields'         => 'ids',
         'orderby'        => 'ID',
         'order'          => 'ASC',
         'no_found_rows'  => false,
      ];

      $query = new \WP_Query( $args );

      $processed       = 0;
      $updated         = 0;
      $updated_by_type = [];
      $filters_touched = [];

      foreach ( $query->posts as $post_id ) {
         $processed++;

         $content = get_post_field( 'post_content', $post_id );
         if ( ! $content ) {
            continue;
         }

         $new_filters = array_values(
            array_unique(
               array_map( 'absint', ymc_extract_filter_ids_from_content( $content ) )
            )
         );

         $old_filters = get_post_meta( $post_id, 'ymc_fg_filter_usage', true );
         $old_filters = is_array( $old_filters ) ? array_map( 'absint', $old_filters ) : [];
         
         if ( empty( $new_filters ) && empty( $old_filters ) ) {
            continue;
         }
         
         if ( empty( $new_filters ) && ! empty( $old_filters ) ) {
            delete_post_meta( $post_id, 'ymc_fg_filter_usage' );
            $updated++;

            foreach ( $old_filters as $fid ) {
               $filters_touched[ $fid ] = true;
            }
            continue;
         }

         sort( $new_filters );
         sort( $old_filters );

         if ( $new_filters === $old_filters ) {
            continue;
         }
         
         update_post_meta( $post_id, 'ymc_fg_filter_usage', $new_filters );
         $updated++;

         $post_type = get_post_type( $post_id );
         $pt_object = $post_type ? get_post_type_object( $post_type ) : null;

         if ( $pt_object && ! empty( $pt_object->labels->singular_name ) ) {
            $label = $pt_object->labels->singular_name;
            $updated_by_type[ $label ] = ( $updated_by_type[ $label ] ?? 0 ) + 1;
         }

         $filters_to_touch = array_unique(
            array_merge( $old_filters, $new_filters )
         );

         foreach ( $filters_to_touch as $fid ) {
            $filters_touched[ $fid ] = true;
         }
      }

      if ( $updated === 0 ) {
         $updated_by_type = [];
      }
      
      foreach ( array_keys( $filters_touched ) as $filter_id ) {
         delete_transient( 'ymc_fg_usage_summary_' . $filter_id );
      }

      return $this->success_response( [
         'page'            => $paged,
         'processed'       => $processed,
         'updated'         => $updated,
         'updated_by_type' => $updated_by_type,
         'total'           => (int) $query->found_posts,
         'has_more'        => ( $paged < $query->max_num_pages ),
         'next_page'       => $paged + 1,
      ] );

   }



   public function check_admin_permissions( $request ) {
      return current_user_can( 'manage_options' );
   }

}