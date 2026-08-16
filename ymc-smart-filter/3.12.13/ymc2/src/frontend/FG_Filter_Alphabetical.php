<?php declare( strict_types = 1 );

namespace YMCFilterGrids\frontend;

use YMCFilterGrids\interfaces\IFilter;

defined( 'ABSPATH' ) || exit;

/**
 * Class FG_Filter_Alphabetical
 *
 * @since 3.4.0
 */

class FG_Filter_Alphabetical implements IFilter {

   public function render(int $filter_id, array $tax_name, array $filter_options): string {

      if (empty($filter_id) && empty($filter_options)) {
			return ''; 
		}

      $all_button_label = __( 'All', 'ymc-smart-filter' );
      $letters = range( 'A', 'Z' );     
     
      ob_start(); ?>

         <div class="filter filter-alphabetical filter-<?php echo esc_attr( $filter_id ); ?>"
              data-filter-type="alphabetical">

            <nav class="filter-alphabetical__inner" aria-label="Alphabetical navigation">

               <ul class="filter-alphabetical__list">

                  <li class="filter-alphabetical__item">
                     <button
                        class="filter-alphabetical__btn js-btn-all is-active"
                        type="button"
                        data-letter="all"
                        aria-pressed="true">
                        <?php
                        // phpcs:ignore WordPress
                        echo esc_html( $all_button_label ); ?>
                     </button>
                  </li>

                  <?php foreach ( $letters as $letter ) : ?>

                     <li class="filter-alphabetical__item">
                        <button
                           class="filter-alphabetical__btn"
                           type="button"
                           data-letter="<?php echo esc_attr( $letter ); ?>">
                           <?php echo esc_html( $letter ); ?>
                        </button>
                     </li>

                  <?php endforeach; ?>

               </ul>
               
            </nav>
	        
        </div>

      <?php

		return ob_get_clean();

   }

}
