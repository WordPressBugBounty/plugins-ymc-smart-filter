<?php

defined( 'ABSPATH' ) || exit; 

use YMCFilterGrids\FG_Data_Store as Data_Store;

global $post;

$post_id = isset( $post->ID ) ? (int) $post->ID : 0;

// Post Types
$post_types = $post_id > 0 ? Data_Store::get_meta_value( $post_id, 'ymc_fg_post_types' ) : [];
$post_types = is_array( $post_types ) ? array_values( array_filter( array_map( 'sanitize_key', $post_types ) ) ) : [];

$post_type_labels = [];
foreach ( $post_types as $pt_slug ) {
   $pt_object = get_post_type_object( $pt_slug );
   if ( $pt_object && ! empty( $pt_object->labels->name ) ) {
      $post_type_labels[] = $pt_object->labels->name;
   } else {
      $post_type_labels[] = $pt_slug;
   }
}

// Source Query
$enable_advanced_query = $post_id > 0 ? Data_Store::get_meta_value( $post_id, 'ymc_fg_enable_advanced_query' ) : 'no';
$advanced_query_type   = $post_id > 0 ? Data_Store::get_meta_value( $post_id, 'ymc_fg_advanced_query_type' ) : 'advanced';
$selected_posts        = $post_id > 0 ? Data_Store::get_meta_value( $post_id, 'ymc_fg_selected_posts' ) : [];
$selected_posts        = is_array( $selected_posts ) ? array_filter( $selected_posts ) : [];

$source_label = esc_html__( 'Standard', 'ymc-smart-filter' );
$source_badge = 'standard';

if ( 'yes' === $enable_advanced_query ) {
   $source_label = ( 'callback' === $advanced_query_type )
      ? esc_html__( 'Advanced Query', 'ymc-smart-filter' )
      : esc_html__( 'Advanced Query', 'ymc-smart-filter' );
   $source_badge = 'custom';
} elseif ( ! empty( $selected_posts ) ) {
   $source_label = sprintf(
      /* translators: %d: number of selected posts */
      esc_html__( 'Selected Posts (%d)', 'ymc-smart-filter' ),
      count( $selected_posts )
   );
   $source_badge = 'selected';
}

// Taxonomies
$raw_taxonomies = $post_id > 0 ? Data_Store::get_meta_value( $post_id, 'ymc_fg_taxonomies' ) : [];
$tax_slugs      = is_array( $raw_taxonomies )
	? array_values( array_unique( array_filter( array_map( 'sanitize_key', $raw_taxonomies ) ) ) )
	: [];

$taxonomy_labels = [];
foreach ( $tax_slugs as $tax_slug ) {
	$tax_object = get_taxonomy( $tax_slug );
	if ( $tax_object && ! empty( $tax_object->labels->name ) ) {
		$taxonomy_labels[] = $tax_object->labels->name;
	} else {
		$taxonomy_labels[] = $tax_slug;
	}
}

// Disable / Enable Filter
$raw_filter_hidden = $post_id > 0 ? Data_Store::get_meta_value( $post_id, 'ymc_fg_filter_hidden' ) : 'no';
$is_filter_hidden  = ( 'yes' === $raw_filter_hidden );

$filter_bar_text  = $is_filter_hidden
	? esc_html__( 'Hidden', 'ymc-smart-filter' )
	: esc_html__( 'Visible', 'ymc-smart-filter' );
$filter_bar_class = $is_filter_hidden
	? 'ymc-summary__badge--warning'
	: 'ymc-summary__badge--active';


// Filter Template:
$raw_filter_type = $post_id > 0 ? Data_Store::get_meta_value( $post_id, 'ymc_fg_filter_type' ) : 'default';
$filter_type     = is_string( $raw_filter_type ) && '' !== trim( $raw_filter_type )
	? sanitize_key( $raw_filter_type )
	: 'default';

$filter_template_labels = [
	'default' => esc_html__( 'Default', 'ymc-smart-filter' ),
];

