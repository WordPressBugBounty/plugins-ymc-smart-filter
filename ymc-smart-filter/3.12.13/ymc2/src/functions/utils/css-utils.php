<?php

defined('ABSPATH') || exit;


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