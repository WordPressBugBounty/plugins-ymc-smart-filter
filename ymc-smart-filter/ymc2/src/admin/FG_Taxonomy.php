<?php declare( strict_types = 1 );

namespace YMCFilterGrids\admin;

use YMCFilterGrids\FG_Data_Store as Data_Store;

defined( 'ABSPATH' ) || exit;

/**
 * FG_Taxonomy Class
 * Get taxonomies and data attributes
 *
 * @since 3.0.0
 */

class FG_Taxonomy {

	/**
	 * @var string
	 */
	private static ?string $tax_background = '';

	/**
	 * @var string
	 */
	private static ?string $tax_color = '';

	/**
	 * @var string
	 */
	private static ?string $tax_label = '';


	/**
	 * @var string
	 */
	private static ?string $tax_status = '';


	/**
	 * Clear data attributes for taxonomy
	 *
	 * @return void
	 */
	private static function clear_data_attributes() : void {
		self::$tax_background = '';
		self::$tax_color      = '';
		self::$tax_label      = '';
		self::$tax_status     = '';
	}


	/**
	 * Set data attributes for taxonomy
	 * Override default values
	 *
	 * @param string $name
	 * @param array $tax_attrs
	 *
	 * @return void
	 */
   private static function set_data_attributes(string $name, array $tax_attrs = []) : void {
      if (empty($tax_attrs) || !is_array($tax_attrs)) {
         return;
      }

      foreach ($tax_attrs as $items) {

         if (!is_array($items)) {
               continue;
         }

         if (isset($items['name']) && (string)$items['name'] === $name) {

            self::$tax_background = isset($items['background']) && is_scalar($items['background'])
               ? (string)$items['background']
               : '';

            self::$tax_color = isset($items['color']) && is_scalar($items['color'])
               ? (string)$items['color']
               : '';

            self::$tax_label = isset($items['label']) && is_scalar($items['label'])
               ? (string)$items['label']
               : '';

            self::$tax_status = isset($items['status']) && is_scalar($items['status'])
               ? (string)$items['status']
               : '';

            break;
         }
      }
   }


	/**
	 * Sort taxonomies
	 *
	 * @param array $tax_sort
	 * @param array $all_tax
	 *
	 * @return void
	 */
	private static function sort_taxonomies(array $tax_sort, array &$all_tax) : void {
		if ( is_array($tax_sort) && $tax_sort ) {
			$temp_array = [];
			foreach ($tax_sort as $slug) {
				if (isset($all_tax[$slug])) {
					$temp_array[$slug] = $all_tax[$slug];
				}
			}
			foreach ($all_tax as $slug => $label) {
				if (!isset($temp_array[$slug])) {
					$temp_array[$slug] = $label;
				}
			}
			$all_tax = $temp_array;
		}
	}


