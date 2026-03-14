<?php declare( strict_types = 1 );

namespace YMCFilterGrids\abstracts;

use YMCFilterGrids\FG_Data_Store as Data_Store;
use YMCFilterGrids\admin\FG_Term as FG_Term;

defined( 'ABSPATH' ) || exit;

abstract class FG_Abstract_Filter_Impl {
	private array $term_slug = [];
	private array $term_name_orig = [];
	private array $term_background = [];
	private array $term_color = [];
	private array $term_class = [];
	private array $term_default = [];
	private array $term_visible = [];
	private array $term_name = [];
	private array $term_checked = [];
	private array $term_status = [];
	private array $icon_class = [];
	private array $icon_alignment = [];
	private array $icon_color = [];
	private array $icon_url = [];

	/**
	 * Get options from DB
	 * @param int $filter_id
	 *
	 * @return void
	 */
	protected function get_options(int $filter_id): void {
		$term_attrs = Data_Store::get_meta_value($filter_id,'ymc_fg_term_attrs');
		$options = [];

		if($term_attrs) {
			foreach ($term_attrs as $item) {
				$index = $item['term_id'];

				$options['term_slug'][$index]       = $item['term_slug'];
				$options['term_name_orig'][$index]  = $item['term_name_orig'];
				$options['term_background'][$index] = $item['term_background'];
				$options['term_color'][$index]      = $item['term_color'];
				$options['term_class'][$index]      = $item['term_class'];
				$options['term_default'][$index]    = $item['term_default'];
				$options['term_visible'][$index]    = $item['term_visible'];
				$options['term_name'][$index]       = $item['term_name'];
				$options['term_checked'][$index]    = $item['term_checked'];
				$options['term_status'][$index]     = $item['term_status'];
				$options['icon_class'][$index]      = $item['icon_class'];
				$options['icon_alignment'][$index]  = $item['icon_alignment'];
				$options['icon_color'][$index]      = $item['icon_color'];
				$options['icon_url'][$index]        = $item['icon_url'];
			}
		}

		$this->set_options($options);
	}

	/**
	 * Set options for a specific term based on provided settings.
	 * @param array $options
	 *
	 * @return void
	 */
	protected function set_options(array $options): void {
		$this->term_slug        = $options['term_slug'] ?? [];
		$this->term_name_orig   = $options['term_name_orig'] ?? [];
		$this->term_background  = $options['term_background'] ?? [];
		$this->term_color       = $options['term_color'] ?? [];
		$this->term_class       = $options['term_class'] ?? [];
		$this->term_default     = $options['term_default'] ?? [];
		$this->term_visible     = $options['term_visible'] ?? [];
		$this->term_name        = $options['term_name'] ?? [];
		$this->term_checked     = $options['term_checked'] ?? [];
		$this->term_status      = $options['term_status'] ?? [];
		$this->icon_class       = $options['icon_class'] ?? [];
		$this->icon_alignment   = $options['icon_alignment'] ?? [];
		$this->icon_color       = $options['icon_color'] ?? [];
		$this->icon_url         = $options['icon_url'] ?? [];
	}

	protected function get_term_background($term_id): string {
		$term_background = $this->term_background[$term_id] ?? '';
		return esc_attr($term_background);
	}
	protected function get_term_color($term_id): string {
		$term_color = $this->term_color[$term_id] ?? '';
		return esc_attr($term_color);
	}

	protected function get_term_name_orig($term_id): string {
		$name = $this->term_name_orig[$term_id] ?? '';
		return esc_attr($name);
	}

	protected function get_term_status($term_id): string {
		$status = $this->term_status[$term_id] ?? '';
		return esc_attr($status);
	}

	protected function get_term_checked($term_id): string {
		$checked = $this->term_checked[$term_id] ?? 'false';
		return esc_attr($checked);
	}

	protected function get_term_slug($term_id): string {
		$slug = $this->term_slug[$term_id] ?? '';
		return esc_attr($slug);
	}

