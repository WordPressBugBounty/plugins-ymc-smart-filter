<?php

defined('ABSPATH') || exit;


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
 * @param int $post_id
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
			'large'     => ['large', 'is-large'],
         'full'      => ['full', 'is-full'],
		];
		
		[$size, $class] = $sizes[$post_image_size] ?? ['large', 'is-large'];

		return get_the_post_thumbnail($post_id, $size, [
          'class' => $class, 
          'alt'   => get_the_title($post_id),          
          'sizes' => '(max-width: 100vw) 100vw'
      ]);
		
	}
}




