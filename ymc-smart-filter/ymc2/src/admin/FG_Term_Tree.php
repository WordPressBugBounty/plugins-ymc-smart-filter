<?php declare( strict_types = 1 );

namespace YMCFilterGrids\admin;

defined( 'ABSPATH' ) || exit;

/**
 * FG_Term_Tree Class
 *
 * @since 3.14.0
 */
class FG_Term_Tree {

	use FG_Term_Trait;

	/**
	 * Rendering the taxonomy as a tree.
	 *
	 * @param string $tax_name
	 * @param string $label
	 * @param array $post_types
	 * @param array $selected_terms
	 * @param array $term_attrs
	 * @param array $term_sort
	 * @param array $tax_attrs
	 * @param string $term_sort_direction
	 * @return string
	 */
	public static function output_taxonomy_tree(
		string $tax_name,
		string $label,
		array $post_types,
		array $selected_terms,
		array $term_attrs,
		array $term_sort,
		array $tax_attrs,
		string $term_sort_direction) : string {

      $selected_terms = array_map('intval', (array) $selected_terms);

		self::set_taxonomy_label($tax_name, $tax_attrs);
		$tax_label = self::$tax_label ? self::$tax_label : $label;
		
		$raw_terms = self::get_hierarchical_terms($tax_name);
     
      $has_children = false;
      foreach ($raw_terms as $t) {
         if ((int) $t->parent > 0) {
            $has_children = true;
            break;
         }
      }

      $tree_class = $has_children ? ' cel--tree' : '';

		if (empty($raw_terms)) {
			return '<article class="cel'. esc_attr($tree_class) .' js-tax-' . esc_attr($tax_name) . '">
				<div class="tax-name"><label class="field-label">' . esc_html($tax_label) . '</label></div>
				<div class="terms-inner">
					<div class="no-terms"><div class="notification notification--warning">' . esc_html__('No terms found.', 'ymc-smart-filter') . '</div></div>
				</div>
			</article>';
		}
		
		$terms_assoc = [];
		foreach ($raw_terms as $t) {
			$terms_assoc[$t->term_id] = $t->name;
		}
      
      self::determine_term_sorting($term_sort_direction, $term_sort, $terms_assoc);

      $sorted_raw_terms = [];
      foreach (array_keys($terms_assoc) as $tid) {
         foreach ($raw_terms as $t) {
            if ((int) $t->term_id === (int) $tid) {
                  $sorted_raw_terms[] = $t;
                  break;
            }
         }
      }
      $raw_terms = $sorted_raw_terms;      

		$checked = (self::checked_selected_terms($terms_assoc, $selected_terms)) ? 'checked' : '';

		ob_start();

		echo '<article class="cel'. esc_attr($tree_class) .' js-tax-'. esc_attr($tax_name) .'">';
		echo '<div class="tax-name">
			  <input class="checkbox-control js-select-all-terms" id="all-terms-'. esc_attr($tax_name) .'" '. esc_attr($checked) .' type="checkbox">
			  <label class="field-label" for="all-terms-'. esc_attr($tax_name) .'">'. esc_html($tax_label) .'</label></div>';
		echo '<div class="terms-inner js-term-sortable">';

		self::$tax_label = '';
		
		$cache_key = self::get_cache_key($tax_name, $post_types);
		if (get_transient($cache_key) === false) {
			foreach ($terms_assoc as $term_id => $name) {
				self::update_post_counts($tax_name, $term_id);
			}
			set_transient($cache_key, true, 60 * MINUTE_IN_SECONDS);
		}
		
		$tree = self::build_tree($raw_terms);
		
		self::render_tree_branch($tree, $tax_name, $post_types, $selected_terms, $term_attrs);

		echo '</div></article>';

		return ob_get_clean();
	}

	/**
	 * Retrieving taxonomy terms.
	 *
	 * @param string $tax
	 * @return array
	 */
	protected static function get_hierarchical_terms(string $tax) : array {
		$args = [
			'taxonomy'   => $tax,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC'
		];
		$terms = get_terms($args);
		if ($terms && !is_wp_error($terms)) {
			return $terms;
		}
		return [];
	}

