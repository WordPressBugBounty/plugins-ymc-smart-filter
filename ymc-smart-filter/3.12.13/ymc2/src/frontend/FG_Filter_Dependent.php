<?php declare( strict_types = 1 );

namespace YMCFilterGrids\frontend;

use YMCFilterGrids\abstracts\FG_Abstract_Filter_Impl;
use YMCFilterGrids\FG_Data_Store as Data_Store;
use YMCFilterGrids\interfaces\IFilter;

defined( 'ABSPATH' ) || exit;


/**
 * FG_Filter_Dependent Class
 * Dependent (Cascading) taxonomy filter
 *
 * @package YMCFilterGrids
 * @since 3.1.0
 */
class FG_Filter_Dependent extends FG_Abstract_Filter_Impl implements IFilter {

	/**
	 * @param int $filter_id
	 * @param array $tax_name
	 * @param array $filter_options
	 *
	 * @return string
	 */
	public function render( int $filter_id, array $tax_name, array $filter_options ): string {

		if (empty($filter_id) && empty($filter_options)) {
			return '';
		}

		$placement = $filter_options['placement'];
		$is_multiple_mode = Data_Store::get_meta_value($filter_id, 'ymc_fg_selection_mode');
		$settings = Data_Store::get_meta_value($filter_id, 'ymc_fg_filter_dependent_settings');

		// normalize $settings to array if it's stored as JSON / serialized string
		if (is_string($settings)) {
			$decoded = json_decode($settings, true);
			if (is_array($decoded)) {
				$settings = $decoded;
			} else {
				$maybe = maybe_unserialize($settings);
				if (is_array($maybe)) {
					$settings = $maybe;
				}
			}
		}

		$settings = is_array($settings) ? $settings : [];

		// ensure tax_settings is an array
		if (isset($settings['tax_settings']) && !is_array($settings['tax_settings'])) {
			$raw = $settings['tax_settings'];
			$decoded = is_string($raw) ? json_decode($raw, true) : null;
			if (is_array($decoded)) {
				$settings['tax_settings'] = $decoded;
			} else {
				$maybe = maybe_unserialize($raw);
				$settings['tax_settings'] = is_array($maybe) ? $maybe : [];
			}
		}

		$sequence = array_map('trim', explode(',', (string) $settings['tax_sequence']));
		$display_all = !empty($settings['display_all_levels']);

		ob_start(); ?>

        <div class="filter filter-dependent filter-dependent-<?php echo esc_attr($placement); ?> filter-<?php echo esc_attr($filter_id); ?>"
             data-filter-type="dependent"
             data-selection-mode="<?php echo esc_attr( $is_multiple_mode ); ?>"
             data-display-all-levels="<?php echo $display_all ? 'true' : 'false'; ?>"
             data-update-mode="<?php echo esc_attr($settings['update_mode']); ?>"
             data-sequence="<?php echo esc_attr($settings['tax_sequence']); ?>">
             <div class="filter-dependent-inner">
                 <?php
                     if (empty($settings['tax_sequence'])) {
                         return '<div class="notification notification--warning">'. __('No sequence defined for dependent filter', 'ymc-smart-filter').'</div>';
                     }
                 ?>
	             <?php if ($display_all) : ?>
		            <?php foreach ($sequence as $index => $taxonomy) : ?>
			            <?php
			            $terms = ($index === 0)
				            ? $this->get_root_terms($taxonomy, $settings) : [];

			            $tax_obj = get_taxonomy($taxonomy);
			            $tax_label = $tax_obj && isset($tax_obj->labels->name)
				            ? $tax_obj->labels->name
				            : ucfirst($taxonomy);

			             $tax_mode = $this->get_tax_mode($taxonomy, $settings);
			             // phpcs:ignore WordPress
			             echo $this->render_dropdown($taxonomy, $terms, $tax_mode, $tax_label, $index === 0, $filter_id);
			            ?>
		            <?php endforeach; ?>
	             <?php else : ?>
		         <?php
		             $root_terms = $this->get_root_terms($sequence[0], $settings);
		             // phpcs:ignore WordPress
		             echo $this->render_dropdown($sequence[0], $root_terms, $this->get_tax_mode($sequence[0], $settings), $sequence[0], true, $filter_id);
		         ?>
	             <?php endif; ?>
                 <?php if ($settings['update_mode'] === 'apply') : ?>
                    <button class="ymc-dependent__apply-button js-apply-button" data-filter-id="<?php echo esc_attr($filter_id); ?>">
                        <?php esc_html_e('Apply','ymc-smart-filter'); ?></button>
                <?php endif; ?>
             </div>
		</div>

		<?php
		return ob_get_clean();

	}

	/**
     * Get root terms
	 * @param string $taxonomy
	 * @param array $settings
	 *
	 * @return array
	 */
	private function get_root_terms(string $taxonomy, array $settings) : array {
		if ($settings['root_source'] === 'manual' && !empty($settings['root_terms'])) {
			return get_terms([
				'taxonomy' => $taxonomy,
				'include'  => $settings['root_terms'],
				'hide_empty' => false,
			]);
		}

		return get_terms([
			'taxonomy' => $taxonomy,
			'parent'   => 0,
			'hide_empty' => false,
		]);
	}

