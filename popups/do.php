<?php
include_once(dirname(__FILE__).'/inc/common.php');
include_once(dirname(__FILE__).'/inc/icdb.php');
include_once(dirname(__FILE__).'/inc/functions.php');

define('DOING_FRONT', true);
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
include_once(dirname(__FILE__).'/inc/plugins.php');

do_action('init');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Halfdata Admin Panel</title>
	<style>
		body {font-family: arial; font-size: 14px; color: #444;}
		.container {position: absolute; top: 0; right: 0; bottom: 0; left: 0; min-width: 240px; height: 100%; display: table; width: 100%;}
		.content {max-width: 1024px; margin: 0px auto; padding: 20px 0; position: relative; display: table-cell; text-align: center; vertical-align: middle;}
		.content-box {border: 1px solid #e0e0e0; padding: 20px 50px; max-width: 720px; display: inline-block;}
		h1, p {font-weight: 400; padding: 0; margin: 5px 0;}
	</style>
<body>
	<div class="container">
		<div class="content">
			<div class="content-box">
				<h1>Halfdata Admin Panel</h1>
				<p>Use popular WordPress plugins anywhere!</p>
			</div>
		</div>
	</div>
</body>
</html>