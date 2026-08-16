<?php declare( strict_types = 1 );

namespace YMCFilterGrids\frontend;

use YMCFilterGrids\abstracts\FG_Abstract_Filter_Impl;
use YMCFilterGrids\FG_Data_Store as Data_Store;
use YMCFilterGrids\interfaces\IFilter;

/**
 * Class FG_Filter_Flatpickr
 *
 * @since 3.10.3
 */

class FG_Filter_Flatpickr extends FG_Abstract_Filter_Impl implements IFilter {

	public function render(	int $filter_id, array $tax_name,	array $filter_options) : string {

		$placement = $filter_options['placement'] ?? 'left';		

		/**
		 * Flatpickr settings
		 */
		$flatpickr_settings = Data_Store::get_meta_value($filter_id, 'ymc_fg_flatpickr_settings') ?: [];

		/**
		 * Data source settings
		 */
		$fp_query_source = sanitize_key($flatpickr_settings['query_source'] ?? 'post_date');

		$fp_meta_key = sanitize_text_field($flatpickr_settings['meta_key'] ?? '');

		/**
		 * Flatpickr mode
		 *
		 * single | range
		 */
		$fp_mode = sanitize_key($flatpickr_settings['mode'] ?? 'single');


      /**
      * Flatpickr picker type
      *
      * date | datetime
      */
      $fp_picker_type = sanitize_key($flatpickr_settings['picker_type'] ?? 'date');

		/**
		 * Date format
		 */
		$fp_format = sanitize_text_field($flatpickr_settings['format'] ?? '');

      if ( empty( $fp_format ) ) {
          $fp_format = 'd.m.Y';
      }

      if ( $fp_picker_type === 'datetime' ) {
         $fp_format .= ' H:i';
      }


      /**
       * Flatpickr custom initialization
       */
      $fp_custom_init = sanitize_text_field($flatpickr_settings['custom_init'] ?? 'false');


      /**
       * Flatpickr theme
       */
      $fp_theme = sanitize_text_field($flatpickr_settings['theme'] ?? 'default');


		/**
		 * Placeholder
		 */
		$placeholder = sanitize_text_field($flatpickr_settings['placeholder'] ?? '');

		if ( empty( $placeholder ) ) {

         if ( $fp_picker_type === 'datetime' ) {

            $placeholder =
               $fp_mode === 'range'
                  ? __( 'Select date & time range...', 'ymc-smart-filter' )
                  : __( 'Select date & time...', 'ymc-smart-filter' );

         } else {

            $placeholder =
               $fp_mode === 'range'
                  ? __( 'Select date range...', 'ymc-smart-filter' )
                  : __( 'Select date...', 'ymc-smart-filter' );
         }
      }

		/**
		 * Flatpickr frontend config
		 */
		$js_options = [
			'mode'        => $fp_mode,
			'dateFormat'  => $fp_format,			
			'inline'      => (isset( $flatpickr_settings['inline'] )	&& $flatpickr_settings['inline'] === 'true'),
			'weekNumbers' => (isset( $flatpickr_settings['week_numbers'] )	&& $flatpickr_settings['week_numbers'] === 'true'),
			'locale'      => ['firstDayOfWeek' => (int) ($flatpickr_settings['first_day'] ?? 1),
			],
		];

      if ( $fp_picker_type === 'datetime' ) {
         $js_options['enableTime'] = true;
         $js_options['time_24hr']  = true;
         $js_options['confirmText'] = __( 'Apply', 'ymc-smart-filter' );
      }

      $allowed_themes = [
         'dark',
         'material_blue',
         'material_green',
         'material_red',
         'airbnb',
         'confetti'
      ];

      if ( $fp_theme !== 'default' && in_array( $fp_theme, $allowed_themes, true ) ) { 

         $theme_handle = 'ymc_flatpickr_' . $fp_theme;
               
         wp_enqueue_style( $theme_handle );         
      }

		ob_start();
		?>

		<div class="filter filter-flatpickr filter-flatpickr-<?php echo esc_attr( $placement ); ?> filter-<?php echo esc_attr( $filter_id ); ?>"
			data-filter-type="flatpickr"			
			data-query-source="<?php echo esc_attr( $fp_query_source ); ?>"
			data-meta-key="<?php echo esc_attr( $fp_meta_key ); ?>"
			data-picker-type="<?php echo esc_attr( $fp_picker_type ); ?>"
         data-custom-init="<?php echo esc_attr( $fp_custom_init ); ?>">

			<div class="filter-flatpickr-inner">

				<div class="flatpickr-wrapper">

					<input
						type="text"
						class="ymc-flatpickr-input js-ymc-flatpickr-input"
						placeholder="<?php echo esc_attr( $placeholder ); ?>"                  
						data-flatpickr-options='<?php echo esc_attr( wp_json_encode( $js_options ) ); ?>'                 
						readonly="readonly"/>

					<button
						type="button"
						class="flatpickr-clear js-flatpickr-clear is-hidden"
						aria-label="<?php esc_attr_e( 'Clear filter', 'ymc-smart-filter' ); ?>"
						title="<?php esc_attr_e( 'Clear filter', 'ymc-smart-filter' ); ?>">
						&times;
					</button>

				</div>

			</div>

		</div>

		<?php

		return ob_get_clean();
	}
}

