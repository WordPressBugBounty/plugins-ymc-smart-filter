<?php
declare(strict_types=1);

namespace YMCFilterGrids\api\controllers\admin;

defined( 'ABSPATH' ) || exit;

use YMCFilterGrids\api\abstracts\FG_REST_Abstract_Controller;


/**
 *  REST controller for admin post endpoints.
 * 
 * Controller for managing plugin settings in the admin area.
 * 
 * @since 3.10.0
 */
class FG_REST_Admin_Settings_Controller extends FG_REST_Abstract_Controller {

   protected $rest_base = 'admin/settings';

   public function register_routes() {}


   public function check_admin_permissions( $request ) {
      return current_user_can( 'manage_options' );
   }

}