	protected function get_term_default($term_id): string {
		$is_default = $this->term_default[$term_id] ?? 'false';
		return esc_attr($is_default);
	}

	protected function get_term_class($term_id): string {
		$term_class = $this->term_class[$term_id] ?? '';
		return esc_attr($term_class);
	}

	protected function get_term_visible($term_id): string {
		$is_visible = $this->term_visible[$term_id] ?? 'true';
		return esc_attr($is_visible);
	}

	protected function get_icon_color($term_id): string {
		$icon_color = $this->icon_color[$term_id] ?? '';
		return esc_attr($icon_color);
	}

	protected function get_icon_class($term_id): string {
		$icon_class = $this->icon_class[$term_id] ?? '';
		return esc_attr($icon_class);
	}

	protected function get_icon_url($term_id): string {
		$icon_url = $this->icon_url[$term_id] ?? '';
		return esc_attr($icon_url);
	}

	protected function get_icon($term_id): string {
		if($this->get_icon_url($term_id)) {
			$icon_url = $this->get_icon_url($term_id);
			return !empty($icon_url) ? '<img class="icon-class icon-class--img" src="'.home_url().$icon_url.'" width="20">' : '';
		} else {
			$icon_color = $this->get_icon_color($term_id);
			$icon_color = !empty($icon_color) ? "color: {$icon_color};" : '';
			return !empty($this->icon_class[$term_id]) ?
				"<span class='".esc_attr($this->icon_class[$term_id])." icon-class' style='".$icon_color."'></span>" : '';
		}
	}

	protected function get_icon_alignment($term_id): string {
		return !empty($this->icon_alignment[$term_id]) ?
			$this->icon_alignment[$term_id].'-icon' : 'left-icon';
	}

	protected function get_term_name($term_id): string {
		return !empty($this->term_name[$term_id]) ?
			$this->term_name[$term_id] : '';
	}

	protected function get_term_style($term_id): string {
		$text_css = '';
		$text_css .= !empty($this->term_color[$term_id]) ? 'color:'.$this->term_color[$term_id].';' : '';
		$text_css .= !empty($this->term_background[$term_id]) ? 'background-color:'. $this->term_background[$term_id] .';' : '';
		return !empty($text_css) ? 'style="'. $text_css .'"' : '';
	}

	/**
	 * Get selected terms from DB
	 * @param $filter_id
	 *
	 * @return array
	 */
	protected function get_all_selected_terms(int $filter_id) : array {
		$terms_selected = Data_Store::get_meta_value($filter_id, 'ymc_fg_terms');
		return array_map('intval', (array) $terms_selected);
	}


	/**
    * Retrieve terms for a specific taxonomy based on filter settings and display modes.
    *
    * This method fetches terms considering the selected display mode (all or specific IDs),
    * applies post count filtering via meta queries for different post types, 
    * and handles database-level pagination using limit and offset.
    *
    * @since 3.8.0
    *
    * @param int    $filter_id The ID of the filter configuration.
    * @param string $taxonomy  The slug of the taxonomy to retrieve terms from.
    * @param int    $limit     The maximum number of terms to return (0 for all).
    * @param int    $offset    The number of terms to skip (used for pagination).
    *
    * @return array<int, string> Associative array where the key is the term_id and the value is the term name.
    */
   protected function get_selected_terms_by_taxonomy(int $filter_id, string $taxonomy, int $limit = 0, int $offset = 0) : array {
      
      if ($taxonomy === '') {
         return [];
      }

      $terms_selected_ids = $this->get_all_selected_terms($filter_id);

      $display_mode  = Data_Store::get_meta_value($filter_id, 'ymc_fg_display_terms_mode');
      $direction     = Data_Store::get_meta_value($filter_id, 'ymc_fg_term_sort_direction');
      $orderby       = Data_Store::get_meta_value($filter_id, 'ymc_fg_term_sort_field');      
      $post_types    = (array) Data_Store::get_meta_value($filter_id, 'ymc_fg_post_types');
      
      $order = ($direction === 'desc') ? 'DESC' : 'ASC';  

      $args = [
         'taxonomy'   => $taxonomy,
         'hide_empty' => false,
         'number'     => $limit,  
         'offset'     => $offset, 
         'orderby'    => $orderby,
         'order'      => $order
      ];
      
      if (in_array($display_mode, ['selected_terms', 'selected_terms_hide_empty'])) {
         $args['include'] = !empty($terms_selected_ids) ? $terms_selected_ids : [0];
      }
      
      if (in_array($display_mode, ['all_terms_hide_empty', 'selected_terms_hide_empty'])) {
         $meta_query = ['relation' => 'OR'];

         foreach ($post_types as $type) {
            $meta_query[] = [
               'key' => "ymc_fg_count_{$type}", 
               'value' => 0, 
               'compare' => '>', 
               'type' => 'NUMERIC'
            ];
         }
         $args['meta_query'] = $meta_query;
      }

      $terms = get_terms($args);
      $list_terms = [];

      if ($terms && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            $list_terms[$term->term_id] = $term->name;
        }
      }

