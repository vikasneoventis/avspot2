<?php
define('DOING_AJAX', true);
include_once(dirname(__FILE__).'/inc/common.php');
include_once(dirname(__FILE__).'/inc/icdb.php');
include_once(dirname(__FILE__).'/inc/functions.php');

$wpdb = null;
$ready = false;
if (file_exists(dirname(__FILE__).'/inc/config.php')) {
	include_once(dirname(__FILE__).'/inc/config.php');
	try {
		$wpdb = new ICDB(UAP_DB_HOST, UAP_DB_HOST_PORT, UAP_DB_NAME, UAP_DB_USER, UAP_DB_PASSWORD, UAP_TABLE_PREFIX);
		create_tables();
		get_options();
		if (!empty($options['login']) && !empty($options['password']) && !empty($options['url'])) $ready = true;
	} catch (Exception $e) {
		//die($e->getMessage());
	}
}
if (!$ready) {
	if (isset($_REQUEST['action'])) {
		$return_object = array();
		$return_object['status'] = 'ERROR';
		$return_object['message'] = 'Please install Admin Panel properly.';
		echo json_encode($return_object);
		exit;
	}
	header('Location: '.admin_url('install.php'));
	exit;
}
$is_logged = false;
$session_id = '';
if (isset($_COOKIE['uap-auth'])) {
	$session_id = preg_replace('/[^a-zA-Z0-9]/', '', $_COOKIE['uap-auth']);
	$session_details = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."sessions WHERE session_id = '".$session_id."' AND registered + valid_period > '".time()."'");
	if ($session_details) {
		$wpdb->query("UPDATE ".$wpdb->prefix."sessions SET registered = '".time()."', ip = '".$_SERVER['REMOTE_ADDR']."' WHERE session_id = '".$session_id."'");
		$is_logged = true;
	}
}
include_once(dirname(__FILE__).'/inc/plugins.php');

do_action('init');
if ($is_logged) do_action('admin_init');