	/**
	 * Reliable tree construction based on the `term_id` and `parent` fields.
	 *
	 * @param array $terms
	 * @return array
	 */
	protected static function build_tree(array $terms) : array {
		$map  = [];
		$tree = [];
		
		foreach ($terms as $term) {
			$map[$term->term_id] = [
				'term' => $term,
				'children' => []
			];
		}
		
		foreach ($terms as $term) {
			$term_id = (int) $term->term_id;
			$parent_id = (int) $term->parent;

			if ($parent_id > 0 && isset($map[$parent_id])) {
				$map[$parent_id]['children'][] = &$map[$term_id];
			} else {
				$tree[] = &$map[$term_id];
			}
		}

		return $tree;
	}

	/**
	 * Recursive rendering of tree branches.
	 *
	 * @param array $branches
	 * @param string $tax_name
	 * @param array $post_types
	 * @param array $selected_terms
	 * @param array $term_attrs
	 * @return void
	 */
	protected static function render_tree_branch(array $branches, string $tax_name, array $post_types, array $selected_terms, array $term_attrs) : void {
		foreach ($branches as $branch) {
			$term = $branch['term'];
			$term_id = (int) $term->term_id;
			$original_name = $term->name;
			
			self::clear_data_attributes();
			self::set_data_attributes($term_id, $term_attrs);			
			
			$display_name = self::$term_name !== '' ? self::$term_name : $original_name;

			$post_count = self::get_post_counts($post_types, $term_id);
			$is_term_sel = (in_array($term_id, $selected_terms, true)) ? 'checked' : '';
			$class_status = (self::$term_status) ? ' '. self::$term_status : '';
			$term_slug = $term->slug;

			echo '<div class="term-item'. esc_attr($class_status) .'" 
			data-term-id="'. esc_attr($term_id) .'" 
			data-term-taxonomy="'. esc_attr($tax_name) .'" 
			data-term-slug="'. esc_attr($term_slug) .'" 
			data-term-name-orig="'. esc_attr($original_name) .'"
			data-term-name="'. esc_attr($display_name) .'"
			data-term-background="'. esc_attr(self::$term_background) .'"
			data-term-color="'. esc_attr(self::$term_color) .'"
			data-term-class="'. esc_attr(self::$term_class) .'"
			data-term-default="'. esc_attr(self::$term_default) .'"
			data-term-visible="'. esc_attr(self::$term_visible) .'"							
			data-term-checked="'. esc_attr(self::$term_checked) .'"
			data-term-status="'. esc_attr(self::$term_status).'"							
			data-term-icon-class="'. esc_attr(self::$icon_class) .'" 
			data-term-icon-alignment="'. esc_attr(self::$icon_alignment) .'" 
			data-term-icon-color="'. esc_attr(self::$icon_color) .'"
			data-term-icon-url="'. esc_attr(self::$icon_url) .'"
			data-term-related-terms="'. esc_attr(self::$related_terms) .'">					  
			<i class="fa-solid fa-up-down-left-right icon-is-drag js-term-handle"></i>
			<input class="checkbox-control" id="term-id-'. esc_attr($term_id) .'" name="ymc_fg_terms[]" type="checkbox" '. esc_attr($is_term_sel) .' value="'. esc_attr($term_id) .'">					  
			<label class="field-label" for="term-id-'. esc_attr($term_id) .'">'. esc_html($display_name) .' <span class="count">('. esc_html($post_count) .')</span></label>
			<i class="fa-solid fa-ellipsis-vertical icon-is-settings js-term-settings"></i>
			<span class="icon-term js-icon-term"></span>';

			// Recursively output child elements, if any exist
			if (!empty($branch['children'])) {
				echo '<div class="term-children js-term-sortable">';
				self::render_tree_branch($branch['children'], $tax_name, $post_types, $selected_terms, $term_attrs);
				echo '</div>';
			}

			echo '</div>';
		}
	}
}


