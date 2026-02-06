<?php 

defined( 'ABSPATH' ) || exit; 

$edit_url = get_edit_post_link( $post_id );
$view_url = get_permalink( $post_id );

?>

<tr>
   <td class="col-title"><strong>
      <a href="<?php echo esc_url( $view_url ); ?>" target="_blank">
      <?php echo esc_html(get_the_title($post_id)); ?></a></strong></td>
   <td><span class="badge badge--type"><?php echo esc_html(get_post_type_object(get_post_type($post_id))->labels->singular_name); ?></span></td>
   <td><span class="badge badge--<?php echo esc_attr(get_post_status($post_id)); ?>">
      <?php echo esc_html(get_post_status_object(get_post_status($post_id))->label); ?></span></td>
   <td><span class="badge badge--lang"><?php echo esc_html(get_bloginfo('language')); ?></span></td>
   <td>
      <span class="badge badge--usage badge--shortcode">
         <?php echo esc_html__( 'Shortcode', 'ymc-smart-filter' ); ?>
      </span>
   </td>
   <td class="col-actions">
      <a href="<?php echo esc_url( $edit_url ); ?>" 
         class="action-icon dashicons dashicons-edit"
         target="_blank"
         aria-label="<?php echo esc_attr__( 'Edit', 'ymc-smart-filter' ); ?>"></a>

      <a href="<?php echo esc_url( $view_url ); ?>"
         class="action-icon dashicons dashicons-admin-links"
         target="_blank"
         aria-label="<?php echo esc_attr__( 'View', 'ymc-smart-filter' ); ?>">
      </a>
   </td>
</tr>
