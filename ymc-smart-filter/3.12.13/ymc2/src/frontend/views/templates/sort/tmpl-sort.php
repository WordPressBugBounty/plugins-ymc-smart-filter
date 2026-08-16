<?php
/**
 * Template Container: Sort
 */

if (!defined( 'ABSPATH' )) exit;

$sortable_fields = [
	'ID'             => __( 'ID', 'ymc-smart-filter' ),
	'author'         => __( 'Author', 'ymc-smart-filter' ),
	'title'          => __( 'Title', 'ymc-smart-filter' ),
	'name'           => __( 'Name', 'ymc-smart-filter' ),
	'date'           => __( 'Date', 'ymc-smart-filter' ),
	'modified'       => __( 'Modified', 'ymc-smart-filter' ),
	'type'           => __( 'Type', 'ymc-smart-filter' ),
	'parent'         => __( 'Parent', 'ymc-smart-filter' ),
	'rand'           => __( 'Random', 'ymc-smart-filter' ),
	'comment_count'  => __( 'Comment count', 'ymc-smart-filter' ),
	'menu_order'     => __( 'Menu order', 'ymc-smart-filter' ),
	'meta_value'     => __( 'Meta value', 'ymc-smart-filter' ),
	'meta_value_num' => __( 'Meta value num', 'ymc-smart-filter' ),
];


?>

<div class="filter-posts-sort">
    <div class="sort-posts-dropdown js-sort-dropdown">
        <div class="dropdown-toggle js-sort-dropdown-toggle">
            <span class="selected-value js-selected-value"><?php echo esc_html($sort_dropdown_label); ?></span>
            <span class="dropdown-arrow"></span>
        </div>
        <ul class="dropdown-menu js-sort-dropdown-menu">
            <li class="sort-item js-sort-item" data-orderby="title" data-order="DESC"><?php esc_html_e('Default sorting', 'ymc-smart-filter'); ?></li>
			<?php foreach ($sortable_fields as $key => $label) :
				if (in_array($key, $allowed_sort_fields, true)) : ?>
                    <li class="sort-item js-sort-item" data-orderby="<?php echo esc_attr($key); ?>" data-order="ASC"><?php echo esc_html($label); ?></li>
				<?php endif;
			endforeach; ?>
        </ul>
        <input class="js-sort-order-input" type="hidden" name="post_sort_order" value="">
    </div>
</div>




