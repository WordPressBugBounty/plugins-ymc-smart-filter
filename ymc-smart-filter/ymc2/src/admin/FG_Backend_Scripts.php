<?php declare( strict_types = 1 );

namespace YMCFilterGrids\admin;

defined( 'ABSPATH' ) || exit;

/**
 * Backend Scripts Class
 *
 * @since 3.0.0
 */
class FG_Backend_Scripts {

	/**
	 * Hook in methods.
	 */
	public static function init() : void {
		add_action('admin_enqueue_scripts', array( __CLASS__, 'load_scripts' ));		
      add_filter('script_loader_tag', [__CLASS__, 'add_module_type'], 10, 3);
	}

   public static function add_module_type($tag, $handle, $src) : string {
      if ($handle === 'ymc_script') {
         return "<script id='{$handle}-js' type='module' src='" . esc_url($src) . "'></script>";
      }
      return $tag;
   }


	/**
	 * Register all Filter Grids scripts.
	 */
	private static function register_scripts() : void {

      $suffix = '.min';
      $version = YMC_VERSION;

      wp_register_script(
         'ymc_handlebar',
         YMC_PLUGIN_URL . 'assets/js/lib/handlebars.min-v4.7.8.js',
         [],
         $version,
         false
      );

      wp_register_script(
         'ymc_color-picker-alpha',
         YMC_PLUGIN_URL . 'assets/js/lib/wp-color-picker-alpha.min.js',
         ['jquery', 'wp-color-picker'],
         $version,
         true
      );

      wp_register_script(
         'ymc_script',
         YMC_PLUGIN_URL . 'assets/js/admin/main' . $suffix . '.js',
         ['jquery', 'jquery-ui-tooltip', 'wp-hooks'],
         $version,
         true
      );
	}


	/**
	 * Register all Filter Grids styles.
	 */
	private static function register_styles() : void {

      $suffix = '.min';
      $version = YMC_VERSION;

      wp_register_style(
         'query_ui',
         YMC_PLUGIN_URL . 'assets/css/lib/query-ui.css',
         [],
         $version
      );

      wp_register_style(
         'ymc_style',
         YMC_PLUGIN_URL . 'assets/css/admin' . $suffix . '.css',
         [],
         $version
      );

	}


	/**
	 * Register/queue backend scripts.
	 */
	public static function load_scripts() : void {

		$screen = get_current_screen();
      
      if ( !$screen ) {
         return;
      }

		if ( $screen->id === 'ymc_filters' ) {

			$settings_css = wp_enqueue_code_editor(array(
				'type'       => 'text/css',
				'codemirror' => array(
					'indentUnit' => 2,
					'tabSize'    => 2,
					'placeholder' => "Code CSS...",
					'lineNumbers'    => true,
					'lineWrapping'   => true
				)
			));

			$settings_js = wp_enqueue_code_editor([
				'type'       => 'text/javascript',
				'codemirror' => [
					'indentUnit'     => 2,
					'tabSize'        => 2,
					'placeholder'    => "Code JS...",
					'lineNumbers'    => true,
					'lineWrapping'   => true
				]
			]);

			wp_enqueue_script( 'wp-codemirror' );
			wp_enqueue_script( 'code-editor' );
			wp_enqueue_style( 'code-editor' );
			wp_add_inline_script(
				'code-editor',
				'window.ymcEditors = {};
				jQuery(function () {
				    if (typeof wp !== "undefined" && wp.codeEditor) {
				        const cssEditor = wp.codeEditor.initialize("ymc-fg-custom-css", ' . wp_json_encode($settings_css) . ');
				        const jsEditor = wp.codeEditor.initialize("ymc-fg-custom-js", ' . wp_json_encode($settings_js) . ');				
				        window.ymcEditors["css"] = cssEditor.codemirror;
				        window.ymcEditors["js"] = jsEditor.codemirror;
				    }
				});'
			);

			wp_enqueue_script('thickbox');
			wp_enqueue_style('thickbox');
			wp_enqueue_script( 'wp-color-picker');

			self::register_scripts();
			self::register_styles();

         wp_enqueue_script('ymc_handlebar');
         wp_enqueue_script('ymc_color-picker-alpha');
         wp_enqueue_script('ymc_script');

         wp_enqueue_style('query_ui');
         wp_enqueue_style('ymc_style');

         self::localize_script();
		}

		if( $screen->id === 'edit-ymc_filters' || 
          $screen->id === 'ymc_filters_page_ymc-license' || 
          $screen->id === 'ymc_filters_page_ymc-settings' ) {

			 self::register_styles();
          wp_enqueue_style('ymc_style');
		}
	}


