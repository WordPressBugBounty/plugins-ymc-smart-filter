<?php

/**
 *
 * Plugin Name:       Filter & Grids
 * Description:       A powerful and flexible plugin to filter and display posts, custom post types, and other content in responsive grid layouts.
 * Version:           3.1.4
 * Author:            YMC
 * Author URI:        https://github.com/YMC-22/Filter-Grids/
 * License:           GPL-2.0-or-later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ymc-smart-filter
 *
 * Copyright 2022-2025 YMC (email : wss.office21@gmail.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**-------------------------------------------------------------------------------
 *    DEFINES
 * -------------------------------------------------------------------------------*/


if ( ! defined('YMC_SMART_FILTER_VERSION') ) {

	define( 'YMC_SMART_FILTER_VERSION', '2.9.71' );
}

if ( ! defined('YMC_SMART_FILTER_DIR') ) {

	define( 'YMC_SMART_FILTER_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined('YMC_SMART_FILTER_URL') ) {

	define( 'YMC_SMART_FILTER_URL', plugins_url( '/', __FILE__ ) );
}


/**-------------------------------------------------------------------------------
 *    LOAD PLUGIN
 * -------------------------------------------------------------------------------*/



/**
 * Include the main YMC_Filter_Grids class.
 * @since 3.0.0
 */
if ( file_exists( YMC_SMART_FILTER_DIR . 'ymc2/YMC_Filter_Grids.php') ) {
	require_once YMC_SMART_FILTER_DIR . 'ymc2/YMC_Filter_Grids.php';
}


if ( class_exists( YMC_Filter_Grids::class, false ) ) {

	/**
	 * Include version plugin
	 * @since 3.0.0
	 */
	if ( 'no' === YMC_Filter_Grids::is_legacy() ) {
		YMC_Filter_Grids::instance();

		/**
		 * Returns the main instance of FG.
		 *
		 * @since  3.0.0
		 * @return YMC_Filter_Grids
		 */
		function YMC() {
			return YMC_Filter_Grids::instance();
		}

	} else {
		/**
		 * Include legacy plugin
		 */
		if ( file_exists( YMC_SMART_FILTER_DIR . 'includes/Plugin.php' ) ) {
			require_once YMC_SMART_FILTER_DIR . 'includes/Plugin.php';
		} else {
			wp_die( 'Filter & Grids: Legacy version file not found.' );
		}
	}
}


