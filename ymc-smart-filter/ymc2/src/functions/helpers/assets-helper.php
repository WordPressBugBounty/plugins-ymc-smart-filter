<?php

defined('ABSPATH') || exit;

/**
 * Enqueue assets for filter types.
 *
 * @param string $filter_layout
 * @param array  $filter_options
 *
 * @return void
 */
if (! function_exists('ymc_enqueue_filter_assets')) {

	function ymc_enqueue_filter_assets(string $filter_layout, array $filter_options = []): void {

		$filter_types = [];

		if (is_array($filter_options)) {
			$filter_types = array_column($filter_options, 'filter_type');
		}

		$all_types = array_merge([$filter_layout], $filter_types);

		$asset_map = [

			'date_picker' => [
				'scripts' => ['jquery-ui-datepicker'],
				'styles'  => ['query_ui'],
			],

			'flatpickr_date_picker' => [
				'scripts' => ['ymc_flatpickr_confirm_date'],
				'styles'  => ['ymc_flatpickr'],
			],

		];

		foreach ($asset_map as $type => $assets) {

			if (! in_array($type, $all_types, true)) {
				continue;
			}

			if (! empty($assets['scripts'])) {
				foreach ($assets['scripts'] as $script) {
					wp_enqueue_script($script);
				}
			}

			if (! empty($assets['styles'])) {
				foreach ($assets['styles'] as $style) {
					wp_enqueue_style($style);
				}
			}
		}
	}
}


