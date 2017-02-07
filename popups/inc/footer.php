<?php
if (!defined('UAP_CORE')) die('What are you doing here?');
?>
					</div>
				</div>
			</div>
			<!-- /page content -->

			<!-- footer content -->
			<footer id="footer-panel">
				<div class="pull-right">
					<?php echo get_bloginfo('name').', version '.UAP_CORE.'. Copyright © 2015-'.date('Y').', Halfdata Team.'; ?>
				</div>
				<div class="clearfix"></div>
			</footer>
			<!-- /footer content -->
		</div>
	</div>
	<script>
		jQuery(".updated, .error").each(function(){
			if (jQuery(this).hasClass("error")) jQuery("#global-message-container").append("<div class='global-message global-message-danger'>"+jQuery(this).html()+"</div>");
			else jQuery("#global-message-container").append("<div class='global-message global-message-success'>"+jQuery(this).html()+"</div>");
			jQuery(this).remove();
		});
		var adjust_content_panel = function() {
			var content_height = jQuery(window).innerHeight() - jQuery("#footer-panel").innerHeight() - jQuery("#top-panel").innerHeight() - 2;
			if (content_height <= 0) content_height = 320;
			jQuery("#content-panel").css({"min-height" : content_height+"px"});
		};
		jQuery("body").show();
		adjust_content_panel();
		jQuery(window).resize(function(){adjust_content_panel();});
	</script>
</body>
</html>