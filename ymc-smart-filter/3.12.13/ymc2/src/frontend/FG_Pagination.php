<?php declare( strict_types = 1 );

namespace YMCFilterGrids\frontend;

use YMCFilterGrids\FG_Data_Store as Data_Store;

defined( 'ABSPATH' ) || exit;

/**
 * Pagination Class
 *
 * @since 3.0.0
 */
class FG_Pagination {


	/**
	 * @param $query
	 * @param int $paged
	 * @param int $filter_id
	 * @param int $counter
	 * @param array $options
	 *
	 * @return string
	 */
	public static function create_numeric_pagination(
		$query,
		int $paged,
		int $filter_id,
		int $counter,
		array $options = []): string {

		if (!$query) {
			return '<div class="notification notification--error">'
			       . esc_html__('Error creating pagination.', 'ymc-smart-filter') . '</div>';
		}

		if ($query->max_num_pages <= 1) {
			return '';
		}

		$prev_button_text = Data_Store::get_meta_value($filter_id, 'ymc_fg_prev_button_text');
		$next_button_text = Data_Store::get_meta_value($filter_id, 'ymc_fg_next_button_text');
		$number_format    = Data_Store::get_meta_value($filter_id, 'ymc_fg_pagination_number_format');
		$pagin_mid_size   = Data_Store::get_meta_value($filter_id, 'ymc_fg_pagination_mid_size');
      $pagin_end_size   = Data_Store::get_meta_value($filter_id, 'ymc_fg_pagination_end_size');

      // defaults + sanitize
      $pagin_mid_size = $pagin_mid_size !== '' ? (int) $pagin_mid_size : 2;
      $pagin_end_size = $pagin_end_size !== '' ? (int) $pagin_end_size : 1;

      // limits
      $pagin_mid_size = max(1, min(5, $pagin_mid_size));
      $pagin_end_size = max(1, min(3, $pagin_end_size));

		$prev_button_text = $prev_button_text ?: esc_html__( 'Prev', 'ymc-smart-filter' );
		$next_button_text = $next_button_text ?: esc_html__( 'Next', 'ymc-smart-filter' );

		$prev_button_text = apply_filters('ymc/pagination/prev_text',  $prev_button_text);
		$prev_button_text = apply_filters('ymc/pagination/prev_text_'. $filter_id, $prev_button_text);
		$prev_button_text = apply_filters('ymc/pagination/prev_text_'. $filter_id .'_'. $counter, $prev_button_text);

		$next_button_text = apply_filters('ymc/pagination/next_text',  $next_button_text);
		$next_button_text = apply_filters('ymc/pagination/next_text_'. $filter_id, $next_button_text);
		$next_button_text = apply_filters('ymc/pagination/next_text_'. $filter_id .'_'. $counter, $next_button_text);

		$pagination = paginate_links([
			'base'      => '%_%',
			'type'      => 'array',
			'total'     => $query->max_num_pages,
			'format'    => '#page=%#%',
			'current'   => max(1, $paged),
			'prev_text' => $prev_button_text,
			'next_text' => $next_button_text,
			'mid_size'  => $pagin_mid_size,
         'end_size'  => $pagin_end_size
		]);

		if (empty($pagination)) {
			return '';
		}

		ob_start();
		echo '<ul class="pagination pagination--numeric format-'. esc_attr($number_format) .' js-pagination-numeric">';

		foreach ($pagination as $page) {
			$class = 'pagination__item';

			if (preg_match('/<span[^>]*>.*<\/span>/', $page)) {
				$class .= ' current-item';
			} elseif (stripos($page, 'class="prev') !== false) {
				$class .= ' prev-item';
			} elseif (stripos($page, 'class="next') !== false) {
				$class .= ' next-item';
			}
			// phpcs:ignore WordPress
			printf('<li class="%s">%s</li>', esc_attr($class), $page);
		}

		echo '</ul>';
		return ob_get_clean();
	}


	/**
	 * @param $query
	 * @param int $filter_id
	 * @param int $counter
	 * @param array $options
	 *
	 * @return string
	 */
	public static function create_load_more_pagination(
		$query,
		int $filter_id,
		int $counter,
		array $options = [] ): string {

		if ( ! $query ) {
			return sprintf(
				'<div class="notification notification--error">%s</div>',
				esc_html__( 'Error creating pagination.', 'ymc-smart-filter' )
			);
		}

		if ( $query->max_num_pages <= 1 ) {
			return '';
		}

		$load_more_text = Data_Store::get_meta_value( $filter_id, 'ymc_fg_load_more_text' );
		$load_more_text = $load_more_text ?: esc_html__( 'Load More', 'ymc-smart-filter' );

		$load_more_text = apply_filters('ymc/pagination/load_more_text',  $load_more_text);
		$load_more_text = apply_filters('ymc/pagination/load_more_text_'. $filter_id,  $load_more_text);
		$load_more_text = apply_filters('ymc/pagination/load_more_text_'. $filter_id .'_'. $counter,  $load_more_text);

		ob_start();
		?>
		<div class="pagination pagination-load-more">
			<button class="button button--secondary js-btn-load-more">
				<i class="fa-solid fa-chevron-down"></i>
				<span class="button__text"><?php echo esc_html( $load_more_text ); ?></span>
			</button>
		</div>
		<?php

		return ob_get_clean();
	}

}