	/**
	 * Output HTML term item.
	 *
	 * @param int $post_id
	 * @param array $post_types
	 *
	 * @return string
	 */
	public static function output_html(int $post_id, array $post_types) : string {

		$all_tax      = self::get_taxonomies($post_types);
		$selected_tax = Data_Store::get_meta_value($post_id, 'ymc_fg_taxonomies');
		$tax_attrs    = Data_Store::get_meta_value($post_id, 'ymc_fg_tax_attrs');
		$tax_sort     = Data_Store::get_meta_value($post_id, 'ymc_fg_tax_sort');
		$all_buttons  = Data_Store::get_meta_value($post_id, 'ymc_fg_filter_all_button');
      $global_filter_type = Data_Store::get_meta_value( $post_id, 'ymc_fg_filter_type' );
      $composite_options  = Data_Store::get_meta_value( $post_id, 'ymc_fg_filter_options' );

      $tax_attrs    = is_array($tax_attrs) ? $tax_attrs : [];
      $tax_sort     = is_array($tax_sort) ? $tax_sort : [];
		
      $selected_tax = is_array($selected_tax) ? $selected_tax : [];

		self::sort_taxonomies($tax_sort, $all_tax);
      
      $all_button_defaults = [
         'all_label'  => 'All',
         'is_visible' => 'yes'
      ];

		ob_start();

		if($all_tax) {

			echo '<div class="taxonomies-list js-tax-insert js-tax-sortable">';

			foreach($all_tax as $name => $label) {

				self::set_data_attributes($name, $tax_attrs);

				$is_tax_sel = (in_array($name, $selected_tax)) ? 'checked' : '';

            $display_label = self::$tax_label !== '' ? self::$tax_label : $label;
            $class_status  = self::$tax_status !== '' ? ' ' . self::$tax_status : '';
            
            $current_tax_options = (isset($all_buttons[$name]) && is_array($all_buttons[$name])) ? $all_buttons[$name] : [];
            $js_all_button_options = wp_parse_args($current_tax_options, $all_button_defaults);

            $current_tax_filter_type = self::get_taxonomy_filter_type( $name, $global_filter_type, $composite_options );

				echo '<div class="taxonomies-list__item'. esc_attr($class_status).'"
					   data-tax-original-name="'. esc_attr($label) .'"                  
					   data-tax-name="'. esc_attr($name) .'"
					   data-tax-label="'. esc_attr($display_label) .'"
					   data-tax-color="'. esc_attr(self::$tax_color) .'"
					   data-tax-bg="'. esc_attr(self::$tax_background) .'"
					   data-tax-status="'. esc_attr(self::$tax_status) .'"
                  data-all-button-options="'. esc_attr(wp_json_encode($js_all_button_options)) .'"
                  data-filter-type="'. esc_attr( $current_tax_filter_type ) .'">
                  <i class="fa-solid fa-up-down-left-right icon-is-drag js-tax-handle"></i>
					   <input class="form-checkbox js-tax-checkbox" id="'. esc_attr($name) .'" data-label="'. esc_attr($label) .'" type="checkbox" name="ymc_fg_taxonomies[]" '. esc_attr($is_tax_sel) .' value="'.esc_attr($name).'">
					   <label class="field-label" for="'. esc_attr($name) .'">'. esc_html($display_label) .'</label>
					   <i class="fa-solid fa-ellipsis-vertical icon-is-settings js-tax-settings"></i>
                  </div>';

				self::clear_data_attributes();
			}
			echo '</div>';

		} 
      else {
			echo '<div class="taxonomies-list js-tax-insert js-tax-sortable">					 
					 <div class="notification notification--warning">'. esc_html__('No taxonomies found.', 'ymc-smart-filter') .'</div>
				  </div>';
		}

		return ob_get_clean();
	}


	/**
	 * Get all taxonomies
	 *
	 * @param array $post_types
	 *
	 * Key is slug and value is label of taxonomy
	 * @return array
	 */
	public static function get_taxonomies(array $post_types = []) : array {
		$result = [];

		if (empty($post_types)) {
         return $result;
      }

		$taxonomies = get_object_taxonomies($post_types, 'objects');
		if(!empty($taxonomies)) {
			foreach ($taxonomies as $tax) {
				$result[$tax->name] = $tax->label;
			}
		}
		asort($result);
		return $result;
	}


   /**
    * Gets the filter type for a given taxonomy.
    *
    * @param string $tax_slug
    * @param string $global_type
    * @param array|string $composite_options
    * @return string
    */
   public static function get_taxonomy_filter_type( $tax_slug, $global_type, $composite_options ) : string {
      
      if ( 'composite' !== $global_type ) {
         return ! empty( $global_type ) ? $global_type : 'default';
      }
      
      if ( is_array( $composite_options ) ) {
         foreach ( $composite_options as $option ) {            
            if ( ! empty( $option['tax_name'] ) && is_array( $option['tax_name'] ) ) {              
               if ( in_array( $tax_slug, $option['tax_name'], true ) ) {
                  return ! empty( $option['filter_type'] ) ? $option['filter_type'] : 'default';
               }
            }
         }
      }
      
      return 'default';
   }

}


