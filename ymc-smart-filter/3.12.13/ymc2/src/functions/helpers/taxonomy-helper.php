<?php

defined('ABSPATH') || exit;



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