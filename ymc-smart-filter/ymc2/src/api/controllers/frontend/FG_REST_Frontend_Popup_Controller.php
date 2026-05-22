<?php
declare(strict_types=1);

namespace YMCFilterGrids\api\controllers\frontend;

defined( 'ABSPATH' ) || exit;

use YMCFilterGrids\api\abstracts\FG_REST_Abstract_Controller;
use YMCFilterGrids\FG_Data_Store as Data_Store;
use WP_REST_Request;
use WP_REST_Server;



/**
 * REST controller for frontend popup endpoints.
 *
 * Handles popup content requests and related frontend popup operations.
 *
 * @since 3.10.0
 */
class FG_REST_Frontend_Popup_Controller extends FG_REST_Abstract_Controller {

    protected $rest_base = 'popup';

    public function register_routes() {

        register_rest_route($this->namespace, '/' . $this->rest_base . '/open',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'get_post_to_popup' ],
                    'permission_callback' => [ $this, 'public_permissions_check' ]
                ],
            ]
        );
    }



    /**
    * Get post to popup
    *
    * @since 3.10.0
    * @param WP_REST_Request $request
    * @return \WP_REST_Response
    */
    public function get_post_to_popup( WP_REST_Request $request ) {

      $params = $request->get_json_params();

      $post_id = absint( $params['post_id'] ?? 0 );
      $grid_id = absint( $params['grid_id'] ?? 0 );
      $counter = absint( $params['counter'] ?? 0 );

      if ( empty( $post_id ) || empty( $grid_id ) || empty( $counter ) ) {

         return $this->error_response(
               'invalid_data',
               __( 'Invalid data received.', 'ymc-smart-filter' ),
               400
         );
      }

      ob_start();

      $post = get_post( $post_id );
      $post_status = Data_Store::get_meta_value( $grid_id, 'ymc_fg_post_status' );
      $selected_statuses = is_array( $post_status ) ? $post_status : [ 'publish' ];

      if ( ! empty( $post ) && in_array( $post->post_status, $selected_statuses, true ) && ! post_password_required( $post )) {

         if ( has_post_thumbnail( $post_id ) ) {
            echo '<figure class="post-image">';
            echo get_the_post_thumbnail( $post_id, 'full' );
            echo '</figure>';
         }

         echo '<h2 class="post-title">';
         echo esc_html( get_the_title( $post_id ) );
         echo '</h2>';

         // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
         echo apply_filters( 'the_content', $post->post_content );

         $content = ob_get_clean();

         $content = apply_filters('ymc/popup/custom_layout', $content, $post_id);
         $content = apply_filters('ymc/popup/custom_layout_' . $grid_id, $content, $post_id);
         $content = apply_filters('ymc/popup/custom_layout_' . $grid_id . '_' . $counter, $content, $post_id);

         return $this->success_response([
            'body' => $content
         ]);
      }

      return $this->error_response('forbidden', 'You do not have permission to view this content.', 403);

    }


}
