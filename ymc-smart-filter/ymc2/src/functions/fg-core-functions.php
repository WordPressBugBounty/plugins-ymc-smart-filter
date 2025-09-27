<?php

use YMCFilterGrids\FG_Data_Store as Data_Store;

/**
 * FG Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get all post types
 * @param array $exclude_posts Array of post types to exclude
 *
 * @return array
 */
if (! function_exists( 'ymc_get_post_types' )) {
	function ymc_get_post_types($exclude_posts = []) {
		$post_types = get_post_types( [ 'public' => true ], 'names' );
		if( count($exclude_posts) > 0 ) {
			foreach ( $exclude_posts as $value ) {
				$pos = array_search( $value, $post_types );
				unset($post_types[$pos]);
			}
		}
		ksort( $post_types, SORT_ASC );
		return $post_types;
	}
}

/**
 * Get all taxonomies
 * @param $post_types
 *
 * @return array
 */
if (! function_exists( 'ymc_get_taxonomies')) {
	function ymc_get_taxonomies($post_types = []) {
		$result = [];
		$taxonomies = get_object_taxonomies($post_types, 'objects');
		if( !empty($taxonomies) ) {
			foreach ( $taxonomies as $tax ) {
				$result[$tax->name] = $tax->label;
			}
		}
		asort($result);
		return $result;
	}
}

/**
 * Get posts ids
 * @param $post_types
 * @param $posts_per_page
 *
 * @return array
 */
if(! function_exists( 'ymc_get_posts_ids')) {
	function ymc_get_posts_ids($post_types = [], $posts_per_page = 20) {
		$found_posts = 0;
		$posts_ids = [];
		if(!empty($post_types)) {
			$arg = [
				'post_type' => $post_types,
				'posts_per_page' => $posts_per_page,
				'orderby' => 'title',
				'order' => 'ASC'
			];
			$query = new \WP_query($arg);
			$found_posts = $query->found_posts;
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$posts_ids[] = get_the_ID();
				}
			}
		}
		$data['found_posts'] = $found_posts;
		$data['posts_ids'] = $posts_ids;

		return $data;
	}
}

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
 * Debug in Console
 */

if (! function_exists( 'ymc_js_console_log')) {
	/*function ymc_js_console_log( $x, $as_text = true ) {
		$str = '<div class="php-to-js-console-log" style="display: none !important;" data-as-text="' . esc_attr( (bool) $as_text ) .
		       '" data-variable="' . htmlspecialchars( wp_json_encode( $x ) ) . '">' . htmlspecialchars( var_export( $x, true ) ) . '</div>';
		echo wp_kses($str, ['div' => ['class' => true, 'style' => true, 'data-as-text' => true, 'data-variable' => true]]);
	}

	if ( function_exists( 'ymc_js_console_log' ) ) {
		add_action( 'wp_footer', function () {
			echo '<script type="text/javascript">jQuery(document).ready(function ($) { 
    		$(".php-to-js-console-log").each(function (i, el) { let $e = $(el); console.log("PHP debug is below:"); 
            (!$e.attr("data-as-text")) ? console.log(JSON.parse($e.attr("data-variable"))) : console.log($e.text()); }); });</script>';
		}, 99999 );
	}*/
}


/**
 * Assembles the filter options structure from POST data.
 *
 * @param int $post_id
 * @param string $filter_type
 * @param array $filter_options
 * @return array
 */