	/**
	 * Localize a FG script once.
	 *
	 * @since 3.0.0
	 * @param string $handle Script handle the data will be attached to.
	 */
	public static function localize_script() : void {

		if ( wp_script_is( 'ymc_script', 'enqueued' ) ) {

			$post_id = isset($_GET['post']) ? intval($_GET['post']) : get_the_ID();

			$saved_schema = '';
         if ( $post_id ) {
				$saved_schema = get_post_meta($post_id, 'ymc_fg_lb_layout_schema', true);
				
				if ( is_string( $saved_schema ) && ! empty( $saved_schema ) ) {
					$decoded = json_decode( $saved_schema, true );
					if ( json_last_error() === JSON_ERROR_NONE ) {						
						$saved_schema = isset($decoded['schema']) ? $decoded['schema'] : $decoded;
					}
				}
         }


			 wp_localize_script( 'ymc_script', '_ymc_fg_object',
				array(
					'ajax_url'                    => admin_url('admin-ajax.php'),
					'getTaxAjax_nonce'            => wp_create_nonce('get-taxonomy-ajax-nonce'),
					'getTermAjax_nonce'           => wp_create_nonce('get-term-ajax-nonce'),
					'removeTermsAjax_nonce'       => wp_create_nonce('remove-terms-ajax-nonce'),
					'updatedTaxAjax_nonce'        => wp_create_nonce('updated-tax-ajax-nonce'),
					'sortTaxAjax_nonce'           => wp_create_nonce('sort-tax-ajax-nonce'),
					'sortTermAjax_nonce'          => wp_create_nonce('sort-term-ajax-nonce'),
					'selectPostsAjax_nonce'       => wp_create_nonce('select-posts-ajax-nonce'),
					'searchFeedPostsAjax_nonce'   => wp_create_nonce('search-feed-posts-ajax-nonce'),
					'saveTaxAttrAjax_nonce'       => wp_create_nonce('save-taxonomy-attr-ajax-nonce'),
					'saveTermAttrAjax_nonce'      => wp_create_nonce('save-term-attr-ajax-nonce'),
					'clearTermsCacheAjax_nonce'   => wp_create_nonce('clear-terms-cache-ajax-nonce'),
					'getSelectTaxAjax_nonce'      => wp_create_nonce('get-select-tax-ajax-nonce'),
					'uploadTermIconAjax_nonce'    => wp_create_nonce('upload-term-icon-ajax-nonce'),
					'exportSettingsAjax_nonce'    => wp_create_nonce('export-settings-ajax-nonce'),
					'importSettingsAjax_nonce'    => wp_create_nonce('import-settings-ajax-nonce'),
					'updateRelatedTerms_nonce'    => wp_create_nonce('update-related-terms-ajax-nonce'),
					'updateRootSourceTerms_nonce' => wp_create_nonce('update-root-source_terms-ajax-nonce'),
					'usegeFiltersPaginAjax_nonce' => wp_create_nonce('usege-filters-pagin-ajax-nonce'),
					'scanExistingPostsAjax_nonce' => wp_create_nonce('scan-existing-posts-ajax-nonce'),
					'lbSaveLayoutAjax_nonce' 	   => wp_create_nonce('lb-save-layout-ajax-nonce'),
					'classicSnapshotAjax_nonce'   => wp_create_nonce('classic-snapshot-ajax_nonce'),
					'savedSchema' 					   => $saved_schema ?: null,
					'acf_fields' 						=> ymc_get_all_acf_fields_for_builder(),				
					'loadedFeedPosts_page'        => 2,
					'path' => YMC_PLUGIN_URL
			));
		}
	}

}