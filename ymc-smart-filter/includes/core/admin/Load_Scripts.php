<?php

namespace YMC_Smart_Filters\Core\Admin;

use YMC_Smart_Filters\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Load_Scripts {

	/**
	 * Init.
	 *
	 * Initialize Scripts CSS & JS.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', [ $this, 'backend_embed_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_embed_css' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'frontend_embed_scripts' ],999999 );
	}

	// Backend enqueue scripts & style.
	public function backend_embed_scripts() {

		$screen = get_current_screen();

		wp_enqueue_style( 'filter-grids-' . $this->generate_handle(), YMC_SMART_FILTER_URL . 'includes/assets/css/admin.css', array(), YMC_SMART_FILTER_VERSION);

		if (in_array( $screen->id, array('ymc_filters'), true )) {
			
			wp_enqueue_style('thickbox');
			wp_enqueue_script('thickbox');
			wp_enqueue_script( 'wp-color-picker');

         $settings_css = wp_enqueue_code_editor( array( 'type' => 'text/css' ) );
         $settings_js  = wp_enqueue_code_editor( array( 'type' => 'application/javascript' ) );
        
        $deps = array( 'jquery' );
        
        if ( false !== $settings_css || false !== $settings_js ) {
            $deps[] = 'code-editor';
        }
         
         wp_enqueue_script( 
            'filter-grids-' . $this->generate_handle(), 
            YMC_SMART_FILTER_URL . 'includes/assets/js/admin.min.js', 
            $deps, 
            YMC_SMART_FILTER_VERSION, 
            true
        );
			
			wp_localize_script( 'filter-grids-'.$this->generate_handle(), '_smart_filter_object',
				array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce'    => wp_create_nonce(Plugin::$instance->token_b),
					'current_page' => 1,
					'path' => YMC_SMART_FILTER_URL,
               'code_editor_css' => $settings_css,
               'code_editor_js'  => $settings_js
				));
		}

		if (in_array( $screen->id, array('edit-ymc_filters','ymc_filters'), true )) {
			wp_enqueue_script( 'filter-grids-update', YMC_SMART_FILTER_URL . 'includes/assets/js/updatePluginVer.js', array( 'jquery' ), YMC_SMART_FILTER_VERSION, true );
			wp_localize_script( 'filter-grids-update', '_ymc_fg_object',
				array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce'    => wp_create_nonce(Plugin::$instance->token_b)
			));
		}
	}

	// Frontend enqueue scripts & style.
	public function frontend_embed_scripts() {

		wp_enqueue_script( 'filter-grids-masonry-' . $this->generate_handle(), YMC_SMART_FILTER_URL . 'includes/assets/js/masonry.js', array('jquery'), YMC_SMART_FILTER_VERSION, true);
		wp_enqueue_script( 'jquery-ui-datepicker');
		wp_enqueue_script( 'filter-grids-' . $this->generate_handle(), YMC_SMART_FILTER_URL . 'includes/assets/js/script.min.js', array('jquery', 'wp-hooks'), YMC_SMART_FILTER_VERSION, true);
		wp_localize_script( 'filter-grids-' . $this->generate_handle(), '_smart_filter_object',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => wp_create_nonce(Plugin::$instance->token_f),
				'current_page' => 1,
				'path' => YMC_SMART_FILTER_URL
			));

	}


	public function frontend_embed_css() {
		wp_enqueue_style( 'filter-datepicker-' . $this->generate_handle(), YMC_SMART_FILTER_URL . 'includes/assets/css/datepicker.css', array(), YMC_SMART_FILTER_VERSION);
		wp_enqueue_style( 'filter-grids-' . $this->generate_handle(), YMC_SMART_FILTER_URL . 'includes/assets/css/style.css', array(), YMC_SMART_FILTER_VERSION);
	}


	// Generate handle
	public function generate_handle() {
		return wp_create_nonce('filter-grids');
	}
}