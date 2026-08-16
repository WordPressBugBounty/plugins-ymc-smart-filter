<?php

defined('ABSPATH') || exit;


/**
 * Get all acf fields for builder
 * 
 * @return array
 */
if (! function_exists( 'ymc_get_all_acf_fields_for_builder')) {
	function ymc_get_all_acf_fields_for_builder() : array {

		if ( ! function_exists('acf_get_field_groups') ) return [];

		$fields_list = [
			[
				'value' => 'none', 
				'label' => '- Select Field -'
			]
		];

		$groups = acf_get_field_groups();

		// List of types we officially support in the current architecture
		$supported_types = [
			'text',
			'textarea', 
			'wysiwyg', 
			'number', 
			'email', 
			'url', 
			'image',
			'file', 
			'oembed', 
			'link', 
			'date_picker', 
			'color_picker', 
			'date_time_picker', 
			'time_picker'
		];

		foreach ( $groups as $group ) {

			if ( ! isset($group['active']) || ! $group['active'] ) {
            continue;
         }

			$fields = acf_get_fields( $group['key'] );

			if ( ! $fields ) continue;

			foreach ( $fields as $field ) {
				if ( ! in_array($field['type'], $supported_types) ) continue;				

				$fields_list[] = [
					'value' => $field['key'],
					'label' => $group['title'] . ': ' . $field['label'],
					'type'  => $field['type']
				];
			}
		}

		return $fields_list;
	}

}




