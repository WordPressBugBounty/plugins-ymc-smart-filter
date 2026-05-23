<?php

defined('ABSPATH') || exit;

use YMCFilterGrids\FG_Data_Store as Data_Store;


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

					// If the filter is not taxonomic, tax_name must be an empty array.
					$filters_without_tax = [
						'date_picker',
						'alphabetical'	
					];

					foreach ($filter_options as $option) {					

						if ( ! empty( $option['tax_name'] ) && is_array( $option['tax_name'] ) ) {
							$tax_name = array_map( 'sanitize_text_field', $option['tax_name'] );
						}							
						elseif ( in_array( $filter_type, $filters_without_tax, true ) ) {
							$tax_name = [];
						}							
						else {
							$tax_name = [];
						}

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