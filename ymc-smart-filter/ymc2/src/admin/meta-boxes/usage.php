<?php
   
   use YMCFilterGrids\FG_Template as Template;

   if (!defined( 'ABSPATH')) exit;

?>

<div class="inner">

   <div class="header"><?php echo esc_html($section_name); ?></div>

   <div class="body">
      <div class="headline js-headline-accordion" data-hash="filter_usege">
         <span class="inner">
               <i class="dashicons dashicons-visibility"></i>
               <span class="text"><?php esc_html_e('Filter usage', 'ymc-smart-filter'); ?></span>
         </span>
         <i class="fa-solid fa-chevron-down js-icon-accordion"></i>
      </div>

      <div class="form-wrap">

         <?php

            $filter_id = $post_id;            
           
            $cache_key = 'ymc_fg_usage_summary_' . $filter_id;

            $post_types = get_post_types( [ 'public' => true ], 'names' );           
            $post_types = array_diff( $post_types, [ 'attachment', 'nav_menu_item' ] );         
            $post_types = apply_filters( 'ymc_fg_usage_post_types', $post_types );           
            $summary    = get_transient( $cache_key );
            
            if ( false === $summary ) {

               $summary_args = [
                  'post_type'      => $post_types,
                  'post_status'    => [ 'publish', 'draft', 'private', 'pending', 'future' ],
                  'posts_per_page' => -1,
                  'fields'         => 'ids',
                  'no_found_rows'  => true,
                  'orderby'        => 'none',
                  'meta_query'     => [
                     [
                        'key'     => 'ymc_fg_filter_usage',
                        'value'   => 'i:' . $filter_id . ';',
                        'compare' => 'LIKE',
                     ],
                  ],
               ];

               $summary_query = new \WP_Query( $summary_args );

               $total_items = 0;
               $type_counts = [];
               $languages   = [];

               if ( ! empty( $summary_query->posts ) ) {

                  $total_items = count( $summary_query->posts );

                  foreach ( $summary_query->posts as $used_post_id ) {

                     // Post type
                     $post_type = get_post_type( $used_post_id );
                     if ( $post_type ) {
                        $type_counts[ $post_type ] = ( $type_counts[ $post_type ] ?? 0 ) + 1;
                     }

                     // Language
                     if ( function_exists( 'pll_get_post_language' ) ) {
                        $lang = pll_get_post_language( $used_post_id );
                     } elseif ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
                        $details = apply_filters( 'wpml_post_language_details', null, $used_post_id );
                        $lang = $details['language_code'] ?? '';
                     } else {
                        $lang = get_bloginfo( 'language' );
                     }

                     if ( $lang ) {
                        $languages[ strtoupper( $lang ) ] = true;
                     }
                  }
               }
             
               $summary = [
                  'total'     => $total_items,
                  'types'     => $type_counts,
                  'languages' => array_keys( $languages )
               ];
               
               set_transient( $cache_key, $summary, DAY_IN_SECONDS );
            }
           
            $total_items  = $summary['total'] ?? 0;
            $type_counts  = $summary['types'] ?? [];
            $langs_string = ! empty( $summary['languages'] )
               ? implode( ', ', $summary['languages'] )
               : esc_html__( 'N/A', 'ymc-smart-filter' );             
      
         ?>

         <fieldset class="form-group form-group--with-bg filter-usage">
            <div class="group-elements">

               <legend class="form-legend">
                  <?php esc_html_e( 'Summary', 'ymc-smart-filter' ); ?>
               </legend>

               <div class="field-description">
                  <?php esc_html_e(
                     'Overview of where this filter is currently used across your site content.',
                     'ymc-smart-filter'); ?>
               </div>               

               <div class="group-elements">

                  <p class="info">
                     <strong><?php esc_html_e( 'Used on:', 'ymc-smart-filter' ); ?></strong>
                     <?php echo esc_html( $total_items ); ?> item(s)
                  </p>

                  <?php foreach ( $type_counts as $type => $count ) :
                     $pt = get_post_type_object( $type );
                     if ( ! $pt ) {
                        continue;
                     }
                  ?>
                     <p class="info">
                        <strong><?php echo esc_html( $pt->labels->singular_name ); ?>:</strong>
                        <?php echo esc_html( $count ); ?>
                     </p>
                  <?php endforeach; ?>

                  <p class="info">
                     <strong><?php esc_html_e( 'Languages:', 'ymc-smart-filter' ); ?></strong>
                     <?php echo esc_html( $langs_string ); ?>
                  </p>

               </div>
            </div>
         </fieldset>

         <fieldset class="form-group form-group--with-bg">
            <div class="group-elements">

               <legend class="form-legend">
                  <?php esc_html_e('Usage Table','ymc-smart-filter'); ?></legend>

                  <div class="field-description">
                     <?php echo wp_kses_post('Displays where this filter is used in posts and pages.
                     <strong>Shortcodes added directly in PHP templates are not tracked.</strong>', 'ymc-smart-filter') ?>
                  </div>

                  <div class="field-description">
                  <?php esc_html_e('Use this to scan older posts and sync filter usage data.', 'ymc-smart-filter'); ?></div>
  
                  <div class="spacer-10"></div>
                  
                  <button class="button button--primary js-button-scan-content" 
                  type="button"
                  aria-label="Scan existing content to sync filter usage">
                  <span class="dashicons dashicons-search"></span>
                  <?php esc_html_e('Scan existing content', 'ymc-smart-filter'); ?></button>                  

                  <div class="scan-status js-scan-status"></div>                   

               <div class="usage-table-wrap">
                  <?php 
                     $paged = 1;
                     $per_page = 20; 

                     $args = [
                        'post_type'      => $post_types,
                        'post_status'    => [ 'publish', 'draft', 'private', 'pending', 'future' ],
                        'posts_per_page' => $per_page,
                        'paged'          => $paged,
                        'meta_query'     => [
                           [
                              'key'     => 'ymc_fg_filter_usage',
                              'value'   => 'i:' . absint( $filter_id ) . ';',
                              'compare' => 'LIKE',
                           ],
                        ],
                        'orderby'        => 'title',
                        'order'          => 'ASC',
                     ];

                     $query = new \WP_Query($args);                
                  ?>

                  <div class="usage-table-stats">
                     <?php esc_html_e('Total:', 'ymc-smart-filter'); ?> <?php echo esc_html( $query->found_posts ); ?></div>
                  <table class="usage-table">
                     <thead>
                        <tr>
                           <th class="title"><?php esc_html_e('Title', 'ymc-smart-filter'); ?></th>
                           <th class="type"><?php esc_html_e('Type', 'ymc-smart-filter'); ?></th>
                           <th class="status"><?php esc_html_e('Status', 'ymc-smart-filter'); ?></th>
                           <th class="lang"><?php esc_html_e('Lang', 'ymc-smart-filter'); ?></th>
                           <th class="usage"><?php esc_html_e('Usage', 'ymc-smart-filter'); ?></th>
                           <th class="is-actions"><?php esc_html_e('Actions', 'ymc-smart-filter'); ?></th>
                        </tr>
                     </thead>
                     <tbody class="js-tbody-usage-table" data-filter-id="<?php echo esc_attr( $filter_id ); ?>">
                     <?php

                     if ($query->have_posts()) {

                        while ($query->have_posts()) {                           
                           $query->the_post();
                           $post_id  = get_the_ID();                         

                           Template::render(YMC_ABSPATH . '/src/admin/php-templates/tmpl-usage-filter.php',
                              [
                                 'post_id' => $post_id
                              ]);
                        }
                     } 
                     else {
                        echo '<tr><td colspan="6" class="no-usage-data text-center">' . esc_html__('No usage data found yet', 'ymc-smart-filter') . '</td></tr>';
                     }                     
                     ?> 
                     </tbody>
                  </table>

                  <?php if ( $query->max_num_pages > 1 ) : ?>
                     <div class="ymc-pagination js-pagination-load-usage-pages"
                        data-current="<?php echo esc_attr( $paged ); ?>"
                        data-max="<?php echo esc_attr( $query->max_num_pages ); ?>">

                        <button type="button" class="button ymc-prev" <?php disabled( $paged <= 1 ); ?>>
                           <?php esc_html_e( 'Previous', 'ymc-smart-filter' ); ?>
                        </button>

                        <span class="ymc-page-info">
                           <?php echo esc_html( $paged . ' / ' . $query->max_num_pages ); ?>
                        </span>

                        <button type="button" class="button ymc-next" <?php disabled( $paged >= $query->max_num_pages ); ?>>
                           <?php esc_html_e( 'Next', 'ymc-smart-filter' ); ?>
                        </button>
                     </div>
                  <?php endif; ?>                   
                  
               </div>

            </div>           

         </fieldset>
        
      </div>

   </div>

</div>