      return $list_terms;
   }

   
   /**
    * Check if term has attached posts for specific post types
    * @param int $term_id
    * @param array $post_types
    * @param string $taxonomy
    *
    * @return bool
    */
   protected function hasAttachedPosts(int $term_id, array $post_types = [], string $taxonomy = '') {
      
      if (empty($post_types)) {         
         $term = get_term($term_id, $taxonomy);
         return !is_wp_error($term) && $term && (int)$term->count > 0;
      }
     
      $count = FG_Term::get_post_counts($post_types, $term_id);

      return $count > 0;
   }


	/**
	 * Gets the number of posts attached to a term by ID,
	 * taking into account the specified taxonomies and post types.
	 *
	 * @param int $term_id
	 * @param array $tax_names
	 * @param array $post_types
	 * @return int
	 */
   protected function get_post_count_by_term_id(int $term_id, array $tax_names, array $post_types): int {
      $count = 0;
      foreach ($post_types as $type) {
         $meta = get_term_meta($term_id, "ymc_fg_count_{$type}", true);
         $count += (int) $meta;
      }

      return $count;
   }


	/**
	 * Sort terms manually
	 * @param array $terms
	 * @param int   $filter_id
	 *
	 * @return array
	 */
	protected function sort_terms_manual(array &$terms, $filter_id): array {
		$term_sort  = (array) Data_Store::get_meta_value($filter_id, 'ymc_fg_term_sort');
		$direction  = Data_Store::get_meta_value($filter_id, 'ymc_fg_term_sort_direction');

		if ($term_sort && 'manual' === $direction) {
			$sorted_terms = [];
			// first we take the terms in order from $term_sort
			foreach ($term_sort as $id) {
				$key = (int) $id;
				if (isset($terms[$key])) {
					$sorted_terms[$key] = $terms[$key];
				}
			}
			// add new terms that are not in $term_sort
			foreach ($terms as $id => $name) {
				if (!isset($sorted_terms[$id])) {
					$sorted_terms[$id] = $name;
				}
			}

			$terms = $sorted_terms;
		}
		return $terms;
	}

   // Wrappers methods
   /**
    * Initialize filter
    */
   public function init_filter(int $filter_id): void {
      $this->get_options($filter_id);
   }

   /**
    * Apply manual sort
    */
   public function apply_manual_sort(array $terms, int $filter_id): array {
      return $this->sort_terms_manual($terms, $filter_id);
   }

   /**
    * Fetch selected terms
    */
   public function fetch_selected_terms_by_taxonomy(int $filter_id, string $taxonomy): array {
      return $this->get_selected_terms_by_taxonomy($filter_id, $taxonomy);
   }



}


