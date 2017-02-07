<?php
function create_tables() {
	global $wpdb;
	$table_name = $wpdb->prefix."options";
	if($wpdb->get_var("SHOW TABLES LIKE '".$table_name."'") != $table_name) {
		$sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
			id int(11) NOT NULL AUTO_INCREMENT,
			options_key varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			options_value text COLLATE utf8_unicode_ci NOT NULL,
			UNIQUE KEY id (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$wpdb->query($sql);
	}
	$table_name = $wpdb->prefix."sessions";
	if($wpdb->get_var("SHOW TABLES LIKE '".$table_name."'") != $table_name) {
		$sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
			id int(11) NOT NULL AUTO_INCREMENT,
			ip varchar(127) COLLATE utf8_unicode_ci NOT NULL,
			session_id varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			registered int(11) NOT NULL,
			valid_period int(11) NOT NULL,
			UNIQUE KEY id (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$wpdb->query($sql);
	}
	$table_name = $wpdb->prefix."plugins";
	if($wpdb->get_var("SHOW TABLES LIKE '".$table_name."'") != $table_name) {
		$sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
			id int(11) NOT NULL AUTO_INCREMENT,
			slug varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			uap int(11) NOT NULL,
			version varchar(31) COLLATE utf8_unicode_ci NOT NULL,
			file varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			active int(11) NOT NULL,
			registered int(11) NOT NULL,
			UNIQUE KEY id (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$wpdb->query($sql);
	}
}
function get_option($_key, $_default_value = '') {
	global $wpdb;
	$option = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."options WHERE options_key = '".esc_sql($_key)."'", ARRAY_A);
	if (!$option) return $_default_value;
	if (is_serialized($option['options_value'])) return unserialize($option['options_value']);
	return $option['options_value'];
}
function get_options() {
	global $wpdb, $options;
	$rows = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."options", ARRAY_A);
	foreach ($rows as $row) {
		if (array_key_exists($row['options_key'], $options)) $options[$row['options_key']] = $row['options_value'];
	}
}
function update_option($_key, $_value) {
	global $wpdb;
	if (is_array($_value) || is_object($_value)) $value = serialize($_value);
	else $value = $_value;
	$option = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."options WHERE options_key = '".esc_sql($_key)."'");
	if ($option) {
		$wpdb->query("UPDATE ".$wpdb->prefix."options SET options_value = '".esc_sql($value)."' WHERE options_key = '".esc_sql($_key)."'");
	} else {
		$wpdb->query("INSERT INTO ".$wpdb->prefix."options (options_key, options_value) VALUES ('".esc_sql($_key)."', '".esc_sql($value)."')");
	}
}
function update_options() {
	global $wpdb, $options;
	foreach ($options as $key => $value) {
		$option = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."options WHERE options_key = '".esc_sql($key)."'");
		if ($option) {
			$wpdb->query("UPDATE ".$wpdb->prefix."options SET options_value = '".esc_sql($value)."' WHERE options_key = '".esc_sql($key)."'");
		} else {
			$wpdb->query("INSERT INTO ".$wpdb->prefix."options (options_key, options_value) VALUES ('".esc_sql($key)."', '".esc_sql($value)."')");
		}
	}
}
function is_hostname($_hostname) {
	return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $_hostname)
		&& preg_match("/^.{1,253}$/", $_hostname)
		&& preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $_hostname));
}
function admin_url($_path) {
	global $options;
	$path = ltrim($_path, '/');
	if (strpos($path, 'admin.php') === 0) $path = substr($path, strlen('admin.php'));
	$path = str_replace(array('admin-ajax.php'), array('ajax.php'), $path);
	return $options['url'].$path;
}
function random_string($_length = 16) {
	$symbols = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$string = "";
	for ($i=0; $i<$_length; $i++) {
		$string .= $symbols[rand(0, strlen($symbols)-1)];
	}
	return $string;
}
function wp_mail($_to, $_subject, $_message, $_headers = '', $_attachments = array(), $_debug = false) {
	global $phpmailer, $options;
	if (!($phpmailer instanceof PHPMailer)) {
		include_once dirname(__FILE__).'/phpmailer/PHPMailerAutoload.php';
		$phpmailer = new PHPMailer;
	}
	if (empty($_headers)) {
		$headers = array();
		$charset = 'utf-8';
		$content_type = 'text/html';
		if ($options['mail_method'] == 'mail') {
			$from_email = $options['mail_from_email'];
			$from_name = $options['mail_from_name'];
		} else if ($options['mail_method'] == 'smtp') {
			$from_email = $options['smtp_username'];
			$from_name = $options['smtp_from_name'];
		}
	} else {
		if (!is_array($_headers)) {
			$tempheaders = explode("\n", str_replace("\r\n", "\n", $_headers));
		} else {
			$tempheaders = $_headers;
		}
		$headers = array();
		$cc = array();
		$bcc = array();
		if (!empty($tempheaders)) {
			foreach ((array)$tempheaders as $header) {
				if (strpos($header, ':') === false) {
					if (false !== stripos( $header, 'boundary=' )) {
						$parts = preg_split('/boundary=/i', trim( $header ) );
						$boundary = trim( str_replace(array( "'", '"' ), '', $parts[1]));
					}
					continue;
				}
				list($name, $content) = explode(':', trim( $header ), 2);
				$name = trim($name);
				$content = trim($content);
				switch (strtolower($name)) {
					case 'from':
						$bracket_pos = strpos($content, '<');
						if ($bracket_pos !== false) {
							if ($bracket_pos > 0) {
								$from_name = substr($content, 0, $bracket_pos - 1);
								$from_name = str_replace('"', '', $from_name);
								$from_name = trim($from_name);
							}
							$from_email = substr($content, $bracket_pos + 1);
							$from_email = str_replace('>', '', $from_email);
							$from_email = trim($from_email);
						} else if ('' !== trim($content)) {
							$from_email = trim($content);
						}
						break;
					case 'content-type':
						if (strpos( $content, ';' ) !== false) {
							list($type, $charset_content) = explode(';', $content);
							$content_type = trim( $type );
							if (false !== stripos( $charset_content, 'charset=')) {
								$charset = trim(str_replace(array('charset=', '"'), '', $charset_content));
							} else if (false !== stripos( $charset_content, 'boundary=')) {
								$boundary = trim(str_replace(array('BOUNDARY=', 'boundary=', '"'), '', $charset_content));
								$charset = '';
							}
						} elseif ('' !== trim($content)) {
							$content_type = trim($content);
						}
						break;
					case 'cc':
						$cc = array_merge((array)$cc, explode(',', $content));
						break;
					case 'bcc':
						$bcc = array_merge((array)$bcc, explode(',', $content));
						break;
					default:
						$headers[trim($name)] = trim( $content );
						break;
				}
			}
		}
	}

	$phpmailer->ClearAllRecipients();
	$phpmailer->ClearAttachments();
	$phpmailer->ClearCustomHeaders();
	$phpmailer->ClearReplyTos();

	if (!isset($from_name)) {
		if ($options['mail_method'] == 'mail') $from_name = $options['mail_from_name'];
		else if ($options['mail_method'] == 'smtp') $from_name = $options['smtp_from_name'];
		else $from_name = 'Admin Panel';
	}

	if (!isset($from_email)) {
		if ($options['mail_method'] == 'mail') $from_email = $options['mail_from_email'];
		else if ($options['mail_method'] == 'smtp') $from_email = $options['smtp_username'];
		else $from_email = 'noreply@'.str_replace('www.', "", $_SERVER["SERVER_NAME"]);
	}
	
	$phpmailer->From = $from_email;
	$phpmailer->FromName = $from_name;
	
	if (!is_array($_to)) $to = explode(',', $_to);
	else $to = $_to;

	foreach ((array)$to as $recipient) {
		try {
			$recipient_name = '';
			if (preg_match( '/(.*)<(.+)>/', $recipient, $matches)) {
				if (count($matches) == 3) {
					$recipient_name = $matches[1];
					$recipient = $matches[2];
				}
			}
			$phpmailer->AddAddress($recipient, $recipient_name);
		} catch (phpmailerException $e) {
			continue;
		}
	}
	
	$phpmailer->Subject = $_subject;
	$phpmailer->Body    = $_message;

	if (!empty($cc)) {
		foreach ((array)$cc as $recipient) {
			try {
				$recipient_name = '';
				if (preg_match( '/(.*)<(.+)>/', $recipient, $matches)) {
					if (count( $matches ) == 3) {
						$recipient_name = $matches[1];
						$recipient = $matches[2];
					}
				}
				$phpmailer->AddCc($recipient, $recipient_name);
			} catch (phpmailerException $e) {
				continue;
			}
		}
	}

	if (!empty($bcc)) {
		foreach ((array)$bcc as $recipient) {
			try {
				$recipient_name = '';
				if (preg_match( '/(.*)<(.+)>/', $recipient, $matches)) {
					if (count( $matches ) == 3) {
						$recipient_name = $matches[1];
						$recipient = $matches[2];
					}
				}
				$phpmailer->AddBcc($recipient, $recipient_name);
			} catch (phpmailerException $e) {
				continue;
			}
		}
	}

	if ($options['mail_method'] == 'smtp') {
		$phpmailer->IsSMTP();
		$phpmailer->IsHTML(true);
		$phpmailer->Timeout = 60;
		if ($_debug) {
			$phpmailer->SMTPDebug = 2;
			$phpmailer->Debugoutput = 'html';
		} else $phpmailer->SMTPDebug = 0;
		$phpmailer->Host       = $options['smtp_server'];
		$phpmailer->Port       = $options['smtp_port'];
		if ($options['smtp_secure'] != 'none') {
			$phpmailer->SMTPSecure = $options['smtp_secure'];
		}
		$phpmailer->SMTPAuth   = true;
		$phpmailer->Username   = $options['smtp_username'];
		$phpmailer->Password   = $options['smtp_password'];
	} else {
		$phpmailer->IsMail();
	}

	if (!isset($content_type)) $content_type = 'text/html';
	$phpmailer->ContentType = $content_type;
	if ('text/html' == $content_type) $phpmailer->IsHTML(true);
	if (!isset($charset)) $charset = 'utf-8';
	$phpmailer->CharSet = $charset;

	if (!empty($headers)) {
		foreach ((array)$headers as $name => $content) {
			$phpmailer->AddCustomHeader(sprintf('%1$s: %2$s', $name, $content));
		}
		if (false !== stripos($content_type, 'multipart') && ! empty($boundary))
			$phpmailer->AddCustomHeader(sprintf("Content-Type: %s;\n\t boundary=\"%s\"", $content_type, $boundary));
	}
	if (!empty($attachments)) {
		foreach ($attachments as $attachment) {
			try {
				$phpmailer->AddAttachment($attachment);
			} catch (phpmailerException $e) {
				continue;
			}
		}
	}
	try {
		if ($_debug && $options['mail_method'] == 'smtp') {
			ob_start();
		}
		$result = $phpmailer->Send();
		if ($_debug && $options['mail_method'] == 'smtp') {
			$errors_html = ob_get_clean();
			if ($result !== true) return $errors_html;
		}
		return $result;
	} catch (Exception $e) {
		if ($_debug && $options['mail_method'] == 'smtp') {
			$errors_html = ob_get_clean();
			if (!empty($errors_html)) return $errors_html;
		}
		return false;
	}
}
function current_user_can($_permissions) {
	global $is_logged;
	if ($is_logged === true) return true;
	return false;
}
function esc_html($_text) {
	if (is_array($_text)) print_r($_text);
	return htmlspecialchars($_text, ENT_QUOTES);
}
function esc_sql($_text) {
	global $wpdb;
	return $wpdb->escape_string($_text);
}
function register_activation_hook($_plugin_file, $_method) {
	global $wpdb;
	if (current_user_can('manage_options')) {
		// $file = basename(dirname($_plugin_file)).'/'.basename($_plugin_file);
		if (file_exists(dirname($_plugin_file).'/uap.txt')) {
			$info = json_decode(file_get_contents(dirname($_plugin_file).'/uap.txt'), true);
			if (is_array($info) && !empty($info)) {
				$plugin_details = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."plugins WHERE slug = '".esc_sql($info['slug'])."'", ARRAY_A);
				if ($plugin_details) {
					if ($info['version'] != $plugin_details['version']) {
						call_user_func($_method);
						//$wpdb->query("UPDATE ".$wpdb->prefix."plugins SET version = '".esc_sql($info['version'])."' WHERE slug = '".esc_sql($info['slug'])."'");
					}
				}
			}
		}
	}
}
function register_deactivation_hook($_plugin_file, $_method) {

}
function wp_upload_dir($_key = null) {
	global $options;
	$dir = array(
		'basedir' => dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.'data',
		'baseurl' => $options['url'].'content/data'
	);
	if (empty($_key) || !array_key_exists($_key, $dir)) return $dir;
	return $dir[$_key];
}
function wp_mkdir_p($_target) {
	if (file_exists($_target)) return is_dir($_target);
	return mkdir($_target, 0777, true);
}
function plugins_url($_path = '', $_plugin = '') {
	global $options;
	$i = 0;
	if (empty($_plugin)) return $options['url'].'content/plugins';
	$directory = dirname($_plugin);
	$i++;
	while (!file_exists($directory.'/uap.txt') && $i < 4) {
		$directory = dirname($directory);
		$i++;
	}
	if ($i == 4) return $options['url'].'content/plugins';
	return $options['url'].'content/plugins/'.basename($directory).$_path;
}
function is_home() {
	return false;
}
function get_bloginfo($_key = null) {
	global $options;
	$bloginfo = array(
		'name' => 'Halfdata Panel',
		'description' => 'Admin Panel that allows to use some WordPress plugins (created by Halfdata Team) with non-WordPress sites.',
		'wpurl' => $options['url'],
		'url' => $options['url'],
		'admin_email' => $options['login'],
		'charset' => 'UTF-8',
		'version' => 'UAP-'.UAP_CORE,
		'html_type' => 'text/html',
		'text_direction' => 'ltr',
		'language' => 'en_US',
		'stylesheet_url' => '',
		'stylesheet_directory' => '',
		'template_url' => '',
		'template_directory' => '',
		'pingback_url' => '',
		'atom_url' => '',
		'rdf_url' => '',
		'rss_url' => '',
		'rss2_url' => '',
		'comments_atom_url' => '',
		'comments_rss2_url' => '',
		'siteurl' => $options['url'],
		'home' => $options['url']
	);
	if ($_key && array_key_exists($_key, $bloginfo)) return $bloginfo[$_key];
}
function is_admin() {
	if (defined('DOING_FRONT')) return false;
	return true;
}
function do_shortcode($_content) {
	return $_content;
}
function add_filter($_tag, $_function_to_add, $_priority = 10, $_accepted_args = 1) {
	global $wp_filters;
	$wp_filters[$_tag][$_priority][] = array('function' => $_function_to_add, 'accepted_args' => $_accepted_args);
	return true;
}
function apply_filters($_tag, $_value) {
	global $wp_filters;
	if (!array_key_exists($_tag, $wp_filters)) return $_value;
	
	$args = array();
	$args = func_get_args();
	ksort($wp_filters[$_tag]);
	reset($wp_filters[$_tag]);
	do {
		foreach ((array)current($wp_filters[$_tag]) as $the_)
			if (!is_null($the_['function']) ){
				$args[1] = $_value;
				$_value = call_user_func_array($the_['function'], array_slice($args, 1, (int)$the_['accepted_args']));
			}

	} while (next($wp_filters[$_tag]) !== false );
	return $_value;
}
function add_action($_tag, $_function_to_add, $_priority = 10, $_accepted_args = 1) {
	return add_filter($_tag, $_function_to_add, $_priority, $_accepted_args);
}
function do_action($_tag, $_arg = '') {
	global $wp_filters;

	if (!array_key_exists($_tag, $wp_filters)) return;
	$args = array();
	if (is_array($_arg) && 1 == count($_arg) && isset($_arg[0]) && is_object($_arg[0])) $args[] =& $_arg[0];
	else $args[] = $_arg;
	for ($a=2, $num=func_num_args(); $a<$num; $a++) {
		$args[] = func_get_arg($a);
	}
	ksort($wp_filters[$_tag]);
	
	reset($wp_filters[$_tag]);
	do {
		foreach ((array)current($wp_filters[$_tag]) as $the_ )
			if (!is_null($the_['function']))
				call_user_func_array($the_['function'], array_slice($args, 0, (int)$the_['accepted_args']));

	} while (next($wp_filters[$_tag]) !== false);
}
function add_shortcode($_var1, $_var2) {
	return;
}
function __($_text, $_textdomain) {
	return $_text;
}
function _e($_text, $_textdomain) {
	echo $_text;
}
function wp_enqueue_script($_slug, $_url = null, $_deps = array(), $_ver = UAP_CORE) {
	global $scripts;
	switch (strtolower($_slug)) {
		case 'jquery':
			break;
			
		default:
			if (!empty($_url)) {
				if (strpos($_url, '?') === false) $_url .= '?ver='.$_ver;
				else  $_url .= '&ver='.$_ver;
				$scripts[$_slug] = array(
					'url' => $_url,
					'deps' => $_deps
				);
			}
			break;
	}
}
function wp_enqueue_style($_slug, $_url = null, $_deps = array(), $_ver = UAP_CORE) {
	global $styles;
	switch (strtolower($_slug)) {
		case 'jquery':
			break;
			
		default:
			if (!empty($_url)) {
				if (strpos($_url, '?') === false) $_url .= '?ver='.$_ver;
				else  $_url .= '&ver='.$_ver;
				$styles[$_slug] = array(
					'url' => $_url,
					'deps' => $_deps
				);
			}
			break;
	}
}
function wp_enqueue_media() {

}
function add_meta_box() {}
function get_post_types() {return array();}
function add_menu_page($_page_title, $_menu_title, $_capability, $_menu_slug, $_function = '', $_icon = 'fa-cog', $_position = null) {
	global $menu;
	$menu[$_menu_slug] = array(
		'page-title' => $_page_title,
		'menu-title' => $_menu_title,
		'function' => $_function,
		'icon' => $_icon
	);
}
function add_submenu_page($_parent_slug, $_page_title, $_menu_title, $_capability, $_menu_slug, $_function = '') {
	global $menu;
	if (array_key_exists($_parent_slug, $menu)) {
		$menu[$_parent_slug]['submenu'][$_menu_slug] = array(
			'page-title' => $_page_title,
			'menu-title' => $_menu_title,
			'function' => $_function
		);
	}
}
function trailingslashit($_string) {
	return rtrim($_string, '/').'/';
}
function download_url($_url, $_file = null) {
	set_time_limit(0);
	if (!$_file) {
		$temp_dir = wp_upload_dir('basedir').DIRECTORY_SEPARATOR.'temp';
		if (!file_exists($temp_dir)) mkdir($temp_dir, 0777, true);
		if (!is_writable($temp_dir)) return new WP_Error('', 'Can not create temp directory <strong>'.$temp_dir.'</strong> or it is non-writable.');
		$_file = $temp_dir.DIRECTORY_SEPARATOR.random_string(12);
	}
	$fp = fopen($_file, 'w+');
	if (!$fp) return new WP_Error('', 'Can not create file <strong>'.$_file.'</strong> or it is non-writable.');
	$ch = curl_init($_url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 50);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FILE, $fp); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2049.0 Safari/537.36');
	$response = curl_exec($ch);
	if (curl_error($ch)) {
		$error = new WP_Error('', curl_error($ch));
		curl_close($ch);
		return $error;
	}
	curl_close($ch);
	fclose($fp);
	return $_file;
}
function is_wp_error($_error) {
	if ($_error instanceof WP_Error) return true;
	return false;
}
function is_feed() {
	return false;
}
function is_serialized( $data, $strict = true ) {
	if (!is_string($data)) return false;
	$data = trim($data);
 	if ('N;' == $data) return true;
	if (strlen($data) < 4) return false;
	if (':' !== $data[1]) return false;
	if ($strict) {
		$lastc = substr($data, -1);
		if (';' !== $lastc && '}' !== $lastc) return false;
	} else {
		$semicolon = strpos($data, ';');
		$brace = strpos($data, '}');
		if (false === $semicolon && false === $brace) return false;
		if (false !== $semicolon && $semicolon < 3) return false;
		if (false !== $brace && $brace < 4) return false;
	}
	$token = $data[0];
	switch ($token) {
		case 's' :
			if ($strict) {
				if ('"' !== substr($data, -2, 1)) return false;
			} else if (false === strpos($data, '"')) return false;
		case 'a' :
		case 'O' :
			return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
		case 'b' :
		case 'i' :
		case 'd' :
			$end = $strict ? '$' : '';
			return (bool)preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
	}
	return false;
}
?>