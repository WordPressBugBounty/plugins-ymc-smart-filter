<?php
declare(strict_types=1);

namespace YMCFilterGrids\api;

defined( 'ABSPATH' ) || exit;

use YMCFilterGrids\api\controllers\admin\FG_REST_Admin_Cache_Controller;
use YMCFilterGrids\api\controllers\admin\FG_REST_Admin_Taxonomies_Controller;
use YMCFilterGrids\api\controllers\admin\FG_REST_Admin_Terms_Controller;
use YMCFilterGrids\api\controllers\admin\FG_REST_Admin_Posts_Controller;
use YMCFilterGrids\api\controllers\admin\FG_REST_Admin_Filter_Builder_Controller;
use YMCFilterGrids\api\controllers\admin\FG_REST_Admin_Settings_Controller;
use YMCFilterGrids\api\controllers\frontend\FG_REST_Frontend_Filter_Controller;
use YMCFilterGrids\api\controllers\frontend\FG_REST_Frontend_Posts_Controller;
use YMCFilterGrids\api\controllers\frontend\FG_REST_Frontend_Search_Controller;
use YMCFilterGrids\api\controllers\frontend\FG_REST_Frontend_Popup_Controller;

/**
 * REST API Manager.
 *
 * Orchestrates the initialization and registration of all plugin REST API routes.
 *
 * @since 3.10.0
 */
class FG_REST_Manager {

   /**
    * Initialize the REST API manager.
    * Hooks into WordPress rest_api_init.
    *
    * @return void
    */
    public static function init() : void {        
      add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
    }

    /**
     * Register all custom REST API controllers.
     *
     * @return void
     */
    public static function register_routes() : void {

      /**         
      * Heare we define all our REST API controllers in one place.
      * @var array
      */
      $controllers = [
         FG_REST_Admin_Cache_Controller::class, 
         FG_REST_Admin_Taxonomies_Controller::class,
         FG_REST_Admin_Terms_Controller::class,
         FG_REST_Admin_Posts_Controller::class,
         FG_REST_Admin_Filter_Builder_Controller::class,
         FG_REST_Admin_Settings_Controller::class,
         FG_REST_Frontend_Filter_Controller::class,
         FG_REST_Frontend_Posts_Controller::class,
         FG_REST_Frontend_Search_Controller::class,
         FG_REST_Frontend_Popup_Controller::class
      ];

      foreach ( $controllers as $controller_class ) {
         
         $controller = new $controller_class();

         if ( method_exists( $controller, 'register_routes' ) ) {
            $controller->register_routes();
         }         
      }
   }
}


