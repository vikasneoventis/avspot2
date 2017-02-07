<?php
if (!defined('UAP_CORE')) die('What are you doing here?');
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<!-- Meta, title, CSS, favicons, etc. -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>Login Admin Panel</title>
		<link href="//fonts.googleapis.com/css?family=Open+Sans:400,300&subset=latin,cyrillic-ext,greek-ext,latin-ext,cyrillic,greek,vietnamese" rel="stylesheet" type="text/css">
		<link href="css/font-awesome.min.css" rel="stylesheet">
		<link href="css/login.css" rel="stylesheet">
		<script src="js/jquery.min.js"></script>
		<script src="js/login.js"></script>
		<script>var login_handler = "<?php echo admin_url('login.php'); ?>";</script>
	</head>

	<body>
		<div class="front-container">
			<div class="front-content">
				<div class="front-box">
					<div id="content">
