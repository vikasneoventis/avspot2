<?php
include_once(dirname(__FILE__).'/inc/common.php');
include_once(dirname(__FILE__).'/inc/icdb.php');
include_once(dirname(__FILE__).'/inc/functions.php');

$url_base = ((empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] == 'off') ? 'http://' : 'https://').$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
$filename = basename(__FILE__);
if (($pos = strpos($url_base, $filename)) !== false) $url_base = substr($url_base, 0, $pos);
$url_base = rtrim($url_base, '/').'/';

$actions = array('start', 'connect-db', 'save-config', 'create-admin');
$wpdb = null;
$db_ready = false;
$admin_created = false;
$tables_created = false;
if (file_exists(dirname(__FILE__).'/inc/config.php')) {
	include_once(dirname(__FILE__).'/inc/config.php');
	try {
		$wpdb = new ICDB(UAP_DB_HOST, UAP_DB_HOST_PORT, UAP_DB_NAME, UAP_DB_USER, UAP_DB_PASSWORD, UAP_TABLE_PREFIX);
		$db_ready = true;
		create_tables();
		$tables_created = true;
		get_options();
		if (!empty($options['login']) && !empty($options['password']) && !empty($options['url'])) $admin_created = true;
	} catch (Exception $e) {
		//die($e->getMessage());
	}
}
if ($db_ready && $tables_created && $admin_created) {
	if (isset($_POST['action'])) {
		$step = 5;
	} else {
		header('Location: '.$url_base);
		exit;
	}
} else if (!isset($_POST['action']) || !in_array($_POST['action'], $actions)) {
	include_once(dirname(__FILE__).'/inc/installer_header.php');
?>
	<h1>Admin Panel Setup</h1>
	<div class="row">
		Hi, I'm Wizard. I gonna help you to setup Admin Panel. You just need perform several simple steps. Let's start?
	</div>
	<div class="row"></div>
	<div class="row right">
		<input type="hidden" name="action" value="start" />
		<a id="continue" class="button" href="#" onclick="return continue_handler();"><i class="fa fa-angle-double-right"></i> Continue</a>
	</div>
<?php
	include_once(dirname(__FILE__).'/inc/installer_footer.php');
	exit;
} else {
	switch ($_POST['action']) {
		case 'start':
			if ($admin_created) $step = 5;
			else if ($tables_created) $step = 4;
			else $step = 2;
			break;
		
		case 'connect-db':
			if ($admin_created) $step = 5;
			else if ($tables_created) $step = 4;
			else {
				$host = trim(stripslashes($_POST['hostname']));
				$port = trim(stripslashes($_POST['port']));
				$username = trim(stripslashes($_POST['username']));
				$password = trim(stripslashes($_POST['password']));
				$database = trim(stripslashes($_POST['database']));
				$prefix = trim(stripslashes($_POST['prefix']));
				$errors = array();
				if (empty($host) || !is_hostname($host)) $errors[] = 'Inavlid MySQL Hostname.';
				if (!empty($port) && $port != preg_replace('/[^0-9]/', '', $port)) $errors[] = 'Port value must be a number.';
				if (empty($username)) $errors[] = 'Username can\'t be empty.';
				else if (strpos($username, "'") !== false) $errors[] = 'Username can\'t contain single quote symbol.';
				if (empty($database)) $errors[] = 'Invalid Database name.';
				else if (strpos($database, "'") !== false) $errors[] = 'Database can\'t contain single quote symbol.';
				if (strpos($password, "'") !== false) $errors[] = 'Password can\'t contain single quote symbol.';
				if (!preg_match('/^[a-zA-Z]+[a-zA-Z_]+$/', $prefix)) $errors[] = 'Table Prefix must contain letters and/or underscore symbol.';
				if (!empty($errors)) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = implode('<br />', $errors);
					echo json_encode($return_object);
					exit;
				}
				try {
					$wpdb = new ICDB($host, $port, $database, $username, $password, $prefix);
				} catch (Exception $e) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = 'Can\'t connect to MySQL database using provided credentials.';
					echo json_encode($return_object);
					exit;
				}
				try {
					create_tables();
				} catch (Exception $e) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = 'Can\'t create database tables. Make sure that user <strong>'.htmlspecialchars($username, ENT_QUOTES).'</strong> has sufficient privileges to manipulate database.';
					echo json_encode($return_object);
					exit;
				}
				$config_content = "<?php
