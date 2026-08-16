<?php

defined('ABSPATH') || exit;


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