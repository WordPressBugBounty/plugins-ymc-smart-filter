<?php declare( strict_types = 1 );

namespace YMCFilterGrids\abstracts;

use YMCFilterGrids\frontend\FG_Filter_Flatpickr;
use YMCFilterGrids\interfaces\IFilter;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class FG_Creator_Filter_Flatpickr
 *
 * Creates a new Range Slider.
 *
 * @version  3.10.3
 * @package YMCFilterGrids\abstracts
 */
class FG_Creator_Filter_Flatpickr extends FG_Abstract_Filter {
	public function factoryFilter() : IFilter {
		return new FG_Filter_Flatpickr();
	}
}