define('UAP_DB_HOST', '".$host."');
define('UAP_DB_HOST_PORT', '".$port."');
define('UAP_DB_USER', '".$username."');
define('UAP_DB_PASSWORD', '".$password."');
define('UAP_DB_NAME', '".$database."');
define('UAP_TABLE_PREFIX', '".$prefix."');
?>";
				$result = file_put_contents(dirname(__FILE__).'/inc/config.php', $config_content);
				if ($result !== false) {
					$login = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."options WHERE options_key = 'login'");
					$password = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."options WHERE options_key = 'password'");
					if (!empty($login) && !empty($password)) $step = 5;
					else $step = 4;
				} else $step = 3;
			}
			break;

		case 'save-config':
			if ($admin_created) $step = 5;
			else if ($tables_created) $step = 4;
			else {
				$return_object = array();
				$return_object['status'] = 'ERROR';
				$return_object['message'] = 'Hm. Seems config.php still doesn\'t contain correct database credentials. Please update it as it\'s said above.';
				echo json_encode($return_object);
				exit;
			}
			break;

		case 'create-admin':
			if ($admin_created) $step = 5;
			else if (!$tables_created || !$db_ready) {
				$return_object = array();
				$return_object['status'] = 'ERROR';
				$return_object['message'] = 'Something went wrong. We still can\'t connect to database. Please try setup procedure again. Just refresh the page.';
				echo json_encode($return_object);
				exit;
			} else {
				$email = strtolower(trim(stripslashes($_POST['email'])));
				$password = trim(stripslashes($_POST['password']));
				$errors = array();
				if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,20})$/i", $email)) $errors[] = 'Invalid e-mail format.';
				if (strlen($password) < 6) $errors[] = 'Password length must be at least 6 characters.';
				if (!empty($errors)) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = implode('<br />', $errors);
					echo json_encode($return_object);
					exit;
				}
				try {
					$row = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."options WHERE options_key = 'login'");
					if ($row) {
						$wpdb->query("UPDATE ".$wpdb->prefix."options SET options_value = '".$wpdb->escape_string($email)."' WHERE options_key = 'login'");
					} else {
						$wpdb->query("INSERT INTO ".$wpdb->prefix."options (options_key, options_value) VALUES ('login', '".$wpdb->escape_string($email)."')");
					}
					$row = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."options WHERE options_key = 'password'");
					if ($row) {
						$wpdb->query("UPDATE ".$wpdb->prefix."options SET options_value = '".$wpdb->escape_string($password)."' WHERE options_key = 'password'");
					} else {
						$wpdb->query("INSERT INTO ".$wpdb->prefix."options (options_key, options_value) VALUES ('password', '".$wpdb->escape_string(md5($password))."')");
					}
					$row = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."options WHERE options_key = 'url'");
					if ($row) {
						$wpdb->query("UPDATE ".$wpdb->prefix."options SET options_value = '".$wpdb->escape_string($url_base)."' WHERE options_key = 'url'");
					} else {
						$wpdb->query("INSERT INTO ".$wpdb->prefix."options (options_key, options_value) VALUES ('url', '".$wpdb->escape_string($url_base)."')");
					}
					$step = 5;
				} catch (Exception $e) {
					$return_object = array();
					$return_object['status'] = 'ERROR';
					$return_object['message'] = 'Can\'t insert record into table. Make sure that user <strong>'.htmlspecialchars(UAP_DB_USERNAME, ENT_QUOTES).'</strong> has sufficient privileges to manipulate database.';
					echo json_encode($return_object);
					exit;
				}
			}
			break;
			
		default:
			echo 'We don\'t have to be here. Never.';
			exit;
	}
}
	$return_object = array();
	$return_object['status'] = 'OK';
	$return_object['html'] = 'We don\'t have to see this message. Never.';
