<?php

defined('ABSPATH') || exit;


/**
 * Sanitize array recursively
 * @param $array
 *
 * @return mixed
 */
if (! function_exists( 'ymc_sanitize_array_recursive')) {
	function ymc_sanitize_array_recursive($value) {

		if (! is_array($value)) {
			return sanitize_text_field($value);
		}

		foreach ($value as $key => &$item) {
			if (is_array($item)) {
				$item = ymc_sanitize_array_recursive($item);
			} else {
				$item = sanitize_text_field($item);
			}
		}

		return $value;
	}
}


/**
 * Sanitize array recursively
 */
if (! function_exists( 'ymc_sanitize_deep')) {
   function ymc_sanitize_deep($value) {
      if (is_array($value)) {
         return array_map('ymc_sanitize_deep', $value);
      }
      if (is_numeric($value)) {
         return (int) $value;
      }
      if (is_string($value)) {
         return sanitize_text_field($value);
      }
      return $value;
   }
}


