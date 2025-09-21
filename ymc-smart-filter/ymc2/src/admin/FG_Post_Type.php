<?php declare( strict_types = 1 );

namespace YMCFilterGrids\admin;

defined( 'ABSPATH' ) || exit;

/**
 * Post Type Class.
 *
 * @since 3.0.0
 */
class FG_Post_Type {

	private const post_type = 'ymc_filters';

	/**
	 * Hook in methods.
	 */
	public static function init() : void {
		add_action('init', array( __CLASS__, 'register_post_types'), 5 );
	}


	/**
	 * Register core post types.
	 */
	public static function register_post_types() {

		if ( post_type_exists( self::post_type) ) return;

		$icon_url = plugin_dir_url(dirname( __DIR__, 2 )) . 'ymc2/assets/images/icon-20x20.svg';

		register_post_type(
			'ymc_filters',
			array(
				'labels'                 => array(
					'name'               => __( 'Filter & Grids', 'ymc-smart-filter' ),
					'singular_name'      => __( 'Filter & Grids', 'ymc-smart-filter' ),
					'add_new'            => __( 'Add Filter', 'ymc-smart-filter' ),
					'add_new_item'       => __( 'Add New Filter', 'ymc-smart-filter' ),
					'edit_item'          => __( 'Edit Filter', 'ymc-smart-filter' ),
					'new_item'           => __( 'New Filter', 'ymc-smart-filter' ),
					'view_item'          => __( 'View Filter', 'ymc-smart-filter' ),
					'search_items'       => __( 'Search Filters', 'ymc-smart-filter' ),
					'not_found'          => __( 'No filters found', 'ymc-smart-filter' ),
					'not_found_in_trash' => __( 'No filters found in Trash', 'ymc-smart-filter' ),
					'all_items'          => __( 'All Filters', 'ymc-smart-filter' ),
				),
				'public'              => false,
				'hierarchical'        => false,
				'exclude_from_search' => true,
				'show_ui'             => current_user_can( 'manage_options' ) ? true : false,
				'show_in_admin_bar'   => false,
				'menu_position'       => 7,
				'menu_icon'           => $icon_url,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array('title')
			));

		remove_post_type_support('ymc_filters', 'thumbnail');

		add_filter( 'manage_edit-ymc_filters_columns', function ( $columns ) {

			$columns = array(
				'cb'        => '&lt;input type="checkbox" />',
				'title'     => __('Title', 'ymc-smart-filter'),
				'shortcode' => __('Shortcode', 'ymc-smart-filter'),
				'id'        => __('ID', 'ymc-smart-filter'),
				'date'      => __('Date', 'ymc-smart-filter')
			);

			return $columns;
		});

		add_action( 'manage_ymc_filters_posts_custom_column', function ($column, $post_id) {
			switch( $column ) {
				case 'shortcode' :
					echo '<input type="text" onclick="this.select();" value="[ymc_filter id=&quot;'.esc_attr($post_id).'&quot;]" readonly="">';
					break;
				case 'id' :
					echo esc_attr($post_id);
					break;
				default :
					break;
			}
		}, 10, 2);
	}

}

