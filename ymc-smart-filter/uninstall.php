<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Uninstall plugin
 * Trigger Uninstall process only if WP_UNINSTALL_PLUGIN is defined
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$postmeta_table = $wpdb->postmeta;
$posts_table    = $wpdb->posts;
$options_table  = $wpdb->options;

$meta_keys = [
	"ymc_cpt_value",
	"ymc_taxonomy",
	"ymc_terms",
	"ymc_choices_posts",
	"ymc_exclude_posts",
	"ymc_terms_align",
	"ymc_preloader_icon",
	"ymc_tax_relation",
	"ymc_tax_sort",
	"ymc_filter_status",
	"ymc_filter_layout",
	"ymc_filter_text_color",
	"ymc_filter_bg_color",
	"ymc_filter_active_color",
	"ymc_post_layout",
	"ymc_post_text_color",
	"ymc_post_bg_color",
	"ymc_post_active_color",
	"ymc_multiple_filter",
	"ymc_empty_post_result",
	"ymc_link_target",
	"ymc_per_page",
	"ymc_term_sort",
	"ymc_sort_status",
	"ymc_multiple_sort",
	"ymc_pagination_type",
	"ymc_pagination_hide",
	"ymc_sort_terms",
	"ymc_order_post_by",
	"ymc_meta_key",
	"ymc_meta_value",
	"ymc_order_post_type",
	"ymc_post_status",
	"ymc_special_post_class",
	"ymc_filter_font",
	"ymc_post_font",
	"ymc_filter_search_status",
	"ymc_search_text_button",
	"ymc_search_placeholder",
	"ymc_autocomplete_state",
	"ymc_scroll_page",
	"ymc_preloader_filters",
	"ymc_preloader_filters_rate",
	"ymc_preloader_filters_custom",
	"ymc_terms_options",
	"ymc_post_animation",
	"ymc_terms_icons",
	"ymc_popup_status",
	"ymc_popup_animation",
	"ymc_popup_animation_origin",
	"ymc_popup_settings",
	"ymc_search_filtered_posts",
	"ymc_advanced_query_status",
	"ymc_query_type",
	"ymc_query_type_custom",
	"ymc_query_type_callback",
	"ymc_desktop_xxl",
	"ymc_desktop_xl",
	"ymc_desktop_lg",
	"ymc_tablet_md",
	"ymc_tablet_sm",
	"ymc_mobile_xs",
	"ymc_suppress_filters",
	"ymc_filter_extra_layout",
	"ymc_post_elements",
	"ymc_pagination_elements",
	"ymc_exact_phrase",
	"ymc_debug_code",
	"ymc_custom_css",
	"ymc_custom_after_js",
	"ymc_carousel_params",
	"ymc_hierarchy_terms",
	"ymc_taxonomy_options",
	"ymc_post_image_size",
	"ymc_image_clickable",
	"ymc_excerpt_truncate_method",
	"ymc_featured_posts",
	"ymc_location_featured_posts",
	"ymc_featured_post_status",
	"ymc_featured_post_layout",
	"ymc_display_terms",
	"ymc_order_term_by",
	"ymc_html_tag_button",

	"ymc_fg_post_types",
	"ymc_fg_taxonomies",
	"ymc_fg_terms",
	"ymc_fg_tax_attrs",
	"ymc_fg_term_attrs",
	"ymc_fg_tax_sort",
	"ymc_fg_term_sort",
	"ymc_fg_selected_posts",
	"ymc_fg_excluded_posts",
	"ymc_fg_tax_relation",
	"ymc_fg_filter_hidden",
	"ymc_fg_filter_type",
	"ymc_fg_post_layout",
	"ymc_fg_selection_mode",
	"ymc_fg_filter_options",
	"ymc_fg_display_terms_mode",
	"ymc_fg_term_sort_direction",
	"ymc_fg_term_sort_field",
	"ymc_fg_pagination_hidden",
	"ymc_fg_pagination_type",
	"ymc_fg_per_page",
	"ymc_fg_filter_all_button",
	"ymc_fg_prev_button_text",
	"ymc_fg_next_button_text",
	"ymc_fg_load_more_text",
	"ymc_fg_post_image_size",
	"ymc_fg_image_clickable",
	"ymc_fg_truncate_post_excerpt",
	"ymc_fg_post_button_text",
	"ymc_fg_target_option",
	"ymc_fg_post_excerpt_length",
	"ymc_fg_post_custom_read_time",
	"ymc_fg_post_order",
	"ymc_fg_post_order_by",
	"ymc_fg_post_status",
	"ymc_fg_no_results_message",
	"ymc_fg_post_animation_effect",
	"ymc_fg_post_display_settings",
	"ymc_fg_order_meta_key",
	"ymc_fg_order_meta_value",
	"ymc_fg_post_order_by_multiple",
	"ymc_fg_post_columns_layout",
	"ymc_fg_post_grid_gap",
	"ymc_fg_popup_enable",
	"ymc_fg_popup_settings",
	"ymc_fg_search_enable",
	"ymc_fg_search_placeholder",
	"ymc_fg_search_mode",
	"ymc_fg_submit_button_text",
	"ymc_fg_results_found_text",
	"ymc_fg_autocomplete_enabled",
	"ymc_fg_search_meta_fields",
	"ymc_fg_exact_phrase",
	"ymc_fg_max_autocomplete_suggestions",
	"ymc_fg_filter_typography",
	"ymc_fg_post_typography",
	"ymc_fg_enable_advanced_query",
	"ymc_fg_advanced_query_type",
	"ymc_fg_advanced_query",
	"ymc_fg_query_allowed_callback",
	"ymc_fg_advanced_suppress_filters",
	"ymc_fg_enable_sort_posts",
	"ymc_fg_post_sortable_fields",
	"ymc_fg_sort_dropdown_label",
	"ymc_fg_custom_container_class",
	"ymc_fg_extra_filter_type",
	"ymc_fg_extra_taxonomy",
	"ymc_fg_custom_css",
	"ymc_fg_custom_js",
	"ymc_fg_preloader_settings",
	"ymc_fg_scroll_to_filters_on_load",
	"ymc_fg_debug_mode",
	"ymc_fg_grid_style",
	"ymc_fg_carousel_settings"

];

$placeholders = implode(', ', array_fill( 0, count( $meta_keys ), '%s' ));
$sql = $wpdb->prepare("DELETE FROM {$postmeta_table} WHERE meta_key IN ($placeholders)", $meta_keys);
$wpdb->query( $sql );

$wpdb->delete( $posts_table, [ 'post_type' => 'ymc_filters' ] );

$wpdb->query( "DELETE FROM {$wpdb->termmeta} WHERE meta_key LIKE 'ymc_fg_count_%'" );

delete_option( 'ymc_plugin_legacy_is' );
delete_option( 'ymc_fg_enable_js_filter_api' );
delete_option( 'ymc_fg_enable_js_masonry' );
delete_option( '_transient_ymc_fg_term_post_counts' );
delete_option( '_transient_timeout_ymc_fg_term_post_counts' );
