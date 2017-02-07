<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      http://www.themepunch.com/
 * @copyright 2016 ThemePunch
 */

use \Nwdthemes\Revslider\Helper\Data;
use \Nwdthemes\Revslider\Helper\Framework;

if( !defined( '\Nwdthemes\Revslider\Helper\Framework::ABSPATH') ) exit();

class rs_whiteboard_update {

    protected $_frameworkHelper;

	private $plugin_url			= 'http://codecanyon.net/item/slider-revolution-responsive-wordpress-plugin/2751380';
	private $remote_url			= 'http://updates.themepunch.tools/check_for_updates.php';
	private $remote_url_info	= 'http://updates.themepunch.tools/addons/revslider-whiteboard-addon/revslider-whiteboard-addon.php';
	private $plugin_slug		= 'revslider-whiteboard-addon';
	private $plugin_path		= 'revslider-whiteboard-addon/revslider-whiteboard-addon.php';
	private $version;
	private $plugins;
	private $option;
	
	
	public function __construct(
        $version,
        \Nwdthemes\Revslider\Helper\Framework $frameworkHelper
    ) {
        $this->_frameworkHelper = $frameworkHelper;

		$this->option = $this->plugin_slug . '_update_info';
		$this->_retrieve_version_info();
		$this->version = $version;
	}

	public function delete_update_transients() {
		$this->_frameworkHelper->delete_transient( 'update_themes' );
		$this->_frameworkHelper->delete_transient( 'update_plugins' );
		$this->_frameworkHelper->delete_site_transient( 'update_plugins' );
		$this->_frameworkHelper->delete_site_transient( 'update_themes' );
	}
	
	
	public function add_update_checks(){
		
		$this->_frameworkHelper->add_filter('pre_set_site_transient_update_plugins', array(&$this, 'set_update_transient'));
		$this->_frameworkHelper->add_filter('plugins_api', array(&$this, 'set_updates_api_results'), 10, 3);
		
	}
	
	
	public function set_update_transient($transient) {
	
		$this->_check_updates();

		if(isset($transient) && !isset($transient->response)) {
			$transient->response = array();
		}

		if(!empty($this->data->basic) && is_object($this->data->basic)) {
			if(version_compare($this->version, $this->data->basic->version, '<')) {

				$this->data->basic->new_version = $this->data->basic->version;
				$transient->response[$this->plugin_path] = $this->data->basic;
			}
		}
		
		return $transient;
	}
	
	
	public function set_updates_api_results($result, $action, $args) {
	
		$this->_check_updates();

		if(isset($args->slug) && $args->slug == $this->plugin_slug && $action == 'plugin_information') {
			if(is_object($this->data->full) && !empty($this->data->full)) {
				$result = $this->data->full;
			}
		}
		
		return $result;
	}


	protected function _check_updates() {
		
		$force_check = false;
		
		if( (isset(Data::$_GET['checkforupdates']) && Data::$_GET['checkforupdates'] == 'true') || isset(Data::$_GET["force-check"])) $force_check = true;

		// Get data
		if(empty($this->data)) {
			$data = $this->_frameworkHelper->get_option($this->option, false);
			$data = $data ? $data : new stdClass;

			$this->data = is_object($data) ? $data : $this->_frameworkHelper->maybe_unserialize($data);
		}

		$last_check = $this->_frameworkHelper->get_option('rs_whiteboard-update-check');


		if($last_check == false){ //first time called
			$last_check = time();
			$this->_frameworkHelper->update_option('rs_whiteboard-update-check', $last_check);
		}
		
		// Check for updates
		if(time() - $last_check > 172800 || $force_check == true){
			
			$data = $this->_retrieve_update_info();	

			if(isset($data->basic)) {
				$this->_frameworkHelper->update_option('rs_whiteboard-update-check', time());

				$this->data->checked = time();
				$this->data->basic = $data->basic;
				$this->data->full = $data->full;
					
				//$this->_frameworkHelper->update_option('rs_whiteboard-stable-version', $data->full->stable);
				$this->_frameworkHelper->update_option('rs_whiteboard-latest-version', $data->full->version);
			}

		}

		// Save results
		$this->_frameworkHelper->update_option($this->option, $this->data);
	}