$filter_template_label = $filter_template_labels[ $filter_type ] ?? ucwords( str_replace( [ '_', '-' ], ' ', $filter_type ) );

// Post Layout
$raw_post_layout = $post_id > 0 ? Data_Store::get_meta_value( $post_id, 'ymc_fg_post_layout' ) : 'layout_standard';
$post_layout     = is_string( $raw_post_layout ) && '' !== trim( $raw_post_layout )
	? sanitize_key( $raw_post_layout )
	: 'layout_standard';

$post_layout_labels = [
	'layout_standard' => esc_html__( 'Standard', 'ymc-smart-filter' ),
	'layout_carousel' => esc_html__( 'Carousel', 'ymc-smart-filter' ),
	'layout_custom'   => esc_html__( 'Custom', 'ymc-smart-filter' ),
];

$post_layout_label = $post_layout_labels[ $post_layout ]
	?? ucwords( str_replace( [ 'layout_', '_', '-' ], [ '', ' ', ' ' ], $post_layout ) );

// Pagination
$raw_per_page     = $post_id > 0 ? Data_Store::get_meta_value( $post_id, 'ymc_fg_per_page' ) : '4';
$per_page         = ( is_numeric( $raw_per_page ) && (int) $raw_per_page > 0 ) ? (int) $raw_per_page : 4;

$pagination_hidden = $post_id > 0 ? Data_Store::get_meta_value( $post_id, 'ymc_fg_pagination_hidden' ) : 'no';
$is_hidden_pag     = ( 'yes' === $pagination_hidden );

$raw_pag_type = $post_id > 0 ? Data_Store::get_meta_value( $post_id, 'ymc_fg_pagination_type' ) : 'numeric';
$pag_type     = is_string( $raw_pag_type ) && '' !== trim( $raw_pag_type )
	? sanitize_key( $raw_pag_type )
	: 'numeric';

$pagination_labels = [
	'numeric'   => esc_html__( 'Numeric Pagination', 'ymc-smart-filter' ),
	'load_more' => esc_html__( 'Load More', 'ymc-smart-filter' ),
	'scroll'    => esc_html__( 'Infinite Scroll', 'ymc-smart-filter' ),
];

if ( $is_hidden_pag ) {
	$pagination_label = esc_html__( 'No Pagination', 'ymc-smart-filter' );
	$pagination_class = 'ymc-summary__badge--inactive';
} else {
	$pagination_label = $pagination_labels[ $pag_type ]
		?? ucwords( str_replace( [ '_', '-' ], ' ', $pag_type ) );
	$pagination_class = 'ymc-summary__badge--active';
}

// Columns
$default_columns = [
	'xl'  => 4,
	'lg'  => 4,
	'md'  => 3,
	'sm'  => 2,
	'xs'  => 1,
	'xxs' => 1,
];

$raw_columns = $post_id > 0 ? Data_Store::get_meta_value( $post_id, 'ymc_fg_post_columns_layout' ) : $default_columns;
$columns     = is_array( $raw_columns ) ? array_merge( $default_columns, $raw_columns ) : $default_columns;

$desktop_cols = isset( $columns['lg'] ) && (int) $columns['lg'] > 0 ? (int) $columns['lg'] : ( (int) ( $columns['xl'] ?? 4 ) ?: 4 );
$tablet_cols  = isset( $columns['md'] ) && (int) $columns['md'] > 0 ? (int) $columns['md'] : ( (int) ( $columns['sm'] ?? 2 ) ?: 2 );
$mobile_cols  = isset( $columns['xs'] ) && (int) $columns['xs'] > 0 ? (int) $columns['xs'] : ( (int) ( $columns['xxs'] ?? 1 ) ?: 1 );

$columns_summary = sprintf(
	'D:%d / T:%d / M:%d',
	$desktop_cols,
	$tablet_cols,
	$mobile_cols
);

// Search / Sort
$search_enabled = $post_id > 0 ? Data_Store::get_meta_value( $post_id, 'ymc_fg_search_enable' ) : 'no';
$sort_enabled   = $post_id > 0 ? Data_Store::get_meta_value( $post_id, 'ymc_fg_enable_sort_posts' ) : 'no';

