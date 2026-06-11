<?php declare( strict_types = 1 );

namespace YMCFilterGrids\admin;

defined( 'ABSPATH' ) || exit;



/**
 * FG_Metadata Class
 * Save meta data
 *
 * @since 3.0.0
 */
class FG_Save_Meta_Boxes {

	/**
	 * Hook in methods.
	 */
	public static function init() : void {
		
		add_action( 'save_post_ymc_filters', array(__CLASS__, 'save_meta_boxes'), 10, 2);
		
		add_action( 'save_post', [ __CLASS__, 'handle_save_post' ], 10, 2 );
	}


	/**
	 * @param int $post_id
	 * @param object $post
	 *
	 * @return void
	 */
	public static function save_meta_boxes(int $post_id, object $post) : void {

		if (! isset($_POST['ymc_admin_data_nonce']) ||
		     ! check_admin_referer('ymc_admin_data_save', 'ymc_admin_data_nonce')) return;

		if (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) return;

		if (! current_user_can('edit_page', $post_id)) {
			wp_die( esc_html__('You do not have permission to edit post.', 'ymc-smart-filter'));
		}

      self::save_text_fields($post_id);

      self::save_array_fields($post_id);

      self::save_recursive_fields($post_id);

      self::save_numeric_fields($post_id);

      self::save_special_fields($post_id);

	}

