<?php declare( strict_types = 1 );

namespace YMCFilterGrids\admin;

use YMCFilterGrids\admin\FG_Taxonomy as Taxonomy;
use YMCFilterGrids\FG_Data_Store as Data_Store;

defined( 'ABSPATH' ) || exit;

/**
 * FG_Term Class
 * Get terms and data attributes
 *
 * @since 3.0.0
 */

class FG_Term {

   use FG_Term_Trait;

	/**
	 * Output HTML term item.
	 *
	 * @param int $post_id
	 * @param array $post_types
	 *
	 * @return string
	 */
	public static function output_html(int $post_id, array $post_types) : string {

		$all_tax             = Taxonomy::get_taxonomies($post_types);
		$selected_tax        = (array) Data_Store::get_meta_value($post_id, 'ymc_fg_taxonomies');
		$selected_terms      = (array) Data_Store::get_meta_value($post_id, 'ymc_fg_terms');
		$term_attrs          = (array) Data_Store::get_meta_value($post_id, 'ymc_fg_term_attrs');
		$term_sort           = (array) Data_Store::get_meta_value($post_id, 'ymc_fg_term_sort');
		$tax_attrs           = (array) Data_Store::get_meta_value($post_id, 'ymc_fg_tax_attrs');
		$term_sort_direction = (string) Data_Store::get_meta_value($post_id, 'ymc_fg_term_sort_direction');
      $display_mode        = Data_Store::get_meta_value($post_id, 'ymc_fg_term_display_mode') ?: 'flat';

		ob_start();

		if($all_tax) {

			foreach( $all_tax as $tax_name => $label ) {

				if( in_array($tax_name, $selected_tax, true) ) {
              
					$is_tree_allowed = ($display_mode === 'tree' && is_taxonomy_hierarchical($tax_name));

					if ( $is_tree_allowed ) {
                  // phpcs:ignore WordPress					
						echo FG_Term_Tree::output_taxonomy_tree($tax_name, $label, $post_types, $selected_terms, $term_attrs, $term_sort, $tax_attrs, $term_sort_direction);
						continue;
					}

					$terms = self::get_terms($tax_name);
					$checked = (self::checked_selected_terms($terms, $selected_terms)) ? 'checked' : '';

					self::set_taxonomy_label($tax_name, $tax_attrs);
					self::$tax_label = (self::$tax_label) ? : $label;

					echo '<article class="cel js-tax-'. esc_attr($tax_name) .'">';
					echo '<div class="tax-name">
                          <input class="checkbox-control js-select-all-terms" id="all-terms-'. esc_attr($tax_name) .'" '. esc_attr($checked) .' type="checkbox">
                          <label class="field-label" for="all-terms-'. esc_attr($tax_name) .'">'. esc_html(self::$tax_label) .'</label></div>';
					echo '<div class="terms-inner js-term-sortable">';

					self::$tax_label = '';
					self::determine_term_sorting($term_sort_direction, $term_sort,$terms);

					if($terms) {
                                    
                  $cache_key = self::get_cache_key($tax_name, $post_types);

                  if (get_transient($cache_key) === false) {
                     foreach ($terms as $term_id => $name) {
                        self::update_post_counts($tax_name, $term_id);
                     }
                     set_transient($cache_key, true, 60 * MINUTE_IN_SECONDS);                     
                  }

						foreach ($terms as $term_id => $name) {							
							self::set_data_attributes($term_id, $term_attrs);
							self::$term_name = (self::$term_name) ? : $name;

							$post_count = self::get_post_counts($post_types, $term_id);
							$is_term_sel = (in_array($term_id, $selected_terms)) ? 'checked' : '';
							$class_status = (self::$term_status) ? ' '. self::$term_status : '';
							$term_slug = get_term( $term_id )->slug;

							echo '<div class="term-item'. esc_attr($class_status) .'" 
							data-term-id="'. esc_attr($term_id) .'" 
							data-term-taxonomy="'. esc_attr($tax_name) .'" 
							data-term-slug="'. esc_attr($term_slug) .'" 
							data-term-name-orig="'. esc_attr($name) .'"
							data-term-name="'. esc_attr(self::$term_name) .'"
							data-term-background="'. esc_attr(self::$term_background) .'"
							data-term-color="'. esc_attr(self::$term_color) .'"
							data-term-class="'. esc_attr(self::$term_class) .'"
							data-term-default="'. esc_attr(self::$term_default) .'"
							data-term-visible="'. esc_attr(self::$term_visible) .'"							
							data-term-checked="'. esc_attr(self::$term_checked) .'"
							data-term-status="'. esc_attr(self::$term_status).'"							
							data-term-icon-class="'. esc_attr(self::$icon_class) .'" 
							data-term-icon-alignment="'. esc_attr(self::$icon_alignment) .'" 
							data-term-icon-color="'. esc_attr(self::$icon_color) .'"
							data-term-icon-url="'. esc_attr(self::$icon_url) .'"
							data-term-related-terms="'. esc_attr(self::$related_terms) .'">					  
							<i class="fa-solid fa-up-down-left-right icon-is-drag js-term-handle"></i>
							<input class="checkbox-control" id="term-id-'. esc_attr($term_id) .'" name="ymc_fg_terms[]" type="checkbox" '. esc_attr($is_term_sel) .' value="'. esc_attr($term_id) .'">					  
							<label class="field-label" for="term-id-'. esc_attr($term_id) .'">'. esc_html(self::$term_name) .' <span class="count">('. esc_html($post_count) .')</span></label>
							<i class="fa-solid fa-ellipsis-vertical icon-is-settings js-term-settings"></i>
							<span class="icon-term js-icon-term"></span></div>';

							self::clear_data_attributes();
						}
					} 
               else {
						echo '<div class="no-terms">
					         <div class="notification notification--warning">'. esc_html__('No terms found.', 'ymc-smart-filter') .'</div></div>';
					}
					echo '</div></article>';
					
				}
			}

		} else {
			echo '<div class="no-terms">
			      <div class="notification notification--warning">'. esc_html__('No terms found.', 'ymc-smart-filter') .'</div></div>';
		}

		return ob_get_clean();
	}
   

}