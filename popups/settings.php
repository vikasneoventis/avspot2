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
if ($is_logged === false) {
	header('Location: '.admin_url('login.php'));
	exit;
}
include_once(dirname(__FILE__).'/inc/plugins.php');
$page = array(
	'slug' => 'settings',
	'page-title' => 'General Settings'
);

do_action('init');
do_action('admin_init');

if (array_key_exists('message', $_SESSION) && !empty($_SESSION['message'])) {
	$global_message .= $_SESSION['message'];
	$_SESSION['message'] = '';
}
do_action('admin_menu');
do_action('admin_enqueue_scripts');
include_once(dirname(__FILE__).'/inc/header.php');
?>
<div id="settings-data">
	<div class="page-title">
		<div class="title_left">
			<h3>General Settings</h3>
		</div>
	</div>
	<div class="x_panel">
		<div class="x_title">
			<h2>Mailing Settings</h2>
			<div class="clearfix"></div>
		</div>
		<div class="x_content">
			<br />
			<div class="form-horizontal form-label-left">
				<div class="form-group">
					<label class="control-label col-md-3 col-sm-3 col-xs-12">Mailing Method:</label>
					<div class="col-md-6 col-sm-6 col-xs-12">
						<div id="mail_method" class="btn-group" data-toggle="buttons">
							<label class="btn btn-default<?php echo $options['mail_method'] == 'smtp' ? '' : ' active'; ?>" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
								<input id="mail_method_mail" type="radio" name="mail_method" value="mail"<?php echo $options['mail_method'] == 'smtp' ? '' : ' checked="checked"'; ?> onchange="toggle_mail_method(this);"> &nbsp; PHP Mail() Function &nbsp;
							</label>
							<label class="btn btn-default<?php echo $options['mail_method'] == 'smtp' ? ' active' : ''; ?>" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
								<input id="mail_method_smtp" type="radio" name="mail_method" value="smtp"<?php echo $options['mail_method'] == 'smtp' ? ' checked="checked"' : ''; ?> onchange="toggle_mail_method(this);"> SMTP
							</label>
						</div>
						<br /><em>All e-mail messages are sent using this mailing method.</em>
					</div>
				</div>
				<div id="mail-method-mail"<?php echo $options['mail_method'] == 'smtp' ? ' style="display: none;"' : ''; ?>>
					<div class="form-group">
						<label class="control-label col-md-3 col-sm-3 col-xs-12" for="mail_from_name">Sender Name:</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input type="text" id="mail_from_name" name="mail_from_name" value="<?php echo esc_html($options['mail_from_name']); ?>" class="form-control col-md-7 col-xs-12">
							<em>Please enter sender name. All e-mail messages are sent using this name as "FROM:" header value.</em>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3 col-sm-3 col-xs-12" for="mail_from_email">Sender E-mail:</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input type="email" id="mail_from_email" name="mail_from_email" value="<?php echo esc_html($options['mail_from_email']); ?>" class="form-control col-md-7 col-xs-12">
							<em>Please enter sender e-mail. All e-mail messages are sent using this e-mail as "FROM:" header value. It is recommended to set existing e-mail address.</em>
						</div>
					</div>
				</div>
				<div id="mail-method-smtp"<?php echo $options['mail_method'] == 'smtp' ? '' : ' style="display: none;"'; ?>>
					<div class="form-group">
						<label class="control-label col-md-3 col-sm-3 col-xs-12" for="smtp_from_name">Sender Name:</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input type="text" id="smtp_from_name" name="smtp_from_name" value="<?php echo esc_html($options['smtp_from_name']); ?>" class="form-control col-md-7 col-xs-12">
							<em>Please enter sender name. All e-mail messages are sent using this name as "FROM:" header value.</em>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3 col-sm-3 col-xs-12" for="smtp_secure">Encryption:</label>
						<div class="col-md-3 col-sm-3 col-xs-12">
							<select class="form-control" id="smtp_secure" name="smtp_secure">
<?php
			foreach ($smtp_secures as $key => $value) {
				echo '
								<option value="'.$key.'"'.($key == $options['smtp_secure'] ? ' selected="selected"' : '').'>'.esc_html($value).'</option>';
			}
?>
							</select>					
							<em>SMTP connection encryption system.</em>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3 col-sm-3 col-xs-12" for="smtp_server">SMTP server:</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input type="text" id="smtp_server" name="smtp_server" value="<?php echo esc_html($options['smtp_server']); ?>" class="form-control col-md-7 col-xs-12">
							<em>Hostname of the mail server.</em>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3 col-sm-3 col-xs-12" for="smtp_port">SMTP port number:</label>
						<div class="col-md-3 col-sm-3 col-xs-12">
							<input type="text" id="smtp_port" name="smtp_port" value="<?php echo esc_html($options['smtp_port']); ?>" class="form-control col-md-7 col-xs-12">
							<em>Hostname of the mail server.</em>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3 col-sm-3 col-xs-12" for="smtp_username">SMTP username:</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input type="text" id="smtp_username" name="smtp_username" value="<?php echo esc_html($options['smtp_username']); ?>" class="form-control col-md-7 col-xs-12">
							<em>Username to use for SMTP authentication.</em>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3 col-sm-3 col-xs-12" for="smtp_password">SMTP password:</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input type="text" id="smtp_password" name="smtp_password" value="<?php echo esc_html($options['smtp_password']); ?>" class="form-control col-md-7 col-xs-12">
							<em>Password to use for SMTP authentication.</em>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-3 col-sm-3 col-xs-12"></div>
					<div class="col-md-6 col-sm-6 col-xs-12">
						<a id="test-mailing-button" class="btn btn-primary" onclick="return test_mailing(this);"><i class="fa fa-envelope-o"></i> Test Mailing</a>
						<br /><em>Press button and check your inbox (<?php echo $options['login']; ?>). If you don't see test message, something doesn't work. Don't forget to check SPAM folder.</em>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="x_panel">
		<div class="x_title">
			<h2>Access Settings</h2>
			<div class="clearfix"></div>
		</div>
		<div class="x_content">
			<br />
			<div class="form-horizontal form-label-left">
				<div class="form-group">
					<label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">E-mail:</label>
					<div class="col-md-6 col-sm-6 col-xs-12">
						<input type="text" id="email" name="email" value="<?php echo esc_html($options['login']); ?>" class="form-control col-md-7 col-xs-12">
						<em>Your e-mail address is used as login to access Admin Panel.</em>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3 col-sm-3 col-xs-12" for="password">Password:</label>
					<div class="col-md-6 col-sm-6 col-xs-12">
						<input type="password" id="password" name="password" value="" class="form-control col-md-7 col-xs-12">
						<em>Enter your new password. Leave it empty if you don't want to change the password.</em>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3 col-sm-3 col-xs-12" for="repeat_password"></label>
					<div class="col-md-6 col-sm-6 col-xs-12">
						<input type="password" id="repeat_password" name="repeat_password" value="" class="form-control col-md-7 col-xs-12">
						<em>Repeat your new password.</em>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="ln_solid"></div>
	<div class="col-md-12 col-sm-12 col-xs-12 bottom30">
		<input type="hidden" name="action" value="save-settings" />
		<a id="save-settings-button" class="button-primary pull-right" onclick="return save_settings(this);"><i class="fa fa-check"></i> Save Settings</a>
	</div>
</div>
<?php
include_once(dirname(__FILE__).'/inc/footer.php');
?>