	public static function handle_save_post( int $post_id, object $post ) : void {
		
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( 'ymc_filters' === $post->post_type ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		$post_types = get_post_types( [ 'public' => true ], 'names' );
		$post_types = apply_filters( 'ymc_fg_usage_post_types', $post_types );

		if ( ! in_array( $post->post_type, $post_types, true ) ) {
			return;
		}
	
		$old_filters = get_post_meta( $post_id, 'ymc_fg_filter_usage', true );
		$old_filters = is_array( $old_filters )
			? array_map( 'absint', $old_filters )
			: [];
		
		$new_filters = ymc_extract_filter_ids_from_content( $post->post_content );
		$new_filters = is_array( $new_filters )
			? array_values( array_unique( array_map( 'absint', $new_filters ) ) )
			: [];
		
		if ( empty( $new_filters ) ) {
			delete_post_meta( $post_id, 'ymc_fg_filter_usage' );
		} else {
			update_post_meta( $post_id, 'ymc_fg_filter_usage', $new_filters );
		}
	
		$filters_to_clear = array_unique(
			array_merge( $old_filters, $new_filters )
		);

		foreach ( $filters_to_clear as $filter_id ) {
			delete_transient( 'ymc_fg_usage_summary_' . absint( $filter_id ) );
		}
	}

   /**
    * Save text fields
    */
   private static function save_text_fields(int $post_id) : void {

      $text_fields = [
         'ymc_fg_tax_relation'       => 'AND',
         'ymc_fg_filter_hidden'      => 'no',
         'ymc_fg_pagination_type'    => 'numeric',
         'ymc_fg_post_layout'        => 'layout_standard',
         'ymc_fg_search_enable'      => 'no',
         'ymc_fg_popup_enable'       => 'no',
         'ymc_fg_excluded_posts'     => 'no',
         'ymc_fg_filter_type'        => 'default',
         'ymc_fg_selection_mode'     => 'single',
         'ymc_fg_display_terms_mode' => 'selected_terms',
         'ymc_fg_term_sort_direction' => 'ASC',
         'ymc_fg_term_sort_field'    => 'name',
         'ymc_fg_pagination_hidden'  => 'no',
         'ymc_fg_pagination_number_format' => 'decimal',
         'ymc_fg_prev_button_text'   => 'Prev',
         'ymc_fg_next_button_text'   => 'Next',
         'ymc_fg_load_more_text'     => 'Load More',
         'ymc_fg_post_image_size'    => 'medium',
         'ymc_fg_image_clickable'    => 'no',
         'ymc_fg_truncate_post_excerpt' => 'excerpt_truncated_text',
         'ymc_fg_post_button_text'   => 'Read More',
         'ymc_fg_target_option'      => '_self',
         'ymc_fg_filtered_posts_label' => 'Filtered posts',
         'ymc_fg_post_order'         => 'ASC',
         'ymc_fg_post_order_by'      => 'title',
         'ymc_fg_no_results_message' => 'No results found',
         'ymc_fg_post_animation_effect' => '',
         'ymc_fg_search_mode'        => 'global',
         'ymc_fg_submit_button_text' => 'Search',
         'ymc_fg_search_placeholder' => '',
         'ymc_fg_autocomplete_enabled' => 'no',
         'ymc_fg_results_found_text' => '',
         'ymc_fg_exact_phrase'       => 'no',
         'ymc_fg_search_meta_fields' => 'no',
         'ymc_fg_enable_advanced_query' => 'no',
         'ymc_fg_advanced_query_type' => 'advanced',
         'ymc_fg_query_allowed_callback' => '',
         'ymc_fg_advanced_query'     => '',
         'ymc_fg_advanced_suppress_filters' => 'no',
         'ymc_fg_enable_sort_posts' => 'no',
         'ymc_fg_sort_dropdown_label' => '',
         'ymc_fg_custom_container_class' => '',
         'ymc_fg_extra_filter_type' => '',
         'ymc_fg_extra_taxonomy' => '',
         'ymc_fg_scroll_to_filters_on_load' => 'no',
         'ymc_fg_debug_mode' => 'no',
         'ymc_fg_show_hidden_cpt' => 'no',
         'ymc_fg_show_post_count' => 'no',
         'ymc_fg_per_page' => '4'
      ];

      foreach ($text_fields as $meta_key => $default) {

         $value = isset($_POST[$meta_key])
            ? sanitize_text_field(wp_unslash($_POST[$meta_key]))
            : $default;

         update_post_meta($post_id, $meta_key, $value);
      }

   }

   /**
    * Save array fields
    */
   private static function save_array_fields(int $post_id) : void {

      $array_text_fields = [
         'ymc_fg_post_types'      => ['post'],
         'ymc_fg_taxonomies'      => [],
         'ymc_fg_terms'           => [],
         'ymc_fg_selected_posts'  => [],
         'ymc_fg_post_status'     => ['publish']
      ];

      foreach ($array_text_fields as $meta_key => $default) {

         $value = isset($_POST[$meta_key])
            ? array_map('sanitize_text_field',  wp_unslash($_POST[$meta_key])) 
            : $default;

         update_post_meta($post_id, $meta_key, $value);
      }

   }

   /**
    * Save recursive fields (arrays with nested arrays)
    */
   private static function save_recursive_fields(int $post_id) : void {

      $recursive_fields = [
         'ymc_fg_post_columns_layout' => [],
         'ymc_fg_post_grid_gap'       => [],
         'ymc_fg_popup_settings'      => [],
         'ymc_fg_flatpickr_settings'  => [],
         'ymc_fg_carousel_settings'   => [],         
         'ymc_fg_order_meta_key'      => '',
         'ymc_fg_order_meta_value'    => '',
         'ymc_fg_post_order_by_multiple' => [],
         'ymc_fg_post_display_settings' => [],
         'ymc_fg_filter_typography'  => [],
         'ymc_fg_post_typography'    => [],
         'ymc_fg_post_sortable_fields' => [],
         'ymc_fg_preloader_settings' => [],
         'ymc_fg_filter_dependent_settings' => [],
         'ymc_fg_custom_layout_builder' => []
      ];

      foreach ($recursive_fields as $meta_key => $default) {

         $value = isset($_POST[$meta_key])
            ? ymc_sanitize_array_recursive(wp_unslash($_POST[$meta_key]))
            : $default;

         update_post_meta($post_id, $meta_key, $value);
      }

   }

   /**
    * Save numeric fields
    */
   private static function save_numeric_fields(int $post_id) : void {

      $numeric_fields = [         
         'ymc_fg_post_excerpt_length'          => 30,
         'ymc_fg_max_autocomplete_suggestions' => 10,
         'ymc_fg_pagination_mid_size'          => 2,
         'ymc_fg_pagination_end_size'          => 1,
         'ymc_fg_post_custom_read_time'        => 200
      ];

      foreach ($numeric_fields as $meta_key => $default) {

         $value = isset($_POST[$meta_key])
            ? absint($_POST[$meta_key])
            : $default;

         update_post_meta($post_id, $meta_key, $value);
      }

   }

   /**
    * Save special fields
    */
   private static function save_special_fields(int $post_id) : void {

      // Filter Options
      if(isset($_POST['ymc_fg_filter_type'])) {
         $filter_options = $_POST['ymc_fg_filter_options'] ?? [];
         $data_filter_options = ymc_build_filter_options_from_post($post_id, $_POST['ymc_fg_filter_type'], $filter_options);
         update_post_meta($post_id, 'ymc_fg_filter_options', $data_filter_options);
      }

      // Performance & Behavior Settings
		$filter_dropdown_setting = isset($_POST['ymc_fg_filter_dropdown_setting'])
			? ymc_sanitize_array_recursive(wp_unslash($_POST['ymc_fg_filter_dropdown_setting'])) : [];
		if (empty($filter_dropdown_setting['threshold'])) {
			$filter_dropdown_setting['threshold'] = 40;
		}
		update_post_meta($post_id, 'ymc_fg_filter_dropdown_setting', $filter_dropdown_setting);

      // Grid Style & Enqueue script Masonry
		if(isset($_POST['ymc_fg_grid_style'])) {
			$grid_style = sanitize_text_field(wp_unslash($_POST['ymc_fg_grid_style']));
			update_post_meta($post_id, 'ymc_fg_grid_style', $grid_style);			
		}

      // Advanced: Custom CSS
		$custom_css = isset($_POST['ymc_fg_custom_css'])
			? wp_kses_post(wp_unslash($_POST['ymc_fg_custom_css'])) : '';
		update_post_meta($post_id, 'ymc_fg_custom_css', $custom_css);

      // Advanced: Custom Action
		$custom_js = isset($_POST['ymc_fg_custom_js'])
			? wp_kses_post(wp_unslash($_POST['ymc_fg_custom_js'])) : '';
		update_post_meta($post_id, 'ymc_fg_custom_js', $custom_js); 

   }

}


