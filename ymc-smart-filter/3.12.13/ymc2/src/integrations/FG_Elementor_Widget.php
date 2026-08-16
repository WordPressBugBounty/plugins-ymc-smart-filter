<?php declare(strict_types=1);

namespace YMCFilterGrids\integrations;

use YMCFilterGrids\frontend\FG_Shortcodes as Shortcodes;

defined( 'ABSPATH' ) || exit;




/**
 * Class FG_Elementor_Widget
 * Elementor Widget for YMC Filter
 * 
 * @package YMCFilterGrids\integrations
 * @since   3.12.0
 */
class FG_Elementor_Widget extends \Elementor\Widget_Base {
   
   public function get_name() {
      return 'ymc_filter_widget';
   }

  
   public function get_title() {
      return __( 'YMC Filter', 'ymc-smart-filter' );
   }

   
   public function get_icon() {
      return 'ymc-icon-filter';
   }

  
   public function get_categories() {
      return [ 'general' ]; 
   }

  
   protected function register_controls() {
      
      $this->start_controls_section(
         'content_section',
         [
            'label' => __( 'Filter Settings', 'ymc-smart-filter' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
         ]
      );

      $this->add_control(
         'filter_id',
         [
            'label'       => __( 'Select Filter', 'ymc-smart-filter' ),
            'type'        => \Elementor\Controls_Manager::SELECT,
            'description' => __( 'Select a filter from the list of available filters.', 'ymc-smart-filter' ),
            'options'     => $this->get_filters_dropdown_options(),
            'default'     => '',
         ]
      );

      $this->end_controls_section();
   } 


   protected function render() {

      $settings = $this->get_settings_for_display();      
     
      $filter_id = !empty( $settings['filter_id'] ) ? (int) $settings['filter_id'] : 0;

      if ( $filter_id > 0 ) {
        
         if ( class_exists( '\YMCFilterGrids\frontend\FG_Shortcodes' ) ) {              
            // phpcs:ignore WordPress
            echo Shortcodes::apply_grid_filters( [ 'id' => $filter_id ] );               
         } 
         else {  
            // phpcs:ignore WordPress             
            echo do_shortcode( '[ymc_filter id="' . esc_attr( $filter_id ) . '"]' );
         }
      } 
      else {
         // phpcs:ignore WordPress
         echo '<div class="ymc-container"><div class="notification notification--warning">' . esc_html__( 'Please select a filter in settings.', 'ymc-smart-filter' ) . '</div></div>';
      }
   }

  
   private function get_filters_dropdown_options() : array {

      $options = [ '' => __( 'Select a filter...', 'ymc-smart-filter' ) ];

      $filters = get_posts( [
         'post_type'      => 'ymc_filters', 
         'posts_per_page' => -1,           
         'post_status'    => 'publish',    
         'orderby'        => 'title',      
         'order'          => 'ASC',
      ] );

      if ( ! empty( $filters ) ) {
         foreach ( $filters as $filter ) {
            $label = sprintf( '%s (ID: %d)', $filter->post_title, $filter->ID );
            $options[ (string) $filter->ID ] = $label;
         }
      }

      return $options;
   }

}