	public function _retrieve_update_info() {

		global $wp_version;
		$data = new stdClass;

		// Build request

		$validated = $this->_frameworkHelper->get_option('rs_whiteboard-valid', 'false');
		$purchase = ($this->_frameworkHelper->get_option('revslider-valid', 'false') == 'true') ? $this->_frameworkHelper->get_option('revslider-code', '') : '';
		$rattr = array(
			'code' => urlencode($purchase),
			'version' => urlencode(WHITEBOARD_VERSION)
		);

		$request = $this->_frameworkHelper->wp_remote_post($this->remote_url_info, array(
			'user-agent' => 'Magento/'.$wp_version.'; '.$this->_frameworkHelper->get_bloginfo('url'),
			'body' => $rattr
		));

		if(!$this->_frameworkHelper->is_wp_error($request)) {
			if($response = $this->_frameworkHelper->maybe_unserialize($request['body'])) {
				if(is_object($response)) {
					$data = $response;
					
					$data->basic->url = $this->plugin_url;
					$data->full->url = $this->plugin_url;
					$data->full->external = 1;
				}
			}
		}
		
		return $data;
	}
	
	
	public function _retrieve_version_info($force_check = false) {
		global $wp_version;
		
		$last_check = $this->_frameworkHelper->get_option('rs_whiteboard-update-check-short');
		if($last_check == false){ //first time called
			$last_check = time();
			$this->_frameworkHelper->update_option('rs_whiteboard-update-check-short', $last_check);
		}
		
		// Check for updates
		if(time() - $last_check > 172800 || $force_check == true){
			
			
			$this->_frameworkHelper->update_option('rs_whiteboard-update-check-short', time());
			
			$purchase = ($this->_frameworkHelper->get_option('revslider-valid', 'false') == 'true') ? $this->_frameworkHelper->get_option('revslider-code', '') : '';
			
			
			$response = $this->_frameworkHelper->wp_remote_post($this->remote_url, array(
				'user-agent' => 'Magento/'.$wp_version.'; '.$this->_frameworkHelper->get_bloginfo('url'),
				'body' => array(
					'item' => urlencode('revslider-whiteboard-addon'),
					'version' => urlencode(WHITEBOARD_VERSION),
					'code' => urlencode($purchase)
				)
			));

			$response_code = $this->_frameworkHelper->wp_remote_retrieve_response_code( $response );
			$version_info = $this->_frameworkHelper->wp_remote_retrieve_body( $response );

			if ( $response_code != 200 || $this->_frameworkHelper->is_wp_error( $version_info ) ) {
				$this->_frameworkHelper->update_option('rs_whiteboard-connection', false);
				return false;
			}else{
				$this->_frameworkHelper->update_option('rs_whiteboard-connection', true);
			}
			
			/*
			$version_info = json_decode($version_info);
			if(isset($version_info->version)){
				$this->_frameworkHelper->update_option('rs_whiteboard-latest-version', $version_info->version);
			}
			
			if(isset($version_info->notices)){
				$this->_frameworkHelper->update_option('rs_whiteboard-notices', $version_info->notices);
			}
			
			if(isset($version_info->dashboard)){
				$this->_frameworkHelper->update_option('rs_whiteboard-dashboard', $version_info->dashboard);
			}
			
			if(isset($version_info->deactivated) && $version_info->deactivated === true){
				if($this->_frameworkHelper->get_option('rs_whiteboard-valid', 'false') == 'true'){
					//remove validation, add notice
					$this->_frameworkHelper->update_option('rs_whiteboard-valid', 'false');
					$this->_frameworkHelper->update_option('rs_whiteboard-deact-notice', true);
				}
			}
			*/
		}
		
		if($force_check == true){ //force that the update will be directly searched
			$this->_frameworkHelper->update_option('rs_whiteboard-update-check', '');
		}
		
	}
}