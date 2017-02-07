<?php
/*
Plugin Name: Slider Revolution Whiteboard Add-on
Plugin URI: http://www.themepunch.com/
Description: Create Hand-Drawn Presentations that are understandable, memorable & engaging
Author: ThemePunch
Version: 1.0.3
Author URI: http://themepunch.com
*/

// If this file is called directly, abort.
if ( ! defined( '\Nwdthemes\Revslider\Helper\Framework::WPINC' ) ) {
	die;
}

define( 'WHITEBOARD_PLUGIN_URL', str_replace('index.php','',$this->plugins_url( 'index.php', __FILE__ )));
define( 'WHITEBOARD_PLUGIN_PATH', $this->plugin_dir_path(__FILE__) );
define( 'WHITEBOARD_FILE_PATH', __FILE__ );
define( 'WHITEBOARD_VERSION', '1.0.3');

require_once(WHITEBOARD_PLUGIN_PATH.'includes/base.class.php');

$this->_framework->add_action('plugins_loaded', 'rs_whiteboard_init');

function rs_whiteboard_init(\Nwdthemes\Revslider\Helper\Framework $frameworkHelper) {
	$wb_base = new rs_whiteboard_base($frameworkHelper);
}