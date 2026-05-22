<?php
declare(strict_types=1);

namespace YMCFilterGrids\api\abstracts;

defined( 'ABSPATH' ) || exit;

use WP_REST_Response;
use WP_REST_Request;
use WP_Error;

/**
 * Abstract Class for YMC REST API Controllers.
 *
 * @since 3.10.0
 */
abstract class FG_REST_Abstract_Controller extends \WP_REST_Controller {

   /**
    * API Namespace.
    *
    * @var string
    */
   protected $namespace = 'ymc/v1';

   /**
    * The base of this controller's route.
    *
    * @var string
    */
   protected $rest_base;

   /**
    * Wrapper for a successful JSON response.
    *
    * @param mixed $data The data to return.
    * @param int $status HTTP status code.
    * @return \WP_REST_Response
    */
   protected function success_response( $data, int $status = 200 ) : WP_REST_Response {
      return new WP_REST_Response( [
         'success' => true,
         'data'    => $data,
         'version' => YMC_VERSION,
      ], $status );
   }

   /**
    * Wrapper for a standardized error response.
    *
    * @param string $message Error message.
    * @param string $code Error code.
    * @param int $status HTTP status code.
    * @return \WP_Error
    */
   protected function error_response( string $message, string $code = 'ymc_api_error', int $status = 400 ) : WP_Error {
      return new WP_Error( $code, $message, [ 'status' => $status ] );
   }

   /**
    * Check if a given request has access to get items.
    *
    * @param \WP_REST_Request $request Full data about the request.
    * @return bool|\WP_Error
    */
   public function public_permissions_check( WP_REST_Request $request ) {
      return true;
   }
}






