<?php

use \Nwdthemes\Revslider\Helper\Data;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderGlobals;

/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      http://www.themepunch.com/
 * @copyright 2016 ThemePunch
 */

if( !defined( '\Nwdthemes\Revslider\Helper\Framework::ABSPATH') ) exit();

class rs_whiteboard_base {

    protected static $frameworkHelper;

	public function __construct(
        \Nwdthemes\Revslider\Helper\Framework $frameworkHelper
    ) {
        self::$frameworkHelper = $frameworkHelper;

		try{
			if(class_exists('\Nwdthemes\Revslider\Model\Revslider\RevSliderFront')){ //check if Slider Revolution is installed
				if(version_compare(RevSliderGlobals::SLIDER_REVISION, '5.2.0', '>=')){
					if($frameworkHelper->get_option('revslider-valid', 'false') == 'true'){
						$frameworkHelper->add_filter('revslider_get_svg_sets', array('rs_whiteboard_base', 'enqueue_svg'));
						
						self::load_plugin_textdomain();
						
						if($frameworkHelper->is_admin()){
							
							require_once(WHITEBOARD_PLUGIN_PATH.'admin/includes/slider.class.php');
							require_once(WHITEBOARD_PLUGIN_PATH.'admin/includes/slide.class.php');
							
							rs_whiteboard_slide::init($frameworkHelper);
							rs_whiteboard_slider::init($frameworkHelper);

							//Updates
							require_once(WHITEBOARD_PLUGIN_PATH.'admin/includes/update.class.php');
							$update_admin = new rs_whiteboard_update(
								WHITEBOARD_VERSION,
								$frameworkHelper
							);
							$frameworkHelper->add_filter( 'pre_set_site_transient_update_plugins', array($update_admin ,'set_update_transient') );
							$frameworkHelper->add_filter( 'plugins_api', array($update_admin ,'set_updates_api_results'),10,3 );

							//Dashboard Slideout
							$frameworkHelper->add_filter('rev_addon_dash_slideouts',array('rs_whiteboard_base','display_plugin_admin_page'));
							$frameworkHelper->add_action('admin_enqueue_scripts', array('rs_whiteboard_base', 'enqueue_dash_scripts'));
							$frameworkHelper->add_action('admin_enqueue_scripts', array('rs_whiteboard_base', 'enqueue_dash_style'));
						}
						//else{

							require_once(WHITEBOARD_PLUGIN_PATH.'public/includes/slider.class.php');
							require_once(WHITEBOARD_PLUGIN_PATH.'public/includes/slide.class.php');
							
							rs_whiteboard_fe_slide::init($frameworkHelper);
							rs_whiteboard_fe_slider::init($frameworkHelper);
							
							$frameworkHelper->add_action('wp_enqueue_scripts', array('rs_whiteboard_fe_slider', 'enqueue_scripts'));

						//}
					}else{
						$frameworkHelper->add_action('admin_notices', array('rs_whiteboard_base', 'add_notice_activation'));
						//add notification that slider revolution needs to be activated
					}
				}else{
					$frameworkHelper->add_action('admin_notices', array('rs_whiteboard_base', 'add_notice_version'));
					//add notification that plugin version of Slider Revolution has to be at least version 5.2.0
				}
			}else{
				$frameworkHelper->add_action('admin_notices', array('rs_whiteboard_base', 'add_notice_plugin'));
				//add notification that plugin Slider Revolution has to be installed
			}
		}catch(\Exception $e){
			$message = $e->getMessage();
			$trace = $e->getTraceAsString();
			echo __("Slider Revolution Whiteboard Add-On:",'rs_whiteboard')." <b>".$message."</b>";
		}

	}
	
	
	public static function enqueue_svg($svg_sets){
		
		$svg_sets['Whiteboard'] = array('path' => WHITEBOARD_PLUGIN_PATH . 'public/assets/svg/busy-icons-svg/', 'url' => WHITEBOARD_PLUGIN_URL . 'public/assets/svg/busy-icons-svg/');
		
		return $svg_sets;
	}
	
	
	public static function add_notice_plugin(){
		?>
		<div class="error below-h2 wb-notice-wrap" id="message"><p><?php __('To use Slider Revolution Whiteboard Add-on, please install Slider Revolution for WordPress first', 'rs_whiteboard'); ?></p></div>
		<?php
	}
	
	
	public static function add_notice_version(){
		?>
		<div class="error below-h2 wb-notice-wrap" id="message"><p><?php __('To use Slider Revolution Whiteboard Add-on, please update Slider Revolution for WordPress to version 5.2.0 or later', 'rs_whiteboard'); ?></p></div>
		<?php
	}
	
	
	public static function add_notice_activation(){
		?>
		<div class="error below-h2 wb-notice-wrap" id="message"><p><?php __('To use Slider Revolution Whiteboard Add-on, please register Slider Revolution for WordPress', 'rs_whiteboard'); ?></p></div>
		<?php
	}
	
	public static function load_plugin_textdomain(){
		self::$frameworkHelper->load_plugin_textdomain('rs_whiteboard', false, WHITEBOARD_PLUGIN_PATH . 'languages/');
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public static function display_plugin_admin_page() {
		include_once( WHITEBOARD_PLUGIN_PATH . 'admin/views/admin-display.php' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public static function enqueue_dash_scripts() {
		if(isset(Data::$_GET["page"]) && Data::$_GET["page"]=="rev_addon"){
			self::$frameworkHelper->wp_enqueue_script( "rs_whiteboard_dash", WHITEBOARD_PLUGIN_URL . 'admin/assets/js/rev_addon_dash-admin.js', array( 'jquery' ), WHITEBOARD_VERSION, false );
			/*self::$frameworkHelper->wp_localize_script( $this->plugin_name, 'rs_whiteboard', array(
				'ajax_url' => $frameworkHelper->admin_url( 'admin-ajax.php' )
			));*/
		}
	}

	/**
	 * Register the CSS for the admin area.
	 *
	 * @since    1.0.0
	 */
	public static function enqueue_dash_style() {
		if(isset(Data::$_GET["page"]) && Data::$_GET["page"]=="rev_addon"){
			self::$frameworkHelper->wp_enqueue_style( "rs_whiteboard_dash", WHITEBOARD_PLUGIN_URL . 'admin/assets/css/whiteboard-dash-admin.css', array() , WHITEBOARD_VERSION );
		}
	}

}