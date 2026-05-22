<?php
declare(strict_types=1);

namespace YMCFilterGrids\api\controllers\admin;

defined( 'ABSPATH' ) || exit;

use YMCFilterGrids\api\abstracts\FG_REST_Abstract_Controller;
use WP_REST_Request;
use WP_REST_Server;
use WP_REST_Response;

/**
 * REST controller for admin cache endpoints.
 *
 * Handles cache invalidation and cached term count operations.
 *
 * @since 3.10.0
 */
class FG_REST_Admin_Cache_Controller extends FG_REST_Abstract_Controller {
    
   protected $rest_base = 'admin/cache';

   public function register_routes() {

      register_rest_route( $this->namespace, '/' . $this->rest_base, [
         [
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => [ $this, 'clear_terms_cache' ],
            'permission_callback' => [ $this, 'check_admin_permissions' ]
         ],
      ]);
   }
   

   /**
    * Clear all cached term counts for taxonomies.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
    */
   public function clear_terms_cache( WP_REST_Request $request ) : WP_REST_Response {

      global $wpdb;

      // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
      // phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
      $wpdb->query("
         DELETE FROM {$wpdb->options}
         WHERE option_name LIKE '_transient_ymc_fg_term_post_counts%'
         OR option_name LIKE '_transient_timeout_ymc_fg_term_post_counts%'
         "
      );

      wp_cache_flush();

      return $this->success_response([
         'cleared' => true,
         'message' => 'Terms cache cleared successfully.',
      ]);
   }

   
   public function check_admin_permissions( $request ) {
      return current_user_can( 'manage_options' );
   }
   
}