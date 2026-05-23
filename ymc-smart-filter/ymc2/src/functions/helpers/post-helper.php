<?php

defined('ABSPATH') || exit;


/**
 * Get all post types
 * @param array $exclude_posts Array of post types to exclude
 *
 * @return array
 */
if (! function_exists( 'ymc_get_post_types' )) {
	function ymc_get_post_types( $exclude_posts = [], $show_admin_only = false ) {

		$args = $show_admin_only
			? [ 'show_ui' => true ]
			: [ 'public' => true ];

		$post_types = get_post_types( $args, 'names' );

		if ( ! empty( $exclude_posts ) ) {
			foreach ( $exclude_posts as $value ) {
				if ( isset( $post_types[$value] ) ) {
					unset( $post_types[$value] );
				}
			}
		}
		ksort( $post_types, SORT_ASC );
		return $post_types;
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
 * Gets all terms (tags, categories, etc.) of all taxonomies to which the specified post is attached.
 *
 * @param int $post_id
 *
 * @return array An array of terms. Each element contains:
 * - name (string)  Name of the term
 * - slug (string)  Term slug
 * - taxonomy (string) Taxonomy name
 * - term_id (int)  ID term
 * - link (string)  URL of the link to the term archive
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
 * Get list of taxonomies for post
 *
 * @param int $post_id
 * 
 * @return array Array of taxonomies (slug => label)
 */

if (! function_exists( 'ymc_get_attached_post_taxonomies')) {
	function ymc_get_attached_post_taxonomies( $post_id ) {
		if ( ! $post_id ) {
			return array();
		}

		$taxonomies = get_post_taxonomies( $post_id );
		$attached   = array();

		if ( empty( $taxonomies ) ) {
			return array();
		}

		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_post_terms( $post_id, $taxonomy );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$tax_obj = get_taxonomy( $taxonomy );
				if ( $tax_obj && isset( $tax_obj->labels->singular_name ) ) {
					$attached[$taxonomy] = $tax_obj->labels->singular_name;
				} else {
					$attached[$taxonomy] = $taxonomy; // fallback
				}
			}
		}

		return $attached;
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





























