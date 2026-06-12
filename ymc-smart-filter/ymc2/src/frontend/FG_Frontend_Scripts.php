<?php declare( strict_types = 1 );

namespace YMCFilterGrids\frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Handle frontend scripts
 *
 * @since 3.0.0
 */

class FG_Frontend_Scripts {

	/**
	 * Hook in methods.
	 */
	public static function init() : void {
		add_action('wp_enqueue_scripts', array( __CLASS__, 'load_styles'));
      add_action('wp_enqueue_scripts', [__CLASS__, 'load_scripts']);			
      add_filter('script_loader_tag', [__CLASS__, 'add_module_type'], 10, 3);
	}

   /**
    * Add module tag
    *
    * @param string $tag
    * @param string $handle
    * @param string $src
    * @return string
    */
   public static function add_module_type($tag, $handle, $src) : string {
      $modules = ['ymc_script', 'ymc_api'];

      if (in_array($handle, $modules, true)) {
         return "<script id='{$handle}-js' type='module' src='" . esc_url($src) . "'></script>";
      }
      return $tag;
   }


	/**
	 * Register all scripts.
	 */
	public static function register_scripts() : void {
		$suffix = '.min';
		$version = YMC_VERSION;
     
      wp_register_script(
         'ymc_handlebar',
         YMC_PLUGIN_URL . 'assets/js/lib/handlebars.min-v4.7.8.js',
         array(),
         $version,
         false
      );
     
      wp_register_script(
         'ymc_script',
         YMC_PLUGIN_URL . 'assets/js/frontend/ymc-public' . $suffix . '.js',
         array('jquery', 'wp-hooks', 'ymc_masonry', 'ymc_handlebar'),
         $version,
         true
      );
     
      wp_localize_script('ymc_script', '_ymc_fg_object', array(
         'ajax_url'                   => admin_url('admin-ajax.php'),        
         'current_page'               => 1,
         'all_dropdown_label'         => __('All', 'ymc-smart-filter'),
         'path'                       => YMC_PLUGIN_URL,
         'rest_url'                   => esc_url_raw(rest_url( 'ymc/v1/' )),
         'nonce'                      => wp_create_nonce('wp_rest')
      ));      
         
      wp_register_script(
         'ymc_api',
         YMC_PLUGIN_URL . 'assets/js/frontend/rest/YMCFilterGrid' . $suffix . '.js',
         array('jquery', 'wp-hooks'),
         $version,
         true
      ); 
   
      wp_register_script(
         'ymc_masonry',
         YMC_PLUGIN_URL . 'assets/js/lib/masonry.min.js',
         array('jquery', 'wp-hooks'),
         $version,
         true
      );


      wp_register_script(
         'ymc_flatpickr',
         YMC_PLUGIN_URL . 'assets/js/lib/flatpickr.min.js',
         array(),
         '4.6.13',
         true
      );   
      
      wp_register_script( 
         'ymc_flatpickr_confirm_date', 
         YMC_PLUGIN_URL . 'assets/js/lib/flatpickrConfirmDate.js', 
         array('ymc_flatpickr'),
         '4.6.13', 
         true 
      );

	}


	/**
	 * Register all styles.
	 */
	public static function register_styles() : void {
		$suffix = '.min';		
		$version = YMC_VERSION;

      wp_register_style(
         'ymc_style',
         YMC_PLUGIN_URL . 'assets/css/style'. $suffix .'.css',
         [],
         $version
      );
      
      wp_register_style(
         'query_ui', 
         YMC_PLUGIN_URL . 'assets/css/lib/query-ui.css',
         array(), 
         $version
      );

      wp_register_style(
         'ymc_flatpickr', 
         YMC_PLUGIN_URL . 'assets/css/lib/flatpickr.min.css',
         array(), 
         $version
      );

      wp_register_style(
         'ymc_flatpickr_dark', 
         YMC_PLUGIN_URL . 'assets/css/lib/flatpickr-dark.css',
         array(), 
         $version
      );

      wp_register_style(
         'ymc_flatpickr_material_blue', 
         YMC_PLUGIN_URL . 'assets/css/lib/flatpickr-material-blue.css',
         array(), 
         $version
      );

      wp_register_style(
         'ymc_flatpickr_airbnb', 
         YMC_PLUGIN_URL . 'assets/css/lib/flatpickr-airbnb.css',
         array(), 
         $version
      );
      
      wp_register_style(
         'ymc_flatpickr_confetti', 
         YMC_PLUGIN_URL . 'assets/css/lib/flatpickr-confetti.css',
         array(), 
         $version
      );

      wp_register_style(
         'ymc_flatpickr_material_green', 
         YMC_PLUGIN_URL . 'assets/css/lib/flatpickr-material-green.css',
         array(), 
         $version
      ); 
      
      wp_register_style(
         'ymc_flatpickr_material_red', 
         YMC_PLUGIN_URL . 'assets/css/lib/flatpickr-material_red.css',
         array(), 
         $version
      );      
      
	}


	/**
	 * Register/queue frontend styles.
	 */
	public static function load_styles() : void {
		self::register_styles();
      wp_enqueue_style('ymc_style');
	}


	/**
	 * Register/queue frontend scripts.
	 */
	public static function load_scripts() : void {
		self::register_scripts();
	}

}