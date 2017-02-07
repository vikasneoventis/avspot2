<?php
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
	if (isset($_POST['action'])) {
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
if ($is_logged === true) {
	if (isset($_GET['logout'])) {
		if (!empty($session_id)) {
			$wpdb->query("UPDATE ".$wpdb->prefix."sessions SET valid_period = '0' WHERE session_id = '".$session_id."'");
		}
		$is_logged = false;
	} else if (isset($_POST['action'])) {
		$return_object = array();
		$return_object['status'] = 'OK';
		$return_object['url'] = admin_url('admin.php');
		echo json_encode($return_object);
		exit;
	} else {
		header('Location: '.admin_url('admin.php'));
		exit;
	}
}
if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'login':
			if (isset($_POST['password'])) $password = trim(stripslashes($_POST['password']));
			else $password = '';
			if (isset($_POST['login'])) $login = trim(stripslashes($_POST['login']));
			else $login = '';
			$return_object = array();
			if ($login == $options['login'] && md5($password) == $options['password']) {
				$session_id = random_string(16);
				$wpdb->query("INSERT INTO ".$wpdb->prefix."sessions (ip, session_id, registered, valid_period) VALUES ('".$_SERVER['REMOTE_ADDR']."', '".$session_id."', '".time()."', '7200')");
				setcookie('uap-auth', $session_id, time()+3600*24*180);
				$_SESSION['ok'] = 'Welcome to admin panel!'.(UAP_DEMO_MODE ? ' Admin Panel operates in <strong>demo mode</strong> for security reasons.' : '');
				$return_object['status'] = 'OK';
				$return_object['url'] = admin_url('admin.php');
			} else {
				$return_object['status'] = 'ERROR';
				$return_object['message'] = 'Invalid email or password!';
			}
			echo json_encode($return_object);
			exit;
			break;
			
		case 'reset-password':
			if (isset($_POST['login'])) $login = strtolower(trim(stripslashes($_POST['login'])));
			else $login = '';
			if ($login != $options['login']) {
				$return_object = array();
				$return_object['status'] = 'ERROR';
				$return_object['message'] = 'Invalid e-mail address.';
				echo json_encode($return_object);
				exit;
			}
			$new_password = random_string(12);
			$wpdb->query("UPDATE ".$wpdb->prefix."options SET options_value = '".$wpdb->escape_string(md5($new_password))."' WHERE options_key = 'password'");
			$message = 'Hi '.$login.',<br /><br />I\'ve requested new password to access Admin Panel. Here it is:<br /><br />'.$new_password.'<br /><br />Regards,<br />Admin Panel';
			if (wp_mail($login, 'New password for Admin Panel', $message)) {
				$return_object = array();
				$return_object['status'] = 'OK';
				$return_object['html'] = 'E-mail with new password for Admin Panel has been sent successfully. Check your inbox and <a class="switch-to-login" href="#" onclick="return switch_reset();">enter Admin Panel</a>.';
				echo json_encode($return_object);
				exit;
			} else {
				$return_object = array();
				$return_object['status'] = 'ERROR';
				$return_object['message'] = 'Hm. Something went wrong. We couldn\'t send e-mail with new password.';
				echo json_encode($return_object);
				exit;
			}
			break;
			
		default:
			echo 'We don\'t have to be here. Never.';
			exit;
	}
}
	include_once(dirname(__FILE__).'/inc/login_header.php');
?>
<div class="content-box" id="login-form">
	<h1>Enter Admin Panel</h1>
	<div class="row">
		<label>E-mail:</label>
		<input class="input-field" type="email" name="login" value="" placeholder="E-mail" />
	</div>
	<div class="row">
		<label>Password:</label>
		<input class="input-field" type="password" name="password" value="" placeholder="Password" />
	</div>
	<div class="row right">
		<a class="switch-form" href="#" onclick="return switch_login();">Forgot Password?</a>
		<input type="hidden" name="action" value="login" />
		<a id="login" class="button" href="#" onclick="return login();"><i class="fa fa-angle-double-right"></i> Login</a>
	</div>
</div>
<div class="content-box" id="reset-form" style="display: none;">
	<h1>Reset Password</h1>
	<div class="row">
		<label>E-mail:</label>
		<input class="input-field" type="email" name="login" value="" placeholder="E-mail" />
	</div>
	<div class="row right">
		<a class="switch-form" href="#" onclick="return switch_reset();">I remember password!</a>
		<input type="hidden" name="action" value="reset-password" />
		<a id="reset" class="button" href="#" onclick="return reset_password();"><i class="fa fa-angle-double-right"></i> Reset</a>
	</div>
</div>
<?php
	include_once(dirname(__FILE__).'/inc/login_footer.php');

?>