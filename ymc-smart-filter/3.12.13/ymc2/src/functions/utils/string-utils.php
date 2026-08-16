<?php

defined('ABSPATH') || exit;


/**
 * Extracts filter IDs from content
 * 
 * @param string $content
 * 
 * @return array
 */
if (! function_exists( 'ymc_extract_filter_ids_from_content')) {
	function ymc_extract_filter_ids_from_content( string $content ) : array {

		$ids = [];

		if ( empty( $content ) ) {
			return $ids;
		}

		preg_match_all(
			'/\[ymc_filter\s+[^]]*id=["\']?(\d+)["\']?[^]]*\]/i',
			$content,
			$matches
		);

		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $id ) {
				$ids[] = (int) $id;
			}
		}

		return $ids;
	}

}