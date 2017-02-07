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

	<title><?php echo $page['page-title']; ?></title>

	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/font-awesome.min.css" rel="stylesheet">
	<link href="css/jquery-ui/jquery-ui.min.css" rel="stylesheet">
	<link href="css/thickbox.css" rel="stylesheet">
	<link href="css/custom.css" rel="stylesheet">
	<link href="css/admin.css" rel="stylesheet">
<?php
	$output = array();
	do {
		$printed = false;
		foreach($styles as $slug => $style) {
			if (!in_array($slug, $output)) {
				$diff = array_diff($style['deps'], $output);
				if (empty($diff)) {
					$output[] = $slug;
					echo '
	<link id="'.$slug.'" href="'.$style['url'].'" rel="stylesheet">';
					$printed = true;
				}
			}
		}
	} while ($printed)
?>	
	
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/jquery-ui.min.js"></script>
	<script src="js/color-picker.min.js"></script>
	<script>var thickboxL10n = {"next":"Next >","prev":"< Prev","image":"Image","of":"of","close":"Close","noiframes":"This feature requires inline frames. You have iframes disabled or your browser does not support them.","loadingAnimation":"images\/loadingAnimation.gif"};</script>
	<script src="js/thickbox.js"></script>
	<script src="js/custom.js"></script>
	<script src="js/admin.js"></script>
<?php
	$output = array('jquery');
	do {
		$printed = false;
		foreach($scripts as $slug => $script) {
			if (!in_array($slug, $output)) {
				$diff = array_diff($script['deps'], $output);
				if (empty($diff)) {
					$output[] = $slug;
					echo '
	<script id="'.$slug.'" src="'.$script['url'].'"></script>';
					$printed = true;
				}
			}
		}
	} while ($printed)
?>	
	<script>var ajax_handler = "<?php echo admin_url('ajax.php'); ?>";</script>
<?php
	do_action('admin_head');
?>
</head>
<body id="uap-body" class="nav-md" style="display: none;">
	<div class="container body">
		<div class="main_container">
			<div class="col-md-3 left_col">
				<div class="left_col scroll-view">
					<div class="navbar nav_title" style="border: 0;">
						<div class="site_title"><i class="fa fa-cogs"></i> <span><?php echo get_bloginfo('name'); ?></span></div>
					</div>

					<div class="clearfix"></div>

					<!-- sidebar menu -->
					<div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
						<div class="menu_section">
							<ul class="nav side-menu">
								<li<?php echo ($page['slug'] == 'dashboard' ? ' class="active"' : ''); ?>><a href="<?php echo $options['url']; ?>"><i class="fa fa-home"></i> Dashboard</a></li>
<?php
foreach($menu as $slug => $item) {
	$icon = 'fa-cog';
	if (substr($item['icon'],0,3) == 'fa-') $icon = $item['icon'];
	echo '
								<li'.(array_key_exists('parent', $page) && $page['parent'] == $slug ? ' class="active"' : '').'><a'.(array_key_exists('submenu', $item) ? '' : ' href="'.$options['url'].'?page='.$slug.'"').'><i class="fa '.$icon.'"></i> '.htmlspecialchars($item['menu-title']).(array_key_exists('submenu', $item) ? '  <span class="fa fa-chevron-down"></span>' : '').'</a>';
	if (array_key_exists('submenu', $item)) {
		echo '
									<ul class="nav child_menu"'.(array_key_exists('parent', $page) && $page['parent'] == $slug ? ' style="display: block;"' : '').'>';
		foreach ($item['submenu'] as $submenu_slug => $submenu_item) {
			echo '
								<li'.($page['slug'] == $submenu_slug ? ' class="active current-page"' : '').'><a href="'.$options['url'].'?page='.$submenu_slug.'">'.htmlspecialchars($submenu_item['menu-title']).'</a></li>';
		}
		echo '
									</ul>';
	}
	echo '</li>';
}
?>								
							</ul>
						</div>
					</div>
					<!-- /sidebar menu -->
				</div>
			</div>

			<!-- top navigation -->
			<div class="top_nav" id="top-panel">
				<div class="nav_menu">
					<nav class="" role="navigation">
						<ul class="nav navbar-nav navbar-right">
							<li class="">
								<a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
									<img src="<?php echo 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($options['login']))).'&s=64'; ?>" alt=""><?php echo $options['login']; ?>
									<span class=" fa fa-angle-down"></span>
								</a>
								<ul class="dropdown-menu dropdown-usermenu pull-right">
									<li><a href="<?php echo $options['url'].'settings.php'; ?>">Settings</a></li>
									<!-- <li><a href="https://layeredpopups.com/documentation">Help</a></li> -->
									<li><a href="<?php echo admin_url('login.php'); ?>?logout=true"><i class="fa fa-sign-out pull-right"></i> Log Out</a>
									</li>
								</ul>
							</li>
						</ul>
					</nav>
				</div>
			</div>
			<!-- /top navigation -->
		
			<!-- page content -->
			<div class="right_col" role="main" id="content-panel">
				<div class="row">
					<div class="col-md-12">
						<div id="global-message-container" class="col-md-12 col-sm-12 col-xs-12">
							<?php echo isset($global_message) ? $global_message : ''; ?>
							<?php do_action('admin_notices'); ?>
						</div>
						<div class="clearfix"></div>