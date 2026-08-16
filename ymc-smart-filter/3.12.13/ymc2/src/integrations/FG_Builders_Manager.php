<?php declare(strict_types=1);

namespace YMCFilterGrids\integrations;

use YMCFilterGrids\integrations\FG_Elementor_Widget as Elementor_Widget;
use YMCFilterGrids\frontend\FG_Shortcodes as Shortcodes;
use YMCFilterGrids\frontend\FG_Frontend_Scripts as Frontend_Scripts;

defined( 'ABSPATH' ) || exit;



/**
 * Class FG_Builders_Manager
 *
 * @package YMCFilterGrids\integrations
 * @since   3.12.0
 */
final class FG_Builders_Manager {


   public static function init() : void {
      
      // WPBakery Page Builder
      if ( self::is_wpbakery_active() ) {
         add_action( 'vc_before_init', [ __CLASS__, 'integrate_wpbakery' ] );         
         add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_builder_styles' ] );
         add_action( 'vc_frontend_editor_enqueue_js_css', [ __CLASS__, 'enqueue_builder_styles' ] );
      }

      // Elementor
      if ( self::is_elementor_active() ) {
         add_action( 'elementor/widgets/register', [ __CLASS__, 'integrate_elementor' ] );
         add_action( 'elementor/frontend/after_enqueue_scripts', [ __CLASS__, 'enqueue_elementor_editor_assets' ] );
         add_action( 'elementor/editor/before_enqueue_scripts', [ __CLASS__, 'enqueue_elementor_icon_styles' ] );         
      }

      // Gutenberg (Block Editor)
      if ( self::is_gutenberg_active() ) {
         add_action( 'init', [ __CLASS__, 'integrate_gutenberg' ] );
         add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'enqueue_gutenberg_editor_assets' ] );
      }
   }

   /**
    * Check if WPBakery Page Builder is active.
    */
   private static function is_wpbakery_active() : bool {
      return defined( 'WPB_VC_VERSION' ) || class_exists( 'Vc_Manager' );
   }

   /**
    * Check if Elementor is active.
    */
   private static function is_elementor_active() : bool {
      return defined( 'ELEMENTOR_VERSION' ) || class_exists( '\Elementor\Plugin' );
   }

   /**
    * Check if Gutenberg is active.
    */
   private static function is_gutenberg_active() : bool {
      return function_exists( 'register_block_type' );
   }

   /**
    * WPBakery Integration
    *
    * @return void
    */
   public static function integrate_wpbakery() : void {

      if ( ! function_exists( 'vc_map' ) ) {
         return;
      }

      \vc_map( array(
         "name"        => __( "YMC Filter", "ymc-smart-filter" ),
         "description" => __( "Adds a YMC Filter to your page.", "ymc-smart-filter" ),
         "base"        => "ymc_filter",
         "class"       => "",
         "controls"    => "full",
         "icon"        => "ymc-custom-vc-icon", 
         "category"    => __( "Content", "ymc-smart-filter" ),
         "params"      => array(
               array(
                  "type"        => "dropdown",
                  "heading"     => __( "Select Filter", "ymc-smart-filter" ),
                  "param_name"  => "id",
                  "value"       => self::get_filters_dropdown_options(),
                  "description" => __( "Select a filter from the list of available filters.", "ymc-smart-filter" ),
                  "admin_label" => true, 
               )
            )
         )      
      );
   }


   /**
    * Elementor Integration
    *
    * @param \Elementor\Widgets_Manager $widgets_manager
    * @return void
    */
   public static function integrate_elementor( $widgets_manager ) : void { 
      
      $widgets_manager->register(new Elementor_Widget());   
   } 


   /**
    * Enqueue custom styles for WPBakery Builders interface
    */
   public static function enqueue_builder_styles() : void {
      
      $icon_url = plugins_url( 'assets/images/icon-builder.svg', dirname( __DIR__, 2 ) . '/ymc-smart-filter.php' );

      $custom_css = "
         .ymc-custom-vc-icon {
            background-color: #098ab8 !important;
            background-image: url('{$icon_url}') !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            background-size: 28px !important;
            border-radius: 4px;
         }     
         .ymc-custom-vc-icon::before {
            content: '' !important;
            display: none !important;
         }
      ";

      wp_register_style( 'ymc-builders-inline-css', false );
      wp_enqueue_style( 'ymc-builders-inline-css' );
      wp_add_inline_style( 'ymc-builders-inline-css', $custom_css );
   }


   /**
    * Gets a list of all created filters for a WPBakery dropdown.
    *
    * @return array
    */
   private static function get_filters_dropdown_options() : array {
      
      $options = array(__( "Select a filter...", "ymc-smart-filter" ) => "");

      $filters = get_posts( array(
         'post_type'      => 'ymc_filters', 
         'posts_per_page' => -1,           
         'post_status'    => 'publish',    
         'orderby'        => 'title',      
         'order'          => 'ASC',
      ) );

      if ( ! empty( $filters ) ) {
         foreach ( $filters as $filter ) {           
            $label = sprintf( '%s (ID: %d)', $filter->post_title, $filter->ID );
            $options[$label] = (string) $filter->ID;
         }
      }

      return $options;
   }


   /**
    * Force loading front-end scripts and filter styles into the Elementor editor
    */
   public static function enqueue_elementor_editor_assets() : void {
      wp_enqueue_style( 'ymc_style' ); 
      wp_enqueue_style( 'query_ui' ); 
      wp_enqueue_style( 'ymc_flatpickr' ); 

      wp_enqueue_script( 'ymc_script' );      
      wp_enqueue_script( 'ymc_flatpickr' );      
      wp_enqueue_script( 'ymc_flatpickr_confirm_date' );      
      wp_enqueue_script( 'ymc_masonry' ); 
      wp_enqueue_script( 'jquery-ui-datepicker' ); 
   }
 

   /**
    * Enqueue custom icon styles for Elementor editor panel
    */
   public static function enqueue_elementor_icon_styles() : void {
     
      $icon_url = YMC_PLUGIN_URL . 'assets/images/icon-builder.svg';

      $custom_css = "
         i.ymc-icon-filter::before {
            content: '' !important;
            display: inline-block !important;
            width: 1em !important;
            height: 1em !important;
            background: url('{$icon_url}') center / contain no-repeat !important;
         }
      ";

      wp_register_style( 'ymc-elementor-icon-css', false );
      wp_enqueue_style( 'ymc-elementor-icon-css' );
      wp_add_inline_style( 'ymc-elementor-icon-css', $custom_css );
   }

  
   /**
    * Gutenberg Integration
    * Registers the block and its editor script.
    *
    * @return void
    */
   public static function integrate_gutenberg() : void {
     
      wp_register_script(
         'ymc-gutenberg-block-js',
         YMC_PLUGIN_URL . 'assets/js/admin/gutenberg/gutenberg-block.js', 
         [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-server-side-render' ],         
         YMC_VERSION
      );
      
      wp_localize_script(
         'ymc-gutenberg-block-js',
         'ymcBlockData',
         [ 
            'filters' => self::get_filters_dropdown_options_gutenberg(),
            'iconUrl' => YMC_PLUGIN_URL . 'assets/images/icon-builder.svg'
         ]
      );
      
      register_block_type( 'ymc-filter-grids/filter', [
         'editor_script'   => 'ymc-gutenberg-block-js',
         'attributes'      => [
            'filterId' => [
               'type'    => 'string',
               'default' => '',
            ],
         ],         
         'render_callback' => [ __CLASS__, 'render_gutenberg_block' ],
      ] );
   }

   /**
    * Render callback for the Gutenberg block
    *
    * @param array $attributes
    * @return string
    */
   public static function render_gutenberg_block( array $attributes ) : string {
      $filter_id = ! empty( $attributes['filterId'] ) ? (int) $attributes['filterId'] : 0;
      
      if ( $filter_id <= 0 ) {
         if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
            return '<div class="ymc-container" style="padding: 20px; border: 1px dashed #ccc; text-align: center;">' . 
                   '<div class="notification notification--warning">' . esc_html__( 'Please select a YMC Filter in the block settings.', 'ymc-smart-filter' ) . '</div>' .
                   '</div>';
         }
         return '';
      }
      
      if ( class_exists( '\YMCFilterGrids\frontend\FG_Shortcodes' ) ) {
         return Shortcodes::apply_grid_filters( [ 'id' => $filter_id ] );
      }

      // Fallback
      return do_shortcode( '[ymc_filter id="' . esc_attr( $filter_id ) . '"]' );
   }


   /**
    * Gets a list of all created filters for a Gutenberg SelectControl.   
    *
    * @return array
    */
   private static function get_filters_dropdown_options_gutenberg() : array {

      $options = [
         [ 'label' => __( 'Select a filter...', 'ymc-smart-filter' ), 'value' => '' ]
      ];

      $filters = get_posts( [
         'post_type'      => 'ymc_filters',
         'posts_per_page' => -1,         
         'post_status'    => 'publish',    
         'orderby'        => 'title',      
         'order'          => 'ASC',
      ] );

      if ( ! empty( $filters ) ) {
         foreach ( $filters as $filter ) {           
            $options[] = [
               'label' => sprintf( '%s (ID: %d)', $filter->post_title, $filter->ID ),
               'value' => (string) $filter->ID
            ];
         }
      }

      return $options;
   }


   /**
    * Force loading front-end scripts and filter styles into the Gutenberg editor
    */
   public static function enqueue_gutenberg_editor_assets() : void {
     
      if ( class_exists( '\YMCFilterGrids\frontend\FG_Frontend_Scripts' ) ) {
         Frontend_Scripts::register_styles();
         Frontend_Scripts::register_scripts();
      }
      
      wp_enqueue_style( 'ymc_style' ); 
      wp_enqueue_style( 'query_ui' ); 
      wp_enqueue_style( 'ymc_flatpickr' );       
      wp_enqueue_script( 'ymc_script' ); 
      wp_enqueue_script( 'ymc_masonry' ); 
   }
   
}


