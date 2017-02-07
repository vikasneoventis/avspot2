<?php
if (isset($_REQUEST['debug'])) error_reporting(-1);
else error_reporting(0);
session_start();
define('UAP_CORE', 1);
define('UAP_TITLE', 'Halfdata Panel');
define('UAP_DEMO_MODE', false);
$options = array(
	'login' => '',
	'password' => '',
	'url' => '',
	'mail_method' => 'mail',
	'mail_from_name' => UAP_TITLE,
	'mail_from_email' => 'noreply@'.str_replace("www.", "", $_SERVER["SERVER_NAME"]),
	'smtp_server' => '',
	'smtp_port' => '',
	'smtp_secure' => 'none',
	'smtp_username' => '',
	'smtp_password' => '',
	'smtp_from_name' => UAP_TITLE
);
$mail_methods = array('mail' => 'PHP Mail() function', 'smtp' => 'SMTP');
$smtp_secures = array('none' => 'None', 'ssl' => 'SSL', 'tls' => 'TLS');

$folders = array();
if (!file_exists(dirname(dirname(__FILE__)).'/content/plugins')) mkdir(dirname(dirname(__FILE__)).'/content/plugins', 0777, true);
if (!file_exists(dirname(dirname(__FILE__)).'/content/data')) mkdir(dirname(dirname(__FILE__)).'/content/data', 0777, true);
if (!file_exists(dirname(dirname(__FILE__)).'/content/data/temp')) mkdir(dirname(dirname(__FILE__)).'/content/data/temp', 0777, true);

if (!is_writable(dirname(dirname(__FILE__)).'/content/plugins')) {
	$folders[] = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.'plugins';
} else {
	if (!file_exists(dirname(dirname(__FILE__)).'/content/plugins/index.html')) {
		$result = file_put_contents(dirname(dirname(__FILE__)).'/content/plugins/index.html', '<html><head><script>location.href="http://codecanyon.net/user/halfdata/portfolio?ref=halfdata";</script></head><body></body></html>');
		if (!$result) $folders[] = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.'plugins';
	}
}
if (!is_writable(dirname(dirname(__FILE__)).'/content/data')) {
	$folders[] = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.'data';
} else {
	if (!file_exists(dirname(dirname(__FILE__)).'/content/data/index.html')) {
		$result = file_put_contents(dirname(dirname(__FILE__)).'/content/data/index.html', '<html><head><script>location.href="http://codecanyon.net/user/halfdata/portfolio?ref=halfdata";</script></head><body></body></html>');
		if (!$result) $folders[] = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.'data';
	}
}
if (!is_writable(dirname(dirname(__FILE__)).'/content/data/temp')) {
	$folders[] = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'temp';
} else {
	if (!file_exists(dirname(dirname(__FILE__)).'/content/data/temp/index.html')) {
		$result = file_put_contents(dirname(dirname(__FILE__)).'/content/data/temp/index.html', '<html><head><script>location.href="http://codecanyon.net/user/halfdata/portfolio?ref=halfdata";</script></head><body></body></html>');
		//if (!$result) $folders[] = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'temp';
	}
	if (!file_exists(dirname(dirname(__FILE__)).'/content/data/temp/plugins.txt')) {
		$items = '[{"slug":"layered-popups","name":"Layered Popups","url":"https://codecanyon.net/item/layered-popups-for-wordpress/5978263?ref=halfdata","icon":"newspaper-o"},{"slug":"layered-popups-tabs","name":"Side Tabs - LP Add-On","url":"https://codecanyon.net/item/side-tabs-layered-popups-addon/10335326?ref=halfdata","icon":"arrow-circle-o-right"},{"slug":"digital-paybox","name":"Digital Paybox","url":"https://codecanyon.net/item/digital-paybox-pay-and-download/2637036?ref=halfdata","icon":"usd"},{"slug":"code-shop","name":"Code Shop","url":"https://codecanyon.net/item/code-shop-for-wordpress/5687817?ref=halfdata","icon":"credit-card"}]';
		$result = file_put_contents(dirname(dirname(__FILE__)).'/content/data/temp/plugins.txt', $items);
		if (!$result) $folders[] = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'temp';
	}
}
$global_message = '';
if (!empty($folders)) {
	$global_message = '<div class="global-message global-message-danger">Please make sure that the following directories exist and writable (set permissions 0777):<br /><em>'.implode('<br />', $folders).'</em></div>';
	$writable = false;
} else $writeable = true;

$wp_filters = array();
$scripts = array();
$styles = array();
$menu = array();

class WP_Error {
	var $message;
	function __construct($_code = '', $_message = '', $_data = '') {
		$this->message = $_message;
	}
	function get_error_message() {
		return $this->message;
	}
}

header('Content-type: text/html; charset=utf-8');
?>