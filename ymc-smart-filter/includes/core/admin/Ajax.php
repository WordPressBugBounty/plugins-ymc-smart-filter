<?php

namespace YMC_Smart_Filters\Core\Admin;

use YMC_Smart_Filters\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {

	/**
	 * @var string
	 */
	public $token;

	/**
	 * Constructor for initializing the Ajax class.
	 */
	public function __construct() {

		// Set the token property
		$this->token = Plugin::$instance->token_b;

		add_action('wp_ajax_ymc_get_taxonomy',array($this, 'ymc_get_taxonomy'));

		add_action('wp_ajax_ymc_get_terms',array($this, 'ymc_get_terms'));

		add_action('wp_ajax_ymc_tax_sort',array($this, 'ymc_tax_sort'));

		add_action('wp_ajax_ymc_term_sort',array($this, 'ymc_term_sort'));

		add_action('wp_ajax_ymc_delete_choices_posts',array($this, 'ymc_delete_choices_posts'));

		add_action('wp_ajax_ymc_delete_choices_icons',array($this, 'ymc_delete_choices_icons'));

		add_action('wp_ajax_ymc_options_icons',array($this, 'ymc_options_icons'));

		add_action('wp_ajax_ymc_options_terms',array($this, 'ymc_options_terms'));

		add_action( 'wp_ajax_ymc_export_settings', array( $this, 'ymc_export_settings'));

		add_action( 'wp_ajax_ymc_import_settings', array( $this, 'ymc_import_settings'));

		add_action( 'wp_ajax_ymc_updated_taxonomy', array( $this, 'ymc_updated_taxonomy'));

		add_action( 'wp_ajax_ymc_taxonomy_options', array( $this, 'ymc_taxonomy_options'));

		add_action( 'wp_ajax_ymc_selected_posts', array( $this, 'ymc_selected_posts'));

		add_action('wp_ajax_ymc_search_posts',array($this, 'ymc_search_posts'));

		add_action('wp_ajax_ymc_search_featured_posts',array($this, 'ymc_search_featured_posts'));

		add_action('wp_ajax_ymc_delete_featured_posts',array($this, 'ymc_delete_featured_posts'));

		add_action('wp_ajax_ymc_loaded_featured_posts',array($this, 'ymc_loaded_featured_posts'));

	}

	/**
	 * Search Posts
	 */
	public function ymc_search_posts() {

		if ( ! isset($_POST['nonce_code']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce_code'])), $this->token) ) exit;

		$post_type = '';
		$phrase = '';

		if( !empty($_POST["cpt"]) ) {
			$post_type   = sanitize_text_field(wp_unslash($_POST["cpt"]));
		}
		if( !empty($_POST["phrase"]) ) {
			$phrase = trim(mb_strtolower(sanitize_text_field(wp_unslash($_POST['phrase']))));
		}

		$post_types = ! empty( $post_type ) ? explode(',', $post_type) : 'post';
		$arr_posts = [];

		$args = [
			'post_type' => $post_types,
			'posts_per_page' => 50,
			'orderby' => 'title',
			'order' => 'asc',
			'sentence' => true,
			's' => $phrase
		];

		$query = new \WP_Query($args);
		$found_posts = $query->found_posts;

		if ( $query->have_posts() ) {
			while ($query->have_posts()) {
				$query->the_post();
				$arr_posts[] = '<li><div class="ymc-rel-item ymc-rel-item-add" data-id="'.get_the_ID().'">
				<span class="postID">ID: '.get_the_ID().'</span> <span class="postTitle">'. get_the_title(get_the_ID()).'</span></div></li>';
			}
			wp_reset_postdata();
		}

		$data = array(
			'found_posts' => $found_posts,
			'lists_posts' => wp_json_encode($arr_posts)
		);

		wp_send_json($data);
	}


	/**
	 * Search Featured Posts
	 */
	public function ymc_search_featured_posts() {

		if ( ! isset($_POST['nonce_code']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce_code'])), $this->token) ) exit;

		$post_type = '';
		$phrase = '';

		if( !empty($_POST["cpt"]) ) {
			$post_type   = sanitize_text_field(wp_unslash($_POST["cpt"]));
		}
		if( !empty($_POST["phrase"]) ) {
			$phrase = trim(mb_strtolower(sanitize_text_field(wp_unslash($_POST['phrase']))));
		}

		$post_types = ! empty( $post_type ) ? explode(',', $post_type) : 'post';
		$arr_posts = [];

		$args = [
			'post_type' => $post_types,
			'posts_per_page' => 50,
			'orderby' => 'title',
			'order' => 'asc',
			'sentence' => true,
			's' => $phrase
		];

		$query = new \WP_Query($args);
		$found_posts = $query->found_posts;

		if ( $query->have_posts() ) {
			while ($query->have_posts()) {
				$query->the_post();
				$arr_posts[] = '<li class="post-item" data-postid="'.esc_attr(get_the_ID()).'">
								<div class="post-id">ID: '.esc_attr(get_the_ID()).'</div>
								<div class="post-title">'.esc_html(get_the_title(get_the_ID())).'</div>
								</li>';
			}

			wp_reset_postdata();
		}

		$data = array(
			'found_posts' => $found_posts,
			'lists_posts' => wp_json_encode($arr_posts)
		);

		wp_send_json($data);

	}


	/**
	 * Posts loaded on scroll
	 */
	public function ymc_selected_posts() {

		if ( !isset($_POST['nonce_code']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce_code'])), $this->token) ) exit;

		$post_type = '';
		$paged = 1;

		if( !empty($_POST["cpt"]) ) {
			$post_type   = sanitize_text_field(wp_unslash($_POST["cpt"]));
		}
		if( !empty($_POST["paged"] ) ) {
			$paged = (int) sanitize_text_field(wp_unslash($_POST['paged']));
			$paged += 1;
		}

		$post_types = ! empty( $post_type ) ? explode(',', $post_type) : 'post';
		$arr_posts = [];

		// Get posts
		$query = new \WP_query([
			'post_type' => $post_types,
			'orderby' => 'title',
			'order' => 'ASC',
			'paged' => $paged,
			'posts_per_page' => 20
		]);
		$found_posts = $query->found_posts;

		if ( $query->have_posts() ) {
			while ($query->have_posts()) {
				$query->the_post();
				$arr_posts[] = '<li><div class="ymc-rel-item ymc-rel-item-add" data-id="'.get_the_ID().'">
				<span class="postID">ID: '.get_the_ID().'</span> <span class="postTitle">'. get_the_title(get_the_ID()).'</span></div></li>';
			}
			wp_reset_postdata();
		}

		$data = array(
			'posts_loaded' => count($arr_posts),
			'found_posts' => $found_posts,
			'lists_posts' => wp_json_encode($arr_posts)
		);

		wp_send_json($data);
	}

	/**
	 * Posts loaded featured on scroll
	 */
	public function ymc_loaded_featured_posts() {

		if ( !isset($_POST['nonce_code']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce_code'])), $this->token) ) exit;

		$post_type = '';
		$paged = 1;

		if( !empty($_POST["cpt"]) ) {
			$post_type   = sanitize_text_field(wp_unslash($_POST["cpt"]));
		}
		if( !empty($_POST["paged"] ) ) {
			$paged = (int) sanitize_text_field(wp_unslash($_POST['paged']));
			$paged += 1;
		}

		$post_types = ! empty( $post_type ) ? explode(',', $post_type) : 'post';
		$arr_posts = [];

		// Get posts
		$query = new \WP_query([
			'post_type' => $post_types,
			'orderby' => 'title',
			'order' => 'ASC',
			'paged' => $paged,
			'posts_per_page' => 20
		]);
		$found_posts = $query->found_posts;

		if ( $query->have_posts() ) {
			while ($query->have_posts()) {
				$query->the_post();
				$arr_posts[] = '<li class="post-item" data-postid="'.esc_attr(get_the_ID()).'">
								<div class="post-id">ID: '.esc_attr(get_the_ID()).'</div>
								<div class="post-title">'.esc_html(get_the_title(get_the_ID())).'</div></li>';
			}
			wp_reset_postdata();
		}

		$data = array(
			'posts_loaded' => count($arr_posts),
			'found_posts' => $found_posts,
			'lists_posts' => wp_json_encode($arr_posts)
		);

		wp_send_json($data);
	}

	/**
	 * Retrieves taxonomy data based on custom post types and sends JSON response.
	 */
	public function ymc_get_taxonomy() {

		if ( ! isset($_POST['nonce_code']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce_code'])), $this->token) ) exit;

		if(isset($_POST["cpt"])) {
			$post_types = sanitize_text_field(wp_unslash($_POST["cpt"]));
			$cpts = !empty( $post_types ) ? explode(',', $post_types) : 'post';
		}
		if(isset($_POST["post_id"])) {
			update_post_meta( (int) $_POST["post_id"], 'ymc_taxonomy', '' );
			update_post_meta( (int) $_POST["post_id"], 'ymc_terms', '' );
		}

		if( is_array($cpts) ) {

			$arr_tax_result = [];

			foreach ( $cpts as $cpt ) {

				$data_object = get_object_taxonomies($cpt, $output = 'objects');

				foreach ($data_object as $val) {

					$arr_tax_result[$val->name] = $val->label;

				}
			}
		}

		update_post_meta( (int) $_POST["post_id"], 'ymc_tax_sort', '' );
		delete_post_meta( (int) $_POST["post_id"], 'ymc_term_sort' );
		delete_post_meta( (int) $_POST["post_id"], 'ymc_choices_posts' );
		delete_post_meta( (int) $_POST["post_id"], 'ymc_featured_posts' );
		delete_post_meta( (int) $_POST["post_id"], 'ymc_terms_options' );
		delete_post_meta( (int) $_POST["post_id"], 'ymc_terms_icons' );
		delete_post_meta( (int) $_POST["post_id"], 'ymc_terms_align' );

		// Get posts
		$query = new \WP_query([
			'post_type' => $cpts,
			'orderby' => 'title',
			'order' => 'ASC',
			'posts_per_page' => 20
		]);

		$arr_posts = [];

		if ( $query->have_posts() ) {

			while ($query->have_posts()) {
				$query->the_post();
				$arr_posts[] = '<li><div class="ymc-rel-item ymc-rel-item-add" data-id="'.get_the_ID().'">
				<span class="postID">ID: '.get_the_ID().'</span> <span class="postTitle">'. get_the_title(get_the_ID()).'</span></div></li>';

				$arr_featured_posts[] = '<li class="post-item" data-postid="'.esc_attr(get_the_ID()).'">
								<div class="post-id">ID: '.esc_attr(get_the_ID()).'</div>
								<div class="post-title">'.esc_html(get_the_title(get_the_ID())).'</div></li>';

			}
			wp_reset_postdata();
		}

		$data = array(
			'data' => wp_json_encode($arr_tax_result),
			'lists_posts' => wp_json_encode($arr_posts),
			'lists_featured_posts' => wp_json_encode($arr_featured_posts),
			'found_posts' => $query->found_posts,
			'posts_loaded' => count($arr_posts)
		);

		wp_send_json($data);
	}

	/**
	 * Retrieves and returns terms based on the provided taxonomy.
	 *
	 * This function first checks the nonce code for security.
	 * Then, it retrieves terms based on the taxonomy and sends the data as a JSON response.
	 */
	public function ymc_get_terms() {

		if ( ! isset($_POST['nonce_code']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce_code'])), $this->token) ) exit;

		$id = '';
		$taxonomy = '';

		if(!empty($_POST["post_id"])) {
			$id = sanitize_text_field(wp_unslash($_POST["post_id"]));
		}
		if(!empty($_POST["taxonomy"])) {
			$taxonomy = sanitize_text_field(wp_unslash($_POST["taxonomy"]));
		}

		$data = [];
		$data['terms'] = [];
		$data['hierarchy'] = [];

		require_once YMC_SMART_FILTER_DIR . '/includes/core/util/variables.php';
		require_once YMC_SMART_FILTER_DIR . '/includes/core/util/helper.php';

		if( $taxonomy )
		{
			$ymc_hierarchy_terms = (bool) $ymc_hierarchy_terms;

			$argsTerms = [
				'taxonomy' => $taxonomy,
				'hide_empty' => false
			];

			// Set parent for terms (Hierarchy Terms Tree)
			( $ymc_hierarchy_terms ) ? $argsTerms['parent'] = 0 : '';

			$terms = get_terms($argsTerms);

			if( $ymc_hierarchy_terms && is_array( $terms ) && ! is_wp_error( $terms ) )
			{
				foreach( $terms as $term )
				{
					$arrayTermsOptions = [
						'style_icon' => $ymc_terms_align,
						'selected_icon' => $ymc_terms_icons,
						'style_term' => $ymc_terms_options,
						'selected_terms' => $terms_sel,
						'order_terms' => $ymc_sort_terms,
						'manual_sort' => null
					];

					$data['hierarchy'][] = hierarchyTermsOutput($term->term_id, $taxonomy, 0, $arrayTermsOptions);
				}
			}

			$data['terms'] = $terms;
		}

		$data = array(
			'data' => $data
		);

		wp_send_json($data);

	}

	/**
	 * Retrieves and updates taxonomy data based on custom post types and sends JSON response.
	 */
	public function ymc_updated_taxonomy() {

		if ( ! isset($_POST['nonce_code']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce_code'])), $this->token) ) exit;

		$arr_tax_result = [];

		$post_id = !empty($_POST["post_id"]) ? (int) sanitize_text_field(wp_unslash($_POST["post_id"])) : 0;

		if( isset($_POST["cpt"]) ) {

			$post_types = sanitize_text_field(wp_unslash($_POST["cpt"]));
			$cpts = !empty( $post_types ) ? explode(',', $post_types) : false;
		}

		if( is_array($cpts) && $post_id > 0 ) {

			foreach ( $cpts as $cpt ) {

				$data_object = get_object_taxonomies($cpt, $output = 'objects');

				foreach ($data_object as $val) {

					$arr_tax_result[$val->name] = $val->label;
				}
			}

			update_post_meta( $post_id, 'ymc_tax_sort', '' );
		}

		$data = array(
			'data' => $arr_tax_result,
		);

		wp_send_json($data);
	}

	/**
	 * Sorts and updates taxonomy data based on the provided data and sends a JSON response.
	 */
	public function ymc_tax_sort() {

		if ( ! isset($_POST['nonce_code']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce_code'])), $this->token) ) exit;

		if(isset($_POST["tax_sort"])) {

			$temp_data = sanitize_text_field(wp_unslash($_POST["tax_sort"]));
			$clean_data = json_decode($temp_data, true);
			$post_id = !empty($_POST["post_id"]) ? (int) sanitize_text_field(wp_unslash($_POST["post_id"])) : 0;

			$id = update_post_meta( $post_id, 'ymc_tax_sort', $clean_data );
		}

		$data = array(
			'updated' => $id
		);

		wp_send_json($data);
	}

	/**
	 * Sorts and updates terms based on the provided data and sends a JSON response.
	 */
	public function ymc_term_sort() {

		if ( ! isset($_POST['nonce_code']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce_code'])), $this->token) ) exit;

		if(isset($_POST["term_sort"])) {

			$temp_data = sanitize_text_field(wp_unslash($_POST["term_sort"]));
			$clean_data = json_decode($temp_data, true);
			$post_id = !empty($_POST["post_id"]) ? (int) sanitize_text_field(wp_unslash($_POST["post_id"])) : 0;

			$id = update_post_meta( $post_id, 'ymc_term_sort', $clean_data );
		}

		$data = array(
			'updated' => $id
		);

		wp_send_json($data);
	}

	/**
	 * Deletes choices posts based on the provided post ID and sends a JSON response.
	 */
	public function ymc_delete_choices_posts() {

		if ( ! isset($_POST['nonce_code']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce_code'])), $this->token) ) exit;

		if(isset($_POST["post_id"])) {
			$id = delete_post_meta( (int) $_POST["post_id"], 'ymc_choices_posts' );
		}

		$data = array(
			'delete' => $id
		);

		wp_send_json($data);
	}

	/**
	 * Deletes featured posts based on the provided post ID and sends a JSON response.
	 */
	public function ymc_delete_featured_posts() {

		if ( ! isset($_POST['nonce_code']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce_code'])), $this->token) ) exit;

		if(isset($_POST["post_id"])) {
			$id = delete_post_meta( (int) $_POST["post_id"], 'ymc_featured_posts' );
		}

		$data = array(
			'delete' => $id
		);

		wp_send_json($data);
	}

	/**
	 * Delete the choices icons associated with a post.
	 */
	public function ymc_delete_choices_icons() {

		if ( ! isset($_POST['nonce_code']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce_code'])), $this->token) ) exit;

		if(isset($_POST["post_id"])) {
			$idIcons = delete_post_meta( (int) $_POST["post_id"], 'ymc_terms_icons' );
		}

		$data = array(
			'deleteIcons' => $idIcons
		);

		wp_send_json($data);
	}

	/**
	 * Updates the post meta with the provided data for terms alignment and sends a JSON response.
	 */
	public function ymc_options_icons() {

		if ( ! isset($_POST['nonce_code']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce_code'])), $this->token) ) exit;

		$tempData = !empty($_POST["params"]) ? sanitize_text_field(wp_unslash($_POST["params"])) : '';
		$cleanData  = json_decode($tempData, true);

		if(isset($_POST["post_id"])) {
			$id = update_post_meta( (int) sanitize_text_field(wp_unslash($_POST["post_id"])), 'ymc_terms_align', $cleanData);
		}

		$data = array(
			'update' => $id
		);

		wp_send_json($data);
	}

	/**
	 * Updates the post meta with the provided data for terms options and sends a JSON response.
	 */
	public function ymc_options_terms() {

		if ( ! isset($_POST['nonce_code']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce_code'])), $this->token) ) exit;

		$tempData = !empty($_POST["params"]) ? sanitize_text_field(wp_unslash($_POST["params"])) : '';
		$cleanData  = json_decode($tempData, true);

		if(isset($_POST["post_id"])) {
			$id = update_post_meta( (int) sanitize_text_field(wp_unslash($_POST["post_id"])), 'ymc_terms_options', $cleanData);
		}

		$data = array(
			'update' => $id
		);

		wp_send_json($data);
	}

	/**
	 * Handles the taxonomy options data sent via AJAX.
	 */
	public function ymc_taxonomy_options() {

		if ( ! isset($_POST['nonce_code']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce_code'])), $this->token) ) exit;

		$tempData = !empty($_POST["params"]) ? sanitize_text_field(wp_unslash($_POST["params"])) : '';
		$cleanData  = json_decode($tempData, true);

		if(isset($_POST["post_id"])) {
			$id = update_post_meta( (int) sanitize_text_field(wp_unslash($_POST["post_id"])), 'ymc_taxonomy_options', $cleanData);
		}

		$data = array(
			'update' => $id
		);

		wp_send_json($data);
	}

	/**
	 * Export settings to a JSON object.
	 */
	public function ymc_export_settings() {

		if ( ! isset($_POST['nonce_code']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce_code'])), $this->token) ) exit;

		$post_id = !empty($_POST["post_id"]) ? (int) sanitize_text_field(wp_unslash($_POST["post_id"])) : 0;

		$need_options = [];
		$options = get_post_meta( $post_id );

		if( is_array($options) && !empty($options) )
		{
			foreach ( $options as $key => $value )
			{
				if( substr($key, 0, 4) === 'ymc_' )
				{
					if( $key !== 'ymc_custom_after_js' && $key !== 'ymc_custom_css'  )
					{
						foreach ( $value as $item ) {
							$val = maybe_unserialize($item);
							$need_options[$key] = $val;
						}
					}
					else {
						$need_options[$key] = '';
					}
				}
			}
		}

		$json_data = wp_json_encode($need_options);

		echo wp_kses_post($json_data);
		exit;
	}

	/**
	 * Processes the import of settings data and sends a JSON response.
	 */
	public function ymc_import_settings() {

		if ( ! isset($_POST['nonce_code']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce_code'])), $this->token) ) exit;

		$post_id = !empty($_POST["post_id"]) ? (int) sanitize_text_field(wp_unslash($_POST["post_id"])) : 0;
		$temp_data = !empty($_POST["params"]) ? sanitize_text_field(wp_unslash($_POST["params"])) : '';
		$clean_data = json_decode($temp_data, true);
		$status = 0;

		if( is_array($clean_data) && count($clean_data) > 0 && $post_id > 0 )
		{
			foreach ( $clean_data as $meta_key => $meta_value )
			{
				update_post_meta( $post_id, $meta_key, $meta_value );
			}

			$mesg = __('Imported settings successfully','ymc-smart-filter');
			$status = 1;
		}
		else {
			$mesg = __('Import of settings unsuccessful. Try again.','ymc-smart-filter');
		}

		$data = array(
			'status' => $status,
			'mesg' => $mesg,
			'data' => $clean_data
		);

		wp_send_json($data);
	}

}