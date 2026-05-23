<?php

defined('ABSPATH') || exit;

use YMCFilterGrids\FG_Data_Store as Data_Store;


/**
 * Render field header
 * @param $label
 * @param $tooltip
 *
 * @return void
 */
if(! function_exists( 'ymc_render_field_header')) {
	function ymc_render_field_header($label, $tooltip) {
		$tooltip = preg_replace('/\s+/', ' ', trim($tooltip))
        ?>
		<header class="form-label">
			<span class="heading-text"><?php echo esc_html($label); ?></span>
			<button type="button" class="btn-tooltip js-btn-tooltip"
               data-tooltip-html="<?php echo esc_attr($tooltip); ?>"
               title="<?php echo esc_attr($tooltip); ?>">
			   <i class="fa-solid fa-question"></i>
			</button>
		</header>
		<?php
	}
}


/**
 * Render single popup
 *
 * @param $post_id
 */
if (! function_exists( 'ymc_render_single_popup')) {
	function ymc_render_single_popup($filter_id) {
		$settings = Data_Store::get_meta_value($filter_id, 'ymc_fg_popup_settings');
		if (empty($settings)) return;

		$width            = esc_attr($settings['width']['default'] ?? '600');
		$height           = esc_attr($settings['height']['default'] ?? '600');
      $width_unit       = esc_attr($settings['width']['unit'] ?? 'px');
      $height_unit      = esc_attr($settings['height']['unit'] ?? 'px');
      $transform_origin = esc_attr($settings['animation_origin'] ?? 'center center');
      $position         = esc_attr($settings['position'] ?? 'center center');
      $animation_type   = esc_attr($settings['animation_type'] ?? 'none');
      $background_overlay = !empty($settings['background_overlay']) ?
         esc_attr($settings['background_overlay']) : 'rgba(20, 21, 24, 0.6)';

      $css = '';
      $css .= 'width:'. $width . $width_unit . ';';
      $css .= 'height:' . $height . $height_unit .';';
      $css .= 'transform-origin:'. $transform_origin .';';

		$class_popup_position = '';

        if( $position === 'center_right' ) {
	        $class_popup_position = 'ymc-popup-right ymc-animation-' . $animation_type;
        }
        if( $position === 'center_left' ) {
	        $class_popup_position = 'ymc-popup-left ymc-animation-' . $animation_type;
        }
		if( $position === 'center' ) {
			$class_popup_position = 'ymc-animation-' . $animation_type;
		}

		echo '<div id="ymc-popup-' . esc_attr($filter_id) . '" class="ymc-popup ymc-popup-overlay js-ymc-popup-overlay" style="background-color:'. esc_attr($background_overlay) .'">';
		echo '<div class="ymc-popup__wrapper '. esc_attr($class_popup_position).' js-ymc-popup-wrapper" style="'. esc_attr($css).'">
                <button class="ymc-popup__close js-ymc-btn-popup-close">close</button>
                <div class="ymc-popup__container">                    
                    <div class="ymc-popup__body js-ymc-popup-body"></div>
                </div>';
		echo '</div>';
		echo '</div>';
	}
}


/**
 * Get column classes
 *
 * @param $columns
 */
if (! function_exists( 'ymc_get_column_classes')) {
	function ymc_get_column_classes( $columns ): string {
		$output = [];
		foreach ( $columns as $breakpoint => $count ) {
			if ( $count ) {
				$output[] = "ymc-cols-{$breakpoint}-{$count}";
			}
		}
		$output = array_reverse($output);

		return implode( ' ', $output );
	}
}


