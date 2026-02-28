<?php

namespace YMCFilterGrids\admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class FG_UiLabels
 * UI labels
 * @package YMCFilterGrids
 * @since 3.0.0
 */
class FG_UiLabels {

	/**
	 * UI labels
	 * @since 3.0.0
	 * @var array|array[]
	 */
	protected static array $labels = [];


   protected static function init_labels(): void
   {
      if (!empty(self::$labels)) {
         return;
      }

      self::$labels = [
         'filter_types' => [
            'default'      => __('Default', 'ymc-smart-filter'),
            'dropdown'     => __('Dropdown', 'ymc-smart-filter'),
            'range_slider' => __('Range Slider', 'ymc-smart-filter'),
            'date_picker'  => __('Date Picker', 'ymc-smart-filter'),
            'dependent'    => __('Dependent Filter', 'ymc-smart-filter'),
            'alphabetical' => __('Alphabetical Navigation', 'ymc-smart-filter'),
            'custom'       => __('Custom filter', 'ymc-smart-filter'),
            'composite'    => __('Combined filter', 'ymc-smart-filter')       
         ],
         'placements' => [
            'top'    => __('Top', 'ymc-smart-filter'),
            'left'   => __('Left', 'ymc-smart-filter'),
            'right'  => __('Right', 'ymc-smart-filter')
         ],
         'post_layouts' => [
            'layout_standard' => __('Standard post layout', 'ymc-smart-filter'),
            'layout_carousel' => __('Carousel post layout', 'ymc-smart-filter'),
            'layout_custom'   => __('Custom post layout', 'ymc-smart-filter')
         ],
         'display_terms_mode' => [
            'selected_terms'            => __('Selected terms', 'ymc-smart-filter'),
            'selected_terms_hide_empty' => __('Selected terms (Hide Empty)', 'ymc-smart-filter'),
            'all_terms'                 => __('All terms (Auto Populate)', 'ymc-smart-filter'),
            'all_terms_hide_empty'      => __('All terms (Hide Empty)', 'ymc-smart-filter')
         ],
         'term_sort_direction' => [
            'asc'    => __('Ascending (A → Z)', 'ymc-smart-filter'),
            'desc'   => __('Descending (Z → A)', 'ymc-smart-filter'),
            'manual' => __('Manual (Custom Order)', 'ymc-smart-filter')
         ],
         'term_sort_field' => [
            'name'           => __('Name', 'ymc-smart-filter'),
            'id'             => __('ID', 'ymc-smart-filter'),
            'count'          => __('Count', 'ymc-smart-filter'),
            'slug'           => __('Slug', 'ymc-smart-filter'),
            'description'    => __('Description', 'ymc-smart-filter'),
            'term_group'     => __('Term group', 'ymc-smart-filter'),
            'parent'         => __('Parent', 'ymc-smart-filter'),
            'include'        => __('Include', 'ymc-smart-filter'),
            'slug__in'       => __('Slug in', 'ymc-smart-filter'),
            'meta_value'     => __('Meta value', 'ymc-smart-filter'),
            'meta_value_num' => __('Meta value number', 'ymc-smart-filter'),
            'none'           => __('None', 'ymc-smart-filter')
         ],
         'pagination_type' => [
            'numeric'  => __('Numeric', 'ymc-smart-filter'),
            'loadmore' => __('Load more', 'ymc-smart-filter'),
            'infinite' => __('Infinite scroll', 'ymc-smart-filter'),
         ],
         'number_format' => [
            'decimal'              => __('Standard (1, 2, 3)', 'ymc-smart-filter'),
            'decimal_leading_zero' => __('Leading Zero (01, 02, 03)', 'ymc-smart-filter'),
            'upper_roman'          => __('Roman Numerals (I, II, III)', 'ymc-smart-filter'),
            'lower_alpha'          => __('Alphabet (a, b, c)', 'ymc-smart-filter')
         ],
         'post_image_size' => [
            'thumbnail' => __('Thumbnail', 'ymc-smart-filter'),
            'medium'    => __('Medium', 'ymc-smart-filter'),
            'large'     => __('Large', 'ymc-smart-filter'),
            'full'      => __('Full', 'ymc-smart-filter')
         ],
         'truncate_post_excerpt' => [
            'excerpt_truncated_text' => __('Truncated text', 'ymc-smart-filter'),
            'excerpt_first_block'    => __('The first block of content', 'ymc-smart-filter'),
            'excerpt_line_break'     => __('At the first line break', 'ymc-smart-filter')
         ],
         'target_option' => [
            '_self'  => __('Same tab', 'ymc-smart-filter'),
            '_blank' => __('New tab', 'ymc-smart-filter')
         ],
         'post_order' => [
            'ASC'  => __('Ascending', 'ymc-smart-filter'),
            'DESC' => __('Descending', 'ymc-smart-filter')
         ],
         'post_order_by' => [
            'title'      => __('Title', 'ymc-smart-filter'),
            'name'       => __('Name', 'ymc-smart-filter'),
            'date'       => __('Date', 'ymc-smart-filter'),
            'ID'         => __('ID', 'ymc-smart-filter'),
            'author'     => __('Author', 'ymc-smart-filter'),
            'modified'   => __('Modified', 'ymc-smart-filter'),
            'type'       => __('Type', 'ymc-smart-filter'),
            'parent'     => __('Parent', 'ymc-smart-filter'),
            'rand'       => __('Random', 'ymc-smart-filter'),
            'menu_order' => __('Menu order', 'ymc-smart-filter'),
            'meta_key'   => __('Meta key', 'ymc-smart-filter'),
            'multiple_fields' => __('Multiple sort', 'ymc-smart-filter')
         ],
         'post_status' => [
            'publish'    => __('Publish', 'ymc-smart-filter'),
            'pending'    => __('Pending', 'ymc-smart-filter'),
            'draft'      => __('Draft', 'ymc-smart-filter'),
            'future'     => __('Future', 'ymc-smart-filter'),
            'private'    => __('Private', 'ymc-smart-filter'),
            'inherit'    => __('Inherit', 'ymc-smart-filter'),
            'trash'      => __('Trash', 'ymc-smart-filter'),
            'any'        => __('Any', 'ymc-smart-filter'),
            'auto-draft' => __('Auto Draft', 'ymc-smart-filter')
         ],
         'post_animation_effect' => [
            ''                       => __('None', 'ymc-smart-filter'),
            'ymc-anim--bounce'       => __('Bounce', 'ymc-smart-filter'),
            'ymc-anim--bounce-in'    => __('Bounce in', 'ymc-smart-filter'),
            'ymc-anim--fade-in'      => __('Fade in', 'ymc-smart-filter'),
            'ymc-anim--fade-in-down' => __('Fade in down', 'ymc-smart-filter'),
            'ymc-anim--grow'         => __('Grow', 'ymc-smart-filter'),
            'ymc-anim--hit-here'     => __('Hit here', 'ymc-smart-filter'),
            'ymc-anim--swing'        => __('Swing', 'ymc-smart-filter'),
            'ymc-anim--shake'        => __('Shake', 'ymc-smart-filter'),
            'ymc-anim--wobble'       => __('Wobble', 'ymc-smart-filter'),
            'ymc-anim--zoom-in-out'  => __('Zoom in out', 'ymc-smart-filter')
         ],
         'post_display_settings' => [
            'author'    => ['label' => __('Author', 'ymc-smart-filter'),    'value' => 'show', 'tooltip' => __('Show or hide the post author', 'ymc-smart-filter')],
            'date'      => ['label' => __('Date', 'ymc-smart-filter'),      'value' => 'show', 'tooltip' => __('Show or hide the post date', 'ymc-smart-filter')],
            'read_time' => ['label' => __('Read time', 'ymc-smart-filter'), 'value' => 'show', 'tooltip' => __('Show or hide the post read time', 'ymc-smart-filter')],
            'category'  => ['label' => __('Categories', 'ymc-smart-filter'), 'value' => 'show', 'tooltip' => __('Show or hide the categories post', 'ymc-smart-filter')],
            'tags'      => ['label' => __('Tags', 'ymc-smart-filter'),      'value' => 'show', 'tooltip' => __('Show or hide the post tags', 'ymc-smart-filter')],
            'title'     => ['label' => __('Title', 'ymc-smart-filter'),     'value' => 'show', 'tooltip' => __('Show or hide the post title', 'ymc-smart-filter')],
            'image'     => ['label' => __('Image', 'ymc-smart-filter'),     'value' => 'show', 'tooltip' => __('Show or hide the post image', 'ymc-smart-filter')],
            'excerpt'   => ['label' => __('Excerpt', 'ymc-smart-filter'),   'value' => 'show', 'tooltip' => __('Show or hide the post excerpt', 'ymc-smart-filter')],
            'button'    => ['label' => __('Button', 'ymc-smart-filter'),    'value' => 'show', 'tooltip' => __('Show or hide the post button', 'ymc-smart-filter')],
            'views'     => ['label' => __('Views', 'ymc-smart-filter'),     'value' => 'show', 'tooltip' => __('Show or hide the post views', 'ymc-smart-filter')]
         ],
         'post_columns_layout' => [
            'xl'  => '≥ 1200px',
            'lg'  => '≥ 992px',
            'md'  => '≥ 768px',
            'sm'  => '≥ 576px',
            'xs'  => '< 576px',
            'xxs' => '< 400px'
         ],
         'popup_fields' => [
            'animation_type' => [
               'none'    => __('None', 'ymc-smart-filter'),
               'fade_in' => __('Fade In', 'ymc-smart-filter'),
               'rotate'  => __('Rotate', 'ymc-smart-filter'),
               'zoom_in' => __('Zoom In', 'ymc-smart-filter'),
               'slide'   => __('Slide', 'ymc-smart-filter'),
            ],
            'position' => [
               'center'       => __('Center', 'ymc-smart-filter'),
               'center_left'  => __('Center Left', 'ymc-smart-filter'),
               'center_right' => __('Center Right', 'ymc-smart-filter'),
            ],
            'animation_origin' => [
               'top'           => __('Top', 'ymc-smart-filter'),
               'left'          => __('Left', 'ymc-smart-filter'),
               'bottom'        => __('Bottom', 'ymc-smart-filter'),
               'right'         => __('Right', 'ymc-smart-filter'),
               'left top'      => __('Left Top', 'ymc-smart-filter'),
               'center top'    => __('Center Top', 'ymc-smart-filter'),
               'right top'     => __('Right Top', 'ymc-smart-filter'),
               'left center'   => __('Left Center', 'ymc-smart-filter'),
               'center center' => __('Center Center', 'ymc-smart-filter'),
               'right center'  => __('Right Center', 'ymc-smart-filter'),
               'left bottom'   => __('Left Bottom', 'ymc-smart-filter'),
               'center bottom' => __('Center Bottom', 'ymc-smart-filter'),
               'right bottom'  => __('Right Bottom', 'ymc-smart-filter')
            ],
            'width' => [
               'default' => 600,
               'unit'    => [ 'px' => 'px', '%' => '%', 'rem' => 'rem', 'em' => 'em', 'vw' => 'vw' ]
            ],
            'height' => [
               'default' => 600,
               'unit'    => [ 'px' => 'px', '%' => '%', 'rem' => 'rem', 'em' => 'em', 'vh' => 'vh' ]
            ],
            'background_overlay' => 'rgba(20, 21, 24, 0.6)'
         ],
         'search_mode' => [
            'global'   => __('Global search', 'ymc-smart-filter'),
            'filtered' => __('Search by filtered posts', 'ymc-smart-filter')
         ],
         'filter_typography' => [
            'font_family' => [
               'inherit'    => __('Inherit', 'ymc-smart-filter'),
               'OpenSans'   => 'OpenSans',
               'Montserrat' => 'Montserrat',
               'Poppins'    => 'Poppins',
               'Roboto'     => 'Roboto',
               'custom'     => __('Custom', 'ymc-smart-filter')
            ],
            'font_weight' => [
               '200' => __('Extra Light (200)', 'ymc-smart-filter'),
               '300' => __('Light (300)', 'ymc-smart-filter'),
               '400' => __('Normal (400)', 'ymc-smart-filter'),
               '500' => __('Medium (500)', 'ymc-smart-filter'),
               '600' => __('Semi Bold (600)', 'ymc-smart-filter'),
               '700' => __('Bold (700)', 'ymc-smart-filter'),
               '800' => __('Extra Bold (800)', 'ymc-smart-filter')
            ],
            'font_size_unit' => [ 'px' => 'px', 'em' => 'em', 'rem' => 'rem', '%' => '%' ],
            'text_transform' => [
               'none'       => 'none',
               'capitalize' => 'capitalize',
               'uppercase'  => 'uppercase',
               'lowercase'  => 'lowercase'
            ],
            'font_style' => [
               'normal' => __('Normal', 'ymc-smart-filter'),
               'italic' => __('Italic', 'ymc-smart-filter')
            ],
            'justify_content' => [
               'flex-start'    => __('Left', 'ymc-smart-filter'),
               'center'        => __('Center', 'ymc-smart-filter'),
               'flex-end'      => __('Right', 'ymc-smart-filter'),
               'space-between' => __('Space Between', 'ymc-smart-filter'),
               'space-around'  => __('Space Around', 'ymc-smart-filter'),
               'space-evenly'  => __('Space Evenly', 'ymc-smart-filter'),
            ]
         ],
         'post_typography' => [
            'font_family' => [
               'inherit'    => __('Inherit', 'ymc-smart-filter'),
               'OpenSans'   => 'OpenSans',
               'Montserrat' => 'Montserrat',
               'Poppins'    => 'Poppins',
               'Roboto'     => 'Roboto',
               'custom'     => __('Custom', 'ymc-smart-filter')
            ],
            'font_weight' => [
               '200' => __('Extra Light (200)', 'ymc-smart-filter'),
               '300' => __('Light (300)', 'ymc-smart-filter'),
               '400' => __('Normal (400)', 'ymc-smart-filter'),
               '500' => __('Medium (500)', 'ymc-smart-filter'),
               '600' => __('Semi Bold (600)', 'ymc-smart-filter'),
               '700' => __('Bold (700)', 'ymc-smart-filter'),
               '800' => __('Extra Bold (800)', 'ymc-smart-filter')
            ],
            'font_size_unit' => [ 'px' => 'px', 'em' => 'em', 'rem' => 'rem', '%' => '%' ],
            'text_transform' => [
               'none'       => 'none',
               'capitalize' => 'capitalize',
               'uppercase'  => 'uppercase',
               'lowercase'  => 'lowercase'
            ],
            'font_style' => [
               'normal' => __('Normal', 'ymc-smart-filter'),
               'italic' => __('Italic', 'ymc-smart-filter')
            ]
         ],
         'query_type' => [
            'advanced' => __('Advanced', 'ymc-smart-filter'),
            'callback' => __('Callback', 'ymc-smart-filter'),
         ],
         'post_sortable_fields' => [
            'ID'             => __('ID', 'ymc-smart-filter'),
            'author'         => __('Author', 'ymc-smart-filter'),
            'title'          => __('Title', 'ymc-smart-filter'),
            'name'           => __('Name', 'ymc-smart-filter'),
            'date'           => __('Date', 'ymc-smart-filter'),
            'modified'       => __('Modified', 'ymc-smart-filter'),
            'type'           => __('Type', 'ymc-smart-filter'),
            'parent'         => __('Parent', 'ymc-smart-filter'),
            'rand'           => __('Rand', 'ymc-smart-filter'),
            'comment_count'  => __('Comment count', 'ymc-smart-filter'),
            'menu_order'     => __('Menu order', 'ymc-smart-filter'),
            'meta_value'     => __('Meta value', 'ymc-smart-filter'),
            'meta_value_num' => __('Meta value num', 'ymc-smart-filter'),
         ],
         'preloader_icons' => [
            'dual_arc'         => __('Dual Arc Spinner', 'ymc-smart-filter'),
            'orbit_spinner'    => __('Orbit Spinner', 'ymc-smart-filter'),
            'pulsing_dots'     => __('Pulsing Dots', 'ymc-smart-filter'),
            'filling_square'   => __('Filling Square', 'ymc-smart-filter'),
            'rotating_paths'   => __('Rotating Paths Loader', 'ymc-smart-filter'),
            'bouncing_bars'    => __('Bouncing Bars Loader', 'ymc-smart-filter'),
            'jumping_bars'     => __('Jumping Bars', 'ymc-smart-filter'),
            'rotating_lines'   => __('Rotating Lines Spinner', 'ymc-smart-filter'),
            'wave_curve'       => __('Wave Curve Spinner', 'ymc-smart-filter'),
            'gear_rotate'      => __('Gear Rotate Spinner', 'ymc-smart-filter'),
            'ripple_pulse'     => __('Ripple Pulse Loader', 'ymc-smart-filter'),
            'sliding_dots'     => __('Sliding Dots Loader', 'ymc-smart-filter'),
            'segment_ring'     => __('Segment Ring Spinner', 'ymc-smart-filter'),
            'bouncing_squares' => __('Bouncing Squares Loader', 'ymc-smart-filter'),
            'fading_squares'   => __('Fading Squares Loader', 'ymc-smart-filter'),
            'gear_spinner'     => __('Gear Spinner', 'ymc-smart-filter'),
            'none'             => __('None', 'ymc-smart-filter')
         ],
         'filter_preloader' => [
            'none'          => __('None', 'ymc-smart-filter'),
            'brightness'    => __('Brightness', 'ymc-smart-filter'),
            'contrast'      => __('Contrast', 'ymc-smart-filter'),
            'grayscale'     => __('Grayscale', 'ymc-smart-filter'),
            'invert'        => __('Invert', 'ymc-smart-filter'),
            'opacity'       => __('Opacity', 'ymc-smart-filter'),
            'saturate'      => __('Saturate', 'ymc-smart-filter'),
            'sepia'         => __('Sepia', 'ymc-smart-filter'),
            'custom_filter' => __('Custom Filter', 'ymc-smart-filter')
         ],
         'grid_style' => [
            'grid'    => __('Standard Grid', 'ymc-smart-filter'),
            'masonry' => __('Masonry', 'ymc-smart-filter')
         ],
         'ymc_fg_carousel_settings' => [
            'general' => [
               'auto_height'     => [ 'true', 'false'],
               'autoplay'        => [ 'true', 'false'],
               'autoplay_delay'  => [ 300, 500, 1000, 1500, 2000, 2500, 3000, 3500, 4000, 4500, 5000 ],
               'loop'            => [ 'true', 'false'],
               'centered_slides' => [ 'true', 'false'],
               'slides_per_view' => [ 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5, 5.5, 6, 6.5, 7, 7.5 ],
               'space_between'   => [ 0, 20, 40, 60, 80, 100 ],
               'mousewheel'      => [ 'true', 'false'],
               'speed'           => [ 200, 300, 400, 500, 600, 700, 1000, 1500, 2000, 2500, 3000, 3500, 4000, 4500, 5000, 5500, 6000 ],
               'effect'          => [ 
                  'slide'     => __('Slide', 'ymc-smart-filter'), 
                  'fade'      => __('Fade', 'ymc-smart-filter'), 
                  'cube'      => __('Cube', 'ymc-smart-filter'), 
                  'coverflow' => __('Coverflow', 'ymc-smart-filter'), 
                  'flip'      => __('Flip', 'ymc-smart-filter'), 
                  'cards'     => __('Cards', 'ymc-smart-filter'), 
                  'creative'  => __('Creative', 'ymc-smart-filter')
               ],
            ],
            'pagination' => [
               'visible'         => [ 'true', 'false'],
               'dynamic_bullets' => [ 'true', 'false'],
               'type'            => [ 
                  'bullets'     => __('Bullets', 'ymc-smart-filter'), 
                  'fraction'    => __('Fraction', 'ymc-smart-filter'), 
                  'progressbar' => __('Progressbar', 'ymc-smart-filter') 
               ],
            ],
            'navigation' => [ 'visible' => [ 'true', 'false'] ],
            'scrollbar'  => [ 'visible' => [ 'true', 'false'] ]
         ]
      ];

   }


	/**
	 * Get label by category and key
	 */
	public static function get(string $category, string $key, string $default = ''): string
	{	
      self::init_labels();

      $value = self::$labels[$category][$key] ?? $default;

      if (is_string($value)) {
         // phpcs:ignore WordPress
         return $value;
      }

      return is_array($value)
         ? self::stringify_recursive($value)
         : (string) $value;
   }

	/**
	 * Get all labels of a specific category
	 */
	public static function all(string $category): array
	{
      self::init_labels();
      return self::$labels[$category] ?? [];
	}


   /**
    * Recursively translate only string values
    */
   protected static function stringify_recursive($value)
    {
        if (is_array($value)) {
            return implode(', ', array_map([self::class, 'stringify_recursive'], $value));
        }
        return (string) $value;
    }

}