if ($step == 2) {
	$return_object['html'] = '
	<h1>Setup Database</h1>
	<div class="row">
		<label class="cell" for="hostname">MySQL Hostname:</label>
		<div class="cell">
			<input type="text" name="hostname" value="localhost" placeholder="localhost" />
			<span>Enter MySQL server hostname. Usually it\'s <strong>localhost</strong>, but we recommend to clarify this parameter from your hosting provider.</span>
		</div>
	</div>
	<div class="row">
		<label class="cell" for="port">Port:</label>
		<div class="cell">
			<input type="text" name="port" value="" placeholder="3306" />
			<span>Enter MySQL server port. Leave it empty if you don\'t know the port.</span>
		</div>
	</div>
	<div class="row">
		<label class="cell" for="username">Username:</label>
		<div class="cell">
			<input type="text" name="username" value="" placeholder="Username" />
			<span>Enter MySQL server username. Find it in your hosting control panel.</span>
		</div>
	</div>
	<div class="row">
		<label class="cell" for="password">Password:</label>
		<div class="cell">
			<input type="text" name="password" value="" placeholder="Password" />
			<span>Enter password for MySQL server user. Find it in your hosting control panel.</span>
		</div>
	</div>
	<div class="row">
		<label class="cell" for="database">Database:</label>
		<div class="cell">
			<input type="text" name="database" value="" placeholder="Database" />
			<span>Enter MySQL database name. Find it in your hosting control panel.</span>
		</div>
	</div>
	<div class="row">
		<label class="cell" for="prefix">Table Prefix:</label>
		<div class="cell">
			<input type="text" name="prefix" value="uap_" placeholder="Table prefix" />
			<span>Enter prefix for MySQL tables. If you plan to have several installations of admin panel, use unique prefix for each installation.</span>
		</div>
	</div>
	<div class="row right">
		<input type="hidden" name="action" value="connect-db" />
		<a id="continue" class="button" href="#" onclick="return continue_handler();"><i class="fa fa-angle-double-right"></i> Continue</a>
	</div>';
} else if ($step == 3) {
	$return_object['html'] = '
	<h1>Save Config File</h1>
	<div class="row">
		Unfortunately, we couldn\'t save database credentials into config.php (due to file permissions). You have to do it manually. Please edit file
		<br /><strong>'.dirname(__FILE__).'/inc/config.php'.'</strong>
		<br />and overwrite its content with the following code.
		<textarea readonly="readonly" onclick="this.focus();this.select();">'.htmlspecialchars($config_content, ENT_QUOTES).'</textarea>
	</div>
	<div class="row right">
		<input type="hidden" name="action" value="save-config" />
		<a id="continue" class="button" href="#" onclick="return continue_handler();"><i class="fa fa-angle-double-right"></i> Continue</a>
	</div>';
} else if ($step == 4) {
	$return_object['html'] = '
	<h1>Create Admin Account</h1>
	<div class="row">
		<label class="cell" for="email">E-mail:</label>
		<div class="cell">
			<input type="text" name="email" placeholder="admin@website.com" />
			<span>E-mail address is your login to enter Admin Panel.</span>
		</div>
	</div>
	<div class="row">
		<label class="cell" for="password">Password:</label>
		<div class="cell">
			<input type="text" name="password" placeholder="Password" />
			<span>Use this password to enter Admin Panel.</span>
		</div>
	</div>
	<div class="row right">
		<input type="hidden" name="action" value="create-admin" />
		<a id="continue" class="button" href="#" onclick="return continue_handler();"><i class="fa fa-angle-double-right"></i> Continue</a>
	</div>';
} else if ($step == 5) {
	$return_object['html'] = '
	<h1>Finished</h1>
	<div class="row">
		Congratulation! Installation successfully completed. Now you can enter Admin Panel using created login/password and work there. Good luck!
	</div>
	<div class="row"></div>
	<div class="row right">
		<a id="continue" class="button" href="'.$url_base.'"><i class="fa fa-angle-double-right"></i> Finish</a>
	</div>';

}
echo json_encode($return_object);
exit;

?>