switch ($_REQUEST['action']) {
	case 'save-settings':
		if (!current_user_can('manage_options')) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = 'Sorry, you don\'t have permissions to perform this operation. Please login as administrator.';
			echo json_encode($return_object);
			exit;
		}
		if (UAP_DEMO_MODE) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = '<strong>Demo mode.</strong> This operation is disabled.';
			echo json_encode($return_object);
			exit;
		}
		foreach ($options as $key => $value) {
			if ($key != 'password') {
				if (isset($_POST[$key])) {
					$options[$key] = trim(stripslashes($_POST[$key]));
				}
			}
		}
		if (isset($_POST['email'])) $options['login'] = trim(stripslashes($_POST['email']));
		$errors = array();
		if ($options['mail_method'] == 'mail') {
			if (empty($options['mail_from_name'])) $errors[] = 'Invalid sender name.';
			if ($options['mail_from_email'] == '' || !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,19})$/i", $options['mail_from_email'])) $errors[] = 'Invalid sender e-mail.';
		} else if ($options['mail_method'] == 'smtp') {
			if (empty($options['smtp_from_name'])) $errors[] = 'Invalid sender name.';
			if (empty($options['smtp_server']) || !is_hostname($options['smtp_server'])) $errors[] = 'Invalid SMTP server.';
			if (empty($options['smtp_port']) || !ctype_digit($options['smtp_port'])) $errors[] = 'Invalid SMTP port.';
			if (empty($options['smtp_username'])) $errors[] = 'Invalid SMTP username.';
			if (empty($options['smtp_password'])) $errors[] = 'Invalid SMTP password.';
		}
		if ($options['login'] == '' || !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,19})$/i", $options['login'])) $errors[] = 'E-mail must be a valid e-mail address.';
		if (isset($_POST['password'])) $password = trim(stripslashes($_POST['password']));
		else $password = '';
		if (isset($_POST['repeat_password'])) $repeat_password = trim(stripslashes($_POST['repeat_password']));
		else $repeat_password = '';
		if (!empty($password)) {
			if ($password == $repeat_password) {
				if (strlen($password) < 6) $errors[] = 'Password length must be at least 6 characters.';
				else $options['password'] = md5($password);
			} else $errors[] = 'Password and its confirmation are not equal.';
		}
		if (!empty($errors)) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = 'Attention! Please correct the errors below and try again.<br /><i class="fa fa-angle-double-right"></i> '.implode('<br /><i class="fa fa-angle-double-right"></i> ', $errors).'</li></ul>';
			echo json_encode($return_object);
			exit;
		}
		update_options();
		$return_object = array();
		$return_object['status'] = 'OK';
		$return_object['message'] = 'Settings successfully saved.';
		echo json_encode($return_object);
		exit;
		break;

	case 'test-mailing':
		if (!current_user_can('manage_options')) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = 'Sorry, you don\'t have permissions to perform this operation. Please login as administrator.';
			echo json_encode($return_object);
			exit;
		}
		if (UAP_DEMO_MODE) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = '<strong>Demo mode.</strong> This operation is disabled.';
			echo json_encode($return_object);
			exit;
		}
		foreach ($options as $key => $value) {
			if (isset($_POST[$key])) {
				$options[$key] = trim(stripslashes($_POST[$key]));
			}
		}
		$message = 'This is test message. It was sent by '.UAP_TITLE.' ('.$options['url'].') using the following mailing parameters:<br />';
		if ($options['mail_method'] == 'smtp') {
			$message .= 'Method: SMTP<br />Sender Name: '.$options['smtp_from_name'].'<br />Encryption: '.$options['smtp_secure'].'<br />Server: '.$options['smtp_server'].'<br />Port: '.$options['smtp_port'].'<br />Username: '.$options['smtp_username'].'<br />Password: '.$options['smtp_password'];
		} else {
			$message .= 'Method: PHP Mail() function<br />Sender Name: '.$options['mail_from_name'].'<br />Sender E-mail: '.$options['mail_from_name'];
		}
		
		$result = wp_mail($options['login'], 'Test Message', $message, '', array(), true);
		if ($result !== true) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = $result;
			echo json_encode($return_object);
			exit;
		}
		
		$return_object = array();
		$return_object['status'] = 'OK';
		$return_object['message'] = 'Test message successfully sent. Please check your inbox ('.$options['login'].').';
		echo json_encode($return_object);
		exit;
		break;
		
	case 'upload-plugin':
		if (!current_user_can('manage_options')) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = 'Sorry, you don\'t have permissions to perform this operation. Please login as administrator.';
			echo '<html><body>'.json_encode($return_object).'</body></html>';
			exit;
		}
		if (UAP_DEMO_MODE) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = '<strong>Demo mode.</strong> This operation is disabled.';
			echo '<html><body>'.json_encode($return_object).'</body></html>';
			exit;
		}
		if (empty($_FILES["upload-plugin"]["tmp_name"])) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = 'File was not uploaded properly. Please check its size. Probably it\'s too large.';
			echo '<html><body>'.json_encode($return_object).'</body></html>';
			exit;
		}
		if (!class_exists('ZipArchive')) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = 'This operation <strong>requires</strong> <em>ZipArchive</em> class. It is <strong>not found</strong>.';
			echo '<html><body>'.json_encode($return_object).'</body></html>';
			exit;
		}
		$zip = new ZipArchive();
		if($zip->open($_FILES["upload-plugin"]["tmp_name"]) !== true) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = 'Can\'t open uploaded file.';
			echo '<html><body>'.json_encode($return_object).'</body></html>';
			exit;
		}
		if (($directory = $zip->getNameIndex($i)) === false) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = 'Uploaded zip-archive seems to be empty.';
			echo '<html><body>'.json_encode($return_object).'</body></html>';
			exit;
		}
		$directory = rtrim($directory, '/');
		if ($zip->locateName($directory.'/uap.txt') === false) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = 'Uploaded zip-archive is not compatible plugin.';
			echo '<html><body>'.json_encode($return_object).'</body></html>';
			exit;
		}
		if (($info_encoded = $zip->getFromName($directory.'/uap.txt')) === false) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = 'Can\'t read plugin\'s info.';
			echo '<html><body>'.json_encode($return_object).'</body></html>';
			exit;
		}
		$info = json_decode($info_encoded, true);
		if (!$info) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = 'Can\'t read plugin\'s info.';
			echo '<html><body>'.json_encode($return_object).'</body></html>';
			exit;
		}
		if (!array_key_exists('slug', $info) || !array_key_exists('uap', $info) || !array_key_exists('version', $info) || !array_key_exists('file', $info) || $zip->locateName($directory.'/'.$info['file']) === false) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = 'Invalid plugin\'s info.';
			echo '<html><body>'.json_encode($return_object).'</body></html>';
			exit;
		}
		$plugin_details = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."plugins WHERE slug = '".esc_sql($info['slug'])."'");
		if ($plugin_details || file_exists(dirname(__FILE__).'/content/plugins/'.$directory)) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = 'Plugin already installed.';
			echo '<html><body>'.json_encode($return_object).'</body></html>';
			exit;
		}
		if ($zip->extractTo(dirname(__FILE__).'/content/plugins/') === false) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = 'Can\'t extract plugin from zip-archive.';
			echo '<html><body>'.json_encode($return_object).'</body></html>';
			exit;
		}
		$return_object = array();
		$return_object['status'] = 'OK';
		$return_object['message'] = 'Plugin successfully installed!';
		$_SESSION['message'] = '<div id="upload-message"><div class="global-message global-message-success">Plugin successfully installed!</div></div>';
		$return_object['url'] = admin_url('admin.php');
		echo '<html><body>'.json_encode($return_object).'</body></html>';
		exit;
		break;

	case 'toggle-plugin':
		if (!current_user_can('manage_options')) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = 'Sorry, you don\'t have permissions to perform this operation. Please login as administrator.';
			echo json_encode($return_object);
			exit;
		}
		if (UAP_DEMO_MODE) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = '<strong>Demo mode.</strong> This operation is disabled.';
			echo json_encode($return_object);
			exit;
		}
		$slug = trim(stripslashes($_POST['slug']));
		$type = trim(stripslashes($_POST['type']));
		$plugin_details = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."plugins WHERE slug = '".esc_sql($slug)."'", ARRAY_A);
		if (!$plugin_details || !file_exists(dirname(__FILE__).'/content/plugins/'.$plugin_details['file'])) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = 'Plugin not found.';
			echo json_encode($return_object);
			exit;
		}
		if ($plugin_details['uap'] > UAP_CORE) {
			$return_object = array();
			$return_object['status'] = 'ERROR';
			$return_object['message'] = 'Plugin requires new version of Admin Panel. Please update it.';
			echo json_encode($return_object);
			exit;
		}
		$return_object = array();
		$return_object['status'] = 'OK';
		if ($type == 'activate') {
			$wpdb->query("UPDATE ".$wpdb->prefix."plugins SET active = '1' WHERE slug = '".esc_sql($slug)."'");
			$message = 'Plugin successfully activated!';
		} else {
			$wpdb->query("UPDATE ".$wpdb->prefix."plugins SET active = '0' WHERE slug = '".esc_sql($slug)."'");
			$message = 'Plugin successfully deactivated!';
		}
		$return_object['message'] = $message;
		$_SESSION['message'] = '<div id="upload-message"><div class="global-message global-message-success">'.$message.'</div></div>';
		$return_object['url'] = admin_url('admin.php');
		echo json_encode($return_object);
		break;
		
	default:
		if ($is_logged) {
			if (array_key_exists('wp_ajax_'.$_REQUEST['action'], $wp_filters)) do_action('wp_ajax_'.$_REQUEST['action']);
		} else {
			if (array_key_exists('wp_ajax_nopriv_'.$_REQUEST['action'], $wp_filters)) do_action('wp_ajax_nopriv_'.$_REQUEST['action']);
		}
		echo '0';
		break;
}
?>