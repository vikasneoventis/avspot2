<?php

namespace Nwdthemes\Revslider\Model\Revslider\Framework;

use \Nwdthemes\Revslider\Helper\Data;
use \Nwdthemes\Revslider\Helper\Framework;
use \Nwdthemes\Revslider\Helper\Plugin;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderGlobals;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       h
 * @since      1.0.0
 *
 * @package    Rev_addon
 * @subpackage Rev_addon/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rev_addon
 * @subpackage Rev_addon/admin
 * @author     ThemePunch <info@themepunch.com>
 */

class RevSliderAddonAdmin {

    protected $_filesystem;
    protected $_framework;
    protected $_plugin;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */

	public function __construct(
        $plugin_name,
        $version,
        \Nwdthemes\Revslider\Helper\Filesystem $filesystem,
        \Nwdthemes\Revslider\Helper\Framework $framework,
        \Nwdthemes\Revslider\Helper\Plugin $plugin
    ) {
        $this->_filesystem = $filesystem;
        $this->_framework = $framework;
        $this->_plugin = $plugin;

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rev_addon_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rev_addon_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if(isset(Data::$_GET["page"]) && Data::$_GET["page"]=="rev_addon"){
			$this->_framework->wp_enqueue_style('rs-plugin-settings', Framework::$RS_PLUGIN_URL .'admin/assets/css/admin.css', array(), RevSliderGlobals::SLIDER_REVISION);
			$this->_framework->wp_enqueue_style( $this->plugin_name, Framework::$RS_PLUGIN_URL . 'admin/assets/css/rev_addon-admin.css', array( ), $this->version);
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rev_addon_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rev_addon_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if(isset(Data::$_GET["page"]) && Data::$_GET["page"]=="rev_addon"){
			$this->_framework->wp_enqueue_script('tp-tools', Framework::$RS_PLUGIN_URL .'public/assets/js/jquery.themepunch.tools.min.js', array(), RevSliderGlobals::SLIDER_REVISION );
			$this->_framework->wp_enqueue_script('unite_admin', Framework::$RS_PLUGIN_URL .'admin/assets/js/admin.js', array(), RevSliderGlobals::SLIDER_REVISION );
			$this->_framework->wp_enqueue_script( $this->plugin_name, Framework::$RS_PLUGIN_URL .'admin/assets/js/rev_addon-admin.js', array( 'jquery' ), $this->version, false );
			$this->_framework->wp_localize_script( $this->plugin_name, 'rev_slider_addon', array(
					'ajax_url' => $this->_framework->admin_url( 'admin-ajax.php' ),
					'please_wait_a_moment' => __("Please Wait a Moment",'revslider'),
					'settings_saved' => __("Settings saved",'revslider')
				));
		}
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		$this->plugin_screen_hook_suffix = add_submenu_page(
			'revslider',
			__( 'Add-Ons', 'revslider' ),
			__( 'Add-Ons', 'revslider' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_admin_page' )
		);
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( Framework::$RS_PLUGIN_PATH.'admin/views/rev_addon-admin-display.php' );
	}

	/**
	 * Activates Installed Add-On/Plugin
	 *
	 * @since    1.0.0
	 */
	public function activate_plugin() {
		// Verify that the incoming request is coming with the security nonce
		if( $this->_framework->wp_verify_nonce( Data::$_REQUEST['nonce'], 'ajax_rev_slider_addon_nonce' ) ) {
			if(isset(Data::$_REQUEST['plugin'])){
				//$this->_framework->update_option( "rev_slider_addon_gal_default", $this->_framework->sanitize_text_field(Data::$_REQUEST['default_gallery']) );
				$result = $this->_plugin->activate_plugin( Data::$_REQUEST['plugin'] );
				if ( $this->_framework->is_wp_error( $result ) ) {
					// Process Error
					die('0');
				}
				die( '1' );
			}
			else{
				die( '0' );
			}
		} 
		else {
			die( '-1' );
		}
	}

	/**
	 * Deactivates Installed Add-On/Plugin
	 *
	 * @since    1.0.0
	 */
	public function deactivate_plugin() {
		// Verify that the incoming request is coming with the security nonce
		if( $this->_framework->wp_verify_nonce( Data::$_REQUEST['nonce'], 'ajax_rev_slider_addon_nonce' ) ) {
			if(isset(Data::$_REQUEST['plugin'])){
				//$this->_framework->update_option( "rev_slider_addon_gal_default", $this->_framework->sanitize_text_field(Data::$_REQUEST['default_gallery']) );
				$result = $this->_plugin->deactivate_plugins( Data::$_REQUEST['plugin'] );
				if ( $this->_framework->is_wp_error( $result ) ) {
					// Process Error
					die('0');
				}
				die( '1' );
			}
			else{
				die( '0' );
			}
		} 
		else {
			die( '-1' );
		}
	}

	/**
	 * Deactivates Installed Add-On/Plugin
	 *
	 * @since    1.0.0
	 */
	public function install_plugin() {
		if(isset(Data::$_REQUEST['plugin'])){
			$wp_version = $this->_framework->getVersion();
			$plugin_slug = basename(Data::$_REQUEST['plugin']);
			$plugin_result = false;
			$plugin_message = 'UNKNOWN';
			$url = 'http://updates.themepunch.tools/magento2/addons/'.$plugin_slug.'/'.$plugin_slug.'.zip';

			$get = $this->_framework->wp_remote_post($url, array(
				'user-agent' => 'Magento/'.$wp_version.'; '.$this->_framework->get_bloginfo('url'),
				'body' => '',
				'timeout' => 45
			));

			if( !$get || $get["response"]["code"] != "200" ){
			  $plugin_message = 'FAILED TO DOWNLOAD';
			}else{
				$plugin_message = 'ZIP is there';
				$upload_dir = $this->_framework->wp_upload_dir();
				$file = $upload_dir['basedir']. '/templates/' . $plugin_slug . '.zip';
				@mkdir(dirname($file));
				$ret = @file_put_contents( $file, $get['body'] );

				$wp_filesystem = $this->_filesystem->WP_Filesystem();

				$d_path = Plugin::getPluginDir();
				$unzipfile = $this->_filesystem->unzip_file( $file, $d_path);

				if( $this->_framework->is_wp_error($unzipfile) ){
					$d_path = Plugin::WP_PLUGIN_DIR;
					$unzipfile = $this->_filesystem->unzip_file( $file, $d_path);

					if( $this->_framework->is_wp_error($unzipfile) ){
						$f = basename($file);
						$d_path = str_replace($f, '', $file);

						$unzipfile = $this->_filesystem->unzip_file( $file, $d_path);
					}
				}
				@unlink($file);
				die('1');
			}
			//$result = $this->_plugin->activate_plugin( $plugin_slug.'/'.$plugin_slug.'.php' );
		} else{
			die( '0' );
		}
	}

} // END of class