$is_search_on = ( 'yes' === $search_enabled );
$is_sort_on   = ( 'yes' === $sort_enabled );

$search_text  = $is_search_on ? esc_html__( 'ON', 'ymc-smart-filter' ) : esc_html__( 'OFF', 'ymc-smart-filter' );
$search_class = $is_search_on ? 'ymc-summary__badge--active' : 'ymc-summary__badge--inactive';

$sort_text  = $is_sort_on ? esc_html__( 'ON', 'ymc-smart-filter' ) : esc_html__( 'OFF', 'ymc-smart-filter' );
$sort_class = $is_sort_on ? 'ymc-summary__badge--active' : 'ymc-summary__badge--inactive';

// Popup
$popup_enabled = $post_id > 0 ? Data_Store::get_meta_value( $post_id, 'ymc_fg_popup_enable' ) : 'no';
$is_popup_on   = ( 'yes' === $popup_enabled );

$popup_text  = $is_popup_on ? esc_html__( 'Enabled', 'ymc-smart-filter' ) : esc_html__( 'Disabled', 'ymc-smart-filter' );
$popup_class = $is_popup_on ? 'ymc-summary__badge--active' : 'ymc-summary__badge--inactive';

?>

<div class="ymc-summary">
   <div class="ymc-summary__body">  

      <section class="ymc-summary__group">
         <h4 class="ymc-summary__group-title">
            <?php echo esc_html__('Query &amp; Content','ymc-smart-filter'); ?>
         </h4>

         <div class="ymc-summary__item">
            <span class="ymc-summary__label"><?php echo esc_html__('Post Type(s):','ymc-smart-filter');  ?></span>
            <div class="ymc-summary__value">
               <?php if ( ! empty( $post_type_labels ) ) : ?>
                  <div class="ymc-summary__chips">
                     <?php foreach ( $post_type_labels as $label ) : ?>
                        <span class="ymc-summary__chip"><?php echo esc_html( $label ); ?></span>
                     <?php endforeach; ?>
                  </div>
               <?php else : ?>
                  <span class="ymc-summary__text"><?php echo esc_html__( 'None selected', 'ymc-smart-filter' ); ?></span>
               <?php endif; ?>
            </div>
         </div>

         <div class="ymc-summary__item">
            <span class="ymc-summary__label"><?php echo esc_html__('Source:','ymc-smart-filter'); ?></span>
            <div class="ymc-summary__value">
               <span class="ymc-summary__text ymc-summary__text--source ymc-summary__text--source-<?php echo esc_attr( $source_badge ); ?>">
                  <?php echo $source_label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
               </span>
            </div>
         </div>

         <div class="ymc-summary__item">
            <span class="ymc-summary__label"><?php echo esc_html__('Taxonomies:','ymc-smart-filter'); ?></span>
            <div class="ymc-summary__value">
               <?php if ( ! empty( $taxonomy_labels ) ) : ?>
                  <div class="ymc-summary__tags">
                     <?php foreach ( $taxonomy_labels as $tax_label ) : ?>
                        <span class="ymc-summary__tag">
                           <span class="ymc-summary__tag-icon" aria-hidden="true"></span>
                           <span class="ymc-summary__tag-text"><?php echo esc_html( $tax_label ); ?></span>
                        </span>
                     <?php endforeach; ?>
                  </div>
               <?php else : ?>
                  <span class="ymc-summary__text ymc-summary__text--muted">
                     <?php echo esc_html__( 'None', 'ymc-smart-filter' ); ?>
                  </span>
               <?php endif; ?>
            </div>
         </div>
      </section>
     
      <section class="ymc-summary__group">
         <h4 class="ymc-summary__group-title"><?php echo esc_html__('Display &amp; Layout','ymc-smart-filter'); ?></h4>

         <div class="ymc-summary__item">
            <span class="ymc-summary__label"><?php echo esc_html__( 'Filter Bar:', 'ymc-smart-filter' ); ?></span>
            <div class="ymc-summary__value ymc-summary__value--inline">
               <span class="ymc-summary__badge <?php echo esc_attr( $filter_bar_class ); ?>">
                  <?php echo esc_html( $filter_bar_text ); ?>
               </span>
            </div>
         </div>

         <div class="ymc-summary__item">
            <span class="ymc-summary__label"><?php echo esc_html__('Filter Template:','ymc-smart-filter'); ?></span>
            <div class="ymc-summary__value">
               <span class="ymc-summary__text ymc-summary__text--template">
                  <?php echo esc_html( $filter_template_label ); ?>
               </span>
            </div>
         </div>

         <div class="ymc-summary__item">
            <span class="ymc-summary__label"><?php echo esc_html__( 'Post Layout:', 'ymc-smart-filter' ); ?></span>
            <div class="ymc-summary__value">
               <span class="ymc-summary__text ymc-summary__text--layout">
                  <?php echo esc_html( $post_layout_label ); ?>
               </span>
            </div>
         </div>

         <div class="ymc-summary__item">
            <span class="ymc-summary__label"><?php echo esc_html__( 'Items / Page:', 'ymc-smart-filter' ); ?></span>
            <div class="ymc-summary__value ymc-summary__value--inline">
               <span class="ymc-summary__text ymc-summary__text--per-page">
                  <?php echo esc_html( (string) $per_page ); ?>
               </span>
               <span class="ymc-summary__badge <?php echo esc_attr( $pagination_class ); ?>">
                  <?php echo esc_html( $pagination_label ); ?>
               </span>
            </div>
         </div>

         <div class="ymc-summary__item">
            <span class="ymc-summary__label"><?php echo esc_html__( 'Columns:', 'ymc-smart-filter' ); ?></span>
            <div class="ymc-summary__value">
               <span class="ymc-summary__text ymc-summary__text--columns">
                  <?php echo esc_html( $columns_summary ); ?>
               </span>
            </div>
         </div>
      </section>
    
      <section class="ymc-summary__group">
         <h4 class="ymc-summary__group-title"><?php echo esc_html__('Features &amp; Placement','ymc-smart-filter'); ?></h4>

         <div class="ymc-summary__item">
            <span class="ymc-summary__label"><?php echo esc_html__('Search / Sorting:','ymc-smart-filter'); ?></span>
            <div class="ymc-summary__value ymc-summary__value--inline">
               <span
                  class="ymc-summary__badge <?php echo esc_attr( $search_class ); ?>"
                  title="<?php echo esc_attr__( 'Search', 'ymc-smart-filter' ); ?>">
                  <?php echo esc_html( sprintf( /* translators: %s: ON or OFF */ __( 'Search: %s', 'ymc-smart-filter' ), $search_text ) ); ?>
               </span>
               <span
                  class="ymc-summary__badge <?php echo esc_attr( $sort_class ); ?>"
                  title="<?php echo esc_attr__( 'Sorting', 'ymc-smart-filter' ); ?>">
                  <?php echo esc_html( sprintf( /* translators: %s: ON or OFF */ __( 'Sort: %s', 'ymc-smart-filter' ), $sort_text ) ); ?>
               </span>
            </div>
         </div>

         <div class="ymc-summary__item">
            <span class="ymc-summary__label"><?php echo esc_html__('Popup:','ymc-smart-filter'); ?></span>
            <div class="ymc-summary__value ymc-summary__value--inline">
               <span class="ymc-summary__badge <?php echo esc_attr( $popup_class ); ?>">
                  <?php echo $popup_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
               </span>
            </div>
         </div>         

         <div class="ymc-summary__item ymc-summary__item--shortcode">
            <span class="ymc-summary__label"><?php echo esc_html__('Shortcode:','ymc-smart-filter'); ?></span>
            <div class="ymc-summary__value">
               <code class="ymc-summary__shortcode">[ymc_filter id="<?php echo esc_attr( $post_id ); ?>"]</code>
            </div>
         </div>
      </section>
      
   </div>
</div>