	/**
     * Render dropdown with terms
	 * @param string $taxonomy
	 * @param array $terms
	 * @param string $placeholder
     * @param string $mode_tax
     * @param bool $is_root
     * @param int $filter_id
	 *
	 * @return string
	 */
	public function render_dropdown(
        string $taxonomy,
        array $terms,
        string $mode_tax,
        string $placeholder = '',
        bool $is_root = false,
        int $filter_id = 0
    ) : string {

		$tax_obj = get_taxonomy($taxonomy);
		$label   = $placeholder ?: ($tax_obj->labels->name ?? ucfirst($taxonomy));

		$post_types = Data_Store::get_meta_value($filter_id, 'ymc_fg_post_types');
		$terms_id = $is_root ? $this->get_terms_id($terms) : [];
		$disabled_class = empty($terms) ? ' is-disabled' : '';
		$filter_id = $filter_id ? : 0;

		$this->get_options($filter_id);

		ob_start(); ?>

        <div class="ymc-dependent js-dependent<?php echo esc_attr($disabled_class); ?>"
             data-taxonomy="<?php echo esc_attr($taxonomy); ?>"
             data-mode="<?php echo esc_attr($mode_tax); ?>"
             data-taxonomy-label="<?php echo esc_attr($label); ?>"
             data-all-terms="<?php echo esc_attr(json_encode($terms_id)); ?>">
             <div class="ymc-dependent__selected js-dependent-selected">
                 <span class="ymc-dependent__label js-dropdown-label">
                    Select <?php echo esc_html($label); ?>
                 </span>
                <span class="ymc-dependent__arrow"></span>
             </div>
             <ul class="ymc-dependent__list">
                <li class="ymc-dependent__close">
                    <button type="button" class="dropdown-close-btn js-dropdown-close-btn" aria-label="Close dropdown">×</button>
                </li>
				<?php if (!empty($terms)) : ?>
					<?php foreach ($terms as $term) :
						if ('false' === $this->get_term_visible( $term->term_id )) {
							continue;
						}

						$term_class = $this->get_term_class( $term->term_id );
						$term_style = $this->get_term_style( $term->term_id );
						$term_name  = $this->get_term_name( $term->term_id );
						$term_name  = ! empty( $term_name ) ? $term_name : $term->name;
						$post_count = $this->get_post_count_by_term_id($term->term_id, [$taxonomy], $post_types);
						$classes = array_filter([
							$term_class
						]);

                        ?>
                        <li class="ymc-dependent__item js-dependent-item <?php echo esc_attr(implode(' ', $classes)); ?>">
                            <label class="ymc-dependent__checkbox">
                                <input type="checkbox" class="js-dependent-checkbox" value="<?php echo esc_attr($term->term_id); ?>">
                                <span class="checkmark"></span>
                                <span class="term-name" <?php echo wp_kses_post( $term_style ); ?>>
                                    <?php echo esc_html($term_name); ?></span>
                                <span class="post-count" <?php echo wp_kses_post( $term_style ); ?>>
                                    (<?php echo esc_html( $post_count ); ?>)</span>
                            </label>
                        </li>
					<?php endforeach; ?>
				<?php else : ?>
                    <li class="ymc-dependent__placeholder">
						<?php echo esc_html__('No terms available','ymc-smart-filter'); ?>
                    </li>
				<?php endif; ?>
            </ul>
        </div>

		<?php
		return ob_get_clean();
	}



	/**
     * Get taxonomy mode
	 * @param string $taxonomy
	 * @param array $settings
	 *
	 * @return string
	 */
	public function get_tax_mode(string $taxonomy, array $settings = []): string {
		// safety: ensure tax_settings is an array
		$tax_settings = $settings['tax_settings'] ?? [];
		if (!is_array($tax_settings)) {
			// try decode if string (extra guard)
			if (is_string($tax_settings)) {
				$decoded = json_decode($tax_settings, true);
				if (is_array($decoded)) {
					$tax_settings = $decoded;
				} else {
					$maybe = maybe_unserialize($tax_settings);
					$tax_settings = is_array($maybe) ? $maybe : [];
				}
			} else {
				$tax_settings = [];
			}
		}

		foreach ($tax_settings as $tax_setting) {
			if (!is_array($tax_setting)) continue;
			if (isset($tax_setting['taxonomy']) && $tax_setting['taxonomy'] === $taxonomy) {
				return $tax_setting['mode'] ?? 'single';
			}
		}

		return 'single'; // default
	}


	/**
     * Get terms id
	 * @param $terms
	 *
	 * @return array
	 */
    private function get_terms_id(array $terms) : array {
        $term_ids = [];
	    if (!empty($terms)) {
            foreach ($terms as $term) {
                $term_ids[] = $term->term_id;
            }
        }
        return $term_ids;
    }


}