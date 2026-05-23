<?php

defined('ABSPATH') || exit;

use YMCFilterGrids\FG_Data_Store as Data_Store;


/**
 * Generate accordion with terms grouped by taxonomies (excluding current taxonomy).
 *
 * @param array  $sequence           Ordered list of taxonomy slugs (e.g. ['category','post_tag','author_book'])
 * @param string $current_taxonomy   (OPTIONAL) taxonomy slug — will be overridden by the taxonomy of $current_term_id if possible
 * @param int    $current_term_id    Current term ID (the term being edited)
 * @param int    $post_id            Filter post ID (where ymc_fg_term_attrs stored)
 *
 * @return string HTML
 */

if (! function_exists( 'ymc_get_terms_accordion')) {
	function ymc_get_terms_accordion(array $sequence, string $current_taxonomy, int $current_term_id, int $post_id) : string {

		// helper: parse related_terms field in flexible way
		$parse_related = function($raw) {
			if (empty($raw)) return [];
			if (is_array($raw)) return array_values(array_map('intval', $raw));

			// try JSON
			if (is_string($raw)) {
				$json = json_decode($raw, true);
				if (is_array($json)) return array_values(array_map('intval', $json));
			}

			// try maybe_unserialize
			$un = maybe_unserialize($raw);
			if (is_array($un)) return array_values(array_map('intval', $un));

			// fallback: extract ints from string
			if (is_string($raw)) {
				preg_match_all('/\d+/', $raw, $m);
				if (!empty($m[0])) return array_map('intval', $m[0]);
			}

			return [];
		};

		// 1) sanitize sequence
		if (empty($sequence) || !is_array($sequence)) {
			return '<div class="notification notification--warning accordion-related-terms__empty">' . esc_html__('No taxonomies provided.', 'ymc-smart-filter') . '</div>';
		}

		// 2) Try to determine current taxonomy from term id (MORE RELIABLE)
		$term_obj = null;
		if ($current_term_id > 0) {
			$term_obj = get_term($current_term_id);
			if ($term_obj && !is_wp_error($term_obj)) {
				$current_taxonomy = (string) $term_obj->taxonomy;
			}
		}

		// 3) validate current taxonomy in sequence
		$current_index = array_search($current_taxonomy, $sequence, true);
		if ($current_index === false) {
			return '<div class="notification notification--warning accordion-related-terms__empty">' . esc_html__('Invalid taxonomy sequence or taxonomy not found in sequence.', 'ymc-smart-filter') . '</div>';
		}

		// 4) available taxonomies AFTER current in sequence (only next level)
		$available_taxonomies = array_slice($sequence, $current_index + 1, 1);
		if (empty($available_taxonomies)) {
			return '<div class="notification notification--warning accordion-related-terms__empty">' . esc_html__('This taxonomy has no related terms (end of sequence).', 'ymc-smart-filter') . '</div>';
		}

		// 5) load saved term attributes
		$term_attrs = Data_Store::get_meta_value($post_id, 'ymc_fg_term_attrs');
		$term_attrs = is_array($term_attrs) ? $term_attrs : maybe_unserialize($term_attrs);
		if (!is_array($term_attrs)) $term_attrs = [];

		// 6) find related_terms for the exact current term entry (match by term_id AND by stored taxonomy if available)
		$related_ids_raw = [];
		foreach ($term_attrs as $attr) {
			$attr_term_id = isset($attr['term_id']) ? (int)$attr['term_id'] : 0;
			// keys may vary: 'term_taxonomy' or 'taxonomy'
			$attr_tax = isset($attr['term_taxonomy']) ? (string)$attr['term_taxonomy'] : (isset($attr['taxonomy']) ? (string)$attr['taxonomy'] : '');

			if ($attr_term_id === $current_term_id && ($attr_tax === '' || $attr_tax === $current_taxonomy)) {
				$related_ids_raw = $parse_related($attr['related_terms'] ?? '');
				break;
			}
		}

		// 7) group related ids by real taxonomy (get_term for each ID)
		$related_by_tax = [];
		if (!empty($related_ids_raw)) {
			$related_ids_raw = array_values(array_unique(array_map('intval', $related_ids_raw)));
			foreach ($related_ids_raw as $rid) {
				if ($rid <= 0) continue;
				$rterm = get_term($rid);
				if ($rterm && !is_wp_error($rterm)) {
					// IMPORTANT: only include related terms whose taxonomy is in available_taxonomies
					$r_tax = (string) $rterm->taxonomy;
					if (in_array($r_tax, $available_taxonomies, true)) { // filter by allowed target taxonomies
						if (!isset($related_by_tax[$r_tax])) $related_by_tax[$r_tax] = [];
						$related_by_tax[$r_tax][] = (int)$rterm->term_id;
					}
				}
			}
		}

		// 8) build blocks only for available taxonomies (and only if they have terms)
		$blocks = [];
		foreach ($available_taxonomies as $taxonomy) {
			$tax_obj = get_taxonomy($taxonomy);
			if (!$tax_obj) continue;

			$terms = get_terms([
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			]);

			if (empty($terms) || is_wp_error($terms)) continue;

			$block  = '<div class="accordion-related-terms__inner">';
			$block .= '<button type="button" class="accordion-related-terms__header js-accordion-terms-header" aria-expanded="false">'
			          .  esc_html($tax_obj->labels->name) . '</button>';
			$block .= '<div class="accordion-related-terms__content js-related-terms-list" style="display:none;">';

			// get only related IDs that belong to this target taxonomy (may be empty)
			$related_for_this_tax = isset($related_by_tax[$taxonomy]) ? $related_by_tax[$taxonomy] : [];

			foreach ($terms as $term) {
				$term_id = (int) $term->term_id;
				$checked = in_array($term_id, $related_for_this_tax, true) ? 'checked' : '';
				$block .= '<label class="field-label accordion-related-terms__term-option">';
				$block .= '<input class="checkbox-control" type="checkbox" name="related_terms[]" value="' . esc_attr($term_id) . '" ' . $checked . '>';
				$block .= esc_html($term->name);
				$block .= '</label>';
			}

			$block .= '</div></div>';
			$blocks[] = $block;
		}

		if (empty($blocks)) {
			return '<div class="notification notification--warning accordion-related-terms__empty">' . esc_html__('No related terms available for this taxonomy.', 'ymc-smart-filter') . '</div>';
		}

		$html  = '<div class="accordion-related-terms js-accordion-terms">' . PHP_EOL;
		$html .= implode(PHP_EOL, $blocks);
		$html .= PHP_EOL . '</div>';

		return $html;
	}
}