if (! function_exists( 'ymc_build_filter_options_from_post')) {
	function ymc_build_filter_options_from_post(int $post_id, string $filter_type, array $filter_options): array {
		$options = [];

		if (!empty($filter_type)) {
			if('composite' !== $filter_type) {
				$tax_name = Data_Store::get_meta_value($post_id, 'ymc_fg_taxonomies');
				$tax_name = !empty($tax_name) ? array_map('sanitize_text_field', $tax_name) : [];

				$options[] = [
					'tax_name'    => $tax_name,
					'filter_type' => $filter_type,
					'placement'   => 'top',
				];
			}
			else {
				if($filter_options) {
					foreach ($filter_options as $option) {
						$tax_name = !empty( $option['tax_name'] ) && is_array($option['tax_name'])
                            ? array_map( 'sanitize_text_field', $option['tax_name'] )
                            : ( $option['filter_type'] === 'date_picker' ? [ 'date_picker' ] :
                              ( $option['filter_type'] === 'dependent' ? [ 'dependent' ] : [] ));

						$filter_type = !empty($option['filter_type'])
							? sanitize_text_field($option['filter_type'])
							: 'default';

						$placement = !empty($option['placement'])
							? sanitize_text_field($option['placement'])
							: 'top';

						$options[] = [
							'tax_name'    => $tax_name,
							'filter_type' => $filter_type,
							'placement'   => $placement
						];
					}
				}
			}
		}

		return $options;
	}
}


/**
 * Sanitize array recursively
 * @param $array
 *
 * @return mixed
 */
if (! function_exists( 'ymc_sanitize_array_recursive')) {
	function ymc_sanitize_array_recursive($array) {
		foreach ($array as $key => &$value) {
			if (is_array($value)) {
				$value = ymc_sanitize_array_recursive($value);
			} else {
				$value = sanitize_text_field($value);
			}
		}
		return $array;
	}
}


/**
 * Gets all terms (tags, categories, etc.) of all taxonomies to which the specified post is attached.
 *
 * @param int $post_id ID поста.
 *
 * @return array An array of terms. Each element contains:
 *               - name (string)  Name of the term
 *               - slug (string)  Term slug
 *               - taxonomy (string) Taxonomy name
 *               - term_id (int)  ID term
 *               - link (string)  URL of the link to the term archive
 */
if (! function_exists( 'ymc_get_all_post_terms')) {
	function ymc_get_all_post_terms( $post_id ) {
		if ( ! $post_id || ! get_post( $post_id ) ) {
			return [];
		}

		$post_type  = get_post_type( $post_id );
		$taxonomies = get_object_taxonomies( $post_type );
		$all_terms  = [];

		foreach ( $taxonomies as $taxonomy ) {
			$terms = get_the_terms( $post_id, $taxonomy );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$all_terms[] = [
						'name'     => $term->name,
						'slug'     => $term->slug,
						'taxonomy' => $term->taxonomy,
						'term_id'  => $term->term_id,
						'link'     => get_term_link( $term )
					];
				}
			}
		}

		return $all_terms;
	}
}


/**
 * Truncate post content / excerpt with safer fallbacks.
 *
 * @param int    $post_id      Post ID.
 * @param string $mode_excerpt Mode: '', 'excerpt_first_block', 'excerpt_line_break'.
 * @param int    $length       Number of words for default trimming (default 30).
 * @return string              Trimmed text (may be empty string).
 */

