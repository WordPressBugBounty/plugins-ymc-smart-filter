<?php declare( strict_types = 1 );

namespace YMCFilterGrids\abstracts;

use YMCFilterGrids\frontend\FG_Filter_Alphabetical;
use YMCFilterGrids\interfaces\IFilter;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class FG_Creator_Alphabetical
 *
 * @version  3.3.5
 * @package YMCFilterGrids\abstracts
 */
class FG_Creator_Filter_Alphabetical extends FG_Abstract_Filter {
	public function factoryFilter() : IFilter {
		return new FG_Filter_Alphabetical();
	}
}