if ( ! function_exists( 'ymc_truncate_post_content' ) ) {
	function ymc_truncate_post_content( $post_id, $mode_excerpt = '', $length = 30 ) {
		$post_id = (int) $post_id;
		if ( ! $post_id ) {
			return '';
		}

		// 1) Prefer explicit manual excerpt (raw, without filters)
		$manual_excerpt = trim((string) get_post_field( 'post_excerpt', $post_id, 'raw' ));

		if ( $manual_excerpt !== '' ) {
			$post_content = $manual_excerpt;
		}
        else {
			// 2) Try raw post_content first: strip shortcodes and tags
			$raw = (string) get_post_field( 'post_content', $post_id, 'raw' );
			$raw = strip_shortcodes( $raw );
	        $raw = preg_replace('/\[\/?[^\]]+\]/', '', $raw);

	        if ( trim( $raw ) !== '' ) {
				$post_content = $raw;
			} else {
				// 3) Fallback to filtered content / get_the_excerpt (closest to original behavior)
				if ( has_excerpt( $post_id ) ) {
					$post_content = get_the_excerpt( $post_id ); // applies excerpt filters
				} else {
					$post_obj = get_post( $post_id );
					$post_content = $post_obj ? apply_filters( 'the_content', $post_obj->post_content ) : '';
				}
				// keep consistent: remove shortcodes and tags from the fallback too
				$post_content = strip_shortcodes( (string) $post_content );
				$post_content = wp_strip_all_tags( $post_content );
			}
		}

		// Ensure we have a string at this point
		$post_content = (string) $post_content;

		// Existing switch logic (kept to avoid changing public behaviour)
		switch ( $mode_excerpt ) {

			case 'excerpt_first_block':
				$post_content = preg_replace('/<\/(p|h[1-6])>/i', "\n", $post_content);
				$post_content = preg_replace("/\r\n|\r/", "\n", $post_content);

				if ( preg_match('/(.+?)\n/', $post_content, $matches) ) {
					$first_block = wp_strip_all_tags( $matches[1] );
					$length_excerpt = strlen( $first_block );
					$post_content = wp_trim_words( $first_block, $length_excerpt );
				} else {
					$post_content = wp_trim_words( wp_strip_all_tags( $post_content ), $length );
				}
				break;

			case 'excerpt_line_break':
				$post_content = preg_replace('/<br\s*\/?>/i', "\n", $post_content);
				$post_content = preg_replace("/\r\n|\r/", "\n", $post_content);

				if ( preg_match('/(.+?)\n/', $post_content, $matches) ) {
					$post_content = wp_strip_all_tags( $matches[1] );
				} else {
					$post_content = wp_trim_words( wp_strip_all_tags( $post_content ), $length );
				}
				break;

			default:
				$post_content = wp_trim_words( $post_content, $length );
				break;
		}

		// Convert entities, strip tags and trim
		$plain = trim( html_entity_decode( wp_strip_all_tags( $post_content ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );

		// If empty after stripping -> return empty string (prevents "…")
		if ( $plain === '' ) {
			return '';
		}

		// If there are no letters/digits (only punctuation / whitespace / symbols), treat as empty
		if ( ! preg_match( '/[\p{L}\p{N}]/u', $plain ) ) {
			return '';
		}

		// Otherwise return the (possibly trimmed) content (kept as-is so caller can escape/kses)
		return $post_content;
	}
}


/**
 * Gets post image.
 *
 * @param int $post_id ID поста.
 * @param string $post_image_size Post image size.
 */

if (! function_exists( 'ymc_post_image_size')) {
	function ymc_post_image_size($post_id, $post_image_size) {
		if ( !has_post_thumbnail($post_id) ) {
			return '';
		}
		$sizes = [
			'thumbnail' => ['thumbnail', 'is-thumbnail'],
			'medium'    => ['medium', 'is-medium'],
			'full'      => ['large', 'is-full'],
		];

		[$size, $class] = $sizes[$post_image_size] ?? ['full', 'is-large'];

		return get_the_post_thumbnail($post_id, $size, ['class' => $class, 'alt' => get_the_title($post_id)]);
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
 * Minify CSS
 * @param $css
 */
if (! function_exists( 'ymc_minify_css')) {
	function ymc_minify_css($css) {
		$css = str_replace(["\t", "\n", "\r"], '', $css);
		$css = preg_replace('/\s+/', ' ', $css);
		$css = preg_replace('/\s*([{};:,])\s*/', '$1', $css);
		$css = preg_replace('/;}/', '}', $css);

		return trim($css);
	}
}


/**
 * Get term settings for the current post.
 *
 * @param int $post_id
 * @param array $terms_attr
 * @return array
 */
if (! function_exists( 'ymc_get_post_terms_settings')) {
	function ymc_get_post_terms_settings(int $post_id, array $terms_attr): array {
		$post_terms_settings = [];
		$taxonomies = get_taxonomies(['public' => true], 'names');
		$post_term_ids = [];

		foreach ($taxonomies as $taxonomy) {
			$terms = get_the_terms($post_id, $taxonomy);

			if (!is_wp_error($terms) && !empty($terms)) {
				foreach ($terms as $term) {
					$post_term_ids[] = (int) $term->term_id;
				}
			}
		}

		$post_term_ids = array_unique($post_term_ids);

		foreach ($terms_attr as $term_setting) {
			if (in_array((int)$term_setting['term_id'], $post_term_ids, true)) {
				$post_terms_settings[(int)$term_setting['term_id']] = $term_setting;
			}
		}

		return $post_terms_settings;
	}
}


/**
 * Calculate post read time
 * @param $post_id
 * @param int $words_per_minute. Average reading speed. Default: 200
 * @return int
 */
if (! function_exists( 'ymc_calculate_read_time')) {
	function ymc_calculate_read_time($post_id, $words_per_minute = 200) : int {
		$content = get_post_field('post_content', $post_id);
		$word_count = str_word_count(strip_tags($content));
		$minutes = ceil($word_count / $words_per_minute);
		return $minutes;
	}
}




/**
 * Generate accordion with terms grouped by taxonomies (excluding current taxonomy).
 *
 * @param array  $sequence           Ordered list of taxonomy slugs (e.g. ['category','post_tag','author_book'])
 * @param string $current_taxonomy   (OPTIONAL) taxonomy slug — will be overridden by the taxonomy of $current_term_id if possible
 * @param int    $current_term_id    Current term ID (the term being edited)
 * @param int    $post_id            Filter post ID (where ymc_fg_term_attrs stored)
 *
 * @return string HTML
 */

if (! function_exists( 'ymc_get_terms_accordion')) {
	function ymc_get_terms_accordion(array $sequence, string $current_taxonomy, int $current_term_id, int $post_id) : string {

		// helper: parse related_terms field in flexible way
		$parse_related = function($raw) {
			if (empty($raw)) return [];
			if (is_array($raw)) return array_values(array_map('intval', $raw));

			// try JSON
			if (is_string($raw)) {
				$json = json_decode($raw, true);
				if (is_array($json)) return array_values(array_map('intval', $json));
			}

			// try maybe_unserialize
			$un = maybe_unserialize($raw);
			if (is_array($un)) return array_values(array_map('intval', $un));

			// fallback: extract ints from string
			if (is_string($raw)) {
				preg_match_all('/\d+/', $raw, $m);
				if (!empty($m[0])) return array_map('intval', $m[0]);
			}

			return [];
		};

		// 1) sanitize sequence
		if (empty($sequence) || !is_array($sequence)) {
			return '<div class="notification notification--warning accordion-related-terms__empty">' . esc_html__('No taxonomies provided.', 'ymc-smart-filter') . '</div>';
		}

		// 2) Try to determine current taxonomy from term id (MORE RELIABLE)
		$term_obj = null;
		if ($current_term_id > 0) {
			$term_obj = get_term($current_term_id);
			if ($term_obj && !is_wp_error($term_obj)) {
				$current_taxonomy = (string) $term_obj->taxonomy;
			}
		}

		// 3) validate current taxonomy in sequence
		$current_index = array_search($current_taxonomy, $sequence, true);
		if ($current_index === false) {
			return '<div class="notification notification--warning accordion-related-terms__empty">' . esc_html__('Invalid taxonomy sequence or taxonomy not found in sequence.', 'ymc-smart-filter') . '</div>';
		}

		// 4) available taxonomies AFTER current in sequence (only next level)
		$available_taxonomies = array_slice($sequence, $current_index + 1, 1);
		if (empty($available_taxonomies)) {
			return '<div class="notification notification--warning accordion-related-terms__empty">' . esc_html__('This taxonomy has no related terms (end of sequence).', 'ymc-smart-filter') . '</div>';
		}

		// 5) load saved term attributes
		$term_attrs = Data_Store::get_meta_value($post_id, 'ymc_fg_term_attrs');
		$term_attrs = is_array($term_attrs) ? $term_attrs : maybe_unserialize($term_attrs);
		if (!is_array($term_attrs)) $term_attrs = [];

		// 6) find related_terms for the exact current term entry (match by term_id AND by stored taxonomy if available)
		$related_ids_raw = [];
		foreach ($term_attrs as $attr) {
			$attr_term_id = isset($attr['term_id']) ? (int)$attr['term_id'] : 0;
			// keys may vary: 'term_taxonomy' or 'taxonomy'
			$attr_tax = isset($attr['term_taxonomy']) ? (string)$attr['term_taxonomy'] : (isset($attr['taxonomy']) ? (string)$attr['taxonomy'] : '');

			if ($attr_term_id === $current_term_id && ($attr_tax === '' || $attr_tax === $current_taxonomy)) {
				$related_ids_raw = $parse_related($attr['related_terms'] ?? '');
				break;
			}
		}

		// 7) group related ids by real taxonomy (get_term for each ID)
		$related_by_tax = [];
		if (!empty($related_ids_raw)) {
			$related_ids_raw = array_values(array_unique(array_map('intval', $related_ids_raw)));
			foreach ($related_ids_raw as $rid) {
				if ($rid <= 0) continue;
				$rterm = get_term($rid);
				if ($rterm && !is_wp_error($rterm)) {
					// IMPORTANT: only include related terms whose taxonomy is in available_taxonomies
					$r_tax = (string) $rterm->taxonomy;
					if (in_array($r_tax, $available_taxonomies, true)) { // filter by allowed target taxonomies
						if (!isset($related_by_tax[$r_tax])) $related_by_tax[$r_tax] = [];
						$related_by_tax[$r_tax][] = (int)$rterm->term_id;
					}
				}
			}
		}

		// 8) build blocks only for available taxonomies (and only if they have terms)
		$blocks = [];
		foreach ($available_taxonomies as $taxonomy) {
			$tax_obj = get_taxonomy($taxonomy);
			if (!$tax_obj) continue;

			$terms = get_terms([
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			]);

			if (empty($terms) || is_wp_error($terms)) continue;

			$block  = '<div class="accordion-related-terms__inner">';
			$block .= '<button type="button" class="accordion-related-terms__header js-accordion-terms-header" aria-expanded="false">'
			          .  esc_html($tax_obj->labels->name) . '</button>';
			$block .= '<div class="accordion-related-terms__content js-related-terms-list" style="display:none;">';

			// get only related IDs that belong to this target taxonomy (may be empty)
			$related_for_this_tax = isset($related_by_tax[$taxonomy]) ? $related_by_tax[$taxonomy] : [];

			foreach ($terms as $term) {
				$term_id = (int) $term->term_id;
				$checked = in_array($term_id, $related_for_this_tax, true) ? 'checked' : '';
				$block .= '<label class="field-label accordion-related-terms__term-option">';
				$block .= '<input class="checkbox-control" type="checkbox" name="related_terms[]" value="' . esc_attr($term_id) . '" ' . $checked . '>';
				$block .= esc_html($term->name);
				$block .= '</label>';
			}

			$block .= '</div></div>';
			$blocks[] = $block;
		}

		if (empty($blocks)) {
			return '<div class="notification notification--warning accordion-related-terms__empty">' . esc_html__('No related terms available for this taxonomy.', 'ymc-smart-filter') . '</div>';
		}

		$html  = '<div class="accordion-related-terms js-accordion-terms">' . PHP_EOL;
		$html .= implode(PHP_EOL, $blocks);
		$html .= PHP_EOL . '</div>';

		return $html;
	}
}


